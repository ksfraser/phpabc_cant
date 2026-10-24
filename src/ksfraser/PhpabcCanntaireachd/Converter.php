<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

/**
 * Converter: converts token arrays to output formats.
 */
class Converter {
    /**
     * Convert tokens to ABC format.
     */
    public function toABC(array $tokens) {
        $out = [];
        foreach ($tokens as $note) {
            if ($note instanceof \ksfraser\PhpabcCanntaireachd\AbcNote) {
                $out[] = $note->get_body_out();
            }
        }
        return implode(' ', $out);
    }
    /**
     * Convert tokens to Canntaireachd format.
     */
    public function toCanntaireachd(array $tokens) {
        $out = [];
        foreach ($tokens as $note) {
            if ($note instanceof \ksfraser\PhpabcCanntaireachd\AbcNote) {
                $out[] = $note->renderCanntaireachd();
            }
        }
        return implode(' ', $out);
    }
    /**
     * Convert tokens to BMW format.
     */
    public function toBMW(array $tokens) {
        $out = [];
        foreach ($tokens as $note) {
            if ($note instanceof \ksfraser\PhpabcCanntaireachd\AbcNote) {
                $out[] = $note->getBmwToken();
            }
        }
        return implode(' ', $out);
    }
}
