<?php
/**
 * Pixel Key Algolia Plugin
 *
 * @package   pixelkey-algolia
 *
 * Plugin Name:  Pixel Key Algolia
 * Description:  Provides indexing services for Algolia
 * Version:      0.1.0
 * Text Domain:  pixelkey-algolia
 * Domain Path:  /languages/
 * Requires PHP: 7.2.0
 *
 */
namespace PixelKey\Algolia;

use PixelKey\Algolia\Commands\CommandInterface;
use PixelKey\Algolia\Commands\RunIndexers;

include 'PixelKeyAutoloader.php';
include 'SettingsPage.php';

$commands = [
    Commands\RunIndexers::class
];



