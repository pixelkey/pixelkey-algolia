<?php

// Exit if accessed directly.
if (!defined('ABSPATH'))
    exit;


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

        add_action('save_post', array(PixelkeyAlgolia()->helpers, 'onPostSaveAndUpdate'), 10, 3);

        add_action(PixelkeyAlgolia()->settings->get_event_name(), array(PixelkeyAlgolia()->helpers, 'runAsCron'));
        add_action(PixelkeyAlgolia()->settings->get_daisyChainEvent(), array(PixelkeyAlgolia()->helpers, 'runAsCron'));
        add_filter('cron_schedules', array(PixelkeyAlgolia()->helpers, 'add_pixelkey_algolia_cron_interval'));
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
        add_options_page('Algolia Indexing', 'Algolia Indexing', 'manage_options', 'algolia-indexing', array(PixelkeyAlgolia()->helpers, 'pixelkey_algolia_admin_menu_page_callback'));
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
        $cron_event = PixelkeyAlgolia()->settings->get_event_name();
        if (!wp_next_scheduled($cron_event)) {
            wp_schedule_event(time(), 'twicedaily', $cron_event);
        }
        PixelkeyAlgolia()->settings->set_default_options();
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
        $cron_event = PixelkeyAlgolia()->settings->get_event_name();
        if (wp_next_scheduled($cron_event)) {
            wp_clear_scheduled_hook($cron_event);
        }
        PixelkeyAlgolia()->settings->delete_all_options();
    }
}
