<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * BaseParser: loads file, tokenizes content, provides common interface.
 */
abstract class BaseParser {
    /**
     * Load file content.
     */
    public function load($filePath) {
        return file_get_contents($filePath);
    }
    /**
     * Tokenize content into array of Token objects.
     * @param string $content
     * @return Token[]
     */
    abstract public function tokenize($content);
}
