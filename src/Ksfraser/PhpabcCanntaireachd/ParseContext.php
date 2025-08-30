<?php
namespace Ksfraser\PhpabcCanntaireachd;

class ParseContext implements \ArrayAccess {
    public $currentVoice = null;
    public $currentBar = 0;
    public $voiceBars;

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

    public function incrementBar(): int {
        $this->currentBar++;
        return $this->currentBar;
    }

    // ArrayAccess methods for backwards compatibility with handlers expecting array context
    public function offsetExists(mixed $offset): bool { return isset($this->$offset); }
    public function offsetGet(mixed $offset): mixed { return $this->$offset; }
    public function offsetSet(mixed $offset, mixed $value): void { $this->$offset = $value; }
    public function offsetUnset(mixed $offset): void { unset($this->$offset); }
}
