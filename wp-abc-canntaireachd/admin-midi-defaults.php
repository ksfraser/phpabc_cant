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
