<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Bagpipe;

use ksfraser\PhpabcCanntaireachd\TokenDictionary;

/**
 * Writes the bagpipe fingering format file for abc2ps.
 *
 * The file draws the fingering of the Highland chanter, in the same way that
 * the whistle file draws the fingering of a tin whistle and the guitar file
 * draws a chord diagram. There is one decoration for each note of the pipe
 * scale — "!pipeG!" is the low G, "!pipeg!" the G above it — and each draws
 * the chanter with its holes closed as the fingers close them.
 *
 * The fingering table and the embellishment words come from
 * {@see BagpipeFingering}, so that the file and the token dictionary cannot
 * drift apart.
 *
 * @package ksfraser\PhpabcCanntaireachd\Bagpipe
 *
 * @since 2.2.0
 */
class BagpipeFingeringWriter
{
    /**
     * Where each hole is drawn, from the top of the chanter down.
     *
     * The thumb hole is at the top and the pinkie hole at the bottom; the
     * line between the hands is between the top ring and the bottom index.
     *
     * @var array<string, float>
     */
    private const HOLE_POSITIONS = [
        'thumb' => 64.0,
        'top index' => 56.0,
        'top middle' => 48.0,
        'top ring' => 40.0,
        'bottom index' => 28.0,
        'bottom middle' => 20.0,
        'bottom ring' => 12.0,
        'pinky' => 4.0,
    ];

    /** @var BagpipeFingering */
    private BagpipeFingering $fingering;

    /** @var TokenDictionary */
    private TokenDictionary $dictionary;

    /**
     * @param BagpipeFingering|null $fingering
     * @param TokenDictionary|null $dictionary
     */
    public function __construct(?BagpipeFingering $fingering = null, ?TokenDictionary $dictionary = null)
    {
        $this->fingering = $fingering ?? new BagpipeFingering();
        $this->dictionary = $dictionary ?? new TokenDictionary();
    }

    /**
     * The name of the decoration that draws the fingering of a note.
     *
     * @param string $note
     * @return string
     */
    public function decorationName(string $note): string
    {
        return 'pipe' . $note;
    }

    /**
     * The format file.
     *
     * @return string
     */
    public function render(): string
    {
        $lines = [];
        $lines[] = '% bagpipe_fingering.fmt — the Highland bagpipe fingering, for abc2ps';
        $lines[] = '%';
        $lines[] = '% Written for the pipe band scores. The fingering of the chanter is';
        $lines[] = '% drawn under a note of the pipe scale, the way the whistle file draws';
        $lines[] = '% the fingering of a tin whistle and the guitar file draws a chord';
        $lines[] = '% diagram.';
        $lines[] = '%';
        $lines[] = '% Use it with:';
        $lines[] = '%   abcm2ps -F bagpipe_fingering.fmt tune.abc';
        $lines[] = '% or, in a tune that uses the include files:';
        $lines[] = '%   %%abc-include "bagpipe_fingering.fmt"';
        $lines[] = '%';
        $lines[] = '% The chanter has eight holes. One to three are closed by the right';
        $lines[] = '% hand, four to six by the left hand, seven is the lower back hole';
        $lines[] = '% (the right little finger) and eight is the upper back hole (the';
        $lines[] = '% thumb). The scale runs from the low G to the high D.';
        $lines[] = '%';
        $lines[] = '% Below the E the holes are closed one at a time from the bottom up.';
        $lines[] = '% Above the E the chanter is open, with the F as the exception: it';
        $lines[] = '% is a cross fingering, with the right index finger down.';
        $lines[] = '%';
        $lines[] = '% The decorations, one for each note of the scale:';
        foreach ($this->fingering->getScale() as $note => $holes) {
            $lines[] = sprintf(
                '%%   !%s!  the %s — %s',
                $this->decorationName($note),
                $this->noteName($note),
                $holes === [] ? 'all the holes open' : count($holes) . ' holes closed'
            );
        }
        $lines[] = '';
        $lines[] = 'beginps';
        $lines = array_merge($lines, $this->renderProcedures());
        $lines = array_merge($lines, $this->renderEmbellishmentProcedure());
        $lines[] = 'endps';
        $lines[] = '';
        $lines = array_merge($lines, $this->renderDecorations());
        $lines[] = '';
        $lines = array_merge($lines, $this->renderGraceFingeringChart());
        $lines = array_merge($lines, $this->renderEmbellishmentChart());

        return implode("\n", $lines) . "\n";
    }

