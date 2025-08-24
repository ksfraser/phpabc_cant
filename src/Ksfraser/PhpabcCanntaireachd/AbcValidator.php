<?php
/**
 * Class AbcValidator
 *
 * Validates ABC notation files and tunes for structure and syntax.
 *
 * @package Ksfraser\PhpabcCanntaireachd
 */
namespace Ksfraser\PhpabcCanntaireachd;

class AbcValidator
{
    /**
     * Validate ABC file content according to ABC notation specs.
     * @param string $abcContent
     * @return array List of errors found
     */
    public function validate($abcContent)
    {
        $errors = [];
        $tunes = preg_split('/\n\s*\n/', $abcContent); // Split by blank lines
        foreach ($tunes as $i => $tune) {
            $lines = explode("\n", trim($tune));
            $hasX = false; $hasK = false; $hasT = false;
            $bodyStarted = false;
            $headerOrder = [];
            foreach ($lines as $n => $line) {
                $line = trim($line);
                if ($line === '') continue;
                // Header checks
                if (preg_match('/^X:/', $line)) {
                    $hasX = true;
                    $headerOrder[] = 'X';
                }
                if (preg_match('/^T:/', $line)) {
                    $hasT = true;
                    $headerOrder[] = 'T';
                }
                if (preg_match('/^K:/', $line)) {
                    $hasK = true;
                    $headerOrder[] = 'K';
                    $bodyStarted = true;
                }
                // Check for required headers
                if (preg_match('/^[A-Z]:/', $line) && !preg_match('/^(X:|K:|T:|M:|L:|Q:|C:|w:|W:|V:|P:|R:|S:|O:|B:|D:|F:|G:|I:|m:|U:|Z:)/', $line)) {
                    $errors[] = "Tune " . ($i+1) . ", line " . ($n+1) . ": Unknown header: $line";
                }
                // Body validation
                if ($bodyStarted && !preg_match('/^[A-Z]:/', $line)) {
                    // Validate notes, barlines, lyrics, etc.
                    if (!preg_match('/^[\|\[\]a-gA-GzZ0-9\s,:!\'\"\/\^_\.\-]+$/', $line)) {
                        $errors[] = "Tune " . ($i+1) . ", line " . ($n+1) . ": Invalid body line: $line";
                    }
                }
                // Lyrics validation
                if (preg_match('/^w:/', $line)) {
                    // Lyrics line, should follow body
                    if (!$bodyStarted) {
                        $errors[] = "Tune " . ($i+1) . ", line " . ($n+1) . ": Lyrics (w:) before body start";
                    }
                }
            }
            // Required header checks
            if (!$hasX) $errors[] = "Tune " . ($i+1) . " missing X: header (reference number)";
            if (!$hasT) $errors[] = "Tune " . ($i+1) . " missing T: header (title)";
            if (!$hasK) $errors[] = "Tune " . ($i+1) . " missing K: header (key signature)";
            // Header order check (X: should be first)
            if (!empty($headerOrder) && $headerOrder[0] !== 'X') {
                $errors[] = "Tune " . ($i+1) . " header X: should be first";
            }
        }
        return $errors;
    }
}
