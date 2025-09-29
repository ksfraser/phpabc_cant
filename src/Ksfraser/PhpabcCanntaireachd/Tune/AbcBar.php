<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;

/**
 * Represents a bar in ABC notation, with notes, lyrics, and canntaireachd.
 */
class AbcBar extends AbcItem
{
    /** @var int Bar number */
    public $number;
    /** @var BarLineRenderer Bar line renderer instance */
    public $barLineRenderer;
    /** @var AbcNote[] Notes in this bar */
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
        $note = new AbcNote($noteStr);
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
