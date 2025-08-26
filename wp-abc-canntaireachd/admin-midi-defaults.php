// Ensure WordPress helper functions are available in admin context
if (!function_exists('esc_html') || !function_exists('esc_attr') || !function_exists('sanitize_text_field')) {
    require_once(ABSPATH . 'wp-includes/formatting.php');
}
<?php
// WordPress admin screen for MIDI defaults
add_action('admin_menu', function() {
    add_menu_page('ABC MIDI Defaults', 'ABC MIDI Defaults', 'manage_options', 'abc-midi-defaults', 'abc_midi_defaults_admin');
});
function abc_midi_defaults_admin() {
    global $wpdb;
    $table = $wpdb->prefix . 'abc_midi_defaults';
    echo '<h1>ABC MIDI Defaults</h1>';
    // Handle add
    if (isset($_POST['add_midi_default'])) {
        $voice = sanitize_text_field($_POST['voice_name']);
        $channel = intval($_POST['midi_channel']);
        $program = intval($_POST['midi_program']);
        $wpdb->insert($table, [
            'voice_name' => $voice,
            'midi_channel' => $channel,
            'midi_program' => $program
        ]);
        echo '<div class="updated"><p>Added MIDI default.</p></div>';
    }
    // Handle delete
    if (isset($_POST['delete_midi_default'])) {
        $id = intval($_POST['delete_id']);
        $wpdb->delete($table, ['id' => $id]);
        echo '<div class="updated"><p>Deleted MIDI default.</p></div>';
    }
    // Handle edit
    if (isset($_POST['edit_midi_default'])) {
        $id = intval($_POST['edit_id']);
        $voice = sanitize_text_field($_POST['edit_voice_name']);
        $channel = intval($_POST['edit_midi_channel']);
        $program = intval($_POST['edit_midi_program']);
        $wpdb->update($table, [
            'voice_name' => $voice,
            'midi_channel' => $channel,
            'midi_program' => $program
        ], ['id' => $id]);
        echo '<div class="updated"><p>Updated MIDI default.</p></div>';
    }
    // List table contents
    $results = $wpdb->get_results("SELECT * FROM $table");
    echo '<table><tr><th>Voice</th><th>Channel</th><th>Program</th><th>Actions</th></tr>';
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>'.esc_html($row->voice_name).'</td>';
        echo '<td>'.esc_html($row->midi_channel).'</td>';
        echo '<td>'.esc_html($row->midi_program).'</td>';
        // Edit form
        echo '<td>';
        echo '<form method="post" style="display:inline-block; margin-right:5px;">';
        echo '<input type="hidden" name="edit_id" value="'.intval($row->id).'">';
        echo '<input type="text" name="edit_voice_name" value="'.esc_attr($row->voice_name).'" size="8">';
        echo '<input type="number" name="edit_midi_channel" value="'.esc_attr($row->midi_channel).'" min="0" max="16" size="4">';
        echo '<input type="number" name="edit_midi_program" value="'.esc_attr($row->midi_program).'" min="0" max="127" size="4">';
        echo '<input type="submit" name="edit_midi_default" value="Update">';
        echo '</form>';
        // Delete form
        echo '<form method="post" style="display:inline-block;">';
        echo '<input type="hidden" name="delete_id" value="'.intval($row->id).'">';
        echo '<input type="submit" name="delete_midi_default" value="Delete" onclick="return confirm(\'Delete this MIDI default?\');">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    // Add form
    echo '<h2>Add MIDI Default</h2>';
    echo '<form method="post">';
    echo '<label>Voice Name: <input type="text" name="voice_name" required></label> ';
    echo '<label>Channel: <input type="number" name="midi_channel" min="0" max="16" required></label> ';
    echo '<label>Program: <input type="number" name="midi_program" min="0" max="127" required></label> ';
    echo '<input type="submit" name="add_midi_default" value="Add">';
    echo '</form>';
    if (isset($_POST['validate_abc'])) {
        $abcFile = $_FILES['abc_file']['tmp_name'];
        $abcName = $_FILES['abc_file']['name'];
        $abcContent = file_get_contents($abcFile);
        $dict = include dirname(__FILE__) . '/../src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
        $result = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abcContent, $dict);
        $output = $result['lines'];
        $canntDiff = $result['canntDiff'];
        $newName = preg_replace('/\.abc$/', '_1.abc', $abcName);
        $upload = function_exists('wp_upload_dir') ? wp_upload_dir() : ['path' => __DIR__, 'url' => ''];
        $savePath = $upload['path'] . '/' . $newName;
        file_put_contents($savePath, implode("\n", $output));
        $links = [];
        $links[] = '<a href="' . $upload['url'] . '/' . $newName . '" target="_blank">ABC File</a>';
        if ($canntDiff) {
            $diffName = 'cannt_diff.txt';
            file_put_contents($upload['path'] . '/' . $diffName, implode("\n", $canntDiff));
            $links[] = '<a href="' . $upload['url'] . '/' . $diffName . '" target="_blank">Canntaireachd Diff</a>';
        }
        if ($result['errors'] ?? false) {
            $errName = 'abc_errors.txt';
            file_put_contents($upload['path'] . '/' . $errName, implode("\n", $result['errors']));
            $links[] = '<a href="' . $upload['url'] . '/' . $errName . '" target="_blank">Error Log</a>';
        }
        echo '<div class="updated">' . implode(' | ', $links) . '</div>';
    }
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="abc_file" />';
    echo '<input type="submit" name="validate_abc" value="Validate & Save" />';
    echo '</form>';
}
