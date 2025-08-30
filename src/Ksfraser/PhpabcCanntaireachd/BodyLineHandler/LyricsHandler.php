<?php
namespace Ksfraser\PhpabcCanntaireachd\BodyLineHandler;
use Ksfraser\PhpabcCanntaireachd\AbcBar;

class LyricsHandler implements AbcBodyLineHandlerInterface {
    protected $forceBarLinesInLyrics;
    public function __construct($forceBarLinesInLyrics) {
        $this->forceBarLinesInLyrics = $forceBarLinesInLyrics;
    }
    public function matches($line): bool {
        return preg_match('/^w:(.*)$/i', trim($line));
    }
    public function handle(&$context, $line) {
        $trimmed = trim($line);
        if (preg_match('/^w:(.*)$/i', $trimmed, $m)) {
            $lyrics = $m[1];
            if ($this->forceBarLinesInLyrics) {
                $lyricBars = preg_split('/\|/', $lyrics);
                foreach ($lyricBars as $i => $lyricBar) {
                    $barNum = $context['currentBar'] + $i;
                    if (!isset($context['voiceBars'][$context['currentVoice']][$barNum])) {
                        $context['voiceBars'][$context['currentVoice']][$barNum] = new AbcBar($barNum);
                    }
                    $context['voiceBars'][$context['currentVoice']][$barNum]->setLyrics(trim($lyricBar));
                }
                $context['currentBar'] += count($lyricBars) - 1;
            } else {
                if (!isset($context['voiceBars'][$context['currentVoice']][$context['currentBar']])) {
                    $context['voiceBars'][$context['currentVoice']][$context['currentBar']] = new AbcBar($context['currentBar']);
                }
                $context['voiceBars'][$context['currentVoice']][$context['currentBar']]->setLyrics($lyrics);
            }
        }
    }
}
