<?php

// Exit if accessed directly.
if (!defined('ABSPATH'))
    exit;

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
        $log_entry = sprintf("%s - %s%s", date("Y-m-d H:i:s"), $logMessage, PHP_EOL);

        // Write to the log file (append if it already exists)
        file_put_contents($log_file_path, $log_entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Verifies the nonce for security.
     *
     * @param string $nonce The nonce to verify.
     * @param string $nonce_action The action associated with the nonce.
     * @return bool
     */
    private function verifyNonce($nonce, $nonce_action)
    {
        return !empty($nonce) && wp_verify_nonce($nonce, $nonce_action);
    }
    /**
     * Runs the indexing process as a cron job.
     * 
     * This method triggers the 'pixelkey_algolia:before_index_cron' action before starting the indexing process,
     * then calls the 'reindex_post_batch' method of the indexer to reindex the 'post' type.
     * Finally, it triggers the 'pixelkey_algolia:after_index_cron' action after completing the indexing process.
     */
    public function runAsCron()
    {
        do_action('pixelkey_algolia:before_index_cron');

        PixelkeyAlgolia()->indexer->reindex_post_batch('post');

        do_action('pixelkey_algolia:after_index_cron');
    }

    /**
     * This method is called when a post is saved or updated.
     * It indexes the post using the appropriate indexers if the post type is supported and the post status is 'publish'.
     *
     * @param int $postId The ID of the post being saved or updated.
     * @return void
     */
    public function onPostSaveAndUpdate($id, WP_Post $post, $update)
    {
        if (wp_is_post_revision($id) || wp_is_post_autosave($id) || !has_filter($post->post_type . '_to_record')) {
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
     * Add custom menu page content for the following
     * menu item: algolia-indexing
     *
     * @access public
     * @since 1.0.0
     *
     * @return void
     */
    public function pixelkey_algolia_admin_menu_page_callback()
    {
        $indexerName = apply_filters('algolia_index_name', 'post');

        // Check if a valid action is submitted and process it
        ['classes' => $classes, 'message' => $statusMessage] = $this->processIndexAction($indexerName);
        ['classes' => $classe_s, 'message' => $s_Message] = $this->process_additional_settings();

        echo "<div class='wrap'>";
        echo "<h1>Algolia Indexing Control</h1>";
        // Output status message if set
        $statusMessage ? PixelkeyAlgolia()->html->pixelkey_algolia_admin_notice($statusMessage, $classes) : '';
        $s_Message ? PixelkeyAlgolia()->html->pixelkey_algolia_admin_notice($s_Message, $classe_s) : '';

        // Output forms
        $nonce = wp_nonce_field('run_index_nonce', '_wpnonce', true, false);
        PixelkeyAlgolia()->html->pixelkey_algolia_run_all_indexers($nonce);
        PixelkeyAlgolia()->html->pixelkey_algolia_run_indexer($indexerName, $nonce);
        PixelkeyAlgolia()->html->pixelkey_algolia_additional_settings($nonce);
        echo "</div>";
    }
    /**
     * Process the index action and return the result.
     * - If the action is 'run_all' or 'run_index', it runs the indexing process as a cron job.
     * - Triggers on click of the 'Run All Indexers' or 'Run $indexerName Indexer' buttons. on the admin page.
     *
     * @param string $indexerName The name of the indexer.
     * @return array The status message and classes.
     */
    public function processIndexAction($indexerName)
    {   // Initialize response array
        $response = array(
            'classes' => '',
            'message' => ''
        );

        $action = $_POST['action'] ?? '';
        if ($action === 'run_all' || $action === 'run_index') {
            $nonce = $_POST['_wpnonce'] ?? '';
            if ($this->verifyNonce($nonce, 'run_index_nonce') === false) {
                return [
                    'classes' => 'notice notice-error',
                    'message' => 'Security check failed.',
                ];
            }
            try {
                // Schedule a one-time event to run as soon as possible.
                $isScheduled = wp_schedule_single_event(time(), PixelkeyAlgolia()->settings->get_event_name());
                if (!$isScheduled || ($isScheduled instanceof WP_Error)) {
                    throw new \Exception('Unable to schedule the event.');
                }

                $message = ($action === 'run_all') ? 'Cron job is running for all indexers.' : "Cron job is running for $indexerName indexer.";
                $message .= " It takes a few minutes to complete. You can continue working on other tasks.";
                // Return a message to the user indicating that the cron job has been triggered.
                $response["message"] = $message;
                $response["classes"] = "notice notice-success";
                PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event($message);
            } catch (\Exception $e) {
                $response["message"] = "There has been a problem running the indexers.";
                $response["classes"] = "notice notice-error";
                PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event($e->getMessage());
            }
        }
        return $response;
    }

    /**
     * Process the additional settings form.
     *
     * @return array The status message and classes.
     */
    function process_additional_settings()
    {
        // Initialize response array
        $response = array(
            'classes' => '',
            'message' => ''
        );

        // Check if the form has been submitted
        if (
            isset($_POST['pixelkey_algolia_batch_size'])
            && isset($_POST['pixelkey_algolia_batch_interval'])
            && isset($_POST['pixelkey_algolia_cron_interval'])
        ) {
            $nonce = $_POST['_wpnonce'] ?? '';
            if ($this->verifyNonce($nonce, 'run_index_nonce') === false) {
                return [
                    'classes' => 'notice notice-error',
                    'message' => 'Security check failed.',
                ];
            }
            // Sanitize the input
            $batch_size = min(1000, max(50, intval($_POST['pixelkey_algolia_batch_size'])));
            $batch_interval = min(10, max(1, intval($_POST['pixelkey_algolia_batch_interval'])));
            $cron_interval = sanitize_text_field($_POST['pixelkey_algolia_cron_interval']);

            if (!in_array($cron_interval, ['twicedaily', 'daily', 'weekly'])) {
                new \Exception('Invalid cron interval.');
            }

            try {
                $is_updated = update_option('pixelkey_algolia_cron_interval', $cron_interval);
                update_option('pixelkey_algolia_batch_size', $batch_size);
                update_option('pixelkey_algolia_batch_interval', $batch_interval);

                if ($is_updated) {
                    $cron_event = PixelkeyAlgolia()->settings->get_event_name();
                    if (wp_next_scheduled($cron_event)) {
                        wp_clear_scheduled_hook($cron_event);
                    }
                    wp_schedule_event(time() + 5 * MINUTE_IN_SECONDS, $cron_interval, $cron_event);
                }
                $response['classes'] = 'updated';
                $response['message'] = 'Data saved successfully.';
            } catch (\Exception $e) {
                PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event($e->getMessage());
                return [
                    'classes' => 'notice notice-error',
                    'message' => 'There has been a problem saving the settings.',
                ];
            }
        }
        return $response;
    }
    /**
     * Add custom cron schedule intervals
     */
    public function add_pixelkey_algolia_cron_interval($schedules)
    {
        $intervals = [];
        array_push($intervals, PixelkeyAlgolia()->settings->get_batch_interval());

        foreach ($intervals as $interval) {
            $schedules["{$interval}min"] = [
                'interval' => $interval * MINUTE_IN_SECONDS,
                'display' => esc_html__("Every {$interval} Minutes"),
            ];
        }

        return $schedules;
    }
}
