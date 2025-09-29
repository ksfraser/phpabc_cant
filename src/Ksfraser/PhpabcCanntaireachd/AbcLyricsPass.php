<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Class AbcLyricsPass
 *
 * Processes lyrics lines in ABC notation using a provided dictionary.
 * Delegates to AbcProcessor for handling lyrics and extracting words.
 *
 * SOLID: Single Responsibility (lyrics processing), DI (dictionary injection).
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @property array $dict Lyrics dictionary for processing
 *
 * @method __construct(array $dict) Inject lyrics dictionary
 * @method process(array $lines): array Process lyrics lines and extract words
 *
 * @uml
 * @startuml
 * class AbcLyricsPass {
 *   - dict: array
 *   + __construct(dict: array)
 *   + process(lines: array): array
 * }
 * AbcLyricsPass --> AbcProcessor
 * @enduml
 */
class AbcLyricsPass {
    private $dict;
    public function __construct($dict) { $this->dict = $dict; }
    public function process(array $lines): array {
        $lyricsWords = [];
        $output = AbcProcessor::handleLyrics($lines, $this->dict, $lyricsWords);
        return ['lines' => $output, 'lyricsWords' => $lyricsWords];
    }
}
