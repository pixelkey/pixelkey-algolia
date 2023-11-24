<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

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
        echo "<div class='<?php echo $classes?>'>";
        echo "<p><?php echo $html?></p>";
        echo "</div>";
    }
    /**
     * Render the admin menu page HTML
     */
    public function pixelkey_algolia_admin_menu_page_render()
    {
?>
        <div class="wrap">
            <h1>Additional Settings</h1>
            <form method="post">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Batch Size</th>
                        <td>
                            <input type="text" name="batch_size" value="<?php echo esc_attr(get_option('batch_size')); ?>" size="50" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
<?php
    }
}
