# Voice Canntaireachd Requirements

> Feature-level requirements, in the style of `requirements/tokenization-and-conversion.md`.
> Implemented by `ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass` and driven by
> `bin/abc-voice-canntaireachd-cli.php`.

## Problem

An ABC file holds the tune. A piper needs the *syllables*. The conversion lives
in the token dictionary (`src/ksfraser/PhpabcCanntaireachd/abc_dict.php`), which
maps ABC tokens — including grace-note groups, doublings, taorluath, shakes,
grips and birls — to real canntaireachd syllables.

The rule this document specifies: **which voices get converted, and where the
result is written.**

## Functional Requirements

### FR-VC-1 — Target voices

A voice is a *target* when either of the following matches, case-insensitively:

- the voice identifier — `V:Melody`, `V:Bagpipes`, `V:Pipes`;
- the declared voice name — `V:M name="Melody"`, `V:P name="Pipes"`.

The default target names are `melody`, `bagpipes`, `bagpipe`, `pipes`, `pipe`.
A value that begins with a target name followed by a separator
(`Bagpipes II`, `Pipes (B)`) also matches.

The target list is configurable at construction time
(`new AbcCanntaireachdPass($dict, ['Chanter'])`) and from the command line
(`--voice=Chanter`).

### FR-VC-2 — Voice membership

- A `V:<id>` header sets the current voice and declares its name.
- An inline `[V:<id>]` marker switches the current voice; the music on that
  line belongs to it.
- A bare music line belongs to the current voice.
- An `X:` header starts a new tune and clears the current voice.

### FR-VC-3 — All lines of a target voice are processed

Every music line of every target voice is converted. A voice that mixes inline
markers and bare lines therefore has all of its lines converted, not just the
first.

### FR-VC-4 — Output placement

The generated canntaireachd is written as a `w:` line **directly beneath** the
ABC line it belongs to:

```
[V:Bagpipes]{g}A3B {g}ce3 | {g}B3A {g}c{d}B3 |
w: hen o ho e | ho en ho do |
```

### FR-VC-5 — The rest of the file is untouched

The pass is line-preserving. Every input line is emitted unchanged, in its
original position and with its original spacing. Header fields, comments
(`%`), `%%` directives, blank lines and non-target voices are never modified
and never receive a `w:` line.

### FR-VC-6 — Existing lyrics are kept

If a target music line already has `w:` lines beneath it, they are kept
verbatim and the generated canntaireachd is added **after** them.

### FR-VC-7 — Token conversion

Conversion uses the token dictionary. A grace-note group is a single token
(`{g}A` → `hen`), never `g` followed by `A`. A token with no dictionary entry
is emitted in brackets (`[C]`) so the gap is visible rather than silent.

### FR-VC-8 — Diff log

The pass returns a `canntDiff` entry (`line`, `generated`) for every
canntaireachd line it generated, so a caller can report what changed.

## Non-Functional Requirements

- The pass must be usable on its own, without the rest of the processing
  pipeline: no other pass may be required to produce correct output.
- The pass must not depend on the current working directory, on an
  `include_path`, or on a second autoloader.
- The pass must not write to the source tree or to stderr.
- The dictionary must have exactly one home in the repository.

## Test Cases

- A `V:Melody` voice gets a `w:` line; a `V:Flute` voice does not.
- A `V:M name="Melody"` voice gets a `w:` line.
- Matching is case-insensitive on both the identifier and the name.
- Every line of a multi-line target voice is converted.
- An inline `[V:…]` marker switches the voice, and the marker line itself is
  converted.
- A voice named in a header that appears *after* its body lines is still
  recognised.
- An existing `w:` line is kept, and the generated line follows it.
- Header fields, comments and `%%` directives are not treated as music.
- Input lines are reproduced verbatim.
- A new tune (`X:`) clears the voice context.
- `canntDiff` records one entry per generated line.
- A custom target list replaces the default one.
- A grace-note group is looked up as one token.

## Out of Scope

- Creating a `Bagpipes` voice when the file has none (that is
  `AbcVoicePass` / `VoiceCopyTransform`).
- Reordering voices, transposing, bar timing validation, header repair.
- Rendering the tune: this feature only inserts `w:` lines.
