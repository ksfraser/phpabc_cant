<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcLyricsPass {
    private $dict;
    public function __construct($dict) { $this->dict = $dict; }
    public function process(array $lines): array {
        $lyricsWords = [];
        $output = AbcProcessor::handleLyrics($lines, $this->dict, $lyricsWords);
        return ['lines' => $output, 'lyricsWords' => $lyricsWords];
    }
}
