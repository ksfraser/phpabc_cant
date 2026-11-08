<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Log\FlowLog;
use ksfraser\PhpabcCanntaireachd\Voices\TargetVoiceResolver;

/**
 * Class AbcCanntaireachdPass
 *
 * Generates canntaireachd for the target voices of an ABC tune and emits it as
 * a `w:` line directly beneath the ABC line it belongs to.
 *
 * The pass is line-preserving: every input line is emitted unchanged, in its
 * original position and with its original spacing. The only additions are the
 * generated `w:` lines.
 *
 * Voice handling:
 *  - a "V:<id>" header sets the current voice, and declares its name;
 *  - an inline "[V:<id>]" marker switches the current voice and carries the
 *    music of that line;
 *  - a bare music line belongs to the current voice.
 *
 * A voice is a target when its identifier or its declared name matches
 * {@see TargetVoiceResolver::DEFAULT_TARGET_VOICES} ("Melody", "Bagpipes",
 * "Pipes"), case-insensitively. All lines of every target voice are processed.
 *
 * If a target music line already has `w:` lines beneath it they are kept, and
 * the generated canntaireachd is added after them.
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @requirement FR2, FR8
 *
 * @since 2.1.0
 */
class AbcCanntaireachdPass
{
    /** @var TokenDictionary */
    private TokenDictionary $dict;

    /** @var TargetVoiceResolver */
    private TargetVoiceResolver $resolver;

    /** @var bool Whether pipebandsnare drumline conversion is enabled */
    private bool $pipebandsnare;

    /** @var string|null Pipebandsnare error message (set when snare has != 2 pitches) */
    private ?string $pipebandsnareError = null;

    /** @var bool Whether knock-on attack roll intro is enabled */
    private bool $knockon;

    /** @var bool Whether to prefix every music line with voice/time/bars */
    private bool $voiceLines;

    /**
     * Constructor for AbcCanntaireachdPass.
     * Uses DI to inject TokenDictionary.
     *
     * @param TokenDictionary|array $dict The token dictionary for translations.
     * @param string[]|null $targetVoices Voice names that receive canntaireachd.
     * @param bool $pipebandsnare Whether pipebandsnare drumline conversion is enabled.
     * @param bool $knockon Whether knock-on attack roll intro is enabled.
     * @param bool $voiceLines Whether to prefix every music line with voice/time/bars.
     * @requirement FR7
     */
    public function __construct($dict, ?array $targetVoices = null, bool $pipebandsnare = false, bool $knockon = false, bool $voiceLines = false)
    {
        if ($dict instanceof TokenDictionary) {
            $this->dict = $dict;
        } else {
            $td = new TokenDictionary();
            $td->prepopulate((array) $dict);
            $this->dict = $td;
        }
        $this->resolver = new TargetVoiceResolver($targetVoices);
        $this->pipebandsnare = $pipebandsnare;
        $this->knockon = $knockon;
        $this->voiceLines = $voiceLines;
    }

