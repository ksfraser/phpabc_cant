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
    use ErrorFormatterTrait;
    use VoiceParserTrait;
    /**
     * Validate ABC file content according to ABC notation specs.
     * @param string $abcContent
     * @return array List of errors found
     */
    public function validate($abcContent)
    {
            $voiceHasTranspose = false;
            $hasC = false; $hasB = false; $hasO = false; $hasZ = false;
        $errors = [];
        $tunes = preg_split('/\n\s*\n/', $abcContent); // Split by blank lines
        foreach ($tunes as $i => $tune) {
            $lines = explode("\n", trim($tune));
            $hasX = false; $hasK = false; $hasT = false;
            $bodyStarted = false;
            $headerOrder = [];
            $voices = [];
            $isBagpipe = false;
            $drumVoiceLines = [];
            $voiceHasTranspose = false;
            $hasC = false; $hasB = false; $hasO = false; $hasZ = false;
            $melodyVoiceName = null;
            $melodyVoiceLine = null;
            $melodyNotes = [];
            $hasBagpipeVoice = false;
            $bodyStartIdx = null;
            $firstVoiceIdx = null;
            $tuneX = null;
            // First pass: gather info
            foreach ($lines as $n => $line) {
                $line = trim($line);
                if ($line === '') continue;
                $params = '';
                if (preg_match('/^X:\s*(\d+)/', $line, $xm)) {
                    $tuneX = $xm[1];
                    $hasX = true;
                }
                if (preg_match('/^T:\s*(.+)/', $line)) {
                    $hasT = true;
                }
                if (preg_match('/^K:/', $line)) {
                    $hasK = true;
                    $headerOrder[] = 'K';
                    $bodyStarted = true;
                    // Detect bagpipe key
                    if (preg_match('/^K:HP/', $line)) {
                        $isBagpipe = true;
                    }
                }
                // Voice checks
                $voiceInfo = self::parseVoiceLine($line);
                if ($voiceInfo['voiceName'] !== null) {
                    $voiceName = $voiceInfo['voiceName'];
                    $params = $voiceInfo['params'];
                    $hasName = preg_match('/name="[^"]+"/', $params);
                    $bodyStartIdx = $n;
                    $hasSname = preg_match('/sname="[^"]+"/', $params);
                    if (!$hasName || !$hasSname) {
                        $errors[] = self::formatError($i, $tuneX, $n+1, "Voice '$voiceName' missing name or sname. Suggest: name=\"$voiceName\" sname=\"$voiceName\"");
                    }
                    $voices[] = $voiceName;
                    // Detect melody voice (first non-drum voice)
                    if (!$melodyVoiceName && !preg_match('/drum|bagpipe/i', $voiceName)) {
                        $melodyVoiceName = $voiceName;
                        $melodyVoiceLine = $n;
                    }
                    // Detect bagpipe voice
                    if (preg_match('/bagpipe/i', $voiceName)) {
                        $hasBagpipeVoice = true;
                    }
                    if (preg_match('/drum/i', $voiceName)) {
                        $drumVoiceLines[] = $n;
                    }
                    // Check for transpose in voice params
                    if (preg_match('/transpose=/', $params)) {
                        $voiceHasTranspose = true;
                    }
                }
                // Check for required headers
                if ($firstVoiceIdx === null) $firstVoiceIdx = $n;
                if (preg_match('/^[A-Z]:/', $line) && !preg_match('/^(X:|K:|T:|M:|L:|Q:|C:|w:|W:|V:|P:|R:|S:|O:|B:|D:|F:|G:|I:|m:|U:|Z:)/', $line)) {
                    $errors[] = self::formatError($i, $tuneX, $n+1, "Unknown header: $line");
                }
                if ($melodyVoiceName && $melodyVoiceLine !== null && $n > $melodyVoiceLine && !preg_match('/^[A-Z]:/', $line)) {
                    $melodyNotes[] = $line;
                }
                // Body validation - use parsers for validation
                if ($bodyStarted && !preg_match('/^[A-Z]:/', $line)) {
                    // Try each parser to validate the line
                    $valid = false;
                    $parsers = [
                        new \Ksfraser\PhpabcCanntaireachd\FormattingParser(),
                        new \Ksfraser\PhpabcCanntaireachd\Midi\MidiParser(),
                        new \Ksfraser\PhpabcCanntaireachd\CommentParser(),
                        new \Ksfraser\PhpabcCanntaireachd\BodyParser()
                    ];
                    
                    foreach ($parsers as $parser) {
                        if ($parser->canParse($line)) {
                            $valid = $parser->validate($line);
                            break;
                        }
                    }
                    
                    if (!$valid) {
                        $errors[] = self::formatError($i, $tuneX, $n+1, "Invalid body line: $line");
                    }
            // Check for missing C: and B: headers
            if (!$hasC) {
                $errors[] = self::formatError($i, $tuneX, null, "missing C: header (composer). Suggest: add C:<composer>.");
            }
            if (!$hasB) {
                $errors[] = self::formatError($i, $tuneX, null, "missing B: header (book). Suggest: add B:<book>.");
            }
            // Check for missing O: and Z: headers, suggest sourcing from DB or override
            if (!$hasO) {
                $errors[] = self::formatError($i, $tuneX, null, "missing O: header (origin). Suggest: source from database or override via CLI/WordPress input.");
            // If melody voice exists and no bagpipe voice, insert bagpipe voice and notes at top of body
            if ($melodyVoiceName && !$hasBagpipeVoice && count($melodyNotes) > 0 && $bodyStartIdx !== null) {
                $bagpipeVoiceLine = 'V:Bagpipes name="Bagpipes" sname="Bagpipes"';
                $scoreLine = '%score {Bagpipes}';
                $insertIdx = $bodyStartIdx + 1; // After K: line
                array_splice($lines, $insertIdx, 0, [$scoreLine, $bagpipeVoiceLine]);
                array_splice($lines, $insertIdx + 2, 0, $melodyNotes);
            }
            // ...existing code...
            }
            if (!$hasZ) {
                $errors[] = self::formatError($i, $tuneX, null, "missing Z: header (transcriber/notes). Suggest: source from database or override via CLI/WordPress input.");
            // If melody voice exists and no bagpipe voice, suggest copying melody to bagpipe
            if ($melodyVoiceName && !$hasBagpipeVoice && count($melodyNotes) > 0) {
                $errors[] = self::formatError($i, $tuneX, null, "Melody voice '$melodyVoiceName' found but no bagpipe voice. Suggest: copy melody voice and its notes to a new bagpipe voice.");
            }
            }
                }
                    if (preg_match('/transpose=/', $params)) {
                        $voiceHasTranspose = true;
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
            if (!$hasX) $errors[] = self::formatError($i, $tuneX, null, "missing X: header (reference number)");
            if (!$hasX) $errors[] = self::formatError($i, null, null, "missing X: header (reference number)");
            if (!$hasT) $errors[] = self::formatError($i, $tuneX, null, "missing T: header (title)");
            if (!$hasK) {
                if ($isBagpipe) {
                    $errors[] = self::formatError($i, $tuneX, null, "missing K: header (key signature). For bagpipe tunes, add K:HP after headers.");
                } else {
                    $errors[] = self::formatError($i, $tuneX, null, "missing K: header (key signature)");
                }
            }
            // Header order check (X: should be first)
            if (!empty($headerOrder) && $headerOrder[0] !== 'X') {
            // If no voices have transpose, suggest adding transpose=2 and secondary title
            if (!$voiceHasTranspose && count($voices) > 0) {
                $errors[] = "Tune " . ($i+1) . ": No voices have transpose. Suggest: add transpose=2 to all voices.";
                $errors[] = "Tune " . ($i+1) . ": Suggest adding secondary title T:Bagpipe Key Relative after the main title.";
            }
                $errors[] = self::formatError($i, $tuneX, null, "header X: should be first");
            }
            // Drum voice check
            foreach ($drumVoiceLines as $drumLine) {
                $errors[] = self::formatError($i, $tuneX, $drumLine+1, "Drum voice detected. Add [K:C] after this voice declaration.");
            }
        }
        return $errors;
    }
}
