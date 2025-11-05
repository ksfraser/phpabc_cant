<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;

class AbcVoiceOrderPass {
    public function process(array $lines, $logFlow = false): array {
        FlowLog::log('AbcVoiceOrderPass::process ENTRY', true);
        $result = AbcProcessor::reorderVoices($lines);
        FlowLog::log('AbcVoiceOrderPass::process EXIT', true);
        return $result;
    }
}
