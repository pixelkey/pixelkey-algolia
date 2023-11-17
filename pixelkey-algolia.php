<?php

/**
 * Pixel Key Algolia Plugin
 *
 * @package   pixelkey-algolia
 *
 * Plugin Name:  Pixel Key Algolia
 * Description:  Provides indexing services for Algolia
 * Version:      0.1.4
 * Text Domain:  pixelkey-algolia
 * Domain Path:  /languages/
 * Requires PHP: 7.2.0
 *
 */

// Plugin Root File
define('PIXELKEY_ALGOLIA_PLUGIN_FILE',    __FILE__);

// Plugin Folder Path
define('PIXELKEY_ALGOLIA_PLUGIN_DIR',    plugin_dir_path(PIXELKEY_ALGOLIA_PLUGIN_FILE));

// Autoload Composer
require PIXELKEY_ALGOLIA_PLUGIN_DIR . '/vendor/autoload.php';

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;

include 'PixelKeyAutoloader.php';
include 'SettingsPage.php';
include 'PostHooks.php';
include 'Setup.php';

PostHooks::init();
Setup::init();



class AlgoliaHelperchanged
{

    static $client;
    static $postIndex;

    public static function initialize()
    {
        $appId = "3UUYP16FCB";
        $apiKey = "9c2329d498e556cb7d05d951f1d7da32";

        if (!$appId || !$apiKey) {
            return;
        }

        if (!static::$client) {
            static::$client = SearchClient::create(
                $appId,
                $apiKey
            );
            console_log(static::$client);
            static::$postIndex = static::$client->initIndex(env('ALGOLIA_POST_INDEX'));
        }
    }

    public static function getPostsFromSearch(string $searchString)
    {
        return static::$postIndex->search($searchString);
    }
}

// AlgoliaHelperchanged::initialize();