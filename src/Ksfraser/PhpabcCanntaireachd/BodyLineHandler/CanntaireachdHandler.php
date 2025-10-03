<?php
namespace Ksfraser\PhpabcCanntaireachd\BodyLineHandler;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcBar;

class CanntaireachdHandler implements AbcBodyLineHandlerInterface {
    public function matches($line): bool {
        return preg_match('/^W:(.*)$/i', trim($line));
    }
    public function handle(&$context, $line) {
        $trimmed = trim($line);
        if (preg_match('/^W:(.*)$/i', $trimmed, $m)) {
            $cannt = $m[1];
            if (!isset($context['voiceBars'][$context['currentVoice']][$context['currentBar']])) {
                $context['voiceBars'][$context['currentVoice']][$context['currentBar']] = new AbcBar($context['currentBar']);
            }
            $context['voiceBars'][$context['currentVoice']][$context['currentBar']]->setCanntaireachd($cannt);
        }
    }
}
