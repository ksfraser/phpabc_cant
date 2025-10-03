<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * LogFlow: Centralized flow/debug logger for pipeline and passes.
 * Usage: LogFlow::log('message', 'LEVEL', $enabled)
 */
class LogFlow {
    const LOG_FILE = __DIR__ . '/../../debug_flow.log';

    /**
     * Log a message to the flow log if enabled.
     * @param string $message
     * @param string $level (e.g., FLOW, ERROR, DEBUG)
     * @param bool $enabled
     */
    public static function log($message, $level = 'FLOW', $enabled = false) {
        if (!$enabled) return;
        $ts = date('Y-m-d H:i:s');
        $line = "[$level][$ts] $message\n";
        file_put_contents(self::LOG_FILE, $line, FILE_APPEND);
    }
}
