<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;

/**
 * Class AbcBar
 *
 * Represents a bar in ABC notation, containing notes, lyrics, canntaireachd, and solfege.
 * Handles parsing of bar content, note management, and rendering for output.
 *
 * SOLID: Single Responsibility (bar model), DRY (delegates note/lyric/canntaireachd rendering).
 *
 * @package Ksfraser\PhpabcCanntaireachd\Tune
 *
 * @property int $number Bar number
 * @property \Ksfraser\PhpabcCanntaireachd\Render\BarLineRenderer $barLineRenderer Bar line renderer instance
 * @property \Ksfraser\PhpabcCanntaireachd\AbcNote[] $notes Notes in this bar
 * @property string|null $lyrics Lyrics for this bar
 * @property string|null $canntaireachd Canntaireachd for this bar (bagpipe voice only)
 * @property string|null $solfege Do-re-mi for this bar (other voices)
 * @property string|null $contentText Raw content string when created from parser without parsing notes
 *
 * @method __construct(int|string $numberOrText, string $barLine)
 * @method addNote(string $noteStr, string|null $lyrics, string|null $canntaireachd, string|null $solfege)
 * @method setLyrics(string $lyrics)
 * @method setCanntaireachd(string $cannt)
 * @method setSolfege(string $solfege)
 * @method renderBarLine(): string
 * @method renderNotes(): string
 * @method renderLyrics(): string
 * @method getCanntaireachd(): string
 * @method renderCanntaireachd(): string
 * @method renderSolfege(): string
 * @method renderSelf(): string
 *
 * @uml
 * @startuml
 * class AbcBar {
 *   - number: int
 *   - barLineRenderer: BarLineRenderer
 *   - notes: AbcNote[]
 *   - lyrics: string
 *   - canntaireachd: string
 *   - solfege: string
 *   - contentText: string
 *   + __construct(numberOrText: int|string, barLine: string)
 *   + addNote(noteStr: string, lyrics: string, canntaireachd: string, solfege: string)
 *   + setLyrics(lyrics: string)
 *   + setCanntaireachd(cannt: string)
 *   + setSolfege(solfege: string)
 *   + renderBarLine(): string
 *   + renderNotes(): string
 *   + renderLyrics(): string
 *   + getCanntaireachd(): string
 *   + renderCanntaireachd(): string
 *   + renderSolfege(): string
use Ksfraser\PhpabcCanntaireachd\AbcItem;
use Ksfraser\PhpabcCanntaireachd\AbcNote;
use Ksfraser\PhpabcCanntaireachd\Render\BarLineRenderer;
 *   + renderSelf(): string
 * }
 * AbcBar --|> AbcItem
 * AbcBar --> AbcNote
 * AbcBar --> BarLineRenderer
 * @enduml
 */
class AbcBar extends \Ksfraser\PhpabcCanntaireachd\AbcItem
{
    /** @var int Bar number */
    public $number;
    /** @var \Ksfraser\PhpabcCanntaireachd\Render\BarLineRenderer Bar line renderer instance */
    public $barLineRenderer;
    /** @var \Ksfraser\PhpabcCanntaireachd\AbcNote[] Notes in this bar */
    public $notes = [];
    /** @var string|null Lyrics for this bar */
    public $lyrics = null;
    /** @var string|null Canntaireachd for this bar (bagpipe voice only) */
    public $canntaireachd = null;
    /** @var string|null Do-re-mi for this bar (other voices) */
    public $solfege = null;
    /** @var string|null Raw content string when created from parser without parsing notes */
    protected $contentText = null;

    public function __construct($numberOrText, $barLine = '|')
    {
        if (is_int($numberOrText) || is_numeric($numberOrText)) {
            $this->number = (int)$numberOrText;
            $this->barLineRenderer = $this->createBarLineRenderer($barLine);
        } else {
            // Created from parser with raw bar text
            $this->contentText = (string)$numberOrText;
            $this->barLineRenderer = $this->createBarLineRenderer($barLine);
            // Parse notes immediately from the raw content so downstream code can use notes
            $this->parseContentNotes($this->contentText);
        }
    }

