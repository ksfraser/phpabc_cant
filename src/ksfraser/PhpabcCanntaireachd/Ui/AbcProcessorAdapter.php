<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Ui;

/**
 * Adapts AbcProcessor::process() for web UI use.
 *
 * Single Responsibility: Bridge the static AbcProcessor with the UI layer,
 * translating web parameters into the dictionary and configuration the
 * processor expects, and normalizing the result back into a format the
 * forms can display.
 *
 * @package ksfraser\PhpabcCanntaireachd\Ui
 *
 * @since 1.0.0
 */
class AbcProcessorAdapter
{
    /**
     * @var TokenDictionary The ABC token dictionary used for conversion.
     */
    private $dictionary;

    /**
     * Create an adapter with a dictionary.  When no dictionary is supplied
     * a fresh TokenDictionary is populated lazily from the package data file.
     *
     * @param \ksfraser\PhpabcCanntaireachd\TokenDictionary|null $dict
     */
    public function __construct($dict = null)
    {
        if ($dict === null) {
            $dict = new \ksfraser\PhpabcCanntaireachd\TokenDictionary();
            $dictFile = __DIR__ . '/../../abc_dict.php';
            if (is_file($dictFile)) {
                $abc = [];
                include $dictFile;
                $dict->prepopulate($abc);
            }
        }
        $this->dictionary = $dict;
    }

    /**
     * Process ABC content and return a uniform result array.
     *
     * @param string $abcContent The ABC source to process.
     * @param bool $convert Whether to add canntaireachd lines.
     * @return array{
     *     success: bool,
     *     output: string,
     *     errors: array<string>,
     *     tuneCount: int
     * }
     */
    public function process(string $abcContent, bool $convert = true): array
    {
        $lines = preg_split('/\r?\n/', $abcContent);
        $tuneCount = $this->countTunes($lines);

        $dict = $this->dictionary;
        if (!($dict instanceof \ksfraser\PhpabcCanntaireachd\TokenDictionary)) {
            $dict = new \ksfraser\PhpabcCanntaireachd\TokenDictionary();
            $dict->prepopulate($dict);
        }

        $result = \ksfraser\PhpabcCanntaireachd\AbcProcessor::process(
            $abcContent,
            $dict,
            null,
            $convert,
            false
        );

        $errors = [];
        if (!empty($result['errors'])) {
            $errors = is_array($result['errors']) ? $result['errors'] : [$result['errors']];
        }

        $output = '';
        if (isset($result['lines']) && is_array($result['lines'])) {
            $output = implode("\n", $result['lines']);
        }

        return [
            'success' => !empty($output),
            'output' => $output,
            'errors' => $errors,
            'tuneCount' => $tuneCount,
        ];
    }

    /**
     * Count how many tunes (X: headers) are in the ABC content.
     *
     * @param array $lines
     * @return int
     */
    private function countTunes(array $lines): int
    {
        $count = 0;
        foreach ($lines as $line) {
            if (preg_match('/^X:/', trim($line))) {
                $count++;
            }
        }
        return max(1, $count);
    }
}