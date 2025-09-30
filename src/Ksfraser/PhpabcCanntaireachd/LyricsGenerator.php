<?php
namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Exceptions\LyricsGeneratorException;
/**
 * Class LyricsGenerator
 *
 * Generates canntaireachd lyrics for bagpipe voices, replacing control blocks with SRP/DI classes and custom exceptions.
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @uml
 * @startuml
 * start
 * :init canntGenerator;
 * :foreach line in lines;
 *   if V:Bagpipes then
 *     :set inBagpipeVoice true;
 *   elseif V: then
 *     :set inBagpipeVoice false;
 *   elseif inBagpipeVoice then
 *     if w: then
 *       :set hasLyrics true;
 *     elseif !hasLyrics and isMusicLine then
 *       :generate canntaireachd;
 *       :align tokens;
 *       if canntTextAligned valid then
 *         :add w: line;
 *       endif
 *     endif
 *   endif
 * endfor
 * :return output;
 * stop
 * @enduml
 */
class LyricsGenerator {
    protected $canntGenerator;
    public function __construct(CanntGenerator $canntGenerator) {
        $this->canntGenerator = $canntGenerator;
    }
    /**
     * Generate canntaireachd lyrics for bagpipe voices.
     * @param array $lines
     * @return array
     * @throws LyricsGeneratorException
     */
    public function generate(array $lines) {
        $output = [];
        $inBagpipeVoice = false;
        $hasLyrics = false;
        try {
            $pendingWLine = null;
            foreach ($lines as $idx => $line) {
                // Preserve blank lines and comments
                if (trim($line) === '' || preg_match('/^%/', $line)) {
                    if ($pendingWLine !== null) {
                        $output[] = $pendingWLine;
                        $pendingWLine = null;
                    }
                    $output[] = $line;
                    continue;
                }
                // Reset lyric state on tune boundary
                if (preg_match('/^X:/', $line)) {
                    if ($pendingWLine !== null) {
                        $output[] = $pendingWLine;
                        $pendingWLine = null;
                    }
                    $inBagpipeVoice = false;
                    $hasLyrics = false;
                    $output[] = $line;
                    continue;
                }
                if (preg_match('/^V:Bagpipes/', $line)) {
                    if ($pendingWLine !== null) {
                        $output[] = $pendingWLine;
                        $pendingWLine = null;
                    }
                    $inBagpipeVoice = true;
                    $hasLyrics = false;
                    $output[] = $line;
                    continue;
                } elseif (preg_match('/^V:/', $line)) {
                    if ($pendingWLine !== null) {
                        $output[] = $pendingWLine;
                        $pendingWLine = null;
                    }
                    $inBagpipeVoice = false;
                    $hasLyrics = false;
                    $output[] = $line;
                    continue;
                }
                if ($inBagpipeVoice) {
                    if (preg_match('/^w:/', $line)) {
                        $hasLyrics = true;
                        $output[] = $line;
                    } elseif (!$hasLyrics && $this->isMusicLine($line)) {
                        $musicLine = preg_replace('/^\[V:[^\]]+\]/', '', $line);
                        $musicTokens = preg_split('/\s+/', trim($musicLine));
                        $canntTextRaw = $this->canntGenerator->generateForNotes($musicLine);
                        $canntTokens = preg_split('/\s+/', trim($canntTextRaw));
                        $canntTextAligned = '';
                        for ($i = 0; $i < count($musicTokens); $i++) {
                            $canntTextAligned .= ($canntTokens[$i] ?? '') . ' ';
                        }
                        $canntTextAligned = trim($canntTextAligned);
                        $output[] = $line;
                        if ($canntTextAligned && $canntTextAligned !== '[?]') {
                            $output[] = 'w: ' + $canntTextAligned;
                        }
                        $hasLyrics = false;
                    } else {
                        $output[] = $line;
                    }
                } else {
                    $output[] = $line;
                }
            }
            // No need to flush pending w: line at end, as it is always output after music line
        } catch (\Throwable $ex) {
            throw new LyricsGeneratorException('Error in LyricsGenerator: ' . $ex->getMessage(), 0, $ex);
        }
        return $output;
    }
    private function isMusicLine($line) {
        return !preg_match('/^[VXT:]/', $line) && trim($line) !== '' && !preg_match('/^%/', $line);
    }
}
