<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Trait HeaderExtractorTrait
 * Provides utility for extracting ABC header fields.
 */
trait HeaderExtractorTrait {
    /**
     * Extract header fields from ABC lines.
     * @param array $lines
     * @param array $fields List of header fields to extract (e.g. ['C','B','K','T','M','L','Q'])
     * @return array Associative array of field => value
     */
    public static function extractHeaders(array $lines, array $fields = ['C','B','K','T','M','L','Q']) {
        $result = [];
        foreach ($lines as $line) {
            foreach ($fields as $field) {
                if (preg_match('/^' . preg_quote($field) . ':\s*(.+)$/', $line, $m)) {
                    $result[$field] = trim($m[1]);
                }
            }
        }
        return $result;
    }
}
