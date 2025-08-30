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
    /** @var array Notes in this bar */
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

    public function addNote($note)
    {
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
        $renderer = new \Ksfraser\PhpabcCanntaireachd\Render\NotesRenderer();
        return $renderer->render($this->notes);
    }

    public function renderLyrics()
    {
        $renderer = new \Ksfraser\PhpabcCanntaireachd\Render\LyricsRenderer();
        return $renderer->render($this->lyrics);
    }

    public function renderCanntaireachd()
    {
        $renderer = new \Ksfraser\PhpabcCanntaireachd\Render\CanntaireachdRenderer();
        return $renderer->render($this->canntaireachd);
    }

    public function renderSolfege()
    {
        $renderer = new \Ksfraser\PhpabcCanntaireachd\Render\SolfegeRenderer();
        return $renderer->render($this->solfege);
    }
}
