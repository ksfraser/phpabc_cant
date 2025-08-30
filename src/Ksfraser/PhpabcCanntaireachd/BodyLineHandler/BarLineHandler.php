<?php
namespace Ksfraser\PhpabcCanntaireachd\BodyLineHandler;
use Ksfraser\PhpabcCanntaireachd\AbcBar;

class BarLineHandler implements AbcBodyLineHandlerInterface {
    protected $barLinePattern;
    public function __construct($barLines) {
        usort($barLines, function($a, $b) { return strlen($b) - strlen($a); });
        $this->barLinePattern = '/^(' . implode('|', array_map('preg_quote', $barLines)) . ')/';
    }
    public function matches($line): bool {
        return preg_match($this->barLinePattern, trim($line));
    }
    public function handle(&$context, $line) {
        $trimmed = trim($line);
        if (preg_match($this->barLinePattern, $trimmed, $bm)) {
            $context['currentBar']++;
            $barObj = new AbcBar($context['currentBar'], $bm[1]);
            $context['voiceBars'][$context['currentVoice']][$context['currentBar']] = $barObj;
        }
    }
}
