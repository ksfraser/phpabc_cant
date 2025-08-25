<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcVoicePass {
    public function process(array $lines): array {
        [$hasMelody, $hasBagpipes] = AbcProcessor::detectVoices($lines);
        return AbcProcessor::copyMelodyToBagpipes($lines, $hasMelody, $hasBagpipes);
    }
}
