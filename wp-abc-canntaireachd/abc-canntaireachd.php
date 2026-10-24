<?php
/**
 * Plugin Name: ABC Canntaireachd Tools
 * Description: Upload, validate, and convert ABC notation files with canntaireachd support.
 * Version: 1.0.0
 * Author: ksfraser
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/../vendor/autoload.php';
use ksfraser\PhpabcCanntaireachd\Ui\AbcUploadForm;
use ksfraser\PhpabcCanntaireachd\Ui\AbcDownloadForm;

/* Load admin pages */
require_once __DIR__ . '/admin-transpose-settings.php';
require_once __DIR__ . '/admin-voice-order-settings.php';

add_action('admin_menu', function() {
    add_menu_page('ABC Canntaireachd', 'ABC Canntaireachd', 'manage_options', 'abc-canntaireachd', 'abc_canntaireachd_admin');
});

function abc_canntaireachd_admin() {
    $uploadForm = new AbcUploadForm();
    $downloadForm = new AbcDownloadForm();

    // Handle download request first (GET with ?download=...)
    if (isset($_GET['download'])) {
        $downloadForm->processedContent = rawurldecode($_GET['download']);
        if ($downloadForm->processedContent !== '') {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="converted.abc"');
            header('Content-Length: ' . strlen($downloadForm->processedContent));
            echo $downloadForm->processedContent;
            exit;
        }
    }

    // Handle form submission
    $uploadForm = AbcUploadForm::handleSubmission();

    echo '<h1>ABC Canntaireachd Tools</h1>';

    // Display errors
    if (!empty($uploadForm->errorMessage)) {
        echo '<div class="notice notice-danger is-dismissible"><p>' . esc_html($uploadForm->errorMessage) . '</p></div>';
    }

    // Show upload form
    echo '<div class="wrap">';
    echo $uploadForm->render();
    echo '</div>';

    // Show download section if we have processed content
    if (!empty($uploadForm->processedData) && isset($uploadForm->processedData['abc_content'])) {
        echo '<hr>';
        echo '<h3>Processed ABC</h3>';
        echo $downloadForm->render();
    }
}