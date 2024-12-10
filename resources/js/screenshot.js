import puppeteer from 'puppeteer';
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import fs from 'fs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

async function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function takeScreenshot(url, outputPath, deviceType = 'desktop') {
    // Ensure output directory exists
    const outputDir = dirname(outputPath);
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    console.log('Environment:', {
        url,
        outputPath,
        deviceType,
        cwd: process.cwd(),
        outputDir,
        nodeVersion: process.version
    });

    const browser = await puppeteer.launch({
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--no-zygote',
            '--single-process'
        ]
    });

    let page = null;
    try {
        console.log('Browser launched successfully');
        page = await browser.newPage();
        console.log('New page created');

        // Set viewport
        if (deviceType === 'mobile') {
            console.log('Setting mobile viewport');
            await page.setViewport({
                width: 390,
                height: 844,
                deviceScaleFactor: 2,
                isMobile: true,
                hasTouch: true
            });
        } else {
            console.log('Setting desktop viewport');
            await page.setViewport({
                width: 1920,
                height: 1080,
                deviceScaleFactor: 1
            });
        }

        // Set timeouts
        await page.setDefaultNavigationTimeout(30000);
        await page.setDefaultTimeout(30000);

        console.log('Navigating to URL...');
        const response = await page.goto(url, {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });

        if (!response) {
            throw new Error('Failed to get response from page');
        }

        console.log(`Page loaded with status: ${response.status()}`);

        // Wait a bit for any dynamic content
        console.log('Waiting for content to settle...');
        await delay(2000);

        console.log('Taking screenshot...');
        await page.screenshot({
            path: outputPath,
            fullPage: true,
            type: 'jpeg',
            quality: 80
        });
        console.log('Screenshot taken successfully');

    } catch (error) {
        console.error('Error during screenshot process:', error);
        // Try to take a basic screenshot anyway
        if (page) {
            try {
                console.log('Attempting fallback screenshot...');
                await page.screenshot({
                    path: outputPath,
                    fullPage: false,
                    type: 'jpeg',
                    quality: 60
                });
                console.log('Fallback screenshot taken');
            } catch (fallbackError) {
                console.error('Fallback screenshot also failed:', fallbackError);
                throw error;
            }
        } else {
            throw error;
        }
    } finally {
        if (browser) {
            console.log('Closing browser');
            await browser.close();
        }
    }
}

// Get command line arguments
const [,, url, outputPath, deviceType] = process.argv;

if (!url || !outputPath) {
    console.error('Usage: node screenshot.js <url> <outputPath> [deviceType]');
    process.exit(1);
}

takeScreenshot(url, outputPath, deviceType)
    .then(() => {
        console.log('Screenshot process completed successfully');
        process.exit(0);
    })
    .catch(error => {
        console.error('Screenshot process failed:', error);
        process.exit(1);
    });
