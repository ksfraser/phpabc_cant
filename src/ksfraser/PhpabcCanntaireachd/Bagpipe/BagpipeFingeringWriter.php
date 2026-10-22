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
                '%%   %-10s sounds the %-3s at the %s',
                $name,
                $grace['note'],
                $grace['hole'] ?? 'vent (every finger closes)'
            );
        }
        $lines[] = '%';
        $lines[] = '% How it is played depends on where the hand is: the least number';
        $lines[] = '% of fingers move, and a finger that comes up is a gracenote while';
        $lines[] = '% a finger that goes down is a strike. So a {g} before the high A is';
        $lines[] = '% a strike, the thumb closing, and a {G} from the D closes every';
        $lines[] = '% finger that is up, for the low G at the vent.';
        $lines[] = '%';
        $lines[] = '% The examples, from the hand they are played from:';
        $lines[] = '%   a {g}a   the thumb closes, a strike';
        $lines[] = '%   g {e}f   the top index and the top middle close, a strike';
        $lines[] = '%   e {A}e   the top ring closes, a strike';
        $lines[] = '%   f {e}f   the top middle closes, a strike';
        $lines[] = '%   B {f}c   the top middle comes up, a gracenote';
        $lines[] = '%   G {A}G   the little finger comes up, a gracenote';
        $lines[] = '%   B {A}B   the right ring closes, a strike';
        $lines[] = '%   B {G}B   the right ring and the little finger close, a strike';
        $lines[] = '%   d {G}dc  every finger that is up closes, a strike';
        $lines[] = '%   c {GdG}A close all, lift the right index for the D, close it back';
        $lines[] = '%           to the G, and then take the fingering of the A';
        $lines[] = '';
        $lines[] = '% The common single graces are g, d and e. The doublings, the';
        $lines[] = '% throws and the half doublings are made of a, g, f, e and d.';
        $lines[] = '%';
        $lines[] = '% The larger movements are calculated note by note, each grace of';
        $lines[] = '% the group played from where the one before it left the hand. The';
        $lines[] = '% groups the dictionary knows are listed below, with the notes they';
        $lines[] = '% are made of.';
        $lines[] = '%';
        foreach ($this->fingering->getCompoundGroups() as $group => $notes) {
            $lines[] = sprintf('%%   %-12s %s', $group, $notes);
        }
        $lines[] = '%';
        $lines[] = '% For example, {gdedc} is the G gracenote to the D, the E gracenote';
        $lines[] = '% back to the D, and the C strike ending on the D. That is one form';
        $lines[] = '% of a hornpipe shake, a doubling and a strike. {gdG} is a heavy';
        $lines[] = '% shake and {gdc} a light one.';
        $lines[] = '% A grace that sounds the note it is attached to cannot be';
        $lines[] = '% heard at all: a G gracenote on a G is out, whatever the note';
        $lines[] = '% before it was.';
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
