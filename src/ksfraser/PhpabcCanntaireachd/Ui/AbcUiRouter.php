<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Ui;

/**
 * Standalone HTTP router for the ABC Canntaireachd web UI.
 *
 * Single Responsibility: Route HTTP requests to the appropriate form handler.
 * No framework dependency — works with plain PHP.
 *
 * @package ksfraser\PhpabcCanntaireachd\Ui
 *
 * @since 1.0.0
 */
class AbcUiRouter
{
    /** @var AbcUploadForm */
    private AbcUploadForm $uploadForm;

    /** @var AbcDownloadForm */
    private AbcDownloadForm $downloadForm;

    /**
     * @param AbcUploadForm|null $uploadForm
     * @param AbcDownloadForm|null $downloadForm
     */
    public function __construct(
        ?AbcUploadForm $uploadForm = null,
        ?AbcDownloadForm $downloadForm = null
    ) {
        $this->uploadForm = $uploadForm ?? new AbcUploadForm();
        $this->downloadForm = $downloadForm ?? new AbcDownloadForm();
    }

    /**
     * Route the current HTTP request.
     *
     * @return void
     */
    public function route(): void
    {
        // Handle download first — it's a GET with ?download=...
        $this->downloadForm->processDownloadRequest();

        // Handle form submission
        $this->uploadForm = AbcUploadForm::handleSubmission();

        // Render the page
        $this->renderPage();
    }

    /**
     * Render the full HTML page.
     *
     * @return void
     */
    public function renderPage(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>';
        echo '<html lang="en"><head>';
        echo '<meta charset="utf-8" />';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';
        echo '<title>ABC Canntaireachd Converter</title>';
        echo '<style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; color: #333; }
            h1 { color: #2c3e50; }
            h2 { color: #34495e; border-bottom: 1px solid #ddd; padding-bottom: .5rem; }
            .form-group { margin-bottom: 1rem; }
            label { display: block; margin-bottom: .25rem; font-weight: 600; }
            .form-control { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
            textarea.form-control { min-height: 120px; font-family: monospace; }
            .btn { display: inline-block; padding: .5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 1rem; }
            .btn-primary { background: #3498db; color: #fff; }
            .btn-primary:hover { background: #2980b9; }
            .btn-secondary { background: #95a5a6; color: #fff; }
            .alert { padding: .75rem 1rem; border-radius: 4px; margin: 1rem 0; }
            .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; }
        </style>';
        echo '</head><body>';
        echo '<h1>ABC Canntaireachd Converter</h1>';
        echo '<p>Upload an ABC file or paste ABC notation to convert to canntaireachd.</p>';
        echo '<hr>';
        echo $this->uploadForm->render();
        echo '<hr>';
        echo '<p>Use the <code>php bin/abc-cannt-cli.php --file tune.abc --convert</code> CLI for batch processing.</p>';
        echo '</body></html>';
    }
}