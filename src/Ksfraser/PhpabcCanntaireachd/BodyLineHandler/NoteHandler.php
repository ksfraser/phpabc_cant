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
        $voice = $context->currentVoice ?? null;
        if ($voice === null) {
            $context->getOrCreateVoice('M');
            $voice = $context->currentVoice;
        }
        $barNum = $context->currentBar;
        if (!isset($context->voiceBars[$voice][$barNum])) {
            $context->voiceBars[$voice][$barNum] = new AbcBar($barNum);
        }
        $context->voiceBars[$voice][$barNum]->addNote($trimmed);
    }
}
