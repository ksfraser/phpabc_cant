<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for header lines (X:, T:, M:, L:, etc.)
 */
class HeaderParser implements AbcLineParser {
    protected $singleHeaderPolicy = 'last';

    public function __construct($singleHeaderPolicy = 'last') {
        $this->singleHeaderPolicy = $singleHeaderPolicy;
    }

    /**
     * @param string $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^([A-Z]):(.*)/', trim($line)) && !preg_match('/^X:/', trim($line));
    }

    /**
     * @param string $line
    * @param \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^([A-Z]):(.*)/', trim($line), $matches)) {
            return false;
        }

        $key = $matches[1];
        $value = trim($matches[2]);

        // Use header class if available
        $headerClass = 'Ksfraser\\PhpabcCanntaireachd\\Header\\AbcHeader' . $key;
        if (class_exists($headerClass)) {
            // Multi-value fields
            if (in_array($key, ['C', 'B'])) {
                $tune->addHeader($key, $value);
            } else {
                // Single-value: first/last policy
                $existing = $tune->getHeaders();
                if ($this->singleHeaderPolicy === 'first' && isset($existing[$key]) && $existing[$key]->get() !== '') {
                    // Ignore subsequent
                } else {
                    $tune->replaceHeader($key, $value);
                }
            }
        } else {
            // Fallback: treat as string
            $tune->addHeader($key, $value);
        }

        return true;
    }
    
    /**
     * @param string $line
     * @return bool
     */
    public function validate($line) {
        // Header lines are valid if they match the expected format
        return preg_match('/^([A-Z]):(.*)/', trim($line)) && !preg_match('/^X:/', trim($line));
    }
}
