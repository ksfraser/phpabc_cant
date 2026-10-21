# Tune Cleanup Requirements

> Feature-level requirements, in the style of `requirements/voice-canntaireachd-w-lines.md`.
> Implemented by the `ksfraser\PhpabcCanntaireachd\Cleanup\` classes and driven by
> `bin/abc-tune-cleanup-cli.php`.

## Problem

A pipe band score is a folder of ABC files, and they drift: the header fields
end up in a different order in every tune, some tunes are missing the include
block that holds the shared definitions, the voices are missing their name or
their clef, the bass and tenor drums are on the wrong staff, and the bars are
laid out three to a line where the book says four.

The clean up puts a tune back into the shape the book wants — without touching
the music.

## The rule

**A clean up never changes the music.** It changes the headers, the voice
definitions, the include block and the layout of the bars. The notes, the
grace notes, the decorations, the spacing and the lyrics are carried through
as they were written.

## Functional Requirements

### FR-TC-1 — One parser for the whole file

The project had no parser that reads a line into its bars and notes. The
clean up needs one, so it has its own:

- a line is split into its notes, its bar lines and the text between them;
- a grace-note group belongs to the note that follows it, so `{g}A` is one
  note;
- a bar line is one of the sixteen ABC bar lines, and the ones that open a
  bar are told from the ones that close it;
- the inline markers of a line — the voice, the meter and the key — are read
  from the start of the line;
- the lyrics under a line belong to the line above them;
- the whitespace of a line is kept, because the lyrics are aligned with the
  notes;
- text that is not a note is kept, so that nothing is lost.

### FR-TC-2 — The header fields are written in the same order

Every tune is written with its header fields in this order:

    X  T  C  B  O  Z  H  Q  K  M  L  R  U  V  I

A comment in the header block keeps the place of the field it follows.

### FR-TC-3 — The include block

Every tune starts with the standard include block:

    %%abc-include "../../../format_layout.abh"
    %%abc-include "../../../format_fonts.abh"
    %%abc-include "../../../voices.abh"
    %%abc-include "../../../bagpipe_chords.abh"
    %%abc-include "../../../midi.abh"
    %%abc-include "../../../guitar_chords.fmt"
    %%abc-include "../../../gchords.fmt"
    %%abc-include "../../../percussion.abh"
    %%abc-include "../../../jazz.fmt"

Every include is written out. The scores used to include a single
`common.abh`, a file that included all the others, but neither abc2midi nor
abc2ps follows an include into another include, so a meta-include would leave
the tunes without their MIDI settings, their fonts and their percussion maps.

The paths are relative to the tune. An include whose file does not exist is
reported.

### FR-TC-4 — The voice definitions

Every voice says what it is and how it is written:

| attribute | value |
|---|---|
| `name` | the name of the instrument, e.g. "Melody" |
| `sname` | the short name, e.g. "ABCD-D" for the brass chords |
| `stem` | `down` |
| `gstem` | `up` |
| `clef` | the clef of the instrument, e.g. `perc` for the drums, `bass` for the brass bass |
| `transpose` | the transposition, e.g. `-1` for the brass chords |
| `octave` | the octave, e.g. `-1` for the piano |
| `stafflines`, `middle` | the percussion staff |

An attribute that is already there is not changed. A voice that is not one of
the known instruments is left alone.

### FR-TC-5 — The pipe band drums

The snare, the bass and the tenor drums are written on a one line percussion
staff with the middle of the staff at G, so the only notes they play are A
and c.

- A missing clef, or a clef of `none`, is changed to the percussion clef.
- A part that is written on other notes is transposed when a single
  transposition brings every note onto A or c.
- A note of the wrong case — `a` or `C` where `A` or `c` was meant — is
  corrected.
- A part that is mostly A and c with one odd note is a typo, and is reported
  rather than changed: there is no way to tell what it should have been.

A voice that is not a pipe band drum — a drum set, cymbals, a timpani — is
never changed.

### FR-TC-6 — The pipe scale

The bagpipe part is checked against the pipe scale, which runs from the low G
to the high D:

    G  A  B  c  d  e  f  g  a  b  c'  d'

A note outside that range cannot be played on the chanter, and a note outside
the scale needs an accidental the pipe does not have. Both are reported.

### FR-TC-7 — The layout

The bars are laid out four to a line, or five on the first line when the tune
starts with an anacrusis, so that the first full line is not short.

- Every line carries the voice it belongs to and the meter in force.
- Every line but the last ends with the `$` line ending marker.
- The lyrics move with their bars. When the number of lyric groups under a
  line does not match the number of bars, the line is left as it is and the
  tune is reported: the lyrics cannot be moved without guessing.

### FR-TC-8 — The output

The cleaned tune is written to a new file: the name of the tune with `_php`
added before the `.abc`. The tune that was read is never changed.

## Non-Functional Requirements

- Every stage is a class of its own, and every stage is injected into the
  pipeline, so that a caller can use the ones it needs.
- The clean up must not lose any of the music: parsing a file and writing it
  back has to give the same body.
- The clean up must not depend on the current working directory.

## Test Cases

- The tokenizer splits the notes, the bar lines and the text of a line, and
  keeps the grace note with its note.
- The parser reads the headers, the voices, the body and the lyrics, and
  keeps a comment in the header block with the headers.
- The headers are written in the pipe band order.
- The include block is written out in full, with every include active and no
  meta-include, its targets are found, and a missing include is reported.
- A voice that is missing attributes gets them; one that is complete is left
  alone; an unknown voice is left alone.
- A drum part on A and c is left alone; one on d and f is transposed; one with
  a note of the wrong case is corrected; one with an odd note is reported.
- The pipe band drums are the snare, the bass and the tenor, and not the drum
  set, the cymbals or the timpani.
- The bars are laid out four to a line, the lines carry the voice and the
  meter, and they end with the `$` marker.
- A line whose lyrics do not match its bars is left as it is.

## Out of Scope

- Writing the music: the clean up does not compose, transpose or correct
  notes, other than the transposition of a drum part onto A and c.
- Deciding which words are the right ones for a drum part that is a typo.
- The contents of the include files themselves.
