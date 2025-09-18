<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcCanntaireachdPass {
    private $dict;
    public function __construct($dict) { $this->dict = $dict; }
    public function process(array $lines): array {
        $canntDiff = [];
        $output = AbcProcessor::validateCanntaireachd($lines, $canntDiff);
        
        // Generate canntaireachd w: lines for bagpipe voices
        $output = $this->generateCanntaireachdLyrics($output);
        
        return ['lines' => $output, 'canntDiff' => $canntDiff];
    }
    
    private function generateCanntaireachdLyrics($lines) {
        $canntGenerator = new CanntGenerator($this->dict);
        $output = [];
        $inBagpipeVoice = false;
        $hasLyrics = false;
        
        foreach ($lines as $line) {
            if (preg_match('/^V:Bagpipes/', $line)) {
                $inBagpipeVoice = true;
                $hasLyrics = false;
                $output[] = $line;
            } elseif (preg_match('/^V:/', $line)) {
                $inBagpipeVoice = false;
                $hasLyrics = false;
                $output[] = $line;
            } elseif ($inBagpipeVoice) {
                if (preg_match('/^w:/', $line)) {
                    // Already has lyrics, don't generate
                    $hasLyrics = true;
                    $output[] = $line;
                } elseif (preg_match('/^%/', $line)) {
                    // Comment line
                    $output[] = $line;
                } elseif (trim($line) === '') {
                    // Empty line
                    $output[] = $line;
                } elseif (preg_match('/^[A-Z]:/', $line)) {
                    // Header line (X:, T:, M:, L:, K:, etc.) - skip for canntaireachd generation
                    $output[] = $line;
                } elseif (!$hasLyrics && !preg_match('/TIMING/', $line)) {
                    // This is a music line without lyrics, generate canntaireachd
                    $canntText = $canntGenerator->generateForNotes($line);
                    if ($canntText && $canntText !== '[?]') {
                        $output[] = 'w: ' . $canntText;
                    }
                    $output[] = $line;
                } else {
                    $output[] = $line;
                }
            } else {
                $output[] = $line;
            }
        }
        
        return $output;
    }
}
