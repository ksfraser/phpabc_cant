<?php
namespace Ksfraser\PhpabcCanntaireachd\Log;

class DebugLog extends Log {
    const LOG_FILE = __DIR__ . '/../../../logs/debug.log';
    public static function log($message, $enabled = false) {
        if (!$enabled) return;
        parent::write(self::LOG_FILE, $message, 'DEBUG');
    }
}
