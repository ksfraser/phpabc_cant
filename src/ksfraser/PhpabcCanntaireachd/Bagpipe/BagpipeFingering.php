<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Bagpipe;

use ksfraser\PhpabcCanntaireachd\TokenDictionary;

/**
 * The fingering of the Highland bagpipe, and the words for the
 * embellishments.
 *
 * The chanter has eight holes:
 *
 *   1  2  3   the right hand, from the bottom
 *   4  5  6   the left hand
 *   7         the lower back hole, closed by the right little finger
 *   8         the upper back hole, closed by the thumb
 *
 * The pipe scale runs from the low G to the high D. Below the E the holes
 * are closed one at a time, from the bottom up; above the E the chanter is
 * open, with the F as the one exception — it is a cross fingering, with the
 * right index finger down.
 *
 * The embellishment words come from the token dictionary, so the two cannot
 * drift apart.
 *
 * @package ksfraser\PhpabcCanntaireachd\Bagpipe
 *
 * @since 2.2.0
 */
class BagpipeFingering
{
    /**
     * The holes of the chanter, from the top of the chanter down.
     *
     * @var string[]
     */
    public const HOLES = [
        'thumb',
        'top index',
        'top middle',
        'top ring',
        'bottom index',
        'bottom middle',
        'bottom ring',
        'pinky',
    ];

    /**
     * The finger that moves for each kind of grace, and the note it sounds.
     *
     * A grace is a brief change of the hole that sounds: the finger opens or
     * closes a hole, and then goes back to where it was.
     *
     * The case of the letter says which note it is: {g} is the high G and
     * {G} the low G, {a} the high A and {A} the low A.
     *
     * Whether it is played as a strike or as a gracenote depends on the note
     * it is played from, not on the case. A grace whose note is lower than
     * the melody note has to come from a hole below it, and that is a hard
     * movement: a strike. A grace that is above both the note before and the
     * note after is a plain gracenote. So {g}a from the a is a strike, {e}f
     * from the g is a strike, and {f}c from the B is a gracenote.
     *
     * @var array<string, array{finger: string, action: string, note: string}>
     */
    public const GRACES = [
        // The case says which note it is: {g} is the high G and {G} the low
        // G. Whether it is played as a strike or as a gracenote depends on
        // the note it is played from.
        // The hole is the one that sounds the note; the low G has no hole of
        // its own, it is the vent, and every finger has to close for it.
        'g grace' => ['note' => 'g', 'hole' => 'top index'],
        'G grace' => ['note' => 'G', 'hole' => null],
        'd grace' => ['note' => 'd', 'hole' => 'bottom index'],
        'e grace' => ['note' => 'e', 'hole' => 'top ring'],
        'f grace' => ['note' => 'f', 'hole' => 'top middle'],
        'a grace' => ['note' => 'a', 'hole' => 'thumb'],
        'A grace' => ['note' => 'A', 'hole' => 'pinky'],
    ];

    /**
     * The steps of each embellishment.
     *
     * A step is the finger that moves, what it does, and the note that
     * sounds. A step with no finger is the melody note: the fingering of the
     * note the embellishment belongs to, which depends on that note.
     *
     * @var array<string, array<int, array{finger: ?string, action: string, note: string}>>
     */
    public const EMBELLISHMENT_STEPS = [
        // A doubling: the G gracenote, the melody note, the false D, and the
        // melody note again.
        'doubling' => [
            ['finger' => 'top index', 'action' => 'open', 'note' => 'g'],
            ['finger' => null, 'action' => 'note', 'note' => 'melody'],
            ['finger' => 'bottom index', 'action' => 'open', 'note' => 'd'],
            ['finger' => null, 'action' => 'note', 'note' => 'melody'],
        ],
        // The D throw: the right index finger beats on the hole, closing to
        // the G, opening to the false D, closing to the C, and opening to
        // the melody note.
        'D throw' => [
            ['finger' => 'bottom index', 'action' => 'close', 'note' => 'G'],
            ['finger' => 'bottom index', 'action' => 'open', 'note' => 'd'],
            ['finger' => 'bottom index', 'action' => 'close', 'note' => 'c'],
            ['finger' => 'bottom index', 'action' => 'open', 'note' => 'melody'],
        ],
        // The grip: close to the G, the false D, close to the G again, and
        // then the hand moves to the fingering of the melody note.
        'grip' => [
            ['finger' => 'bottom index', 'action' => 'close', 'note' => 'G'],
            ['finger' => 'bottom index', 'action' => 'open', 'note' => 'd'],
            ['finger' => 'bottom index', 'action' => 'close', 'note' => 'G'],
            ['finger' => null, 'action' => 'note', 'note' => 'melody'],
        ],
    ];

