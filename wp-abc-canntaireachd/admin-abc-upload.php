<?php
// WordPress admin: ABC file upload and processing
add_action('admin_menu', function() {
    add_menu_page('ABC Canntaireachd Upload', 'ABC Upload', 'manage_options', 'abc-upload', 'abc_upload_page');
});
function abc_upload_page() {
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/abc_canntaireachd/';
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $files = [];
    if (!empty($_FILES['abc_files']['name'][0])) {
        foreach ($_FILES['abc_files']['tmp_name'] as $idx => $tmp_name) {
            $name = basename($_FILES['abc_files']['name'][$idx]);
            $target_file = $target_dir . $name;
            move_uploaded_file($tmp_name, $target_file);
            $files[] = $target_file;
        }
        echo '<div class="updated"><p>Uploaded: ' . implode(', ', array_map('basename', $files)) . '</p></div>';
    }
    // Process files
    $processed = [];
    foreach (glob($target_dir . '*.abc') as $abcFile) {
        $abcContent = file_get_contents($abcFile);
        $dict = include dirname(__DIR__) . '/src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
        $result = Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abcContent, $dict);
        $newFile = preg_replace('/\.abc$/', '_1.abc', $abcFile);
        file_put_contents($newFile, implode("\n", $result['lines']));
        $processed[] = $newFile;
        // Save diff/errors if present
        if ($result['canntDiff']) {
            $diffFile = preg_replace('/\.abc$/', '_diff.txt', $abcFile);
            file_put_contents($diffFile, implode("\n", $result['canntDiff']));
            $processed[] = $diffFile;
        }
        if ($result['errors']) {
            $errFile = preg_replace('/\.abc$/', '_errors.txt', $abcFile);
            file_put_contents($errFile, implode("\n", $result['errors']));
            $processed[] = $errFile;
        }
    }
    // List files for download
    echo '<h2>ABC Files</h2><form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="abc_files[]" multiple><input type="submit" value="Upload">';
    echo '</form><ul>';
    foreach (glob($target_dir . '*') as $file) {
        $url = $upload_dir['baseurl'] . '/abc_canntaireachd/' . basename($file);
        echo '<li><a href="' . esc_url($url) . '" download>' . esc_html(basename($file)) . '</a></li>';
    }
    echo '</ul>';
}