    /**
     * The name of a note, for the chart.
     *
     * @param string $note
     * @return string
     */
    private function noteName(string $note): string
    {
        if ($note === 'G') {
            return 'low G';
        }
        if ($note === 'a') {
            return 'high A';
        }
        return $note;
    }

    /**
     * The PostScript procedures.
     *
     * @return string[]
     */
    private function renderProcedures(): array
    {
        $lines = [];
        $lines[] = '% -- the chanter ----------------------------------------------------';
        $lines[] = '% A tube with the eight holes down it. An open hole is an outline,';
        $lines[] = '% a closed one is filled.';
        $lines[] = '% A hole is drawn at the point that is on the stack. The path is';
        $lines[] = '% started again first, so that the hole is filled on its own and';
        $lines[] = '% not together with the body of the chanter.';
        $lines[] = '/pipe_hole_open{';
        $lines[] = '  gsave newpath moveto 0 0 2.6 0 360 arc stroke grestore';
        $lines[] = '}!';
        $lines[] = '/pipe_hole_closed{';
        $lines[] = '  gsave newpath moveto 0 0 2.6 0 360 arc fill grestore';
        $lines[] = '}!';
        $lines[] = '% The body of the chanter, and the line between the hands.';
        $lines[] = '/pipe_body{';
        $lines[] = '  gsave';
        $lines[] = '  0.3 SLW';
        $lines[] = '  -5 70 M 10 0 RL 0 -76 RL -10 0 RL 0 76 RL stroke';
        $lines[] = '  -5 34 M 10 0 RL stroke';
        $lines[] = '  grestore';
        $lines[] = '}!';
        foreach ($this->fingering->getScale() as $note => $holes) {
            $name = $this->decorationName($note);
            $lines[] = '% The ' . $this->noteName($note) . '.';
            $lines[] = '/' . $name . '{';
            $lines[] = '  gsave';
            $lines[] = '  pipe_body';
            foreach (self::HOLE_POSITIONS as $hole => $position) {
                // The array keys are numbers, the hole names are strings.
                $open = !in_array((string)$hole, $holes, true);
                $lines[] = sprintf(
                    '  0 %s %s',
                    $position,
                    $open ? 'pipe_hole_open' : 'pipe_hole_closed'
                );
            }
            $lines[] = '  /Helvetica-Bold 9 selectfont 0 -12 M (' . $this->noteName($note) . ') show';
            $lines[] = '  grestore';
            $lines[] = '}!';
        }

        return $lines;
    }

    /**
     * The decoration table.
     *
     * The layout is the one the guitar file uses: the name, the x offset, the
     * text, and the anchor.
     *
     * @return string[]
     */
    private function renderDecorations(): array
    {
        $lines = [];
        $lines[] = '% == Decoration definitions ==';
        $lines[] = '';
        foreach ($this->fingering->getScale() as $note => $holes) {
            $name = $this->decorationName($note);
            $lines[] = sprintf('deco %-10s 3 %-10s 38 0 0', $name, $name);
        }
        $lines[] = sprintf('deco %-10s 3 %-10s 38 0 0', 'pipeEmbellishments', 'pipeEmbellishments');
        $lines[] = '';

        return $lines;
    }

