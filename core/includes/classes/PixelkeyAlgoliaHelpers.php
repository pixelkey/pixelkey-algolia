<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * Class PixelkeyAlgoliaHelpers
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package		pixelkey-algolia
 * @subpackage	Classes/PixelkeyAlgoliaHelpers
 * @author		Pixel Key
 * @since		1.0.1
 */
class PixelkeyAlgoliaHelpers
{

    /**
     * To access this function from any other class, you can call it as followed:
     *  PixelkeyAlgolia()->helpers->function_name( 'my text' );
     */

    /**
     * log cron job run to file named pixelkey_algolia.log
     * 		WP_CONTENT_DIR is the path to the wp-content directory
     * 
     * 		FILE_APPEND flag is used to append the content to the end of the file
     * 		LOCK_EX flag is used to prevent others from writing to the file at the same time
     */
    public function pixelkey_algolia_log_event($logMessage)
    {
        // The log file path
        $log_file_path = WP_CONTENT_DIR . '/pixelkey_algolia.log';

        // The log entry with the current date and time
        $log_entry = date("Y-m-d H:i:s") . " - " . $logMessage . PHP_EOL;

        // Write to the log file (append if it already exists)
        file_put_contents($log_file_path, $log_entry, FILE_APPEND | LOCK_EX);
    }
}