    /**
     * Processes input to generate canntaireachd for the target voices.
     *
     * @param string[]|\ksfraser\PhpabcCanntaireachd\Tune\AbcTune $lines The input ABC lines or an AbcTune object.
     * @return array{lines: string[], canntDiff: array<int, array{line: string, generated: string}>}
     *         The processed lines with canntaireachd added, and a diff log.
     * @requirement FR2, FR8
     */
    public function process($lines): array
    {
        if ($lines instanceof \ksfraser\PhpabcCanntaireachd\Tune\AbcTune) {
            return $this->processTune($lines);
        }

        $canntDiff = [];
        $output = [];

        $generator = new CanntGenerator($this->dict);
        $voiceNames = $this->resolver->collectVoiceNames($lines);
        $currentVoiceId = null;
        $currentVoiceName = null;

        // Step 1: Pipebandsnare transformation (BEFORE knock-on so all voices get rests)
        if ($this->pipebandsnare) {
            $lines = $this->applyPipebandSnare($lines, $canntDiff);
        }

        // Knock-on: prepend rest measures before all voices for attack roll intro
        if ($this->knockon) {
            $output = array_merge($output, $this->generateKnockOnRests($lines));
        }

        // Voice-lines: collect M: and L: per voice from headers
        $voiceMeter = [];
        $voiceUnit = [];
        $scanVoiceId = null;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) === 1) {
                $scanVoiceId = $matches[1];
                continue;
            }
            if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $vm) === 1) {
                $scanVoiceId = $vm[1];
                continue;
            }
            if ($scanVoiceId !== null && preg_match('/^M:\s*(\S+)/i', $trimmed, $m) === 1) {
                $voiceMeter[$scanVoiceId] = $m[1];
            }
            if ($scanVoiceId !== null && preg_match('/^L:\s*(\S+)/i', $trimmed, $m) === 1) {
                $voiceUnit[$scanVoiceId] = $m[1];
            }
        }

        $count = count($lines);
        $firstLineAfterVoice = true;   // track anacrusis: first music line may be partial
        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            // A new tune starts a new voice context.
            if (preg_match('/^X:/i', $trimmed) === 1) {
                $currentVoiceId = null;
                $currentVoiceName = null;
                $firstLineAfterVoice = true;
                $output[] = $line;
                continue;
            }

            // A voice header declares the current voice and, optionally, its name.
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) === 1) {
                $currentVoiceId = $matches[1];
                $currentVoiceName = TargetVoiceResolver::extractName($matches[2]);
                if ($currentVoiceName !== null) {
                    $voiceNames[$currentVoiceId] = $currentVoiceName;
                }
                $firstLineAfterVoice = true;
                $output[] = $line;
                continue;
            }

            // An inline [V:id] marker switches the current voice.
            if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $matches) === 1) {
                $currentVoiceId = $matches[1];
                $currentVoiceName = $voiceNames[$currentVoiceId] ?? null;
                $firstLineAfterVoice = true;
            }

            // Voice-lines: prefix music lines with voice/time/bars
            if ($this->voiceLines && $this->isMusicLine($line)) {
                $vid = $currentVoiceId ?? '';
                $meter = $voiceMeter[$vid] ?? '4/4';
                $unit = $voiceUnit[$vid] ?? '1/8';
                $prefix = '[V:' . $vid . '][M:' . $meter . '][L:' . $unit . ']';
                $barStart = $firstLineAfterVoice ? '|' : 'x|';
                $firstLineAfterVoice = false;
                $line = $prefix . ' ' . $barStart . ' ' . ltrim($trimmed) . ' |';
            }

            // For non-pipebandsnare lines, output the (possibly voiceLines-modified) line
            $isTarget = $this->resolver->isTarget($currentVoiceId, $currentVoiceName);
            if (!$isTarget || !$this->isMusicLine($line)) {
                $output[] = $line;
                continue;
            }

            $output[] = $line;

            // Keep any hand-written lyrics that already sit under this line …
            while ($i + 1 < $count && preg_match('/^w:/i', trim($lines[$i + 1])) === 1) {
                $output[] = $lines[++$i];
            }

            // … and add the generated canntaireachd underneath them.
            $canntText = $this->normaliseCanntaireachd($generator->generateForNotes($line));
            if ($canntText !== '' && $canntText !== '[?]') {
                $output[] = 'w: ' . $canntText;
                $canntDiff[] = [
                    'line' => $line,
                    'generated' => $canntText,
                ];
            }
        }

        return ['lines' => $output, 'canntDiff' => $canntDiff];
    }

    /**
     * Check if a voice is a snare/drumline voice.
     */
    private function isSnareVoice(?string $voiceId, ?string $voiceName = null): bool
    {
        $value = strtolower(trim($voiceId ?? ''));
        if ($value !== '' && ($value === 'snare' || str_starts_with($value, 'snare') || str_ends_with($value, 'snare'))) {
            return true;
        }
        if ($voiceName !== null) {
            $name = strtolower(trim($voiceName));
            if ($name !== '' && ($name === 'snare' || str_starts_with($name, 'snare') || str_ends_with($name, 'snare'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Collect all ABC note pitches from a music line body.
     */
    private function collectPitches(string $body, array &$pitches): void
    {
        // Match notes: A-G, a-g, optionally with accidentals (#, b, ^, =, _) and octave markers (, ')
        preg_match_all('/[A-Ga-g][#^=_b]?[,]*[\']*/', $body, $matches);
        foreach ($matches[0] as $note) {
            $pitches[$note] = true;
        }
    }

    /**
     * Convert a music line to drumline C/D notation.
     * Higher pitch → D (Right), lower pitch → C (Left).
     */
    private function convertToDrumline(string $line, array $pitches): string
    {
        if (count($pitches) !== 2) {
            // Not exactly 2 distinct pitches - leave unchanged
            return $line;
        }

        // Sort pitches by MIDI-ish value: uppercase = lower octave (lower pitch), lowercase = higher octave
        // Within same case: C < D < E < F < G < A < B
        $pitchList = array_keys($pitches);
        usort($pitchList, function ($a, $b) {
            return $this->pitchValue($a) <=> $this->pitchValue($b);
        });

        $lowerPitch = $pitchList[0]; // → C (Left)
        $upperPitch = $pitchList[1]; // → D (Right)

        // Replace all occurrences of lowerPitch with C, upperPitch with D
        $result = $line;
        // Need word-boundary aware replacement to avoid partial matches
        $result = preg_replace('/\b' . preg_quote($lowerPitch, '/') . '\b/i', 'C', $result);
        $result = preg_replace('/\b' . preg_quote($upperPitch, '/') . '\b/i', 'D', $result);

        return $result;
    }

    /**
     * Get a numeric pitch value for sorting.
     */
    private function pitchValue(string $note): int
    {
        $base = strtoupper($note[0]);
        $order = ['C' => 0, 'D' => 1, 'E' => 2, 'F' => 3, 'G' => 4, 'A' => 5, 'B' => 6];
        $baseVal = $order[$base] ?? 0;
        $isLowercase = ctype_lower($note);
        // Lowercase notes are higher octave (higher pitch)
        return $isLowercase ? $baseVal + 7 : $baseVal;
    }

    /**
     * Generate drumline canntaireachd: C=la (Left), D=ra (Right).
     */
    private function generateDrumlineCannt(string $line, array $pitches): string
    {
        if (count($pitches) !== 2) {
            return '';
        }

        $pitchList = array_keys($pitches);
        usort($pitchList, function ($a, $b) {
            return $this->pitchValue($a) <=> $this->pitchValue($b);
        });

        $lowerPitch = $pitchList[0]; // → C (Left) → la
        $upperPitch = $pitchList[1]; // → D (Right) → ra

        $canntParts = [];
        // Extract notes from the line and map to syllables
        preg_match_all('/[A-Ga-g]/', $line, $matches);
        foreach ($matches[0] as $note) {
            $upper = strtoupper($note);
            if ($upper === 'C') {
                $canntParts[] = 'la';
            } elseif ($upper === 'D') {
                $canntParts[] = 'ra';
            }
        }

        return implode(' ', $canntParts);
    }

    /**
     * Process an AbcTune object: generate canntaireachd on notes in target voices.
     *
     * @param AbcTune $tune
     * @return array{lines: string[], canntDiff: array<int, array{line: string, generated: string}>}
     */
    public function processTune(\ksfraser\PhpabcCanntaireachd\Tune\AbcTune $tune): array
    {
        $canntDiff = [];
        $output = [];

        $generator = new CanntGenerator($this->dict);
        $voiceNames = $this->resolver->collectVoiceNamesFromTune($tune);
        $currentVoiceId = null;
        $currentVoiceName = null;

        $voices = $tune->getVoices();
        foreach ($voices as $voiceId => $voice) {
            $voiceNames[$voiceId] = $voice->getName() ?? $voiceId;
        }

        $count = count($voices);
        $voiceIndex = 0;
        foreach ($voices as $voiceId => $voice) {
            $currentVoiceId = $voiceId;
            $currentVoiceName = $voice->getName() ?? $voiceId;

            $bars = $tune->getVoiceBars()[$voiceId] ?? [];
            foreach ($bars as $bar) {
                if (!($bar instanceof \ksfraser\PhpabcCanntaireachd\Tune\AbcBar)) {
                    continue;
                }
                $isTarget = $this->resolver->isTarget($currentVoiceId, $currentVoiceName);
                if (!$isTarget) {
                    continue;
                }
                $bar->translateNotes(new BagpipeAbcToCanntTranslator($this->dict));
            }
        }

        return ['lines' => $output, 'canntDiff' => $canntDiff];
    }

    /**
     * Normalise generated canntaireachd: single spaces, " | " between bars.
     *
     * @param string $cannt
     * @return string
     */
    private function normaliseCanntaireachd(string $cannt): string
    {
        $cannt = preg_replace('/\s*\|\s*/', ' | ', $cannt) ?? $cannt;
        $cannt = preg_replace('/ {2,}/', ' ', $cannt) ?? $cannt;
        return trim($cannt);
    }

    /**
     * Determines if canntaireachd should be added for this voice.
     *
     * @param string|null $voiceId   Voice identifier.
     * @param string|null $voiceName Declared voice name.
     * @return bool True if canntaireachd should be added.
     */
    public function shouldAddCanntaireachd(?string $voiceId, ?string $voiceName = null): bool
    {
        return $this->resolver->isTarget($voiceId, $voiceName);
    }

    /**
     * Determines if a line is a music line (contains notes or bars).
     *
     * @param string $line The line to check.
     * @return bool True if it's a music line.
     */
    private function isMusicLine(string $line): bool
    {
        $trimmed = trim($line);
        if ($trimmed === '') {
            return false;
        }
        // Comments and %% directives are not music.
        if (str_starts_with($trimmed, '%')) {
            return false;
        }
        // Header fields (X:, T:, K:, w:, …) are not music.
        if (preg_match('/^[A-Za-z]:/', $trimmed) === 1) {
            return false;
        }
        // Strip an inline voice marker before looking for notes.
        $body = preg_replace('/^\[V:[^\]]*\]/', '', $trimmed) ?? $trimmed;
        $body = trim($body);
        if ($body === '') {
            return false;
        }
        // A music line carries at least one note or rest. A line of nothing
        // but bar lines has no syllables to sing.
        return preg_match('/[A-Ga-gzZ]/', $body) === 1;
    }

    /**
     * Generate knock-on (attack roll intro) measures for all voices.
     *
     * Prepends rest measures for each voice so the tune begins with a
     * 12-beat knock-on intro. When pipebandsnare is active, snare voice
     * knock-on uses C/D drumline notation.
     *
     * @param string[] $lines Original ABC lines (used to discover voices).
     * @return string[] Knock-on lines to prepend.
     */
    private function generateKnockOnRests(array $lines): array
    {
    // Discover all voice IDs declared in the file
    $voiceIds = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^V:\s*(\S+)/', $trimmed, $m) === 1) {
            $voiceIds[$m[1]] = true;
        }
        if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $m) === 1) {
            $voiceIds[$m[1]] = true;
        }
    }

    // Extract L: time unit denominator (default L:1/8)
    // Quarter note multiplier = L_denominator / 4
    //   L:1/8 → z2 (quarter note = 2 eighth notes)
    //   L:1/16 → z4 (quarter note = 4 sixteenth notes)
    $lDenominator = 8;
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^L:\s*1\/(\d+)/', $trimmed, $m) === 1) {
            $lDenominator = (int)$m[1];
            break;
        }
    }
    $quarterNoteMult = max(1, (int)($lDenominator / 4));
    $restUnit = 'z' . $quarterNoteMult;
    $restBar = $restUnit . ' ' . $restUnit;
    $rest12Beats = implode(' | ', array_fill(0, 6, $restBar)) . ' ||';

    $knockOn = [];
    $knockOn[] = '%% Knock-on attack roll intro';

    foreach ($voiceIds as $voiceId => $_) {
        $isSnare = $this->isSnareVoice($voiceId);
        $knockOn[] = 'V:' . $voiceId . ' [M:2/4][L:' . '1/' . $lDenominator . ']';

        if ($isSnare && $this->pipebandsnare) {
            // Snare knock-on: attack roll in C/D drumline notation
            // c2 c2 = R R, c/>A/c = R-L-R, Rc-Rc-Rc-Rc = roll
            $knockOn[] = '!fff! C C | C>/D/C C>/D/C | (C-D-C-D | C) z2 | (C-D-C-D | C) z2 ||';
        } elseif ($isSnare) {
            // Snare knock-on: attack roll in original notation
            $knockOn[] = '!fff! c2 c2 | c/>A/c c/>A/c | (Rc-Rc-Rc-Rc | c2) z2 | (Rc-Rc-Rc-Rc | c2) z2 ||';
        } else {
            // Other voices: rest for 12 beats using correct L: unit
            $knockOn[] = $rest12Beats;
        }
    }

    $knockOn[] = '%% End knock-on';
    return $knockOn;
    }

    /**
     * Check if a voice is a drumline voice.
     */
    private function isDrumlineVoice(?string $voiceId, ?string $voiceName = null): bool
    {
        $value = strtolower(trim($voiceId ?? ''));
        if ($value !== '' && ($value === 'drumline' || str_starts_with($value, 'drumline') || str_ends_with($value, 'drumline'))) {
            return true;
        }
        if ($voiceName !== null) {
            $name = strtolower(trim($voiceName));
            if ($name !== '' && ($name === 'drumline' || str_starts_with($name, 'drumline') || str_ends_with($name, 'drumline'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Convert drumline C/D notation back to ABC snare notes (A/c).
     * C → A (Left/la), D → c (Right/ra).
     * Uses the drumline pitches to determine mapping direction.
     */
    private function convertFromDrumline(string $line, array $pitches): string
    {
        if (count($pitches) !== 2) {
            return $line;
        }

        $pitchList = array_keys($pitches);
        usort($pitchList, function ($a, $b) {
            return $this->pitchValue($a) <=> $this->pitchValue($b);
        });

        $lowerPitch = $pitchList[0]; // C (Left) → A
        $upperPitch = $pitchList[1]; // D (Right) → c

        $result = $line;
        $result = preg_replace('/\b' . preg_quote($lowerPitch, '/') . '\b/i', 'A', $result);
        $result = preg_replace('/\b' . preg_quote($upperPitch, '/') . '\b/i', 'c', $result);

        return $result;
    }

    /**
     * Generate reverse drumline canntaireachd: C=la→A, D=ra→c.
     */
    private function generateReverseDrumlineCannt(string $line, array $pitches): string
    {
        if (count($pitches) !== 2) {
            return '';
        }

        $pitchList = array_keys($pitches);
        usort($pitchList, function ($a, $b) {
            return $this->pitchValue($a) <=> $this->pitchValue($b);
        });

        $lowerPitch = $pitchList[0]; // C (Left) → la → A
        $upperPitch = $pitchList[1]; // D (Right) → ra → c

        $canntParts = [];
        preg_match_all('/[A-Ga-g]/', $line, $matches);
        foreach ($matches[0] as $note) {
            $upper = strtoupper($note);
            if ($upper === 'C') {
                $canntParts[] = 'en';   // A → en
            } elseif ($upper === 'D') {
                $canntParts[] = 'o';    // c → o
            }
        }

        return implode(' ', $canntParts);
    }

    /**
     * Apply pipebandsnare transformation to lines BEFORE knock-on.
     *
     * Actions:
     *   add:    Snare exists, no Drumline → append V:Drumline with converted Snare
     *   replace: Snare + Drumline → skip Drumline content, append V:Drumline with converted Snare
     *   reverse: Drumline only, no Snare → append V:Snare with reverse-converted Drumline
     *   error:   Snare has 3+ pitches → return unchanged, set error message
     *
     * @param string[] $lines Original ABC lines.
     * @param array    &$canntDiff Diff log (modified in place).
     * @return string[] Modified lines.
     */
    private function applyPipebandSnare(array $lines, array &$canntDiff): array
    {
        // Pre-scan: detect snare/drumline voices, collect pitches
        $snareVoiceIds = [];
        $drumlineVoiceIds = [];
        $snarePitches = [];
        $drumlinePitches = [];

        $scanVoiceId = null;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) === 1) {
                $scanVoiceId = $matches[1];
                $vname = TargetVoiceResolver::extractName($matches[2]);
                if ($this->isSnareVoice($scanVoiceId, $vname)) {
                    $snareVoiceIds[$scanVoiceId] = true;
                }
                if ($this->isDrumlineVoice($scanVoiceId, $vname)) {
                    $drumlineVoiceIds[$scanVoiceId] = true;
                }
                continue;
            }
            if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $vm) === 1) {
                $scanVoiceId = $vm[1];
                continue;
            }
            if ($trimmed !== '' && !str_starts_with($trimmed, '%') && !preg_match('/^[A-Za-z]:/', $trimmed)) {
                if (isset($snareVoiceIds[$scanVoiceId ?? ''])) {
                    $this->collectPitches($trimmed, $snarePitches);
                }
                if (isset($drumlineVoiceIds[$scanVoiceId ?? ''])) {
                    $this->collectPitches($trimmed, $drumlinePitches);
                }
            }
        }

        // Determine action
        $hasSnare = !empty($snareVoiceIds);
        $hasDrumline = !empty($drumlineVoiceIds);

        if ($hasSnare && count($snarePitches) > 2) {
            $this->pipebandsnareError = 'Snare voice uses ' . count($snarePitches) .
                ' distinct pitches (' . implode(', ', array_keys($snarePitches)) .
                '); drumline requires exactly 2';
            return $lines;
        }

        if ($hasSnare && $hasDrumline) {
            $action = 'replace';
        } elseif ($hasSnare) {
            $action = 'add';
        } elseif ($hasDrumline) {
            $action = 'reverse';
        } else {
            return $lines;
        }

        // Get M:/L: for the source voice (for the new voice header)
        $meter = '2/4';
        $unit = '1/8';
        $sourceVoiceId = $hasSnare ? array_key_first($snareVoiceIds) : array_key_first($drumlineVoiceIds);
        $foundMeter = false;
        $foundUnit = false;
        $scanVoiceId = null;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)/', $trimmed, $m) === 1) {
                $scanVoiceId = $m[1];
                continue;
            }
            if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $vm) === 1) {
                $scanVoiceId = $vm[1];
                continue;
            }
            if ($scanVoiceId === $sourceVoiceId && !$foundMeter && preg_match('/^M:\s*(\S+)/i', $trimmed, $m) === 1) {
                $meter = $m[1];
                $foundMeter = true;
            }
            if ($scanVoiceId === $sourceVoiceId && !$foundUnit && preg_match('/^L:\s*(\S+)/i', $trimmed, $m) === 1) {
                $unit = $m[1];
                $foundUnit = true;
            }
            if ($foundMeter && $foundUnit) {
                break;
            }
        }

        $result = [];
        $currentVoiceId = null;
        $inDrumlineVoice = false;
        $drumlineSkipped = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Track voice changes
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) === 1) {
                $currentVoiceId = $matches[1];
                $inDrumlineVoice = $this->isDrumlineVoice($currentVoiceId);

                // For 'replace': skip original drumline header
                if ($action === 'replace' && $inDrumlineVoice && !$drumlineSkipped) {
                    $drumlineSkipped = true;
                    continue;
                }

                $result[] = $line;
                continue;
            }

            if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $vm) === 1) {
                $currentVoiceId = $vm[1];
                $inDrumlineVoice = $this->isDrumlineVoice($currentVoiceId);
                $result[] = $line;
                continue;
            }

            // Skip drumline music lines when replacing
            if ($action === 'replace' && $inDrumlineVoice) {
                continue;
            }

            // Skip drumline voice entirely when adding (it's created from snare)
            if ($action === 'add' && $inDrumlineVoice) {
                continue;
            }

            $result[] = $line;
        }

        // Append new voice at end
        if ($action === 'add' || $action === 'replace') {
            // Convert snare music lines to C/D drumline
            $firstSnare = array_key_first($snareVoiceIds);
            $snareMusicLines = [];
            $scanVoiceId = null;
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (preg_match('/^V:\s*(\S+)/', $trimmed, $m) === 1) {
                    $scanVoiceId = $m[1];
                    continue;
                }
                if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $vm) === 1) {
                    $scanVoiceId = $vm[1];
                    continue;
                }
                if ($scanVoiceId === $firstSnare && $this->isMusicLine($trimmed)) {
                    $snareMusicLines[] = $trimmed;
                }
            }

            $result[] = 'V:Drumline name="Drumline" [M:' . $meter . '][L:' . $unit . ']';
            $firstLine = true;
            foreach ($snareMusicLines as $musicLine) {
                $drumLine = $this->convertToDrumline($musicLine, $snarePitches);
                $drumCannt = $this->generateDrumlineCannt($drumLine, $snarePitches);

                $barStart = $firstLine ? '|' : 'x|';
                $firstLine = false;

                $result[] = $barStart . ' ' . $drumLine . ' |';

                if ($drumCannt !== '') {
                    $result[] = 'w: ' . $drumCannt;
                    $canntDiff[] = ['line' => $musicLine, 'generated' => $drumCannt];
                }
            }
        }

        if ($action === 'reverse') {
            // Convert drumline music lines back to snare A/c notation
            $firstDrumline = array_key_first($drumlineVoiceIds);
            $drumlineMusicLines = [];
            $scanVoiceId = null;
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (preg_match('/^V:\s*(\S+)/', $trimmed, $m) === 1) {
                    $scanVoiceId = $m[1];
                    continue;
                }
                if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $vm) === 1) {
                    $scanVoiceId = $vm[1];
                    continue;
                }
                if ($scanVoiceId === $firstDrumline && $this->isMusicLine($trimmed)) {
                    $drumlineMusicLines[] = $trimmed;
                }
            }

            $result[] = 'V:Snare name="Snare" [M:' . $meter . '][L:' . $unit . ']';
            $firstLine = true;
            foreach ($drumlineMusicLines as $musicLine) {
                $snareLine = $this->convertFromDrumline($musicLine, $drumlinePitches);
                $snareCannt = $this->generateReverseDrumlineCannt($snareLine, $drumlinePitches);

                $barStart = $firstLine ? '|' : 'x|';
                $firstLine = false;

                $result[] = $barStart . ' ' . $snareLine . ' |';

                if ($snareCannt !== '') {
                    $result[] = 'w: ' . $snareCannt;
                    $canntDiff[] = ['line' => $musicLine, 'generated' => $snareCannt];
                }
            }
        }

        return $result;
    }

    /**
     * Log the pass entry/exit for flow tracing.
     *
     * @param string $message
     * @return void
     */
    private function logFlow(string $message): void
    {
        FlowLog::log('AbcCanntaireachdPass: ' . $message, true);
    }
}
