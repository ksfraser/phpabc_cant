<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Log: Centralized logger for all log types. Use child classes for each log type.
 * Usage: Log::Flow::log('message', $enabled)
 */
class Log {
    protected static function write($logFile, $message, $level) {
        $ts = date('Y-m-d H:i:s');
        $line = "[$level][$ts] $message\n";
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        file_put_contents($logFile, $line, FILE_APPEND);
    }

    // Child classes for each log type
    class Flow {
        const LOG_FILE = __DIR__ . '/../../logs/debug_flow.log';
        public static function log($message, $enabled = false) {
            if (!$enabled) return;
            Log::write(self::LOG_FILE, $message, 'FLOW');
        }
    }
    class Debug {
        const LOG_FILE = __DIR__ . '/../../logs/debug.log';
        public static function log($message, $enabled = false) {
            if (!$enabled) return;
            Log::write(self::LOG_FILE, $message, 'DEBUG');
        }
    }
    class Cannt {
        const LOG_FILE = __DIR__ . '/../../logs/cannt_debug.log';
        public static function log($message, $enabled = false) {
            if (!$enabled) return;
            Log::write(self::LOG_FILE, $message, 'CANNT');
        }
    }
    class General {
        const LOG_FILE = __DIR__ . '/../../logs/general.log';
        public static function log($message, $enabled = false) {
            if (!$enabled) return;
            Log::write(self::LOG_FILE, $message, 'GENERAL');
        }
    }
}
