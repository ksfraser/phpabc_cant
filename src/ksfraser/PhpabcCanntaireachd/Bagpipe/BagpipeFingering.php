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
     * The pipe scale, and the holes that are closed for each note.
     *
     * The note names are the ABC ones: "G" is the low G, "c" the middle C,
     * "a" the high A.
     *
     * @var array<string, string[]>
     */
    public const SCALE = [
        // The low G: every hole closed, so the note sounds at the vent.
        'G' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle', 'bottom ring', 'pinky'],
        // The A: the right little finger up.
        'A' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle', 'bottom ring'],
        // The B: the right little finger and the right ring finger up.
        'B' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle'],
        // The C: the right index down, the right little finger down as well.
        'c' => ['thumb', 'top index', 'top middle', 'top ring', 'bottom index'],
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
