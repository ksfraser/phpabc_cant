<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;

/**
 * Class AbcBar
 *
 * Represents a bar in ABC notation, containing notes, lyrics, canntaireachd, solfege, and M/L/Q headers.
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
 * @property string|null $mHeader M: header value for this bar
 * @property string|null $lHeader L: header value for this bar
 * @property string|null $qHeader Q: header value for this bar
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
 *   - mHeader: string|null
 *   - lHeader: string|null
 *   - qHeader: string|null
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
 *   + renderSelf(): string
 * }
 * AbcBar --|> AbcItem
 * AbcBar --> AbcNote
 * AbcBar --> BarLineRenderer
 * @enduml
 */
use Ksfraser\PhpabcCanntaireachd\AbcItem;
use Ksfraser\PhpabcCanntaireachd\AbcNote;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcBarline;
use Ksfraser\PhpabcCanntaireachd\Contract\RenderableCanntaireachdInterface;
use Ksfraser\PhpabcCanntaireachd\Contract\TranslatableNoteInterface;
use Ksfraser\PhpabcCanntaireachd\Log\DebugLog;

class AbcBar extends \Ksfraser\PhpabcCanntaireachd\AbcItem implements RenderableCanntaireachdInterface
{
    /** @var AbcBarline|null $barLine The barline object for this bar */
    public $barLine = null;

    public function __construct($numberOrText, $barLine = '|')
    {
        if (is_int($numberOrText) || is_numeric($numberOrText)) {
            $this->number = (int)$numberOrText;
        } else {
            // Created from parser with raw bar text
            $this->contentText = (string)$numberOrText;
            // Parse notes immediately from the raw content so downstream code can use notes
            $this->parseContentNotes($this->contentText);
        }
        $this->barLine = new AbcBarline($barLine);
    }

    /**
     * Parse a raw bar content string into notes and add them to this bar.
     */
    protected function parseContentNotes(string $text): void
    {
        // Strip leading/trailing barline characters and repeat markers
        $clean = preg_replace('/^[|:\s]+|[|:\s]+$/', '', trim($text));
        // Split on whitespace to tokens (simple heuristic)
        $tokens = preg_split('/\s+/', $clean);
        foreach ($tokens as $tok) {
            $tok = trim($tok);
            if ($tok === '') continue;
            $this->addNote($tok);
        }
    }


    /**
     * @param string $noteStr
     * @param string|null $lyrics
     * @param string|null $canntaireachd
     * @param string|null $solfege
     * @param array|null $decoratorShortcutMap Dependency-injected decorator shortcut map
     */
    public function addNote($noteStr, $lyrics = null, $canntaireachd = null, $solfege = null, $decoratorShortcutMap = null)
    {
        if ($decoratorShortcutMap === null && method_exists($this, 'getDecoratorShortcutMap')) {
            $decoratorShortcutMap = $this->getDecoratorShortcutMap();
        }
        $note = new \Ksfraser\PhpabcCanntaireachd\AbcNote($noteStr, null);
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
        return $this->barLine ? $this->barLine->getType() : '';
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

    public function renderCanntaireachd(): string
    {
        DebugLog::log('AbcBar::renderCanntaireachd: called', true);
        // If bar-level cannt available return it first
        if ($this->canntaireachd !== null) return $this->canntaireachd;
        $out = [];
        $i = 0;
        foreach ($this->notes as $note) {
            if (!($note instanceof RenderableCanntaireachdInterface)) {
                throw new \LogicException('Note does not implement RenderableCanntaireachdInterface');
            }
            $cannt = $note->renderCanntaireachd();
            DebugLog::log("AbcBar::renderCanntaireachd: note $i canntaireachd='".$cannt."'", true);
            $out[] = $cannt;
            $i++;
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
    public function renderSelf(): string
    {
        if ($this->contentText !== null) {
            return $this->contentText . $this->renderBarLine();
        }
        // If notes parsed, render notes and barline
        $notes = $this->renderNotes();
        return $notes . $this->renderBarLine();
    }

    /**
     * Get the notes array (for testing and inspection)
     * @return array
     */
    public function getContent(): array
    {
        return $this->notes;
    }
}
