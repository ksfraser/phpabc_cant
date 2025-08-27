<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Trait VoiceParserTrait
 * Provides utility for parsing ABC voice lines and parameters.
 */
trait VoiceParserTrait {
    /**
     * Parse a voice line and extract name and parameters.
     * @param string $line
     * @return array ['voiceName' => string, 'params' => string]
     */
    public static function parseVoiceLine($line) {
        if (preg_match('/^V:([^\n\s]+)(.*)$/', $line, $matches)) {
            $voiceName = $matches[1];
            $params = isset($matches[2]) ? $matches[2] : '';
            return [
                'voiceName' => $voiceName,
                'params' => $params
            ];
        }
        return [
            'voiceName' => null,
            'params' => ''
        ];
    }
}
