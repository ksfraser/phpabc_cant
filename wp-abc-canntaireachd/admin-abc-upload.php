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
    // List files for download and add renumber button
    echo '<h2>ABC Files</h2><form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="abc_files[]" multiple><input type="submit" value="Upload">';
    echo '</form>';
    // Renumber form
    echo '<form method="post"><input type="hidden" name="renumber" value="1">';
    echo '<select name="renumber_file">';
    foreach (glob($target_dir . '*.abc') as $file) {
        echo '<option value="' . htmlspecialchars($file) . '">' . htmlspecialchars(basename($file)) . '</option>';
    }
    echo '</select> <input type="submit" value="Renumber Duplicated Tune Numbers"></form>';
    echo '<form method="post"><input type="hidden" name="reorder" value="1">';
    echo '<select name="reorder_file">';
    foreach (glob($target_dir . '*.abc') as $file) {
        echo '<option value="' . htmlspecialchars($file) . '">' . htmlspecialchars(basename($file)) . '</option>';
    }
    echo '</select> <input type="submit" value="Reorder Tunes by X Number"></form>';
    echo '<ul>';
    foreach (glob($target_dir . '*') as $file) {
        $url = $upload_dir['baseurl'] . '/abc_canntaireachd/' . basename($file);
        echo '<li><a href="' . $url . '" download>' . htmlspecialchars(basename($file)) . '</a></li>';
    }
    echo '</ul>';
    // Handle renumber request
    if (!empty($_POST['renumber']) && !empty($_POST['renumber_file'])) {
        $file = $_POST['renumber_file'];
        $cmd = 'php ' . escapeshellarg(__DIR__ . '/../bin/abc-renumber-tunes-cli.php') . ' ' . escapeshellarg($file);
        $output = shell_exec($cmd);
        echo '<div class="updated"><pre>' . htmlspecialchars($output) . '</pre></div>';
        // Show link to renumbered file
        $renumFile = $file . '.renumbered';
        if (file_exists($renumFile)) {
            $url = $upload_dir['baseurl'] . '/abc_canntaireachd/' . basename($renumFile);
            echo '<div class="updated"><a href="' . $url . '" download>Download Renumbered File</a></div>';
        }
    }
    // Handle reorder request
    if (!empty($_POST['reorder']) && !empty($_POST['reorder_file'])) {
        $file = $_POST['reorder_file'];
        $cmd = 'php ' . escapeshellarg(__DIR__ . '/../bin/abc-reorder-tunes-cli.php') . ' ' . escapeshellarg($file);
        $output = shell_exec($cmd);
        echo '<div class="updated"><pre>' . htmlspecialchars($output) . '</pre></div>';
        // Show link to reordered file
        $reorderFile = $file . '.reordered';
        if (file_exists($reorderFile)) {
            $url = $upload_dir['baseurl'] . '/abc_canntaireachd/' . basename($reorderFile);
            echo '<div class="updated"><a href="' . $url . '" download>Download Reordered File</a></div>';
        }
    }
}
