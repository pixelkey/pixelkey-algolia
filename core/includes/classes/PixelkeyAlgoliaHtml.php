<?php

// Exit if accessed directly.
if (!defined('ABSPATH'))
    exit;

/**
 * Class PixelkeyAlgoliaHtml
 *
 * This class contains functions that are used to output HTML.
 * are used globally within the plugin.
 *
 * @package		pixelkey-algolia
 * @subpackage	Classes/PixelkeyAlgoliaHtml
 * @author		Pixel Key
 * @since		1.0.0
 */
class PixelkeyAlgoliaHtml
{

    /**
     * Within this class, you can define common functions that helps to output HTML.
     * 
     * To access this function from any other class, you can call it as followed:
     * PixelkeyAlgolia()->html->function_name( 'my text' );
     */

    /**
     * ####################
     * ### Functions Responsible HTML Output ###
     * ####################
     */
    public function pixelkey_algolia_admin_notice($html, $classes)
    {
        $classes .= " index__status";
        echo "<div class='$classes'>";
        echo "<p><strong>$html</strong></p>";
        echo "</div>";
    }
    /**
     * Render the admin menu page HTML for Additional Settings.
     */
    public function pixelkey_algolia_additional_settings($nonceField)
    {
        ?>
        <form method="post">
            <table class="form-table">
                <thead>
                    <th>Additional Settings</th>
                </thead>
                <tr valign="top">
                    <th scope="row">Re_Index CRON Interval</th>
                    <td>
                        <select name="pixelkey_algolia_cron_interval">
                            <option value="twicedaily" <?php selected(get_option('pixelkey_algolia_cron_interval'), 'twicedaily'); ?>>Twice Daily</option>
                            <option value="daily" <?php selected(get_option('pixelkey_algolia_cron_interval'), 'daily'); ?>>
                                Daily</option>
                            <option value="weekly" <?php selected(get_option('pixelkey_algolia_cron_interval'), 'weekly'); ?>>
                                Weekly (since WP 5.4) </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Batch Size
                        <i class="dashicons dashicons-editor-help"
                            title="The number of posts indexed at a time should fall within the recommended batch size of 50 to 1000."></i>
                    </th>
                    <td>
                        <input type="number" name="pixelkey_algolia_batch_size" value="<?php echo esc_attr(get_option('pixelkey_algolia_batch_size')); ?>"
                            min="50" max="1000" size="50" placeholder="100" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Batch Processing Interval (in minutes)
                        <i class="dashicons dashicons-editor-help"
                            title="The time interval between one batch processing and the next should not be less than 1 minute or more than 10 minutes."></i>
                    </th>
                    <td>
                        <input type="number" name="pixelkey_algolia_batch_interval" value="<?php echo esc_attr(get_option('pixelkey_algolia_batch_interval')); ?>"
                            min="1" max="10" size="50" placeholder="1" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
            <?php echo $nonceField; ?>
        </form>
        <?php
    }
    /** 
     * Render the admin menu page HTML for Indexing all the indexers.
     */
    public function pixelkey_algolia_run_all_indexers($nonceField)
    {

        echo "<form action='?page=algolia-indexing' method='post'>
            <button class='button button-primary' name='action' value='run_all'>Run All Indexers</button>
            $nonceField
        </form></br>";
    }

    /** Render the html form for rending run <index_name> indexer
     * @param string $indexerName The name of the indexer.
     * @param string $nonceField The nonce field.
     */
    public function pixelkey_algolia_run_indexer($indexerName, $nonceField)
    {
        echo "<form action='?page=algolia-indexing' method='post'>
            <button class='button button-primary' name='action' value='run_index'>Run $indexerName Indexer</button>
            $nonceField
        </form></br>";
    }
}
