<?php
namespace Ksfraser\PhpabcCanntaireachd;

class ParseContext implements \ArrayAccess {
    public $currentVoice = null;
    // Track current bar number per voice
    public $currentBar = [];
    public $voiceBars;
    // Add these for handler-based parsing context
    public $headerLines = [];
    public $formattingLines = [];
    public $voices = [];

    public function __construct(array &$voiceBars) {
        $this->voiceBars = &$voiceBars;
    }

    public function getOrCreateVoice(string $voiceId) {
        if (!isset($this->voiceBars[$voiceId])) {
            $this->voiceBars[$voiceId] = [];
        }
        $this->currentVoice = $voiceId;
        return $this->voiceBars[$voiceId];
    }

    // Increment and return the current bar number for a given voice
    public function incrementBar($voiceId = null): int {
        $voice = $voiceId ?? $this->currentVoice;
        if (!isset($this->currentBar[$voice])) {
            $this->currentBar[$voice] = 1;
        } else {
            $this->currentBar[$voice]++;
        }
        return $this->currentBar[$voice];
    }

    // Get the current bar number for a given voice
    public function getCurrentBar($voiceId = null): int {
        $voice = $voiceId ?? $this->currentVoice;
        return $this->currentBar[$voice] ?? 1;
    }

    // ArrayAccess methods for compatibility with handlers using array syntax
    public function offsetExists(mixed $offset): bool {
        return isset($this->$offset);
    }
    public function offsetGet(mixed $offset): mixed {
        return $this->$offset ?? null;
    }
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->$offset = $value;
    }
    public function offsetUnset(mixed $offset): void {
        unset($this->$offset);
    }
}
