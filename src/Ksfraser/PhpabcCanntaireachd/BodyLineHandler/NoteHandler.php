<?php
namespace Ksfraser\PhpabcCanntaireachd\BodyLineHandler;
use Ksfraser\PhpabcCanntaireachd\AbcBar;

class NoteHandler implements AbcBodyLineHandlerInterface {
    public function matches($line): bool {
        $trimmed = trim($line);
        // Not a header, not empty
        return $trimmed !== '' && !preg_match('/^(?:\[)?V:([^
\]]+)(?:\])?/', $trimmed);
    }
    public function handle(&$context, $line) {
        $trimmed = trim($line);
        if (!isset($context['voiceBars'][$context['currentVoice']][$context['currentBar']])) {
            $context['voiceBars'][$context['currentVoice']][$context['currentBar']] = new AbcBar($context['currentBar']);
        }
        $context['voiceBars'][$context['currentVoice']][$context['currentBar']]->addNote($trimmed);
    }
}
