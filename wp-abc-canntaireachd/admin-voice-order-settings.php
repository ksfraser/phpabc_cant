<?php
/**
 * WordPress Admin Page: Voice Ordering Settings
 * 
 * Provides UI for configuring voice ordering modes and custom voice order
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_submenu_page(
        'abc-canntaireachd',
        'Voice Order Settings',
        'Voice Order',
        'manage_options',
        'abc-voice-order-settings',
        'abc_voice_order_settings_page'
    );
});

function abc_voice_order_settings_page() {
    global $wpdb;
    
    /* Handle form submission */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['abc_voice_order_nonce'])) {
        if (!wp_verify_nonce($_POST['abc_voice_order_nonce'], 'abc_voice_order_settings')) {
            wp_die('Security check failed');
        }
        
        /* Save voice ordering mode */
        if (isset($_POST['voice_order_mode'])) {
            update_option('abc_voice_order_mode', sanitize_text_field($_POST['voice_order_mode']));
        }
        
        /* Save custom voice order */
        $custom_order = array();
        if (isset($_POST['custom_order']) && !empty($_POST['custom_order'])) {
            $lines = explode("\n", $_POST['custom_order']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $custom_order[] = sanitize_text_field($line);
                }
            }
        }
        update_option('abc_custom_voice_order', $custom_order);
        
        echo '<div class="updated"><p>Voice ordering settings saved.</p></div>';
    }
    
    /* Get current settings */
    $voice_order_mode = get_option('abc_voice_order_mode', 'source');
    $custom_order = get_option('abc_custom_voice_order', array());
    
    /* Get available voices from database */
    $table = $wpdb->prefix . 'abc_voice_names';
    $voices = $wpdb->get_results("SELECT voice_name, name FROM $table ORDER BY name", ARRAY_A);
    
    /* Get orchestral order from database */
    $order_table = $wpdb->prefix . 'abc_voice_order_defaults';
    $orchestral_order = $wpdb->get_results(
        "SELECT voice_name, sort_order FROM $order_table WHERE mode = 'orchestral' ORDER BY sort_order",
        ARRAY_A
    );
    
    ?>
    <div class="wrap">
        <h1>Voice Ordering Settings</h1>
        <p>Configure the order in which voices appear in ABC notation output.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('abc_voice_order_settings', 'abc_voice_order_nonce'); ?>
            
            <h2>Voice Ordering Mode</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Mode</th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="voice_order_mode" value="source" <?php checked($voice_order_mode, 'source'); ?>>
                                <strong>Source Order</strong> - Preserve original voice order from input
                            </label><br>
                            <p class="description">Voices appear in the same order as the input ABC file.</p>
                            
                            <label>
                                <input type="radio" name="voice_order_mode" value="orchestral" <?php checked($voice_order_mode, 'orchestral'); ?>>
                                <strong>Orchestral Order</strong> - Standard orchestral score order
                            </label><br>
                            <p class="description">Woodwinds → Brass → Percussion → Keyboards → Strings → Voices</p>
                            
                            <label>
                                <input type="radio" name="voice_order_mode" value="custom" <?php checked($voice_order_mode, 'custom'); ?>>
                                <strong>Custom Order</strong> - User-defined voice order
                            </label><br>
                            <p class="description">Define your own voice ordering below.</p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <h2>Custom Voice Order</h2>
            <p>Enter voice names in the desired order (one per line). Only used when Custom Order mode is selected.</p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Voice Order</th>
                    <td>
                        <textarea name="custom_order" rows="15" cols="40" class="large-text code"><?php 
                            echo esc_textarea(implode("\n", $custom_order)); 
                        ?></textarea>
                        <p class="description">
                            Enter voice names in order, one per line.<br>
                            Example: Piccolo, Flute, Oboe, Clarinet, etc.
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" class="button button-primary" value="Save Voice Order Settings">
            </p>
        </form>
        
        <hr>
        
        <h2>Standard Orchestral Order</h2>
        <p>When Orchestral Order mode is selected, voices are arranged in this order:</p>
        
        <?php if (!empty($orchestral_order)): ?>
            <ol style="column-count: 3; column-gap: 20px;">
                <?php foreach ($orchestral_order as $voice): ?>
                    <li><?php echo esc_html($voice['voice_name']); ?></li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p><em>No orchestral order defined in database. Default order:</em></p>
            <ol style="column-count: 3; column-gap: 20px;">
                <li>Piccolo</li>
                <li>Flute</li>
                <li>Oboe</li>
                <li>English Horn</li>
                <li>Clarinet</li>
                <li>Bass Clarinet</li>
                <li>Bassoon</li>
                <li>Contrabassoon</li>
                <li>Soprano Sax</li>
                <li>Alto Sax</li>
                <li>Tenor Sax</li>
                <li>Baritone Sax</li>
                <li>French Horn</li>
                <li>Trumpet</li>
                <li>Trombone</li>
                <li>Tuba</li>
                <li>Timpani</li>
                <li>Percussion</li>
                <li>Piano</li>
                <li>Harp</li>
                <li>Guitar</li>
                <li>Violin</li>
                <li>Viola</li>
                <li>Cello</li>
                <li>Double Bass</li>
                <li>Soprano</li>
                <li>Alto</li>
                <li>Tenor</li>
                <li>Bass</li>
            </ol>
        <?php endif; ?>
        
        <hr>
        
        <h2>Available Voices</h2>
        <p>These voices are defined in the database and can be used in custom ordering:</p>
        
        <?php if (!empty($voices)): ?>
            <ul style="column-count: 4; column-gap: 20px;">
                <?php foreach ($voices as $voice): ?>
                    <li><strong><?php echo esc_html($voice['name']); ?></strong><br>
                        <small><code><?php echo esc_html($voice['voice_name']); ?></code></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><em>No voices found. Please load the database schema.</em></p>
        <?php endif; ?>
    </div>
    <?php
}
