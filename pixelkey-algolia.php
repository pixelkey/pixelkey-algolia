<?php
/**
 * Pixel Key Algolia Plugin
 *
 * @package   pixelkey-algolia
 *
 * Plugin Name:  Pixel Key Algolia
 * Description:  Provides indexing services for Algolia
 * Version:      0.1.2
 * Text Domain:  pixelkey-algolia
 * Domain Path:  /languages/
 * Requires PHP: 7.2.0
 *
 */
namespace PixelKey\Algolia;

include 'PixelKeyAutoloader.php';
include 'SettingsPage.php';
include 'PostHooks.php';
include 'Setup.php';

PostHooks::init();
Setup::init();
