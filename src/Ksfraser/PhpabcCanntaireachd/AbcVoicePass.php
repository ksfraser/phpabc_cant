<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;

class AbcVoicePass {
    public function process(array $lines, $logFlow = false): array {
        FlowLog::log('AbcVoicePass::process ENTRY', true);
        [$hasMelody, $hasBagpipes] = AbcProcessor::detectVoices($lines);
        $result = AbcProcessor::copyMelodyToBagpipes($lines, $hasMelody, $hasBagpipes);
        FlowLog::log('AbcVoicePass::process EXIT', true);
        return $result;
    }
}
