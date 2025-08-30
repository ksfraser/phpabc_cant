<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Represents a bar in ABC notation, with notes, lyrics, and canntaireachd.
 */
class AbcBar
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

    public function __construct($number, $barLine = '|')
    {
        $this->number = $number;
        $this->barLineRenderer = $this->createBarLineRenderer($barLine);
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

    public function renderCanntaireachd()
    {
        $out = [];
        foreach ($this->notes as $note) {
            $out[] = $note->renderCanntaireachd();
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
}
