<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Log\FlowLog;
use ksfraser\PhpabcCanntaireachd\Tune\MelodyToBagpipesCopier;

/**
 * Copies the melody voice into a Bagpipes voice when the tune has none.
 *
 * The pass is line-preserving: the tune is not round-tripped through the
 * object model, so bar spacing, comment placement and header order survive
 * untouched. Only the Bagpipes voice header and its copied music are added.
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @since 2.1.0
 */
class AbcVoicePass
{
    /**
     * @param string[] $lines
     * @param bool $logFlow
     * @return string[]
     */
    public function process(array $lines, $logFlow = false): array
    {
        FlowLog::log('AbcVoicePass::process ENTRY', true);

        $copier = new MelodyToBagpipesCopier();
        $result = $copier->copy($lines);

        if ($logFlow) {
            FlowLog::log('AbcVoicePass: ' . (count($result) - count($lines)) . ' line(s) added', true);
        }

        FlowLog::log('AbcVoicePass::process EXIT', true);
        return $result;
    }
}
