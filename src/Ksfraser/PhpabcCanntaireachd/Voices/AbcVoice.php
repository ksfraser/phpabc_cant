<?php
namespace Ksfraser\PhpabcCanntaireachd\Voices;

/**
 * Class AbcVoice
 *
 * Represents a voice in ABC notation.
 *
 * @uml
 * @startuml
 * class AbcVoice {
 *   - voiceIndicator: string
 *   - name: string
 *   - sname: string
 *   - stem: string|null
 *   - gstem: string|null
 *   - octave: int
 *   - transpose: int
 *   - callback: callable|null
 *   - clef: string|null
 *   + __construct(...)
 *   + getHeaderOut(): string
 *   + getBodyOut(): string
 *   + getLineStartOut(): string
 *   + getVoiceIndicator(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AbcVoice
{
    /**
     * @var array Bars assigned to this voice
     */
    public $bars = [];
    /**
     * Get the voice indicator (V: field)
     * @return string
     */
    public function getVoiceIndicator()
    {
        return $this->voiceIndicator;
    }

    /**
     * Get the name of the voice
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    private string $voiceIndicator;
    private string $name;
    private string $sname;
    private ?string $stem;
    private ?string $gstem;
    private int $octave;
    private int $transpose;
    private $callback;
    private ?string $clef;
    protected array $lyricsLines = [];
    /**
     * Add a lyrics line to this voice.
     * @param string $lyrics
     */
    public function addLyricsLine(string $lyrics): void
    {
        $this->lyricsLines[] = $lyrics;
    }

    /**
     * Render all lyrics lines for this voice.
     * @return array of strings (each a w: line)
     */
    public function renderLyrics(): array
    {
        $out = [];
        foreach ($this->lyricsLines as $lyrics) {
            $out[] = 'w: ' . $lyrics;
        }
        return $out;
    }

    public function __construct(
        string $voiceIndicator,
        string $name = '',
        string $sname = '',
        ?string $stem = null,
        ?string $gstem = null,
        int $octave = 0,
        int $transpose = 0,
        $callback = null,
        ?string $clef = null
    ) {
        $this->voiceIndicator = $voiceIndicator;
        $this->name = $name;
        $this->sname = $sname;
        $this->stem = $stem;
        $this->gstem = $gstem;
        $this->octave = $octave;
        $this->transpose = $transpose;
        $this->callback = $callback;
        $this->clef = $clef;
    }

    public function getHeaderOut(): string
    {
        $out = "V:" . $this->voiceIndicator;
        if ($this->name !== '') {
            $out .= ' name="' . $this->name . '"';
        }
        if ($this->sname !== '') {
            $out .= ' sname="' . $this->sname . '"';
        }
        if ($this->stem !== null) {
            $out .= " stem=" . $this->stem;
        }
        if ($this->gstem !== null) {
            $out .= " gstem=" . $this->gstem;
        }
        $out .= " octave=" . $this->octave;
        $out .= " transpose=" . $this->transpose;
        if ($this->clef !== null) {
            $out .= ' clef="' . $this->clef . '"';
        }
        return $out;
    }

    public function getBodyOut(): string
    {
        $out = "[V:" . $this->voiceIndicator;
        if ($this->name !== '') {
            $out .= ' name="' . $this->name . '"';
        }
        if ($this->sname !== '') {
            $out .= ' sname="' . $this->sname . '"';
        }
        if ($this->stem !== null) {
            $out .= " stem=" . $this->stem;
        }
        if ($this->gstem !== null) {
            $out .= " gstem=" . $this->gstem;
        }
        $out .= " octave=" . $this->octave;
        $out .= " transpose=" . $this->transpose;
        if ($this->clef !== null) {
            $out .= ' clef="' . $this->clef . '"';
        }
        $out .= "]";
        return $out;
    }

    public function getLineStartOut(): string
    {
        return "[V:" . $this->voiceIndicator . "]";
    }
}
