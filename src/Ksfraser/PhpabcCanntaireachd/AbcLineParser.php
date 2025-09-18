<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Interface for line parsers that can handle different types of ABC lines
 */
interface AbcLineParser {
    /**
     * Check if this parser can handle the given line
     * @param string $line
     * @return bool
     */
    public function canParse(string $line): bool;
    
    /**
     * Parse the line and add appropriate objects to the tune
     * @param string $line
     * @param AbcTune $tune
     * @return bool True if parsing was successful
     */
    public function parse(string $line, AbcTune $tune): bool;
}
