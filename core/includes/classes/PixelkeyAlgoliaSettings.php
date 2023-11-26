<?php

// Exit if accessed directly.
if (!defined('ABSPATH'))
    exit;

/**
 * HELPER COMMENT START
 * 
 * This class contains all of the plugin related settings.
 * Everything that is relevant data and used multiple times throughout 
 * the plugin.
 * 
 * To define the actual values, we recommend adding them as shown above
 * within the __construct() function as a class-wide variable. 
 * This variable is then used by the callable functions down below. 
 * These callable functions can be called everywhere within the plugin 
 * as followed using the get_plugin_name() as an example: 
 * 
 * PixelkeyAlgolia->settings->get_plugin_name();
 * 
 * HELPER COMMENT END
 */

/**
 * Class PixelkeyAlgoliaSettings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package		pixelkey-algolia
 * @subpackage	Classes/PixelkeyAlgoliaSettings
 * @author		Pixel Key
 * @since		1.0.0
 */
class PixelkeyAlgoliaSettings
{

    /**
     * The plugin name
     *
     * @var		string
     * @since   1.0.0
     */
    private $plugin_name;
    private $eventName;
    private $daisyChainEvent;
    private $pageIndex;
    /**
     * Our Pixel_Tooltips_Settings constructor 
     * to run the plugin logic.
     *
     * @since 1.0.0
     */
    function __construct()
    {
        $this->plugin_name = PIXELKEY_ALGOLIA_PLUGIN_NAME;
        $this->eventName = 'pixelkey_algolia/run_indexers';
        $this->daisyChainEvent = 'pixelkey_algolia_run_daisychain_indexers';
        $this->pageIndex = 'last_processed_page_number';
    }

    /**
     * ######################
     * ###
     * #### CALLABLE FUNCTIONS
     * ###
     * ######################
     */

    /** Set Default Value options on plugin activation
     * @access	public
     * @since	1.0.3
     * @return	void
     */
    public function set_default_options()
    {
        add_option('pixelkey_algolia_cron_interval', 'twicedaily');
        add_option('pixelkey_algolia_batch_size', 100);
        add_option('pixelkey_algolia_batch_interval', 1);
        add_option($this->pageIndex, 1);
    }

    /** Deletes all options on plugin deactivation
     * @access	public
     * @since	1.0.3
     * @return	void
     */
    public function delete_all_options()
    {
        delete_option('pixelkey_algolia_cron_interval');
        delete_option('pixelkey_algolia_batch_size');
        delete_option('pixelkey_algolia_batch_interval');
        delete_option($this->pageIndex);
    }

    /**
     * Return the batch size
     * Default batch size is 100
     * @access	public
     * @since	1.0.3
     * @return	int The batch size
     */
    public function get_batch_size()
    {
        return get_option('pixelkey_algolia_batch_size', 100);
    }

    /** 
     * Return batch interval
     * Default batch interval is 1 minute
     * @access	public
     * @since	1.0.3
     * @return	int The batch interval
     */
    public function get_batch_interval()
    {
        return get_option('pixelkey_algolia_batch_interval', 1);
    }
    /**
     * Return the last processed page number
     * @access	public
     * @since	1.0.3
     * @return	int The last processed page number
     */
    public function get_last_processed_page_number()
    {
        return get_option($this->pageIndex, 1);
    }

    /** 
     * Update/Delete the last processed page number
     * @access	public
     * @since	1.0.3
     * @param	int $number The last processed page number
     * @param	string $mode The mode of the update (update/delete)
     * @return	void
     */
    public function update_last_processed_page_number($number, $mode = 'update')
    {
        $mode == 'update' ? update_option($this->pageIndex, $number) : delete_option($this->pageIndex);
    }
    /**
     * Return the event name
     * @access	public
     * @since	1.0.0
     * @return	string The event name
     */
    public function get_event_name()
    {
        return $this->eventName;
    }

    /**
     * Return the daisy chain event name
     * @access	public
     * @since	1.0.2
     * @return	string The daisy chain event name
     */
    public function get_daisyChainEvent()
    {
        return $this->daisyChainEvent;
    }
    /**
     * Return the plugin name
     *
     * @access	public
     * @since	1.0.0
     * @return	string The plugin name
     */
    public function get_plugin_name()
    {
        return apply_filters('PIXELKEY_ALGOLIA/settings/get_plugin_name', $this->plugin_name);
    }
}