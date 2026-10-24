<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Ui;

/**
 * Generates the ABC file download form HTML and handles download submission.
 *
 * Single Responsibility: Download handling — generating the download HTML
 * and managing the HTTP response for file downloads.
 *
 * @package ksfraser\PhpabcCanntaireachd\Ui
 *
 * @since 1.0.0
 */
class AbcDownloadForm
{
    /** @var string|null The processed ABC content to download */
    private ?string $processedContent = null;

    /** @var string|null Content-Type header value */
    private ?string $contentType = 'application/octet-stream';

    /** @return string|null */
    public function getProcessedContent(): ?string
    {
        return $this->processedContent;
    }

    /** @param string $content */
    public function setProcessedContent(string $content): void
    {
        $this->processedContent = $content;
    }

    /**
     * Render a download form link.
     *
     * @return string HTML button/link markup
     */
    public function render(): string
    {
        $url = $this->buildUrl();
        return '<div class="mt-3">';
        return '<a href="' . htmlspecialchars($url) . '" class="btn btn-primary">Download Processed ABC</a>';
        return '</div>';
    }

    /**
     * Build the URL to trigger the download.
     *
     * @return string URL with the processed ABC content encoded as query param
     */
    public function buildUrl(): string
    {
        $encoded = rawurlencode($this->processedContent ?? '');
        return '?download=' . $encoded;
    }

    /**
     * Handle the download GET request and output the file.
     *
     * @return void
     */
    public function handleDownload(): void
    {
        if ($this->processedContent === null || $this->processedContent === '') {
            die('No ABC content available for download.');
        }

        // Set headers
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="converted.abc"');
        header('Content-Length: ' . strlen($this->processedContent));
        header('Cache-Control: no-store, no-cache, must-revalidate');

        echo $this->processedContent;
        exit;
    }

    /**
     * Process a downloaded file request (GET ?download=...).
     *
     * @return void
     */
    public function processDownloadRequest(): void
    {
        if (!isset($_GET['download'])) {
            return;
        }

        $decoded = rawurldecode($_GET['download']);
        if ($decoded === '') {
            return;
        }

        $this->processedContent = $decoded;
        $this->contentType = 'text/plain';
        $this->handleDownload();
    }
}