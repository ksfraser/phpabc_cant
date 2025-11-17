<?php
/**
 * WordPress Admin Page: Transpose Settings
 * 
 * Provides UI for configuring transpose modes and per-voice overrides
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_submenu_page(
        'abc-canntaireachd',
        'Transpose Settings',
        'Transpose',
        'manage_options',
        'abc-transpose-settings',
        'abc_transpose_settings_page'
    );
});

function abc_transpose_settings_page() {
    global $wpdb;
    
    /* Handle form submission */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['abc_transpose_nonce'])) {
        if (!wp_verify_nonce($_POST['abc_transpose_nonce'], 'abc_transpose_settings')) {
            wp_die('Security check failed');
        }
        
        /* Save transpose mode */
        if (isset($_POST['transpose_mode'])) {
            update_option('abc_transpose_mode', sanitize_text_field($_POST['transpose_mode']));
        }
        
        /* Save per-voice overrides */
        $overrides = array();
        if (isset($_POST['voice_overrides']) && is_array($_POST['voice_overrides'])) {
            foreach ($_POST['voice_overrides'] as $voice => $transpose) {
                if ($transpose !== '' && $transpose !== null) {
                    $overrides[sanitize_text_field($voice)] = intval($transpose);
                }
            }
        }
        update_option('abc_transpose_overrides', $overrides);
        
        /* Update database voice defaults */
        if (isset($_POST['update_db_defaults']) && $_POST['update_db_defaults'] === '1') {
            $table = $wpdb->prefix . 'abc_voice_names';
            foreach ($overrides as $voice => $transpose) {
                $wpdb->update(
                    $table,
                    array('transpose' => $transpose),
                    array('voice_name' => $voice),
                    array('%d'),
                    array('%s')
                );
            }
        }
        
        echo '<div class="updated"><p>Transpose settings saved.</p></div>';
    }
    
    /* Get current settings */
    $transpose_mode = get_option('abc_transpose_mode', 'midi');
    $overrides = get_option('abc_transpose_overrides', array());
    
    /* Get available voices from database */
    $table = $wpdb->prefix . 'abc_voice_names';
    $voices = $wpdb->get_results("SELECT voice_name, name, transpose, octave FROM $table ORDER BY name", ARRAY_A);
    
    ?>
    <div class="wrap">
        <h1>Transpose Settings</h1>
        <p>Configure how instruments are transposed in ABC notation output.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('abc_transpose_settings', 'abc_transpose_nonce'); ?>
            
            <h2>Transpose Mode</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Mode</th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="transpose_mode" value="midi" <?php checked($transpose_mode, 'midi'); ?>>
                                <strong>MIDI Mode</strong> - All instruments at concert pitch (transpose=0)
                            </label><br>
                            <p class="description">Use for ABC files imported from MIDI or audio. All instruments sound as written.</p>
                            
                            <label>
                                <input type="radio" name="transpose_mode" value="bagpipe" <?php checked($transpose_mode, 'bagpipe'); ?>>
                                <strong>Bagpipe Mode</strong> - Bagpipes at written pitch, others +2 semitones
                            </label><br>
                            <p class="description">Highland bagpipes sound Bb when written in A. Other instruments transpose up a whole step.</p>
                            
                            <label>
                                <input type="radio" name="transpose_mode" value="orchestral" <?php checked($transpose_mode, 'orchestral'); ?>>
                                <strong>Orchestral Mode</strong> - Standard orchestral transpositions
                            </label><br>
                            <p class="description">Use standard transposing instrument values (Bb=+2, Eb=+9, F=+7).</p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <h2>Per-Voice Transpose Overrides</h2>
            <p>Override the transpose value for specific voices. Leave blank to use the mode default.</p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="30%">Voice Name</th>
                        <th width="20%">Current Transpose</th>
                        <th width="20%">Override Transpose</th>
                        <th width="30%">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($voices)): ?>
                        <?php foreach ($voices as $voice): ?>
                            <tr>
                                <td><strong><?php echo esc_html($voice['name']); ?></strong><br>
                                    <small><?php echo esc_html($voice['voice_name']); ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $current_transpose = intval($voice['transpose']);
                                    echo $current_transpose;
                                    if ($current_transpose > 0) {
                                        echo ' (+' . $current_transpose . ' semitones)';
                                    } elseif ($current_transpose < 0) {
                                        echo ' (' . $current_transpose . ' semitones)';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="voice_overrides[<?php echo esc_attr($voice['voice_name']); ?>]" 
                                           value="<?php echo isset($overrides[$voice['voice_name']]) ? esc_attr($overrides[$voice['voice_name']]) : ''; ?>"
                                           min="-12" 
                                           max="12" 
                                           step="1"
                                           placeholder="<?php echo $current_transpose; ?>"
                                           style="width: 80px;">
                                </td>
                                <td>
                                    <small>
                                        <?php
                                        $transpose = intval($voice['transpose']);
                                        if ($transpose === 0) {
                                            echo 'Concert pitch';
                                        } elseif ($transpose === 2) {
                                            echo 'Bb instrument';
                                        } elseif ($transpose === 9) {
                                            echo 'Eb instrument';
                                        } elseif ($transpose === 7) {
                                            echo 'F instrument';
                                        } else {
                                            echo 'Custom transpose';
                                        }
                                        ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No voices found. Please load the database schema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <label>
                    <input type="checkbox" name="update_db_defaults" value="1">
                    Update database defaults (saves overrides permanently)
                </label><br>
                <input type="submit" class="button button-primary" value="Save Transpose Settings">
            </p>
        </form>
        
        <hr>
        
        <h2>Transpose Reference</h2>
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th>Instrument Type</th>
                    <th>Transpose Value</th>
                    <th>Examples</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Concert Pitch</strong></td>
                    <td>0</td>
                    <td>Piano, Flute, Violin, Viola, Cello, Trombone, Strings</td>
                </tr>
                <tr>
                    <td><strong>Bb Instruments</strong></td>
                    <td>+2 semitones</td>
                    <td>Trumpet, Clarinet, Tenor Sax, Soprano Sax</td>
                </tr>
                <tr>
                    <td><strong>Eb Instruments</strong></td>
                    <td>+9 semitones</td>
                    <td>Alto Sax, Baritone Sax, Eb Clarinet</td>
                </tr>
                <tr>
                    <td><strong>F Instruments</strong></td>
                    <td>+7 semitones</td>
                    <td>French Horn, English Horn</td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}
