<?php
// WordPress admin screen for MIDI defaults
add_action('admin_menu', function() {
    add_menu_page('ABC MIDI Defaults', 'ABC MIDI Defaults', 'manage_options', 'abc-midi-defaults', 'abc_midi_defaults_admin');
});
function abc_midi_defaults_admin() {
    global $wpdb;
    $table = $wpdb->prefix . 'abc_midi_defaults';
    // Handle add/edit/delete here
    echo '<h1>ABC MIDI Defaults</h1>';
    // List table contents
    $results = $wpdb->get_results("SELECT * FROM $table");
    echo '<table><tr><th>Voice</th><th>Channel</th><th>Program</th></tr>';
    foreach ($results as $row) {
        echo '<tr><td>'.esc_html($row->voice_name).'</td><td>'.esc_html($row->midi_channel).'</td><td>'.esc_html($row->midi_program).'</td></tr>';
    }
    echo '</table>';
    // Add/edit/delete forms would go here
}
