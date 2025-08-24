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
            foreach ($lines as $n => $line) {
                $line = trim($line);
            $melodyVoiceName = null;
            $melodyVoiceLine = null;
            $melodyNotes = [];
            $hasBagpipeVoice = false;
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
            // First pass: gather info
            foreach ($lines as $n => $line) {
                $line = trim($line);
                if ($line === '') continue;
                    }
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
                if (preg_match('/^V:([^
\s]+)(.*)$/', $line, $matches)) {
                    $voiceName = $matches[1];
                    $params = $matches[2];
                    $hasName = preg_match('/name="[^"]+"/', $params);
                    $bodyStartIdx = $n;
                    $hasSname = preg_match('/sname="[^"]+"/', $params);
                    if (!$hasName || !$hasSname) {
                        $errors[] = "Tune " . ($i+1) . ", line " . ($n+1) . ": Voice '$voiceName' missing name or sname. Suggest: name=\"$voiceName\" sname=\"$voiceName\"";
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
                }
                // Check for required headers
                    if ($firstVoiceIdx === null) $firstVoiceIdx = $n;
                if (preg_match('/^[A-Z]:/', $line) && !preg_match('/^(X:|K:|T:|M:|L:|Q:|C:|w:|W:|V:|P:|R:|S:|O:|B:|D:|F:|G:|I:|m:|U:|Z:)/', $line)) {
                    $errors[] = "Tune " . ($i+1) . ", line " . ($n+1) . ": Unknown header: $line";
                }
                if ($melodyVoiceName && $melodyVoiceLine !== null && $n > $melodyVoiceLine && !preg_match('/^[A-Z]:/', $line)) {
                    $melodyNotes[] = $line;
                }
                // Body validation
                if ($bodyStarted && !preg_match('/^[A-Z]:/', $line)) {
                    // Validate notes, barlines, lyrics, etc.
                    if (!preg_match('/^[\|\[\]a-gA-GzZ0-9\s,:!\'\"\/\^_\.\-]+$/', $line)) {
                        $errors[] = "Tune " . ($i+1) . ", line " . ($n+1) . ": Invalid body line: $line";
                    }
            // Check for missing C: and B: headers
            if (!$hasC) {
                $errors[] = "Tune " . ($i+1) . " missing C: header (composer). Suggest: add C:<composer>.";
            }
            if (!$hasB) {
                $errors[] = "Tune " . ($i+1) . " missing B: header (book). Suggest: add B:<book>.";
            }
            // Check for missing O: and Z: headers, suggest sourcing from DB or override
            if (!$hasO) {
                $errors[] = "Tune " . ($i+1) . " missing O: header (origin). Suggest: source from database or override via CLI/WordPress input.";
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
                $errors[] = "Tune " . ($i+1) . " missing Z: header (transcriber/notes). Suggest: source from database or override via CLI/WordPress input.";
            // If melody voice exists and no bagpipe voice, suggest copying melody to bagpipe
            if ($melodyVoiceName && !$hasBagpipeVoice && count($melodyNotes) > 0) {
                $errors[] = "Tune " . ($i+1) . ": Melody voice '$melodyVoiceName' found but no bagpipe voice. Suggest: copy melody voice and its notes to a new bagpipe voice.";
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
            if (!$hasX) $errors[] = "Tune " . ($i+1) . " missing X: header (reference number)";
            if (!$hasT) $errors[] = "Tune " . ($i+1) . " missing T: header (title)";
            if (!$hasK) {
                if ($isBagpipe) {
                    $errors[] = "Tune " . ($i+1) . " missing K: header (key signature). For bagpipe tunes, add K:HP after headers.";
                } else {
                    $errors[] = "Tune " . ($i+1) . " missing K: header (key signature)";
                }
            }
            // Header order check (X: should be first)
            if (!empty($headerOrder) && $headerOrder[0] !== 'X') {
            // If no voices have transpose, suggest adding transpose=2 and secondary title
            if (!$voiceHasTranspose && count($voices) > 0) {
                $errors[] = "Tune " . ($i+1) . ": No voices have transpose. Suggest: add transpose=2 to all voices.";
                $errors[] = "Tune " . ($i+1) . ": Suggest adding secondary title T:Bagpipe Key Relative after the main title.";
            }
                $errors[] = "Tune " . ($i+1) . " header X: should be first";
            }
            // Drum voice check
            foreach ($drumVoiceLines as $drumLine) {
                $errors[] = "Tune " . ($i+1) . ", line " . ($drumLine+1) . ": Drum voice detected. Add [K:C] after this voice declaration.";
            }
        }
        return $errors;
    }
}
