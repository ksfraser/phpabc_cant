<?php
namespace Ksfraser\PhpabcCanntaireachd;

class CanntGenerator {
    public function generateForNotes(string $noteBody): string {
        // Very simple placeholder: echo the note body back as 'cannt' tokens separated by spaces
        // TODO: Replace with dictionary-driven tokenization and mapping
        $parts = preg_split('/\s+/', trim($noteBody));
        $out = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            $out[] = '[' . $p . ']';
        }
        return implode(' ', $out);
    }
}
