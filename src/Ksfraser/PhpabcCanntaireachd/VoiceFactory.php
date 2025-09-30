// MOVED TO Voices/VoiceFactory.php
<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * VoiceFactory: Creates AbcVoice objects with instrument defaults.
 *
 * Loads defaults from PHP array (can be extended to load from DB/config).
 */
class VoiceFactory {
    protected string $voiceIndicator = '';
    protected string $name = '';
    protected string $sname = '';
    protected ?string $stem = null;
    protected ?string $gstem = null;
    protected int $octave = 0;
    protected int $transpose = 0;
    protected $callback = null;
    protected ?string $clef = null;

    public function __construct(
        string $voiceIndicator = '',
        string $name = '',
        string $sname = '',
        ?string $stem = null,
        ?string $gstem = null,
        int $octave = 0,
        int $transpose = 0,
        $callback = null,
        ?string $clef = null
    ) {
        $this->setVoiceIndicator($voiceIndicator);
        $this->setName($name);
        $this->setSName($sname);
        $this->setStem($stem);
        $this->setGStem($gstem);
        $this->setOctave($octave);
        $this->setTranspose($transpose);
        $this->setCallback($callback);
        $this->setClef($clef);
    }

    public function setVoiceIndicator(string $voiceIndicator): void {
        if ($voiceIndicator === '' || strlen($voiceIndicator) > 8) {
            throw new \InvalidArgumentException('Invalid voice indicator for ABC spec.');
        }
        $this->voiceIndicator = $voiceIndicator;
    }
    public function getVoiceIndicator(): string { return $this->voiceIndicator; }
    public function setName(string $name): void { $this->name = $name; }
    public function getName(): string { return $this->name; }
    public function setSName(string $sname): void { $this->sname = $sname; }
    public function getSName(): string { return $this->sname; }
    public function setStem(?string $stem): void {
        if ($stem !== null && !in_array($stem, ['up', 'down', 'auto'])) {
            throw new \InvalidArgumentException('Invalid stem value for ABC spec.');
        }
        $this->stem = $stem;
    }
    public function getStem(): ?string { return $this->stem; }
    public function setGStem(?string $gstem): void {
        if ($gstem !== null && !in_array($gstem, ['up', 'down', 'auto'])) {
            throw new \InvalidArgumentException('Invalid gstem value for ABC spec.');
        }
        $this->gstem = $gstem;
    }
    public function getGStem(): ?string { return $this->gstem; }
    public function setOctave(int $octave): void {
        if ($octave < -2 || $octave > 2) {
            throw new \InvalidArgumentException('Octave out of ABC spec range.');
        }
        $this->octave = $octave;
    }
    public function getOctave(): int { return $this->octave; }
    public function setTranspose(int $transpose): void { $this->transpose = $transpose; }
    public function getTranspose(): int { return $this->transpose; }
    public function setCallback($callback): void { $this->callback = $callback; }
    public function getCallback() { return $this->callback; }
    public function setClef(?string $clef): void {
        $allowed = [null, 'treble', 'bass', 'baritone', 'tenor', 'alto', 'mezzo', 'soprano'];
        if (!in_array($clef, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid clef for ABC spec.');
        }
        $this->clef = $clef;
    }
    public function getClef(): ?string { return $this->clef; }
    public function createVoice(): AbcVoice {
        return new AbcVoice(
            $this->voiceIndicator,
            $this->name,
            $this->sname,
            $this->stem,
            $this->gstem,
            $this->octave,
            $this->transpose,
            $this->callback,
            $this->clef
        );
    }
}



