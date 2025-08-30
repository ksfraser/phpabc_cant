<?php
namespace Ksfraser\PhpabcCanntaireachd\BodyLineHandler;
use Ksfraser\PhpabcCanntaireachd\AbcBar;

class SolfegeHandler implements AbcBodyLineHandlerInterface {
    public function matches($line): bool {
        return preg_match('/^S:(.*)$/i', trim($line));
    }
    public function handle(&$context, $line) {
        $trimmed = trim($line);
        if (preg_match('/^S:(.*)$/i', $trimmed, $m)) {
            $solfege = $m[1];
            if (!isset($context['voiceBars'][$context['currentVoice']][$context['currentBar']])) {
                $context['voiceBars'][$context['currentVoice']][$context['currentBar']] = new AbcBar($context['currentBar']);
            }
            $context['voiceBars'][$context['currentVoice']][$context['currentBar']]->setSolfege($solfege);
        }
    }
}
