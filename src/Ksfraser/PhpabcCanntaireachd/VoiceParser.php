<?php
namespace Ksfraser\PhpabcCanntaireachd;

class VoiceParser {
    private $voices = [];
    private $currentVoice = null;
    private $currentVoiceContent = [];

    /**
     * Parse ABC content and extract voice information
     *
     * @param array $lines Array of ABC file lines
     */
    public function __construct(array $lines) {
        $this->parseVoices($lines);
    }

    /**
     * Parse voices from ABC lines
     *
     * @param array $lines
     */
    private function parseVoices(array $lines) {
        foreach ($lines as $line) {
            $this->processLine($line);
        }

        // Handle the last voice
        if ($this->currentVoice) {
            $this->finalizeCurrentVoice();
        }
    }

    /**
     * Process a single line of ABC content
     *
     * @param string $line
     */
    private function processLine(string $line) {
        $trimmed = trim($line);

        // Check if this is a voice header
        if (preg_match('/^V:([^\s]+)(.*)$/', $line, $matches)) {
            // Save previous voice if any
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
        } elseif ($this->currentVoice) {
            // Add line to current voice content
            $this->currentVoiceContent[] = $line;
        }
    }

    /**
     * Finalize the current voice and add it to the voices array
     */
    private function finalizeCurrentVoice() {
        if ($this->currentVoice) {
            $this->currentVoice['content'] = $this->currentVoiceContent;
            $this->voices[] = $this->currentVoice;
            $this->currentVoice = null;
            $this->currentVoiceContent = [];
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
     * Get all parsed voices
     *
     * @return array
     */
    public function getVoices(): array {
        return $this->voices;
    }

    /**
     * Get a specific voice by ID
     *
     * @param string $voiceId
     * @return array|null
     */
    public function getVoice(string $voiceId): ?array {
        foreach ($this->voices as $voice) {
            if ($voice['id'] === $voiceId) {
                return $voice;
            }
        }
        return null;
    }

    /**
     * Get voices by type (melody, bagpipes, etc.)
     *
     * @param string $type
     * @return array
     */
    public function getVoicesByType(string $type): array {
        $matchingVoices = [];

        foreach ($this->voices as $voice) {
            if ($this->isVoiceType($voice, $type)) {
                $matchingVoices[] = $voice;
            }
        }

        return $matchingVoices;
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
     * Validate voice syntax
     *
     * @return array Array of validation errors
     */
    public function validateVoices(): array {
        $errors = [];

        foreach ($this->voices as $index => $voice) {
            $voiceErrors = $this->validateVoice($voice, $index + 1);
            $errors = array_merge($errors, $voiceErrors);
        }

        return $errors;
    }

    /**
     * Validate a single voice
     *
     * @param array $voice
     * @param int $voiceNumber
     * @return array
     */
    private function validateVoice(array $voice, int $voiceNumber): array {
        $errors = [];

        // Check for required parameters
        if (empty($voice['params']['name'])) {
            $errors[] = "Voice $voiceNumber: Missing name parameter";
        }

        if (empty($voice['params']['sname'])) {
            $errors[] = "Voice $voiceNumber: Missing sname parameter";
        }

        // Check for empty content
        if (empty($voice['content'])) {
            $errors[] = "Voice $voiceNumber: No content found";
        }

        // Check for invalid characters in voice ID
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $voice['id'])) {
            $errors[] = "Voice $voiceNumber: Invalid voice ID '{$voice['id']}'";
        }

        return $errors;
    }

    /**
     * Get voice count
     *
     * @return int
     */
    public function getVoiceCount(): int {
        return count($this->voices);
    }

    /**
     * Check if the parsed content has any voices
     *
     * @return bool
     */
    public function hasVoices(): bool {
        return !empty($this->voices);
    }

    /**
     * Get voice IDs
     *
     * @return array
     */
    public function getVoiceIds(): array {
        return array_map(function($voice) {
            return $voice['id'];
        }, $this->voices);
    }
}