    /**
     * The pipe scale, and the holes that are closed for each note.
     *
     * The note names are the ABC ones: "G" is the low G, "c" the middle C,
     * "a" the high A.
     *
     * @var array<string, string[]>
     */
    /**
     * The graces that are written on their own.
     *
     * @var string[]
     */
    public const COMMON_GRACES = ['g', 'd', 'e'];

    /**
     * The notes the doublings, the throws and the half doublings are made of.
     *
     * @var string[]
     */
    public const EMBELLISHMENT_NOTES = ['a', 'g', 'f', 'e', 'd'];

    public const SCALE = [
        // The low G: every hole closed, so the note sounds at the vent.
        'G' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle', 'bottom ring', 'pinky'],
        // The A: the right little finger up.
        'A' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle', 'bottom ring'],
        // The B: the right little finger and the right ring finger up.
        'B' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle'],
        // The C: the right index down, the right little finger down as well.
        'c' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'pinky'],
        // The D: the right little finger down, the other right hand fingers up.
        'd' => ['thumb', 'top index', 'top middle', 'top ring', 'pinky'],
        // The E: the top hand's thumb, index and middle down, the ring up.
        'e' => ['thumb', 'top index', 'top middle', 'bottom index', 'bottom middle', 'bottom ring'],
        // The F: the top hand's thumb and index down, the middle and ring up.
        'f' => ['thumb', 'top index', 'bottom index', 'bottom middle', 'bottom ring'],
        // The high G: the thumb down, the top hand's three fingers up.
        'g' => ['thumb', 'bottom index', 'bottom middle', 'bottom ring'],
        // The high A: the thumb up, the top hand's ring finger down to keep
        // the left hand on the chanter.
        'a' => ['top ring', 'bottom index', 'bottom middle', 'bottom ring'],
    ];

    /**
     * The embellishments, and the ABC token each one is written as.
     *
     * @var array<string, string>
     */
    public const EMBELLISHMENTS = [
        'doubling on G' => '{gGd}G',
        'doubling on A' => '{gAd}A',
        'doubling on B' => '{gBd}B',
        'doubling on c' => '{gcd}c',
        'doubling on d' => '{gde}d',
        'doubling on e' => '{gef}e',
        'doubling on f' => '{gfg}f',
        'thumb doubling on G' => '{aGd}G',
        'thumb doubling on A' => '{aAd}A',
        'thumb doubling on d' => '{ade}d',
        'half doubling on G' => '{Gd}G',
        'half doubling on A' => '{Ad}A',
        'half doubling on d' => '{de}d',
        'taorluath on G' => '{GdGe}G',
        'taorluath on A' => '{GdGe}A',
        'taorluath on B' => '{GdGe}B',
        'taorluath on c' => '{GdGe}c',
        'taorluath on d' => '{GdGe}d',
        'grip on G' => '{GdG}G',
        'grip on A' => '{GdG}A',
        'grip on B' => '{GdG}B',
        'grip on c' => '{GdG}c',
        'grip on d' => '{GdG}d',
        'grip on e' => '{GdG}e',
        'grip on f' => '{GdG}f',
        'grip on g' => '{GdG}g',
        'grip on a' => '{GdG}a',
        'strike on A' => '{G}A',
        'strike on B' => '{G}B',
        'strike on c' => '{G}c',
        'strike on d' => '{G}d',
        'light D throw' => '{Gdc}d',
        'heavy D throw' => '{GdGc}d',
        'birl' => '{GAG}A',
        'second birl' => '{AGAG}A',
        'third birl' => '{gAGAG}A',
        'fourth birl' => '{aAGAG}A',
        'shake on f' => '{gfe}f',
        'shake on d' => '{gdc}d',
        'shake on d (high)' => '{gdG}d',
    ];

    /**
     * The pipe scale, and the holes that are closed for each note.
     *
     * @return array<string, string[]>
     */
    public function getScale(): array
    {
        return self::SCALE;
    }

    /**
     * The notes of the pipe scale.
     *
     * @return string[]
     */
    public function getScaleNotes(): array
    {
        return array_keys(self::SCALE);
    }

    /**
     * The holes that are closed for a note.
     *
     * @param string $note
     * @return string[]|null The holes, or null when the note is not in the scale.
     */
    public function getClosedHoles(string $note): ?array
    {
        return self::SCALE[$note] ?? null;
    }

    /**
     * Is this note in the pipe scale?
     *
     * @param string $note
     * @return bool
     */
    public function isInScale(string $note): bool
    {
        return isset(self::SCALE[$note]);
    }

    /**
     * The graces, and the finger that moves for each of them.
     *
     * @return array<string, array{finger: string, action: string, note: string}>
     */
    public function getGraces(): array
    {
        return self::GRACES;
    }

    /**
     * The steps of the embellishments.
     *
     * @return array<string, array<int, array{finger: ?string, action: string, note: string}>>
     */
    public function getEmbellishmentSteps(): array
    {
        return self::EMBELLISHMENT_STEPS;
    }

    /**
     * The name of a grace, from the letter it is written with.
     *
     * A lower case letter is a gracenote and an upper case letter is a
     * strike: {g} is the G gracenote and {G} the G strike.
     *
     * @param string $letter
     * @return string
     */
    public function getGraceName(string $letter): string
    {
        if ($letter === '') {
            return '';
        }
        return $letter . ' grace';
    }

    /**
     * The finger that moves for a grace.
     *
     * @param string $grace
     * @return array{finger: string, action: string, note: string}|null
     */
    /**
     * The graces of a grace group, one for each letter of it.
     *
     * A grace group is a sequence of graces: {GdGeAfA} is the low G, the D
     * gracenote, the high G, the E gracenote, the low A, the F gracenote and
     * the low A. The piobruchds use movements like that.
     *
     * @param string $group The grace group, without the braces.
     * @return string[]
     */
    public function getGraceLetters(string $group): array
    {
        $letters = [];
        foreach (str_split($group) as $letter) {
            if (preg_match('/^[A-Ga-g]$/', $letter) === 1) {
                $letters[] = $letter;
            }
        }
        return $letters;
    }

    public function getGrace(string $grace): ?array
    {
        return self::GRACES[$grace] ?? null;
    }

    /**
     * The steps of an embellishment.
     *
     * @param string $embellishment
     * @return array<int, array{finger: ?string, action: string, note: string}>
     */
    public function getSteps(string $embellishment): array
    {
        return self::EMBELLISHMENT_STEPS[$embellishment] ?? [];
    }

    /**
     * The fingering of a grace, played from a note.
     *
     * The grace is a change of one hole, so the fingering is the fingering of
     * the note it is played from with that one hole changed.
     *
     * @param string $grace   The name of the grace, e.g. "g grace".
     * @param string $previousNote The note the grace is played from.
     * @return string[]|null The holes that are closed, or null when the grace
     *                       cannot be played from that note.
     */
    public function getGraceFingering(string $grace, string $previousNote): ?array
    {
        $base = $this->getClosedHoles($previousNote);
        $movement = $this->getGraceMovement($grace, $previousNote);
        if ($base === null || $movement['action'] === '') {
            return null;
        }
        if ($movement['action'] === 'lift') {
            return array_values(array_diff($base, $movement['fingers']));
        }
        $holes = $base;
        foreach ($movement['fingers'] as $finger) {
            $holes[] = $finger;
        }
        return array_values(array_unique($holes));
    }

    /**
     * Can this grace be played?
     *
     * A grace sounds the note it is named after, so a grace on the note it
     * sounds cannot be heard at all: a G gracenote on a G is out, whatever
     * the note before it was, and from the high A the G gracenote is played
     * as a G strike, because the thumb has to close for the sound to come
     * out at the top index hole.
     *
     * @param string $grace       The name of the grace, e.g. "g grace".
     * @param string $previousNote The note the grace is played from.
     * @param string|null $melodyNote The note the grace is attached to.
     * @return bool
     */
    public function canPlayGrace(string $grace, string $previousNote, ?string $melodyNote = null): bool
    {
        $spec = $this->getGrace($grace);
        $base = $this->getClosedHoles($previousNote);
        if ($spec === null || $base === null) {
            return false;
        }
        // A grace that sounds the note it is attached to cannot be heard.
        if ($melodyNote !== null && $this->soundsSameNote($spec['note'], $melodyNote)) {
            return false;
        }
        return true;
    }

    /**
     * Does the grace sound the same note as the one it is attached to?
     *
     * @param string $graceNote
     * @param string $melodyNote
     * @return bool
     */
    public function soundsSameNote(string $graceNote, string $melodyNote): bool
    {
        if ($graceNote === '' || $melodyNote === '') {
            return false;
        }
        // The case matters: "G" is the low G and "g" the high G.
        return $graceNote[0] === $melodyNote[0];
    }

    /**
     * Is this grace played as a strike from this note?
     *
     * The G gracenote lifts the top index finger. Where that finger is
     * already up and the thumb hole is open, the note is played as a G
     * strike: the thumb closes, and the sound comes out at the top index
     * hole.
     *
     * @param string $grace
     * @param string $previousNote
     * @return bool
     */

    /**
     * Is this the low version of the note?
     *
     * The case of the letter says which note it is: {g} is the high G and {G}
     * the low G.
     *
     * @param string $grace
     * @return bool
     */
    public function isLowNote(string $grace): bool
    {
        if ($grace === '') {
            return false;
        }
        $letter = $grace[0];
        return $letter === strtoupper($letter);
    }

    /**
     * Is this grace played as a strike from this note?
     *
     * A grace whose note is lower than the melody note it is played from is
     * a strike: the sound has to come from a hole that is below the note, and
     * that is a hard movement. A grace that is above both the note before
     * and the note after is a plain gracenote.
     *
     * @param string $grace
     * @param string $previousNote
     * @return bool
     */
    /**
     * How the grace is played, from the note it is played from.
     *
     * A grace is the best approximation of the melody note: to sound it, the
     * least number of fingers move. A finger that comes up is a gracenote, and
     * a finger that goes down is a strike, so {g} is usually a gracenote and
     * {G} is usually a strike.
     *
     * The sound comes out at the first hole that is open, so a hole only
     * sounds when the holes above it are closed.
     *
     * @param string $grace       The name of the grace, e.g. "g grace".
     * @param string $previousNote The note the grace is played from.
     * @return array{fingers: string[], action: string, note: string}
     *         The fingers that move, whether they come up or go down, and the
     *         note that sounds.
     */
    public function getGraceMovement(string $grace, string $previousNote): array
    {
        $spec = $this->getGrace($grace);
        if ($spec === null) {
            return ['fingers' => [], 'action' => '', 'note' => ''];
        }
        $base = $this->getClosedHoles($previousNote);
        if ($base === null) {
            return ['fingers' => [], 'action' => '', 'note' => $spec['note']];
        }

        $hole = $spec['hole'];
        if ($hole === null) {
            // The low G is the vent: every finger that is up has to close.
            $fingers = array_values(array_diff(self::HOLES, $base));
            return ['fingers' => $fingers, 'action' => 'close', 'note' => $spec['note']];
        }

        $position = array_search($hole, self::HOLES, true);
        if ($position === false) {
            return ['fingers' => [], 'action' => '', 'note' => $spec['note']];
        }
        // The holes above the one that sounds: they have to be closed, or the
        // sound comes out at one of them instead.
        $above = array_slice(self::HOLES, 0, $position);

        if (in_array($hole, $base, true)) {
            // The hole is closed: it comes up, and anything open above it goes
            // down, so that the sound comes out here.
            $fingers = [$hole];
            foreach ($above as $open) {
                if (!in_array($open, $base, true)) {
                    $fingers[] = $open;
                }
            }
            return ['fingers' => $fingers, 'action' => 'lift', 'note' => $spec['note']];
        }

        // The hole is already open: whatever is open above it goes down, so
        // that the sound comes out here.
        $fingers = [];
        foreach ($above as $open) {
            if (!in_array($open, $base, true)) {
                $fingers[] = $open;
            }
        }
        return ['fingers' => $fingers, 'action' => 'close', 'note' => $spec['note']];
    }

    /**
     * Is this grace played as a strike from this note?
     *
     * A finger that goes down is a strike, and a finger that comes up is a
     * gracenote.
     *
     * @param string $grace
     * @param string $previousNote
     * @return bool
     */
    public function isPlayedAsStrikeFrom(string $grace, string $previousNote): bool
    {
        $movement = $this->getGraceMovement($grace, $previousNote);
        return $movement['action'] === 'close' && $movement['fingers'] !== [];
    }

    /**
     * Where a note is in the pipe scale, counted from the low G.
     *
     * @param string $note
     * @return int|null
     */
    public function scalePosition(string $note): ?int
    {
        if ($note === '') {
            return null;
        }
        // The case matters: "G" is the low G and "g" the high G.
        $notes = array_keys(self::SCALE);
        foreach ($notes as $index => $name) {
            if ($name === $note) {
                return $index;
            }
        }
        return null;
    }

    /**
     * The embellishments, and the word each one is sung to.
     *
     * The word is taken from the token dictionary, so that the chart and the
     * dictionary cannot drift apart.
     *
     * @param TokenDictionary|null $dictionary
     * @return array<string, array{token: string, word: string}>
     */
    public function getEmbellishments(?TokenDictionary $dictionary = null): array
    {
        $dictionary = $dictionary ?? new TokenDictionary();
        $out = [];
        foreach (self::EMBELLISHMENTS as $name => $token) {
            $out[$name] = [
                'token' => $token,
                'word' => $dictionary->convertAbcToCannt($token) ?? '?',
            ];
        }
        return $out;
    }
}
