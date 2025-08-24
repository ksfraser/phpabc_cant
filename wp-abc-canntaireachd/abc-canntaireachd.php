<?php
/**
 * Plugin Name: ABC Canntaireachd Tools
 * Description: Upload, validate, and convert ABC notation files with canntaireachd support.
 * Version: 0.1.0
 * Author: ksfraser
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcParser;

add_action('admin_menu', function() {
    add_menu_page('ABC Canntaireachd', 'ABC Canntaireachd', 'manage_options', 'abc-canntaireachd', 'abc_canntaireachd_admin');
});

function abc_canntaireachd_admin() {
    echo '<h1>ABC Canntaireachd Tools</h1>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="abc_file" /> <input type="submit" value="Upload & Validate" />';
    echo '</form>';
    if (!empty($_FILES['abc_file']['tmp_name'])) {
        $abcContent = file_get_contents($_FILES['abc_file']['tmp_name']);
        $parser = new AbcParser();
        // Example validation output
        // (Replace with real validation logic)
        echo '<pre>File processed. (Example output)</pre>';
    }
    echo '<hr><form method="post"><textarea name="abc_text" rows="10" cols="80"></textarea><br><input type="submit" value="Validate Text" /></form>';
    if (!empty($_POST['abc_text'])) {
        $abcContent = $_POST['abc_text'];
        $parser = new AbcParser();
        // Example validation output
        // (Replace with real validation logic)
        echo '<pre>Text processed. (Example output)</pre>';
    }
}
