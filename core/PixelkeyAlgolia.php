<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * HELPER COMMENT START
 * 
 * This is the main class that is responsible for registering
 * the core functions, including the files and setting up all features. 
 * 
 * To add a new class, here's what you need to do: 
 * 1. Add your new class within the following folder: core/includes/classes
 * 2. Create a new variable you want to assign the class to (as e.g. public $runIndexers)
 * 3. Assign the class within the instance() function ( as e.g. self::$instance->runIndexers = new PixelkeyAlgolia();)
 * 4. Register the class you added to core/includes/classes within the includes() function
 * 
 * HELPER COMMENT END
 */

if (!class_exists('PixelkeyAlgolia')) :

    /**
     * Main PixelkeyAlgolia Class.
     *
     * @package		pixelkey-algolia
     * @subpackage	Classes/PixelkeyAlgolia
     * @since		1.0.0
     * @author		Pixel Key
     */
    final class PixelkeyAlgolia
    {

        /**
         * The real instance
         *
         * @access	private
         * @since	1.0.0
         * @var		object|PixelkeyAlgolia
         */
        private static $instance;

        /**
         * pixelkey-algolia runIndexers object.
         *
         * @access	public
         * @since	1.0.0
         * @var		object|RunIndexers
         */
        public $runIndexers;

        /**
         * Throw error on object clone.
         *
         * Cloning instances of the class is forbidden.
         *
         * @access	public
         * @since	1.0.0
         * @return	void
         */
        public function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to clone this class.', 'pixelkey-algolia'), '1.0.1');
        }

        /**
         * Disable unserializing of the class.
         *
         * @access	public
         * @since	1.0.0
         * @return	void
         */
        public function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to unserialize this class.', 'pixelkey-algolia'), '1.0.1');
        }

        /**
         * Main PixelkeyAlgolia Instance.
         *
         * Insures that only one instance of PixelkeyAlgolia exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @access		public
         * @since		1.0.0
         * @static
         * @return		object|PixelkeyAlgolia	The one true PixelkeyAlgolia
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof PixelkeyAlgolia)) {
                self::$instance                    = new PixelkeyAlgolia;
                self::$instance->base_hooks();
                self::$instance->includes();
                self::$instance->runIndexers      = new RunIndexers();

                //Fire the plugin logic
                new PixelkeyAlgoliaRun();

                /**
                 * Fire a custom action to allow dependencies
                 * after the successful plugin setup
                 */
                do_action('PixelkeyAlgolia/plugin_loaded');
            }

            return self::$instance;
        }

        /**
         * Include required files.
         *
         * @access  private
         * @since   1.0.0
         * @return  void
         */
        private function includes()
        {
            require_once PIXELKEY_ALGOLIA_PLUGIN_DIR . 'core/includes/classes/CommandInterface.php';
            require_once PIXELKEY_ALGOLIA_PLUGIN_DIR . 'core/includes/classes/IndexerAbstract.php';
            require_once PIXELKEY_ALGOLIA_PLUGIN_DIR . 'core/includes/classes/PixelkeyAlgoliaRun.php';
            require_once PIXELKEY_ALGOLIA_PLUGIN_DIR . 'core/includes/classes/RunIndexers.php';
        }
        /**
         * Add base hooks for the core functionality
         *
         * @access  private
         * @since   1.0.0
         * @return  void
         */
        private function base_hooks()
        {
            add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
        }

        /**
         * Loads the plugin language files.
         *
         * @access  public
         * @since   1.0.0
         * @return  void
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('pixelkey-algolia', FALSE, dirname(plugin_basename(PIXELKEY_ALGOLIA_PLUGIN_FILE)) . '/languages/');
        }
    }

endif; // End if class_exists check.