    /**
     * Parse a raw bar content string into notes and add them to this bar.
     */
    protected function parseContentNotes(string $text): void
    {
        // Strip leading/trailing barline characters and repeat markers
        $clean = trim($text);
        $clean = preg_replace('/^[|:\s]+|[|:\s]+$/', '', $clean);
        if ($clean === '') return;
        // Split on whitespace to tokens (simple heuristic)
        $tokens = preg_split('/\s+/', $clean);
        foreach ($tokens as $tok) {
            $tok = trim($tok);
            if ($tok === '') continue;
            $this->addNote($tok);
        }
    }

    protected function createBarLineRenderer($barLine)
    {
        switch ($barLine) {
            case '||':
                return new \Ksfraser\PhpabcCanntaireachd\Render\DoubleBarLineRenderer();
            case '|:':
                return new \Ksfraser\PhpabcCanntaireachd\Render\StartRepeatBarLineRenderer();
            case ':|':
                return new \Ksfraser\PhpabcCanntaireachd\Render\EndRepeatBarLineRenderer();
            case '[:':
                return new \Ksfraser\PhpabcCanntaireachd\Render\StartBarLineRenderer();
            case ':]':
                return new \Ksfraser\PhpabcCanntaireachd\Render\EndBarLineRenderer();
            case '|':
            default:
                return new \Ksfraser\PhpabcCanntaireachd\Render\SimpleBarLineRenderer();
        }
    }

    public function addNote($noteStr, $lyrics = null, $canntaireachd = null, $solfege = null)
    {
    $note = new \Ksfraser\PhpabcCanntaireachd\AbcNote($noteStr);
        if ($lyrics !== null) $note->setLyrics($lyrics);
        if ($canntaireachd !== null) $note->setCanntaireachd($canntaireachd);
        if ($solfege !== null) $note->setSolfege($solfege);
        $this->notes[] = $note;
    }

    public function setLyrics($lyrics)
    {
        $this->lyrics = $lyrics;
    }

    public function setCanntaireachd($cannt)
    {
        $this->canntaireachd = $cannt;
    }

    public function setSolfege($solfege)
    {
        $this->solfege = $solfege;
    }

    public function renderBarLine()
    {
        return $this->barLineRenderer->render();
    }

    public function renderNotes()
    {
        $out = [];
        foreach ($this->notes as $note) {
            $out[] = $note->get_body_out();
        }
        return implode(' ', $out);
    }

    public function renderLyrics()
    {
        $out = [];
        foreach ($this->notes as $note) {
            $out[] = $note->renderLyrics();
        }
        return implode(' ', $out);
    }

    public function getCanntaireachd() {
        if ($this->canntaireachd !== null) return $this->canntaireachd;
        $out = $this->renderCanntaireachd();
        return trim($out);
    }

    public function renderCanntaireachd()
    {
        // If bar-level cannt available return it first
        if ($this->canntaireachd !== null) return $this->canntaireachd;
        $out = [];
        foreach ($this->notes as $note) {
            if (method_exists($note, 'renderCanntaireachd')) {
                $out[] = $note->renderCanntaireachd();
            } else {
                $out[] = '';
            }
        }
        return implode(' ', $out);
    }

    public function renderSolfege()
    {
        $out = [];
        foreach ($this->notes as $note) {
            $out[] = $note->renderSolfege();
        }
        return implode(' ', $out);
    }

    // Render the bar content for inclusion in a line (without surrounding '|')
    protected function renderSelf(): string
    {
        if ($this->contentText !== null) {
            return $this->contentText;
        }
        // If notes parsed, render notes
        $notes = $this->renderNotes();
        return $notes;
    }
}
