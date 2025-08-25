<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcVoiceOrderPass {
    public function process(array $lines): array {
        return AbcProcessor::reorderVoices($lines);
    }
}
