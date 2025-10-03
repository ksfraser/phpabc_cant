<?php
namespace Ksfraser\PhpabcCanntaireachd\Log;

class FlowLog extends Log {
    const LOG_FILE = __DIR__ . '/../../../logs/debug_flow.log';
    public static function log($message, $enabled = false) {
        if (!$enabled) return;
        parent::write(self::LOG_FILE, $message, 'FLOW');
    }
}
