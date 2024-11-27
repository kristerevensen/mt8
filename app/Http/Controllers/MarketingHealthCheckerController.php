<?php

namespace App\Http\Controllers;

use App\Models\MarketingHealthCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use DOMDocument;
use DOMXPath;
use Carbon\Carbon;
use Inertia\Inertia;

class MarketingHealthCheckerController extends Controller
{
    public function index()
    {
        return Inertia::render('MarketingHealthChecker/Index', [
            'recentChecks' => MarketingHealthCheck::latest()
                ->take(5)
                ->get()
        ]);
    }
    public function analyze(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'url' => ['required', 'url', function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('URL-en er ikke gyldig.');
                    }
                }]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Ugyldig URL format',
                    'details' => $validator->errors()->first()
                ], 422);
            }

            $client = new Client([
                'verify' => false,
                'timeout' => 15,
                'allow_redirects' => true,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; MarketingHealthBot/1.0; +http://measuretank.com)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'nb,no,en;q=0.9',
                ]
            ]);

            try {
                $response = $client->get($request->url);
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $statusCode = $e->getResponse()->getStatusCode();
                    $errorMessage = match ($statusCode) {
                        404 => 'Siden ble ikke funnet. Sjekk at URL-en er korrekt.',
                        403 => 'Ingen tilgang til siden. Sjekk at URL-en er offentlig tilgjengelig.',
                        500 => 'Det oppstod en serverfeil på nettsiden.',
                        503 => 'Nettsiden er midlertidig utilgjengelig.',
                        default => 'Kunne ikke koble til nettsiden. Status: ' . $statusCode
                    };
                } else {
                    $errorMessage = 'Kunne ikke koble til nettsiden. Sjekk at URL-en er korrekt og at siden er tilgjengelig.';
                }
                return response()->json(['error' => $errorMessage], 400);
            }

            $body = (string) $response->getBody();
            if (empty($body)) {
                return response()->json([
                    'error' => 'Nettsiden returnerte ingen innhold. Sjekk at URL-en er korrekt.'
                ], 400);
            }

            // Parse HTML med feilhåndtering
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);

            if (!@$dom->loadHTML($body, LIBXML_NOWARNING | LIBXML_NOERROR)) {
                return response()->json([
                    'error' => 'Kunne ikke analysere nettsidens HTML. Sjekk at siden er tilgjengelig og har gyldig HTML.'
                ], 400);
            }

            $xpath = new DOMXPath($dom);

            // Analyser siden
            $analysis = [
                'trust_signals' => $this->analyzeTrustSignals($xpath),
                'conversion_elements' => $this->analyzeConversionElements($xpath),
                'localization' => $this->analyzeLocalization($xpath, $body),
                'technical_seo' => $this->analyzeTechnicalSEO($xpath, $headers),
                'privacy_compliance' => $this->analyzePrivacyCompliance($xpath, $body),
                'mobile_experience' => $this->analyzeMobileExperience($xpath),
                'analytics_setup' => $this->analyzeAnalyticsSetup($body)
            ];

            Cache::put('analysis_' . md5($request->url), $analysis, now()->addHours(24));

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        } catch (\Exception $e) {
            Log::error('Marketing health check error', [
                'url' => $request->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'En feil oppstod under analysen. Vennligst prøv igjen senere.',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function analyzeTrustSignals(DOMXPath $xpath): array
    {
        $trustSignals = [
            'has_norwegian_business_info' => false,
            'has_org_number' => false,
            'has_trust_badges' => false,
            'has_customer_reviews' => false,
            'details' => []
        ];

        // Få tak i hele HTML-innholdet fra DOMDocument
        $body = $xpath->document->saveHTML();

        // Sjekk etter org.nummer (9 siffer)
        $orgNumber = preg_match('/\b(NO)?[0-9]{9}(MVA)?\b/', $body);
        $trustSignals['has_org_number'] = $orgNumber > 0;

        // Sjekk etter kjente norske trust badges
        $trustBadges = [
            'klarna',
            'vipps',
            'trygg-handel',
            'verified-reviews',
            'prisjakt',
            'prisguide',
            'trustpilot'
        ];

        foreach ($trustBadges as $badge) {
            if (str_contains(strtolower($body), $badge)) {
                $trustSignals['has_trust_badges'] = true;
                $trustSignals['details'][] = "Fant {$badge}";
            }
        }

        // Sjekk etter kontaktinformasjon i norsk format
        $hasNorwegianPhone = preg_match('/(\+47|0047)?\s*[0-9]{8}/', $body);
        $hasNorwegianAddress = preg_match('/\d{4}\s+\w+/', $body); // Postnummer + sted

        $trustSignals['has_norwegian_business_info'] = ($hasNorwegianPhone || $hasNorwegianAddress);

        // Sjekk etter kundeanmeldelser
        $reviewPatterns = [
            'kundevurdering',
            'anmeldelser',
            'tilbakemeldinger',
            'trustpilot',
            'reviews',
            'ratings',
            'stars'
        ];

        foreach ($reviewPatterns as $pattern) {
            if (stripos($body, $pattern) !== false) {
                $trustSignals['has_customer_reviews'] = true;
                break;
            }
        }

        return $trustSignals;
    }

    private function analyzeConversionElements($xpath)
    {
        $elements = [
            'cta_buttons' => [],
            'forms' => [],
            'pricing_elements' => [],
            'checkout_optimization' => [],
            'score' => 0
        ];

        // Analyser CTA-knapper
        $ctaButtons = $xpath->query('//button|//a[contains(@class, "btn") or contains(@class, "button")]');
        foreach ($ctaButtons as $button) {
            $buttonText = trim($button->nodeValue);
            $elements['cta_buttons'][] = [
                'text' => $buttonText,
                'placement' => $this->getElementPlacement($button),
                'contrast_ratio' => $this->calculateContrastRatio($button)
            ];
        }

        // Analyser skjemaer
        $forms = $xpath->query('//form');
        foreach ($forms as $form) {
            $fields = $xpath->query('.//input', $form);
            $elements['forms'][] = [
                'fields_count' => $fields->length,
                'required_fields' => $xpath->query('.//input[@required]', $form)->length,
                'has_validation' => $this->hasFormValidation($form)
            ];
        }

        // Sjekk priselementer
        $priceElements = $xpath->query('//*[contains(@class, "price") or contains(@class, "pris")]');
        foreach ($priceElements as $price) {
            $elements['pricing_elements'][] = [
                'price_text' => trim($price->nodeValue),
                'has_vat_info' => $this->hasVATInfo($price),
                'has_comparison_price' => $this->hasComparisonPrice($price)
            ];
        }

        // Sjekk checkout-optimalisering
        $elements['checkout_optimization'] = [
            'has_guest_checkout' => $this->hasGuestCheckout($xpath),
            'has_express_payment' => $this->hasExpressPayment($xpath),
            'has_saved_cards' => $this->hasSavedCards($xpath),
            'has_shipping_estimate' => $this->hasShippingEstimate($xpath)
        ];

        // Beregn score basert på implementerte elementer
        $elements['score'] = $this->calculateConversionScore($elements);

        return $elements;
    }

    private function analyzeLocalization($xpath, $body)
    {
        return [
            'language' => [
                'has_norwegian_content' => $this->hasNorwegianContent($body),
                'has_language_selector' => $this->hasLanguageSelector($xpath),
                'detected_languages' => $this->detectLanguages($body)
            ],
            'regional' => [
                'uses_norwegian_currency' => $this->usesNorwegianCurrency($body),
                'has_norwegian_shipping' => $this->hasNorwegianShipping($body),
                'has_norwegian_payment' => $this->hasNorwegianPayment($body)
            ],
            'cultural' => [
                'uses_norwegian_dates' => $this->usesNorwegianDates($body),
                'uses_norwegian_numbers' => $this->usesNorwegianNumbers($body),
                'has_norwegian_holidays' => $this->hasNorwegianHolidays($body)
            ]
        ];
    }

    private function analyzeTechnicalSEO($xpath, $headers)
    {
        return [
            'meta_tags' => [
                'has_norwegian_meta' => $this->hasNorwegianMetaTags($xpath),
                'has_geo_meta' => $this->hasGeoMetaTags($xpath),
                'has_local_business_schema' => $this->hasLocalBusinessSchema($xpath)
            ],
            'performance' => [
                'uses_cdn' => isset($headers['Server']) && str_contains($headers['Server'][0], 'cloudflare'),
                'has_caching_headers' => isset($headers['Cache-Control']),
                'has_compression' => isset($headers['Content-Encoding'])
            ],
            'indexing' => [
                'has_norwegian_sitemap' => $this->hasNorwegianSitemap($xpath),
                'has_hreflang_tags' => $this->hasHreflangTags($xpath),
                'regional_targeting' => $this->hasRegionalTargeting($xpath)
            ]
        ];
    }

    private function analyzePrivacyCompliance($xpath, $body)
    {
        return [
            'gdpr' => [
                'has_privacy_policy' => $this->hasPrivacyPolicy($xpath),
                'has_cookie_notice' => $this->hasCookieNotice($xpath),
                'has_consent_manager' => $this->hasConsentManager($body)
            ],
            'norwegian_requirements' => [
                'has_angrerett_info' => $this->hasAngrerettInfo($body),
                'has_forbrukerkjøpsloven' => $this->hasForbrukerkjøpslov($body),
                'has_klagerett' => $this->hasKlagerett($body)
            ],
            'marketing_compliance' => [
                'has_marketing_consent' => $this->hasMarketingConsent($xpath),
                'has_newsletter_gdpr' => $this->hasNewsletterGDPR($xpath),
                'has_social_media_notice' => $this->hasSocialMediaNotice($xpath)
            ]
        ];
    }

    private function analyzeMobileExperience($xpath)
    {
        return [
            'responsive_design' => [
                'has_viewport_meta' => $this->hasViewportMeta($xpath),
                'has_responsive_images' => $this->hasResponsiveImages($xpath),
                'has_mobile_menu' => $this->hasMobileMenu($xpath)
            ],
            'touch_optimization' => [
                'has_touch_elements' => $this->hasTouchElements($xpath),
                'has_adequate_spacing' => $this->hasAdequateSpacing($xpath),
                'has_mobile_cta' => $this->hasMobileCTA($xpath)
            ],
            'mobile_specific' => [
                'has_app_install_banner' => $this->hasAppInstallBanner($xpath),
                'has_mobile_payment' => $this->hasMobilePayment($xpath),
                'has_click_to_call' => $this->hasClickToCall($xpath)
            ]
        ];
    }

    private function analyzeAnalyticsSetup($body)
    {
        return [
            'tracking_tools' => [
                'has_google_analytics' => str_contains($body, 'google-analytics.com'),
                'has_google_tag_manager' => str_contains($body, 'googletagmanager.com'),
                'has_facebook_pixel' => str_contains($body, 'facebook.com/tr'),
                'has_hotjar' => str_contains($body, 'hotjar.com'),
                'has_plausible' => str_contains($body, 'plausible.io')
            ],
            'conversion_tracking' => [
                'has_ecommerce_tracking' => $this->hasEcommerceTracking($body),
                'has_goal_tracking' => $this->hasGoalTracking($body),
                'has_event_tracking' => $this->hasEventTracking($body)
            ],
            'custom_tracking' => [
                'has_custom_dimensions' => $this->hasCustomDimensions($body),
                'has_user_id_tracking' => $this->hasUserIdTracking($body),
                'has_enhanced_ecommerce' => $this->hasEnhancedEcommerce($body)
            ]
        ];
    }

    private function getElementPlacement($element)
    {
        $parent = $element->parentNode;
        $path = [$element->nodeName];
        while ($parent->nodeName !== 'body') {
            $path[] = $parent->nodeName;
            $parent = $parent->parentNode;
        }
        return implode(' > ', array_reverse($path));
    }
    private function hasNorwegianShipping($body): bool
    {
        $norwegianTerms = [
            'posten',
            'bring',
            'postnord',
            'helthjem',
            'norgespakke',
            'postkontor',
            'post i butikk',
            'leveringstid',
            'frakt',
            'porto',
            'sending',
            'forsendelse'
        ];

        foreach ($norwegianTerms as $term) {
            if (stripos($body, $term) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasNorwegianPayment($body): bool
    {
        $norwegianPayments = [
            'vipps',
            'klarna',
            'nets',
            'bankid',
            'dnb',
            'sparebank',
            'bankaxept',
            'faktura',
            'avbetaling'
        ];

        foreach ($norwegianPayments as $payment) {
            if (stripos($body, $payment) !== false) {
                return true;
            }
        }
        return false;
    }

    private function usesNorwegianDates($body): bool
    {
        // Sjekk etter norske datoformater (dd.mm.yyyy eller dd.mm.åå)
        return (bool) preg_match('/\d{1,2}\.\d{1,2}\.\d{2,4}/', $body);
    }

    private function usesNorwegianNumbers($body): bool
    {
        // Sjekk etter tall med komma som desimalskilletegn
        return (bool) preg_match('/\d+,\d+/', $body);
    }

    private function hasNorwegianHolidays($body): bool
    {
        $holidays = [
            'jul',
            'påske',
            'pinse',
            '17. mai',
            'nasjonaldag',
            'nyttår',
            'kristi himmelfartsdag',
            'sankthans'
        ];

        foreach ($holidays as $holiday) {
            if (stripos($body, $holiday) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasNorwegianMetaTags($xpath): bool
    {
        $metaTags = $xpath->query('//meta[@lang="nb" or @lang="no" or @content="nb" or @content="no"]');
        return $metaTags->length > 0;
    }

    private function hasGeoMetaTags($xpath): bool
    {
        $geoTags = $xpath->query('//meta[contains(@name, "geo") or contains(@property, "geo")]');
        return $geoTags->length > 0;
    }

    private function hasLocalBusinessSchema($xpath): bool
    {
        $schemas = $xpath->query('//*[@type="LocalBusiness" or contains(@class, "LocalBusiness")]');
        return $schemas->length > 0;
    }

    private function hasNorwegianSitemap($xpath): bool
    {
        $sitemapLinks = $xpath->query('//a[contains(@href, "sitemap") and (contains(text(), "no") or contains(text(), "nb"))]');
        return $sitemapLinks->length > 0 || $this->checkExternalSitemap($xpath);
    }

    private function checkExternalSitemap($xpath): bool
    {
        $links = $xpath->query('//link[@rel="sitemap"]');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (strpos($href, 'no') !== false || strpos($href, 'nb') !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasHreflangTags($xpath): bool
    {
        $hreflangTags = $xpath->query('//link[@rel="alternate" and (@hreflang="nb" or @hreflang="no")]');
        return $hreflangTags->length > 0;
    }

    private function hasRegionalTargeting($xpath): bool
    {
        // Sjekk meta tags for regional targeting
        $regionalMeta = $xpath->query('//meta[contains(@name, "geo.region") or contains(@content, "NO")]');

        // Sjekk canonical link for .no domene
        $canonical = $xpath->query('//link[@rel="canonical"]');
        if ($canonical->length > 0) {
            $href = $canonical->item(0)->getAttribute('href');
            if (strpos($href, '.no') !== false) {
                return true;
            }
        }

        return $regionalMeta->length > 0;
    }

    private function hasPrivacyPolicy($xpath): bool
    {
        $privacyLinks = $xpath->query('//a[contains(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "personvern") or contains(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "privacy")]');
        return $privacyLinks->length > 0;
    }

    private function hasCookieNotice($xpath): bool
    {
        $cookieElements = $xpath->query('//*[contains(@class, "cookie") or contains(@id, "cookie")]');
        return $cookieElements->length > 0;
    }

    private function hasConsentManager($body): bool
    {
        $consentManagers = [
            'cookiebot',
            'onetrust',
            'cookie-consent',
            'CookieConsent',
            'gdpr',
            'samtykke'
        ];

        foreach ($consentManagers as $manager) {
            if (stripos($body, $manager) !== false) {
                return true;
            }
        }
        return false;
    }

    // Norske lovkrav
    private function hasAngrerettInfo($body): bool
    {
        $terms = [
            'angrerett',
            'angrefrist',
            '14 dager',
            'returrett',
            'retur av varer',
            'angrerettskjema',
            'angrerettloven'
        ];

        foreach ($terms as $term) {
            if (stripos($body, $term) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasForbrukerkjøpslov($body): bool
    {
        $terms = [
            'forbrukerkjøpslov',
            'forbrukerkjøp',
            'reklamasjonsrett',
            'forbrukerrettighet',
            '5 års reklamasjonsrett',
            'garanti',
            'lovbestemt reklamasjonsrett'
        ];

        foreach ($terms as $term) {
            if (stripos($body, $term) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasKlagerett($body): bool
    {
        $terms = [
            'klagerett',
            'klagemulighet',
            'klagebehandling',
            'forbrukerrådet',
            'forbrukertvistutvalget',
            'klage på',
            'reklamasjon',
            'kundeservice'
        ];

        foreach ($terms as $term) {
            if (stripos($body, $term) !== false) {
                return true;
            }
        }
        return false;
    }

    // Marketing Compliance
    private function hasMarketingConsent($xpath): bool
    {
        // Sjekk etter markedsførings-samtykke i skjemaer
        $marketingConsent = $xpath->query('//input[@type="checkbox"][contains(@name, "newsletter") or contains(@name, "marketing") or contains(@name, "consent")]');

        // Sjekk etter tekst om markedsføringssamtykke
        $consentText = $xpath->query('//*[contains(text(), "markedsføring") or contains(text(), "nyhetsbrev")]');

        return $marketingConsent->length > 0 || $consentText->length > 0;
    }

    private function hasNewsletterGDPR($xpath): bool
    {
        // Sjekk etter GDPR-relatert tekst nær nyhetsbrev-påmelding
        $newsletterForms = $xpath->query('//form[contains(., "nyhetsbrev") or contains(., "newsletter")]');

        foreach ($newsletterForms as $form) {
            $gdprText = $xpath->query('.//text()[contains(., "GDPR") or contains(., "personvern") or contains(., "samtykke")]', $form);
            if ($gdprText->length > 0) {
                return true;
            }
        }
        return false;
    }

    private function hasSocialMediaNotice($xpath): bool
    {
        // Sjekk etter informasjon om sosiale medier og personvern
        $socialPrivacy = $xpath->query('//*[contains(text(), "sosiale medier") and (contains(text(), "personvern") or contains(text(), "cookies"))]');
        return $socialPrivacy->length > 0;
    }

    // Mobile Experience
    private function hasViewportMeta($xpath): bool
    {
        $viewport = $xpath->query('//meta[@name="viewport"]');
        return $viewport->length > 0;
    }

    private function hasResponsiveImages($xpath): bool
    {
        // Sjekk etter responsive bilder
        $srcsetImages = $xpath->query('//img[@srcset]');
        $pictureElements = $xpath->query('//picture');
        $responsiveClasses = $xpath->query('//img[contains(@class, "responsive") or contains(@class, "fluid")]');

        return $srcsetImages->length > 0 || $pictureElements->length > 0 || $responsiveClasses->length > 0;
    }

    private function hasMobileMenu($xpath): bool
    {
        // Sjekk etter vanlige mobile meny-elementer
        $mobileMenus = $xpath->query('//*[contains(@class, "mobile-menu") or contains(@class, "hamburger") or contains(@class, "nav-toggle")]');
        return $mobileMenus->length > 0;
    }

    private function hasTouchElements($xpath): bool
    {
        // Sjekk etter touch-optimaliserte elementer
        $touchElements = $xpath->query('//*[contains(@class, "touch") or @role="button" or contains(@class, "tap")]');

        // Sjekk minimum størrelse på klikkbare elementer
        $clickableElements = $xpath->query('//a | //button | //[role="button"]');
        $hasAdequateSize = false;

        foreach ($clickableElements as $element) {
            // Sjekk om elementet har minimum 44x44px størrelse (iOS guidelines)
            $style = $element->getAttribute('style');
            if (preg_match('/(?:width|height):\s*([0-9]+)px/', $style, $matches)) {
                if (intval($matches[1]) >= 44) {
                    $hasAdequateSize = true;
                    break;
                }
            }
        }

        return $touchElements->length > 0 || $hasAdequateSize;
    }

    private function hasAdequateSpacing($xpath): bool
    {
        // Sjekk etter CSS-klasser som indikerer god spacing
        $spacingElements = $xpath->query('//*[contains(@class, "spacing") or contains(@class, "gap") or contains(@class, "margin")]');
        return $spacingElements->length > 0;
    }

    private function hasMobileCTA($xpath): bool
    {
        // Sjekk etter mobile-spesifikke CTA-er
        $mobileCTA = $xpath->query('//*[contains(@class, "mobile-cta") or contains(@class, "sticky-button")]');
        return $mobileCTA->length > 0;
    }

    private function hasAppInstallBanner($xpath): bool
    {
        // Sjekk etter app-install bannere og meta-tags
        $appBanner = $xpath->query('//meta[@name="apple-itunes-app"] | //meta[@name="google-play-app"]');
        $appLinks = $xpath->query('//a[contains(@href, "play.google.com") or contains(@href, "apps.apple.com")]');

        return $appBanner->length > 0 || $appLinks->length > 0;
    }

    private function hasMobilePayment($xpath): bool
    {
        // Sjekk etter mobile betalingsløsninger
        $mobilePayments = [
            'vipps',
            'mobilepay',
            'apple pay',
            'google pay',
            'samsung pay',
            'swish'
        ];

        foreach ($mobilePayments as $payment) {
            $elements = $xpath->query("//*[contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '$payment')]");
            if ($elements->length > 0) {
                return true;
            }
        }
        return false;
    }

    private function hasClickToCall($xpath): bool
    {
        // Sjekk etter click-to-call lenker
        $phoneLinks = $xpath->query('//a[contains(@href, "tel:")]');
        return $phoneLinks->length > 0;
    }

    // Analytics
    private function hasEcommerceTracking($body): bool
    {
        $ecommercePatterns = [
            'gtag.*purchase',
            'analytics.*ecommerce',
            'dataLayer.push.*ecommerce',
            'ga.*ecommerce',
            'fbq.*Purchase',
            'track.*purchase'
        ];

        foreach ($ecommercePatterns as $pattern) {
            if (preg_match("/$pattern/i", $body)) {
                return true;
            }
        }
        return false;
    }

    private function hasGoalTracking($body): bool
    {
        $goalPatterns = [
            'gtag.*event',
            'ga.*send.*event',
            'dataLayer.push.*event',
            'fbq.*track',
            '_gaq.push.*_trackEvent'
        ];

        foreach ($goalPatterns as $pattern) {
            if (preg_match("/$pattern/i", $body)) {
                return true;
            }
        }
        return false;
    }

    private function hasEventTracking($body): bool
    {
        return str_contains($body, 'addEventListener') ||
            str_contains($body, 'onclick') ||
            str_contains($body, 'gtag') ||
            str_contains($body, 'dataLayer.push');
    }

    private function hasCustomDimensions($body): bool
    {
        return str_contains($body, 'dimension') ||
            str_contains($body, 'metric') ||
            preg_match('/custom(Dimension|Metric)/i', $body);
    }

    private function hasUserIdTracking($body): bool
    {
        $userIdPatterns = [
            'set.*userId',
            'gtag.*user_id',
            'analytics.*userId',
            'dimension.*user'
        ];

        foreach ($userIdPatterns as $pattern) {
            if (preg_match("/$pattern/i", $body)) {
                return true;
            }
        }
        return false;
    }

    private function hasEnhancedEcommerce($body): bool
    {
        $enhancedPatterns = [
            'enhanced[_-]ecommerce',
            'dataLayer.push.*ecommerce',
            'gtag.*items',
            'ec:addProduct',
            'ec:setAction'
        ];

        foreach ($enhancedPatterns as $pattern) {
            if (preg_match("/$pattern/i", $body)) {
                return true;
            }
        }
        return false;
    }

    private function usesNorwegianCurrency($body): bool
    {
        // Sjekk etter norske kroner (NOK, kr, ,-)
        return preg_match('/(?:NOK|kr\.?|,-)?\s*\d+(?:[.,]\d{2})?(?:\s*(?:NOK|kr\.?|,-))/i', $body) > 0 ||
            preg_match('/\d+(?:[.,]\d{2})?\s*(?:NOK|kr\.?|,-)/i', $body) > 0;
    }

    private function detectLanguages($body): array
    {
        $languages = [];

        // Sjekk HTML lang attribute
        if (preg_match('/html[^>]+lang=["\']([^"\']+)["\']/i', $body, $matches)) {
            $languages[] = $matches[1];
        }

        // Sjekk hreflang tags
        if (preg_match_all('/hreflang=["\']([^"\']+)["\']/i', $body, $matches)) {
            $languages = array_merge($languages, $matches[1]);
        }

        // Sjekk meta tags
        if (preg_match_all('/content=["\']([^"\']*(?:nb|no|nn|en)[^"\']*)["\'][^>]+name=["\']language["\']/i', $body, $matches)) {
            $languages = array_merge($languages, $matches[1]);
        }

        return array_unique($languages);
    }

    private function hasLanguageSelector($xpath): bool
    {
        // Sjekk etter språkvelger i navigasjon eller footer
        $languageSelectors = $xpath->query(
            '//*[contains(@class, "lang") or contains(@class, "language")] |
         //select[contains(., "Norsk") or contains(., "English")] |
         //a[contains(., "Norsk") or contains(., "English")] |
         //button[contains(., "Norsk") or contains(., "English")]'
        );

        return $languageSelectors->length > 0;
    }

    private function hasNorwegianContent($body): bool
    {
        // Liste over vanlige norske ord og fraser
        $norwegianWords = [
            'og',
            'eller',
            'på',
            'med',
            'har',
            'som',
            'kan',
            'vil',
            'kjøp',
            'handlekurv',
            'til salgs',
            'tilbud',
            'nyheter',
            'om oss',
            'kontakt',
            'hjem',
            'tjenester',
            'produkter'
        ];

        $wordCount = 0;
        foreach ($norwegianWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/ui', $body)) {
                $wordCount++;
            }
        }

        // Returner true hvis vi finner minst 5 norske ord
        return $wordCount >= 5;
    }

    private function calculateConversionScore(array $elements): int
    {
        $score = 0;
        $weights = [
            'cta_buttons' => 20,
            'forms' => 15,
            'pricing_elements' => 15,
            'checkout_optimization' => 50
        ];

        // Score CTA buttons
        if (!empty($elements['cta_buttons'])) {
            $score += min(count($elements['cta_buttons']), 5) * 4; // Max 20 points
        }

        // Score forms
        if (!empty($elements['forms'])) {
            foreach ($elements['forms'] as $form) {
                if ($form['has_validation']) $score += 5;
                if ($form['fields_count'] <= 5) $score += 5; // Reward shorter forms
            }
        }

        // Score pricing elements
        if (!empty($elements['pricing_elements'])) {
            foreach ($elements['pricing_elements'] as $price) {
                if ($price['has_vat_info']) $score += 3;
                if ($price['has_comparison_price']) $score += 2;
            }
        }

        // Score checkout optimization
        $checkoutFeatures = $elements['checkout_optimization'];
        if ($checkoutFeatures['has_guest_checkout']) $score += 15;
        if ($checkoutFeatures['has_express_payment']) $score += 15;
        if ($checkoutFeatures['has_saved_cards']) $score += 10;
        if ($checkoutFeatures['has_shipping_estimate']) $score += 10;

        return min($score, 100); // Cap at 100
    }

    private function hasShippingEstimate($xpath): bool
    {
        // Sjekk etter fraktestimator eller leveringstidsinfo
        return $xpath->query(
            '//*[contains(text(), "leveringstid") or
            contains(text(), "frakttid") or
            contains(text(), "estimert levering") or
            contains(text(), "leveringsdato")]'
        )->length > 0;
    }

    private function hasExpressPayment($xpath): bool
    {
        $expressPayments = [
            'vipps',
            'express checkout',
            'hurtigkasse',
            'express payment',
            'quick checkout',
            'klarna checkout'
        ];

        foreach ($expressPayments as $payment) {
            if ($xpath->query("//*[contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '$payment')]")->length > 0) {
                return true;
            }
        }
        return false;
    }

    private function hasGuestCheckout($xpath): bool
    {
        // Sjekk etter gjestekjøp-mulighet
        return $xpath->query(
            '//*[contains(text(), "gjestekjøp") or
            contains(text(), "handle som gjest") or
            contains(text(), "uten konto") or
            contains(text(), "guest checkout")]'
        )->length > 0;
    }

    private function hasSavedCards($xpath): bool
    {
        // Sjekk etter lagrede kort-funksjonalitet
        return $xpath->query(
            '//*[contains(text(), "lagre kort") or
            contains(text(), "save card") or
            contains(text(), "husk betalingsinformasjon") or
            contains(@class, "saved-payment-method")]'
        )->length > 0;
    }

    private function hasVATInfo($price): bool
    {
        $priceText = $price->nodeValue;
        return stripos($priceText, 'mva') !== false ||
            stripos($priceText, 'inkl.') !== false ||
            stripos($priceText, 'eks.') !== false;
    }

    private function hasComparisonPrice($price): bool
    {
        // Sjekk etter sammenlignbar pris (f.eks. pris per kilo)
        $priceText = $price->nodeValue;
        return preg_match('/per\s+(?:kg|kilo|liter|stk|m²)/i', $priceText) > 0 ||
            preg_match('/(?:kr|NOK)[\s0-9.,]+\/(?:kg|l|stk)/i', $priceText) > 0;
    }

    private function calculateContrastRatio($element): float
    {
        // Hent bakgrunnsfarge og tekstfarge fra element
        $backgroundColor = $this->getBackgroundColor($element);
        $textColor = $this->getTextColor($element);

        // Konverter farger til relative luminance
        $l1 = $this->getRelativeLuminance($backgroundColor);
        $l2 = $this->getRelativeLuminance($textColor);

        // Beregn kontrastratio
        $ratio = ($l1 + 0.05) / ($l2 + 0.05);
        if ($ratio < 1) {
            $ratio = 1 / $ratio;
        }

        return round($ratio, 2);
    }


    private function hasFormValidation(\DOMElement $form): bool
    {
        try {
            // 1. HTML5 Validering
            $hasHtml5Validation = $this->checkHtml5Validation($form);

            // 2. JavaScript Validering
            $hasJsValidation = $this->checkJavaScriptValidation($form);

            // 3. Validerings-biblioteker
            $hasValidationLibrary = $this->checkValidationLibraries($form);

            // 4. Custom Form Validering
            $hasCustomValidation = $this->checkCustomValidation($form);

            return $hasHtml5Validation || $hasJsValidation ||
                $hasValidationLibrary || $hasCustomValidation;
        } catch (\Exception $e) {
            Log::error('Feil ved sjekk av skjemavalidering: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sjekker HTML5 validerings-attributter
     */
    private function checkHtml5Validation(\DOMElement $form): bool
    {
        $xpath = new \DOMXPath($form->ownerDocument);

        // Liste over HTML5 validerings-attributter å se etter
        $validationAttributes = [
            'required',
            'pattern',
            'minlength',
            'maxlength',
            'min',
            'max',
            'type="email"',
            'type="url"',
            'type="tel"',
            'type="number"',
            'type="date"'
        ];

        // Bygg XPath query for å finne elementer med validerings-attributter
        $queries = [];
        foreach ($validationAttributes as $attr) {
            if (str_contains($attr, 'type=')) {
                $type = trim(explode('=', $attr)[1], '"');
                $queries[] = "//input[@type='$type']";
            } else {
                $queries[] = "//*[@$attr]";
            }
        }

        $query = implode(' | ', $queries);
        $validationElements = $xpath->query($query, $form);

        return $validationElements->length > 0;
    }

    /**
     * Sjekker JavaScript-basert validering
     */
    private function checkJavaScriptValidation(\DOMElement $form): bool
    {
        // Sjekk inline validering
        $jsAttributes = ['onsubmit', 'onchange', 'oninput', 'onblur'];
        foreach ($jsAttributes as $attr) {
            $value = $form->getAttribute($attr);
            if ($value && (
                str_contains($value, 'valid') ||
                str_contains($value, 'check') ||
                str_contains($value, 'verify')
            )) {
                return true;
            }
        }

        // Sjekk data-attributter for validering
        $validationDataAttributes = [
            'data-validate',
            'data-validation',
            'data-parsley',
            'data-vv',
            'data-rules'
        ];

        foreach ($validationDataAttributes as $attr) {
            if ($form->hasAttribute($attr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sjekker etter kjente validerings-biblioteker
     */
    private function checkValidationLibraries(\DOMElement $form): bool
    {
        $doc = $form->ownerDocument;
        $xpath = new \DOMXPath($doc);

        // Liste over kjente validerings-biblioteker og deres indikatorer
        $libraries = [
            // jQuery Validate
            ['script[src*="jquery.validate"]', 'jquery.validate'],

            // Parsley
            ['script[src*="parsley"]', 'Parsley'],

            // Vee-Validate
            ['script[src*="vee-validate"]', 'VeeValidate'],

            // FormValidation.io
            ['script[src*="formvalidation"]', 'FormValidation'],

            // Validate.js
            ['script[src*="validate.js"]', 'validate.js'],

            // YUP
            ['script[src*="yup"]', 'yup'],

            // Formik
            ['script[src*="formik"]', 'Formik'],

            // HTML5 Form Validation
            ['script[src*="h5validate"]', 'h5validate']
        ];

        // Sjekk script tags
        foreach ($libraries as [$selector, $keyword]) {
            $scripts = $xpath->query('//script');
            foreach ($scripts as $script) {
                if ($script instanceof \DOMElement) {
                    $src = $script->getAttribute('src') ?? '';
                    $content = $script->nodeValue ?? '';

                    if (
                        str_contains($src, $keyword) ||
                        str_contains($content, $keyword)
                    ) {
                        return true;
                    }
                }
            }
        }

        // Sjekk for klasser som indikerer validering
        $validationClasses = [
            'validate',
            'validation',
            'validated',
            'needs-validation',
            'parsley',
            'vee-validate',
            'formvalidation'
        ];

        foreach ($validationClasses as $class) {
            if (str_contains($form->getAttribute('class'), $class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sjekker etter custom validering
     */
    private function checkCustomValidation(\DOMElement $form): bool
    {
        $doc = $form->ownerDocument;
        $xpath = new \DOMXPath($doc);

        // Sjekk etter error/success containers
        $errorContainers = $xpath->query('.//*[contains(@class, "error") or contains(@class, "invalid") or contains(@class, "success") or contains(@class, "valid")]', $form);
        if ($errorContainers->length > 0) {
            return true;
        }

        // Sjekk etter ARIA attributter relatert til validering
        $ariaAttributes = [
            'aria-invalid',
            'aria-errormessage',
            'aria-describedby'
        ];

        foreach ($ariaAttributes as $attr) {
            $elements = $xpath->query(".//*[@$attr]", $form);
            if ($elements->length > 0) {
                return true;
            }
        }

        // Sjekk etter custom validerings-meldinger
        $messageContainers = $xpath->query('.//*[contains(@class, "help-block") or contains(@class, "form-text") or contains(@class, "feedback")]', $form);
        if ($messageContainers->length > 0) {
            return true;
        }

        return false;
    }

    // Hjelpemetoder for kontrastberegning
    private function getBackgroundColor($element)
    {
        $style = $element->getAttribute('style');
        if (preg_match('/background(?:-color)?:\s*([^;]+)/', $style, $matches)) {
            return $this->parseColor($matches[1]);
        }
        return [255, 255, 255]; // Default hvit
    }

    private function getTextColor($element)
    {
        $style = $element->getAttribute('style');
        if (preg_match('/color:\s*([^;]+)/', $style, $matches)) {
            return $this->parseColor($matches[1]);
        }
        return [0, 0, 0]; // Default svart
    }

    private function parseColor($color)
    {
        // Håndter hex
        if (preg_match('/#([0-9a-f]{3}|[0-9a-f]{6})/i', $color, $matches)) {
            $hex = $matches[1];
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            ];
        }

        // Håndter rgb/rgba
        if (preg_match('/rgba?\s*\((\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $color, $matches)) {
            return [
                (int)$matches[1],
                (int)$matches[2],
                (int)$matches[3]
            ];
        }

        return [0, 0, 0]; // Default svart
    }

    private function getRelativeLuminance($rgb)
    {
        list($r, $g, $b) = $rgb;
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
