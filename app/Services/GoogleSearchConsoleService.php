<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;

class GoogleSearchConsoleService
{
    protected $client;
    protected $searchConsole;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName("Measuretank");
        $this->client->setAuthConfig(storage_path('app/google-service-account.json'));
        $this->client->addScope(SearchConsole::WEBMASTERS_READONLY);

        $this->searchConsole = new SearchConsole($this->client);
    }

    public function getSearchAnalytics($siteUrl, $startDate, $endDate)
    {
        $request = new SearchConsole\SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions(['page']);

        $response = $this->searchConsole->searchanalytics->query($siteUrl, $request);

        return $response->getRows();
    }
}
