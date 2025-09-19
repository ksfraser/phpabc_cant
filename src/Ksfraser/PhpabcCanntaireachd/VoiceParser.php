<?php
namespace Ksfraser\PhpabcCanntaireachd;

class VoiceParser {
    private $tunes = [];
    private $currentTune = null;
    private $currentVoice = null;
    private $currentVoiceContent = [];

    /**
     * Parse ABC content and extract voice information grouped by tunes
     *
     * @param array $lines Array of ABC file lines
     */
    public function __construct(array $lines) {
        $this->parseContent($lines);
    }

    /**
     * Parse content and group voices by tunes
     *
     * @param array $lines
     */
    private function parseContent(array $lines) {
        foreach ($lines as $line) {
            $this->processLine($line);
        }

        // Handle the last voice and tune
        if ($this->currentVoice) {
            $this->finalizeCurrentVoice();
        }
        if ($this->currentTune) {
            $this->finalizeCurrentTune();
        }
    }

    /**
     * Process a single line of ABC content
     *
     * @param string $line
     */
    private function processLine(string $line) {
        $trimmed = trim($line);

        // Check if this is the start of a new tune
        if (preg_match('/^X:([^\s]*)/', $line, $matches)) {
            // Finalize previous tune and voice
            if ($this->currentVoice) {
                $this->finalizeCurrentVoice();
            }
            if ($this->currentTune) {
                $this->finalizeCurrentTune();
            }

            // Start new tune
            $tuneId = $matches[1];
            $this->currentTune = [
                'id' => $tuneId,
                'header' => $line,
                'voices' => []
            ];
        }
        // Check if this is a voice header
        elseif (preg_match('/^V:([^\s]+)(.*)$/', $line, $matches)) {
            // Finalize previous voice if any
            if ($this->currentVoice) {
                $this->finalizeCurrentVoice();
            }

            // Start new voice
            $voiceId = $matches[1];
            $voiceParams = trim($matches[2]);

            $this->currentVoice = [
                'id' => $voiceId,
                'header' => $line,
                'params' => $this->parseVoiceParams($voiceParams),
                'content' => []
            ];

            $this->currentVoiceContent = [];
        }
        elseif ($this->currentVoice && $this->isVoiceContent($line)) {
            // Add line to current voice content only if it's actual voice content
            $this->currentVoiceContent[] = $line;
        }
    }

    /**
     * Check if a line should be considered voice content
     *
     * @param string $line
     * @return bool
     */
    private function isVoiceContent(string $line): bool {
        $trimmed = trim($line);

        // Empty lines are voice content (separators)
        if ($trimmed === '') {
            return true;
        }

        // Comments are voice content
        if (preg_match('/^%/', $line)) {
            return true;
        }

        // Lyrics are voice content
        if (preg_match('/^w:/', $line)) {
            return true;
        }

        // Music notation is voice content (contains notes, rests, bar lines, etc.)
        if (preg_match('/[A-Ga-gz]|\\||\\[|\\]|\\(|\\)|!|"/', $line)) {
            return true;
        }

        // Voice-specific directives are voice content
        if (preg_match('/^\\[[A-Za-z]/', $line)) {
            return true;
        }

        // Grace notes and ornaments are voice content
        if (preg_match('/[~{}]/', $line)) {
            return true;
        }

        // Header fields that can appear within voices (like K:, M:, L:) are voice content
        if (preg_match('/^[A-Z]:/', $line)) {
            return true;
        }

        // Everything else (tune headers, MIDI directives, etc.) is not voice content
        return false;
    }

    /**
     * Finalize the current voice and add it to the current tune
     */
    private function finalizeCurrentVoice() {
        if ($this->currentVoice && $this->currentTune) {
            $this->currentVoice['content'] = $this->currentVoiceContent;
            $this->currentTune['voices'][] = $this->currentVoice;
            $this->currentVoice = null;
            $this->currentVoiceContent = [];
        }
    }

    /**
     * Finalize the current tune and add it to the tunes array
     */
    private function finalizeCurrentTune() {
        if ($this->currentTune) {
            $this->tunes[] = $this->currentTune;
            $this->currentTune = null;
        }
    }

    /**
     * Parse voice parameters from the V: line
     *
     * @param string $params
     * @return array
     */
    private function parseVoiceParams(string $params): array {
        $parsed = [];

        // Parse common parameters like name, sname, clef, etc.
        $paramPairs = explode(' ', $params);

        foreach ($paramPairs as $pair) {
            if (preg_match('/^([^=]+)="?([^"]*)"?$/', $pair, $matches)) {
                $key = $matches[1];
                $value = $matches[2];
                $parsed[$key] = $value;
            }
        }

        return $parsed;
    }

    /**
     * Get all parsed tunes with their voices
     *
     * @return array
     */
    public function getTunes(): array {
        return $this->tunes;
    }

    /**
     * Get a specific tune by ID (optimized with associative array)
     *
     * @param string $tuneId
     * @return array|null
     */
    public function getTune(string $tuneId): ?array {
        // For better performance with many tunes, we could use an associative array
        // $tunesById = array_column($this->tunes, null, 'id');
        // return $tunesById[$tuneId] ?? null;
        
        // Current implementation is fine for typical use cases
        foreach ($this->tunes as $tune) {
            if ($tune['id'] === $tuneId) {
                return $tune;
            }
        }
        return null;
    }

