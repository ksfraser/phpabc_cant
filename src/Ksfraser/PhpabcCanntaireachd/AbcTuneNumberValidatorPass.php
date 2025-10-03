<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;
/**
 * Checks for duplicate X: tune numbers in ABC files.
 * Lists duplicates with their first associated T: header.
 */
class AbcTuneNumberValidatorPass {
    /**
     * @param array $lines ABC file lines
     * @return array [lines, errors]
     */
    public function validate(array $lines, $logFlow = false): array {
        FlowLog::log('AbcTuneNumberValidatorPass::validate ENTRY', true);
        $xNumbers = [];
        $errors = [];
        $currentX = null;
        $currentT = null;
        foreach ($lines as $line) {
            if (preg_match('/^X:\s*(\d+)/', $line, $m)) {
                $currentX = $m[1];
                $currentT = null;
                if (isset($xNumbers[$currentX])) {
                    $errors[] = "Duplicate X:$currentX (first T:'{$xNumbers[$currentX]}')";
                } else {
                    $xNumbers[$currentX] = '';
                }
            }
            if ($currentX && preg_match('/^T:\s*(.+)/', $line, $m)) {
                if ($xNumbers[$currentX] === '') {
                    $xNumbers[$currentX] = $m[1];
                }
            }
        }
        $result = ['lines' => $lines, 'errors' => $errors];
        FlowLog::log('AbcTuneNumberValidatorPass::validate EXIT', true);
        return $result;
    }
}
