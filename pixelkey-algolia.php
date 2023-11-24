<?php

/**
 * Pixel Key Algolia Plugin
 *
 * @package   pixelkey-algolia
 *
 * Plugin Name:  Pixel Key Algolia
 * Description:  Provides indexing services for Algolia
 * Version:      1.0.3
 * Text Domain:  pixelkey-algolia
 * Domain Path:  /languages/
 * Requires PHP: ^7.3
 *
 */

// Plugin name
define('PIXELKEY_ALGOLIA_PLUGIN_NAME',    'Pixel Key Algolia');

// Plugin version
define('PIXELKEY_ALGOLIA_PLUGIN_VERSION',        '1.0.3');

// Plugin Root File
define('PIXELKEY_ALGOLIA_PLUGIN_FILE',    __FILE__);

// Plugin Folder Path
define('PIXELKEY_ALGOLIA_PLUGIN_DIR',    plugin_dir_path(PIXELKEY_ALGOLIA_PLUGIN_FILE));

// Plugin Folder URL
define('PIXELKEY_ALGOLIA_PLUGIN_URL',    plugin_dir_url(PIXELKEY_ALGOLIA_PLUGIN_FILE));

// Autoload Composer
require PIXELKEY_ALGOLIA_PLUGIN_DIR . '/vendor/autoload.php';

/**
 * Load the main class for the core functionality
 */
require_once PIXELKEY_ALGOLIA_PLUGIN_DIR . 'core/PixelkeyAlgolia.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Pixel Key
 * @since   1.0.0
 * @return  object|PixelkeyAlgolia
 */
function PixelkeyAlgolia()
{
    return PixelkeyAlgolia::instance();
}

PixelkeyAlgolia();