    /**
     * Get all voices across all tunes
     *
     * @return array
     */
    public function getAllVoices(): array {
        $allVoices = [];
        foreach ($this->tunes as $tune) {
            $allVoices = array_merge($allVoices, $tune['voices']);
        }
        return $allVoices;
    }

    /**
     * Get voices for a specific tune
     *
     * @param string $tuneId
     * @return array
     */
    public function getVoicesForTune(string $tuneId): array {
        $tune = $this->getTune($tuneId);
        return $tune ? $tune['voices'] : [];
    }

    /**
     * Get a specific voice by tune ID and voice ID
     *
     * @param string $tuneId
     * @param string $voiceId
     * @return array|null
     */
    public function getVoice(string $tuneId, string $voiceId): ?array {
        $voices = $this->getVoicesForTune($tuneId);
        foreach ($voices as $voice) {
            if ($voice['id'] === $voiceId) {
                return $voice;
            }
        }
        return null;
    }

    /**
     * Get voices by type across all tunes
     *
     * @param string $type
     * @return array
     */
    public function getVoicesByType(string $type): array {
        $matchingVoices = [];
        foreach ($this->tunes as $tune) {
            foreach ($tune['voices'] as $voice) {
                if ($this->isVoiceType($voice, $type)) {
                    $matchingVoices[] = $voice;
                }
            }
        }
        return $matchingVoices;
    }

    /**
     * Get voices by type for a specific tune
     *
     * @param string $tuneId
     * @param string $type
     * @return array
     */
    public function getVoicesByTypeForTune(string $tuneId, string $type): array {
        $voices = $this->getVoicesForTune($tuneId);
        return array_filter($voices, function($voice) use ($type) {
            return $this->isVoiceType($voice, $type);
        });
    }

    /**
     * Check if a voice matches a specific type
     *
     * @param array $voice
     * @param string $type
     * @return bool
     */
    private function isVoiceType(array $voice, string $type): bool {
        $voiceId = strtolower($voice['id']);
        $name = strtolower($voice['params']['name'] ?? '');

        switch ($type) {
            case 'melody':
                return $voiceId === 'melody' || $voiceId === 'm' || $name === 'melody';
            case 'bagpipes':
                return $voiceId === 'bagpipes' || $voiceId === 'b' || $name === 'bagpipes';
            case 'guitar':
                return $voiceId === 'guitar' || $voiceId === 'g' || $name === 'guitar';
            default:
                return $voiceId === strtolower($type);
        }
    }

    /**
     * Validate voices across all tunes
     *
     * @return array Array of validation errors
     */
    public function validateVoices(): array {
        $errors = [];

        foreach ($this->tunes as $tuneIndex => $tune) {
            foreach ($tune['voices'] as $voiceIndex => $voice) {
                $voiceErrors = $this->validateVoice($voice, $tuneIndex + 1, $voiceIndex + 1);
                $errors = array_merge($errors, $voiceErrors);
            }
        }

        return $errors;
    }

    /**
     * Validate a single voice
     *
     * @param array $voice
     * @param int $tuneNumber
     * @param int $voiceNumber
     * @return array
     */
    private function validateVoice(array $voice, int $tuneNumber, int $voiceNumber): array {
        $errors = [];

        // Check for required parameters
        if (empty($voice['params']['name'])) {
            $errors[] = "Tune $tuneNumber, Voice $voiceNumber: Missing name parameter";
        }

        if (empty($voice['params']['sname'])) {
            $errors[] = "Tune $tuneNumber, Voice $voiceNumber: Missing sname parameter";
        }

        // Check for empty content
        if (empty($voice['content'])) {
            $errors[] = "Tune $tuneNumber, Voice $voiceNumber: No content found";
        }

        // Check for invalid characters in voice ID
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $voice['id'])) {
            $errors[] = "Tune $tuneNumber, Voice $voiceNumber: Invalid voice ID '{$voice['id']}'";
        }

        return $errors;
    }

    /**
     * Get total voice count across all tunes
     *
     * @return int
     */
    public function getVoiceCount(): int {
        $count = 0;
        foreach ($this->tunes as $tune) {
            $count += count($tune['voices']);
        }
        return $count;
    }

    /**
     * Get tune count
     *
     * @return int
     */
    public function getTuneCount(): int {
        return count($this->tunes);
    }

    /**
     * Check if the parsed content has any tunes with voices
     *
     * @return bool
     */
    public function hasVoices(): bool {
        foreach ($this->tunes as $tune) {
            if (!empty($tune['voices'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all tune IDs
     *
     * @return array
     */
    public function getTuneIds(): array {
        return array_map(function($tune) {
            return $tune['id'];
        }, $this->tunes);
    }

    /**
     * Get all voice IDs across all tunes
     *
     * @return array
     */
    public function getVoiceIds(): array {
        $voiceIds = [];
        foreach ($this->tunes as $tune) {
            foreach ($tune['voices'] as $voice) {
                $voiceIds[] = $voice['id'];
            }
        }
        return $voiceIds;
    }
}
