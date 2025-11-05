<?php
namespace Ksfraser\PhpabcCanntaireachd\Log;

class Log {
    // PEAR_LOG_* levels
    const LEVELS = [
        'EMERG', 'ALERT', 'CRIT', 'ERR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'
    ];

    /**
     * Write a log entry with PEAR_LOG_* level and optional file/line info.
     * @param string $logFile
     * @param string $message
     * @param string $level
     */
    protected static function write($logFile, $message, $level) {
        $ts = date('Y-m-d H:i:s');
        $levelNorm = strtoupper($level);
        // Map custom levels to PEAR_LOG_* if needed
        $levelMap = [
            'FATAL' => 'EMERG',
            'CRITICAL' => 'CRIT',
            'ERROR' => 'ERR',
            'WARN' => 'WARNING',
            'WARNING' => 'WARNING',
            'NOTICE' => 'NOTICE',
            'INFO' => 'INFO',
            'DEBUG' => 'DEBUG',
            'CANNT' => 'DEBUG',
            'FLOW' => 'DEBUG',
            'GENERAL' => 'INFO',
        ];
        $pearLevel = isset($levelMap[$levelNorm]) ? $levelMap[$levelNorm] : $levelNorm;

        // Only add file/line for levels ERR and above (EMERG, ALERT, CRIT, ERR)
        $includeFileLine = in_array($pearLevel, ['EMERG', 'ALERT', 'CRIT', 'ERR']);
        if ($includeFileLine) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            // 0 = this function, 1 = log() in child, 2 = caller
            $caller = isset($bt[2]) ? $bt[2] : (isset($bt[1]) ? $bt[1] : null);
            if ($caller && isset($caller['file']) && isset($caller['line'])) {
                $message .= " [at {$caller['file']}:{$caller['line']}]";
            }
        }
        $line = "[$pearLevel][$ts] $message\n";
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        file_put_contents($logFile, $line, FILE_APPEND);
    }
}
