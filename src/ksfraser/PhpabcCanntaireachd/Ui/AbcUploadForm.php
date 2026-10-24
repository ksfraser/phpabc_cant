<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Ui;

/**
 * Generates the ABC file upload form HTML.
 *
 * Single Responsibility: HTML generation for the ABC upload interface.
 * Delegates processing to AbcProcessorAdapter.
 *
 * @package ksfraser\PhpabcCanntaireachd\Ui
 *
 * @since 1.0.0
 */
class AbcUploadForm
{
    /** @var string|null Path to the uploaded file, if any */
    private ?string $uploadedFile = null;

    /** @var string|null Error message from processing, if any */
    private ?string $errorMessage = null;

    /** @var array Processed result data, if any */
    private ?array $processedData = null;

    /** @return string|null */
    public function getUploadedFile(): ?string
    {
        return $this->uploadedFile;
    }

    /** @return string|null */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /** @return array|null */
    public function getProcessedData(): ?array
    {
        return $this->processedData;
    }

    /**
     * Render the ABC upload form.
     *
     * @param string|null $actionUrl URL to POST to (defaults to self)
     * @return string HTML form markup
     */
    public function render(string $actionUrl = ''): string
    {
        $url = $actionUrl ? $actionUrl : $_SERVER['PHP_SELF'];
        $uploadedFile = $this->uploadedFile ?? '';
        $error = $this->errorMessage ?? '';
        $data = $this->processedData ?? [];

        $output = '<form method="post" enctype="multipart/form-data" action="' . htmlspecialchars($url) . '">';
        $output .= '<h2>ABC Canntaireachd Converter</h2>';

        // File upload field
        $output .= '<div class="form-group">';
        $output .= '<label for="abc_file">ABC File:</label>';
        $output .= '<input type="file" name="abc_file" id="abc_file" class="form-control" accept=".abc" value="' . htmlspecialchars($uploadedFile) . '" />';
        $output .= '</div>';

        // Text area for manual entry
        $output .= '<div class="form-group">';
        $output .= '<label for="abc_text">Or paste ABC text:</label>';
        $output .= '<textarea name="abc_text" id="abc_text" rows="8" cols="80" class="form-control">' . htmlspecialchars($data['abc_text'] ?? '') . '</textarea>';
        $output .= '</div>';

        // Convert checkbox
        $output .= '<div class="form-check">';
        $output .= '<input type="checkbox" name="convert" id="convert" class="form-check-input" ' . ($data['convert'] ?? false ? 'checked' : '') . ' />';
        $output .= '<label for="convert" class="form-check-label">Add canntaireachd lines</label>';
        $output .= '</div>';

        // Submit button
        $output .= '<button type="submit" name="submit" value="process" class="btn btn-primary">Process ABC</button>';
        $output .= '</form>';

        // Display errors
        if ($error) {
            $output .= '<div class="alert alert-danger mt-3" role="alert">' . htmlspecialchars($error) . '</div>';
        }

        // Display results
        if (!empty($data['result']) && is_array($data['result'])) {
            $output .= '<div class="alert alert-success mt-3" role="alert">';
            $output .= '<h4>Processing Result</h4>';
            $output .= '<pre class="mb-0">' . htmlspecialchars(json_encode($data['result'], JSON_PRETTY_PRINT)) . '</pre>';
            $output .= '</div>';

            // Provide download button
            $output .= '<div class="mt-3">';
            $output .= '<a href="?download=' . rawurlencode(json_encode($data['abc_content'] ?? '')) . '" class="btn btn-secondary">Download Processed ABC</a>';
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Handle form submission.
     *
     * Processes the submitted ABC content and returns an AbcUploadForm instance
     * with the results populated.
     *
     * @return static Returns self to allow method chaining
     */
    public static function handleSubmission(): static
    {
        $form = new static();

        if (isset($_POST['submit']) && $_POST['submit'] === 'process') {
            // Get ABC content from file upload or text area
            $abcContent = '';

            if (isset($_FILES['abc_file']) && $_FILES['abc_file']['error'] === UPLOAD_ERR_OK) {
                $abcContent = file_get_contents($_FILES['abc_file']['tmp_name']);
                $form->uploadedFile = basename($_FILES['abc_file']['name']);
            } elseif (isset($_POST['abc_text']) && strlen($_POST['abc_text']) > 0) {
                $abcContent = $_POST['abc_text'];
                $form->uploadedFile = 'pasted.txt';
            }

            if (strlen($abcContent) > 0) {
                try {
                    $adapter = new AbcProcessorAdapter();
                    $result = $adapter->process($abcContent, isset($_POST['convert']) && $_POST['convert'] === '1');

                    $form->errorMessage = '';
                    $form->processedData = [
                        'abc_content' => $result['output'] ?? '',
                        'abc_text' => $abcContent,
                        'convert' => isset($_POST['convert']) && $_POST['convert'] === '1',
                        'result' => true,
                    ];
                } catch (\Exception $e) {
                    $form->errorMessage = 'Error processing ABC: ' . $e->getMessage();
                    $form->processedData = [
                        'abc_text' => $abcContent,
                        'convert' => isset($_POST['convert']) && $_POST['convert'] === '1',
                        'result' => false,
                    ];
                }
            } else {
                $form->errorMessage = 'No ABC content provided.';
                $form->processedData = [
                    'abc_text' => '',
                    'convert' => isset($_POST['convert']) && $_POST['convert'] === '1',
                    'result' => false,
                ];
            }
        }

        return $form;
    }
}