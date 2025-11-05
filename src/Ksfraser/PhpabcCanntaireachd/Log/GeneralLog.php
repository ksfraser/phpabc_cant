<?php
namespace Ksfraser\PhpabcCanntaireachd\Log;

class GeneralLog extends Log {
    const LOG_FILE = __DIR__ . '/../../../logs/general.log';
    public static function log($message, $enabled = false) {
        if (!$enabled) return;
        parent::write(self::LOG_FILE, $message, 'GENERAL');
    }
}
