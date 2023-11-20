<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;


/**
 * Class PixelkeyAlgoliaRun
 *
 * Thats where we bring the plugin to life
 *
 * @package		pixelkey-algolia
 * @subpackage	Classes/PixelkeyAlgoliaRun
 * @author		Pixel Key
 * @since		1.0.0
 */

class PixelkeyAlgoliaRun
{
    // Define the event name for the cron job
    private static $eventName = 'pixelkey_algolia/run_indexers';

    /**
     * Our PixelkeyAlgoliaRun constructor 
     * to run the plugin logic.
     *
     * @since 1.0.0
     */
    function __construct()
    {
        $this->add_hooks();
    }


    /**
     * Adds hooks for various actions and filters.
     *
     * @access	private
     * @since	1.0.0
     * @return	void
     */
    private function add_hooks()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_scripts_and_styles'), 20);
        add_action('admin_menu', array($this, 'register_custom_admin_menu_pages'), 20);

        add_action('save_post', [self::class, 'onPostSaveAndUpdate']);
        add_action('transition_post_status', [self::class, 'onPostStatusTransition'], 10, 3);

        add_action(self::$eventName, array(PixelkeyAlgolia()->runIndexers, 'runAsCron'));
        register_activation_hook(PIXELKEY_ALGOLIA_PLUGIN_FILE, array($this, 'activation_hook_callback'));
        register_deactivation_hook(PIXELKEY_ALGOLIA_PLUGIN_FILE, array($this, 'deactivate_hook_callback'));
    }


    /**
     * Enqueue the backend related scripts and styles for this plugin.
     * All of the added scripts andstyles will be available on every page within the backend.
     *
     * @access	public
     * @since	1.0.0
     *
     * @return	void
     */
    public function enqueue_backend_scripts_and_styles()
    {
        wp_enqueue_style('PIXELKEY_ALGOLIA_PLUGIN-backend-styles', PIXELKEY_ALGOLIA_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', array(), PIXELKEY_ALGOLIA_PLUGIN_VERSION, 'all');
    }

    /**
     * Add custom menu pages
     *
     * @access	public
     * @since	1.0.0
     *
     * @return	void
     */
    public function register_custom_admin_menu_pages()
    {
        add_options_page('Algolia Indexing', 'Algolia Indexing', 'manage_options', 'algolia-indexing', function () {
            echo "
                <h3>Algolia Indexing Control</h3>
                <div class='run-index__wrapper'>
            ";

            if (isset($_POST['action']) && $_POST['action'] === 'run_all') {
                try {
                    echo '<div class="run-index__status"><b>Running All Indexers</b>' . PHP_EOL;
                    PixelkeyAlgolia()->runIndexers::run();
                    foreach (RunIndexers::getIndexers() as $indexer) {
                        $indexerName = $indexer::DISPLAY_NAME;
                        echo "<div>Running $indexerName indexer... ✓</div>";
                    }
                    echo '</div>';
                } catch (\Exception $exception) {
                    echo $exception->getMessage();
                }
            }

            if (isset($_POST['action']) && $_POST['action'] === 'run_index') {
                try {
                    $indexerName = str_replace('\\\\', '\\', $_POST['index']);

                    $indexerClasses = [];

                    foreach (PixelkeyAlgolia()->runIndexers::getIndexers() as $indexer) {
                        $indexerClasses[] = get_class($indexer);
                    }

                    if (!in_array($indexerName, $indexerClasses)) {
                        throw new \Exception('Class does not exist as Indexer.');
                    }

                    $indexer = new $indexerName();
                    $indexer::index();

                    echo '<div class="run-index__status">Running the <b>' . $indexer::DISPLAY_NAME . '</b> indexer... ✓</div>';
                } catch (\Exception $exception) {
                    echo '<div class="run-index__failed-status">There has been a problem running the <b>' . $indexer::DISPLAY_NAME . '</b> indexer ❌</div>';
                    throw $exception; //Rethrow so it can be displayed or logged at the environments discretion
                }
            }

            echo "
                <form action='?page=algolia-indexing' method='post'>
                    <input type='hidden' name='page' value='algolia-indexing' />
                    <button class='button button-primary' name='action' value='run_all'>Run All Indexers</button><br /><br/>
                    " . wp_nonce_field() . "
                </form>
            ";

            foreach (PixelkeyAlgolia()->runIndexers::getIndexers() as $indexer) {
                $instance = $indexer;
                $indexerName = get_class($instance);

                echo "<form action='?page=algolia-indexing' method='post'>
                    <input type='hidden' name='page' value='algolia-indexing' />
                    <input type='hidden' name='index' value='$indexerName'>

                    <button class='button button-primary' name='action' value='run_index'>Run " . $instance::DISPLAY_NAME . " Indexer</button><br/><br />
                    " . wp_nonce_field() . "
                </form>";
            }

            echo "</div>";
        });
    }

    /**
     * ####################
     * ### Activation/Deactivation hooks
     * ####################
     */

    /*
	 * This function is called on activation of the plugin
	 *		- Register the cron job
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
    public function activation_hook_callback()
    {
        if (!wp_next_scheduled(self::$eventName)) {
            wp_schedule_event(time(), 'twicedaily', self::$eventName);
        }
    }

    /*
	 * This function is called on deactivation of the plugin
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
    public function deactivate_hook_callback()
    {
        if (wp_next_scheduled(self::$eventName)) {
            wp_clear_scheduled_hook(self::$eventName);
        }
    }

    /**
     * This method is called when a post is saved or updated.
     * It indexes the post using the appropriate indexers if the post type is supported and the post status is 'publish'.
     *
     * @param int $postId The ID of the post being saved or updated.
     * @return void
     */
    public static function onPostSaveAndUpdate($postId)
    {
        $indexers = PixelkeyAlgolia()->runIndexers::getIndexers();
        $post = get_post($postId);

        foreach ($indexers as $indexer) {
            if ($indexer::POST_TYPE === $post->post_type && $post->post_status === 'publish') {
                $indexer::index([$postId]);
            }
        }

        do_action('pixelkey_algolia:update_success');
    }

    /**
     * Handles the post status transition event.
     *
     * @param string $newStatus The new status of the post.
     * @param string $oldStatus The old status of the post.
     * @param WP_Post $post The post object.
     * @return void
     */
    public static function onPostStatusTransition($newStatus, $oldStatus, $post)
    {
        if ($newStatus == 'publish' || $newStatus == $oldStatus) return;

        $indexers = PixelkeyAlgolia()->runIndexers::getIndexers();
        $post = get_post($post->ID);

        foreach ($indexers as $indexer) {
            if ($indexer::POST_TYPE === $post->post_type) {
                $indexer::remove([$post->ID]);
            }
        }

        do_action('pixelkey_algolia:update_success');
    }
}