    /**
     * The embellishment chart: the words for the embellishments.
     *
     * The words are in the file twice: as comments, which is how they are read,
     * and as a chart that can be drawn on a page of a tune with the decoration
     * !pipeEmbellishments!.
     *
     * @return string[]
     */
    private function renderEmbellishmentChart(): array
    {
        $embellishments = $this->fingering->getEmbellishments($this->dictionary);

        $lines = [];
        $lines[] = '% == The embellishments, and the words they are sung to ==';
        $lines[] = '%';
        $lines[] = '% The word is the one in the token dictionary, so the chart and the';
        $lines[] = '% dictionary cannot disagree.';
        $lines[] = '%';
        $lines[] = '% The chart below is drawn by the decoration !pipeEmbellishments!. Put';
        $lines[] = '% it on a rest of a tune to have the reference page.';
        $lines[] = '';
        foreach ($embellishments as $name => $embellishment) {
            $lines[] = sprintf('%%   %-22s %-10s %s', $name, $embellishment['token'], $embellishment['word']);
        }
        $lines[] = '';
        return $lines;
    }

    /**
     * The fingerings of the graces and the embellishments.
     *
     * A grace is a brief change of the hole that sounds: a finger opens or
     * closes a hole and then goes back. Which hole it is depends on the note
     * that comes before, but the finger that moves does not.
     *
     * @return string[]
     */
    private function renderGraceFingeringChart(): array
    {
        $lines = [];
        $lines[] = '% == The fingerings of the graces ==';
        $lines[] = '%';
        $lines[] = '% A grace is a brief change of the hole that sounds: the finger';
        $lines[] = '% opens or closes a hole, and then goes back to where it was.';
        $lines[] = '% The rest of the fingers do not move during the grace.';
        $lines[] = '%';
        $lines[] = '% The lower case g is the G gracenote, which sounds the G. The';
        $lines[] = '% upper case G is the hard G, the strike, which sounds the low';
        $lines[] = '% G: every hole is closed.';
        $lines[] = '%';
        foreach ($this->fingering->getGraces() as $name => $grace) {
            $lines[] = sprintf(
                '%%   %-10s the %-14s %-6s  sounds the %s',
                $name,
                $grace['finger'] . ' finger',
                $grace['action'],
                $grace['note']
            );
        }
        $lines[] = '';
        $lines[] = '% The G gracenote cannot be played from the high A or the high';
        $lines[] = '% G, because the top index hole is already open on them.';
        $lines[] = '';
        $lines[] = '% == The fingerings of the embellishments ==';
        $lines[] = '%';
        $lines[] = '% The steps are the finger that moves, what it does, and the';
        $lines[] = '% note that sounds. A step with no finger is the melody note.';
        $lines[] = '% The fingering of the melody note depends on the note, and the';
        $lines[] = '% fingering of the first grace depends on the note before it.';
        $lines[] = '%';
        foreach ($this->fingering->getEmbellishmentSteps() as $name => $steps) {
            $lines[] = sprintf('%%   %s:', $name);
            foreach ($steps as $step) {
                if ($step['finger'] === null) {
                    $lines[] = sprintf('%%     the melody note (%s)', $step['note']);
                    continue;
                }
                $lines[] = sprintf(
                    '%%     the %-14s %-6s  sounds the %s',
                    $step['finger'] . ' finger',
                    $step['action'],
                    $step['note']
                );
            }
        }
        $lines[] = '';

        return $lines;
    }

    /**
     * The chart of the embellishments, drawn as PostScript.
     *
     * @return string[]
     */
    private function renderEmbellishmentProcedure(): array
    {
        $embellishments = $this->fingering->getEmbellishments($this->dictionary);

        $lines = [];
        $lines[] = '% The chart of the embellishments and the words they are sung to.';
        $lines[] = '/pipeEmbellishments{';
        $lines[] = '  gsave';
        $lines[] = '  /Helvetica 7 selectfont';
        $y = 0;
        foreach ($embellishments as $name => $embellishment) {
            $text = sprintf('%-22s %-10s %s', $name, $embellishment['token'], $embellishment['word']);
            $lines[] = sprintf('  0 %s M (%s) show', $y, $text);
            $y -= 9;
        }
        $lines[] = '  grestore';
        $lines[] = '}!';

        return $lines;
    }
}
