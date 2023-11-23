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

    /**
     * Runs the indexing process as a cron job.
     * 
     * This method triggers the 'pixelkey_algolia:before_index_cron' action before starting the indexing process,
     * then calls the 'reindex_post_atomic' method of the indexer to reindex the 'post' type.
     * Finally, it triggers the 'pixelkey_algolia:after_index_cron' action after completing the indexing process.
     */
    public function runAsCron()
    {
        do_action('pixelkey_algolia:before_index_cron');

        PixelkeyAlgolia()->indexer->reindex_post_atomic('post');

        do_action('pixelkey_algolia:after_index_cron');
    }

    /**
     * This method is called when a post is saved or updated.
     * It indexes the post using the appropriate indexers if the post type is supported and the post status is 'publish'.
     *
     * @param int $postId The ID of the post being saved or updated.
     * @return void
     */
    public function onPostSaveAndUpdate($id, \WP_Post $post, $update)
    {
        if (wp_is_post_revision($id) || wp_is_post_autosave($id)) {
            return $post;
        }
        $algolia = PixelkeyAlgolia()->indexer->createAlgoliaSearchClient();
        $record = (array) apply_filters($post->post_type . '_to_record', $post);

        if (!isset($record['objectID'])) {
            $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
        }

        $index = $algolia->initIndex(
            apply_filters('algolia_index_name', 'post')
        );

        if ('publish' == $post->post_status) {
            $index->saveObject($record);
        } else {
            $index->deleteObject($record['objectID']);
        }
        do_action('pixelkey_algolia:update_success');
    }

    /**
     * Process the index action and return the result.
     * - If the action is 'run_all' or 'run_index', it runs the indexing process as a cron job.
     * - Triggers on click of the 'Run All Indexers' or 'Run $indexerName Indexer' buttons. on the admin page.
     *
     * @param string $action The action to perform.
     * @param string $indexerName The name of the indexer.
     * @return string The result of the index action.
     */
    public function processIndexAction($action, $indexerName)
    {
        if ($action === 'run_all' || $action === 'run_index') {
            $nonce = $_POST['_wpnonce'] ?? '';
            if (!wp_verify_nonce($nonce, 'run_index_nonce')) {
                return '<div class="error"><p>Security check failed.</p></div>';
            }

            try {
                // Schedule a one-time event to run as soon as possible.
                $isScheduled = wp_schedule_single_event(time(), 'pixelkey_algolia/run_indexers');
                if (!$isScheduled || ($isScheduled instanceof WP_Error)) {
                    throw new \Exception('Unable to schedule the event.');
                }
                $message = ($action === 'run_all') ? 'Cron job is running for all indexers.' : "Cron job is running for $indexerName indexer.";
                $message .= " It takes a few minutes to complete. You can continue working on other tasks.";

                // Return a message to the user indicating that the cron job has been triggered.
                return "<div class='notice notice-success run-index__status'>$message</div>";
            } catch (\Exception $e) {
                PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event($e->getMessage());
                return '<div class="notice notice-error run-index__status">There has been a problem running the indexers ❌</div>';
            }
        }
    }

    /**
     * Outputs the indexer forms to the admin page.
     *
     * @param string $indexerName The name of the indexer.
     * @return void
     */
    public function outputIndexerForms($indexerName)
    {
        $nonceField = wp_nonce_field('run_index_nonce', '_wpnonce', true, false);
        echo "<form action='?page=algolia-indexing' method='post'>
            <button class='button button-primary' name='action' value='run_all'>Run All Indexers</button>
            $nonceField
        </form>";

        echo "<form action='?page=algolia-indexing' method='post'>
            <button class='button button-primary' name='action' value='run_index'>Run $indexerName Indexer</button>
            $nonceField
        </form>";
    }
}
