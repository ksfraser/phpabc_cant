<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcCanntaireachdPass {
    public function process(array $lines): array {
        $canntDiff = [];
        $output = AbcProcessor::validateCanntaireachd($lines, $canntDiff);
        return ['lines' => $output, 'canntDiff' => $canntDiff];
    }
}
