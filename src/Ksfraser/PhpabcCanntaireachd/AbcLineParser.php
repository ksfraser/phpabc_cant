<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Tune;

/**
 * Interface for line parsers that can handle different types of ABC lines
 */
interface AbcLineParser {
    /**
     * Check if this parser can handle the given line
     * @param string $line
     * @return bool
     */
    /**
     * @param string $line
     * @return bool
     */
    public function canParse($line);
    
    /**
     * Parse the line and add appropriate objects to the tune
     * @param string $line
    * @param \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune $tune
     * @return bool True if parsing was successful
     */
    /**
     * @param string $line
     * @param AbcTune $tune
     * @return bool
     */
    public function parse($line, $tune);
    
    /**
     * Validate that the line is valid for this parser type
     * @param string $line
     * @return bool True if the line is valid
     */
    /**
     * @param string $line
     * @return bool
     */
    public function validate($line);
}
