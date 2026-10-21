# BMW to ABC Requirements

> Feature-level requirements, in the style of `requirements/tune-cleanup.md`.
> Implemented by the `ksfraser\PhpabcCanntaireachd\Bmw\` classes and driven by
> `bin/bmw2abc-cli.php`.

## Problem

Bagpipe Music Writer writes the tune in its own notation, and Bagpipe Reader
reads it. The ABC notation is what the rest of the world reads, so a tune has
to be converted.

The project already had the dictionary — `bmw_dict.php`, 551 tokens and 18
header entries — and an attempt at a converter. The attempt replaced over the
whole line, and its own comment says why that cannot work:

> During the search/replace, we end up in the situation where a doubling
> becomes `{gde}` and then the next pass replaces the `gd` with `{d}` so we
> end up with `{{d}e}`.

## The rule

**The tokens are matched one at a time, longest first.** A token is never
replaced twice.

## Functional Requirements

### FR-BMW-1 — The dictionary

The dictionary is a data file, `bmw_dict.php`, with two parts:

- `$bmw['<token>']['abc']` — a token of the music;
- `$bmw_header['<text>']` — a header of the file.

The keys are matched longest first, so that `Fr_8` is found before `F`. A key
that is written with spaces around it is also used without them, because the
tokens are matched word by word.

### FR-BMW-2 — The file

The BMW file is read as:

- the header block — the version, the MIDI and frequency mappings, the
  instrument mappings, the grace note durations, the font sizes, the tune
  format and the tempo. These become comments, and the tempo becomes the
  `Q:` field.
- the records of the title, the type, the composer and the foot. These
  become the `T:`, `R:`, `C:` and `O:` fields.
- the header of the tune, the line that starts with `&`: the key, the meter and
  the bar lines. The key is written in words — "sharpf sharpc" — and is
  looked up in the header part of the dictionary.
- the music, which is tokenised and converted token by token.

### FR-BMW-3 — Nothing is lost

A token that is not in the dictionary is left as it was written, so that the
conversion never loses music. The converter reports how many tokens it did
not know.

### FR-BMW-4 — The output

The ABC is written to a file: the name of the BMW file with the `.abc`
extension. An existing file is not overwritten unless `--force` is given.

## Test Cases

- The dictionary is read, and its keys are sorted longest first.
- A note is converted: `LA_8` is the low A, `Fr_8` the F of the right hand.
- A grace note keeps its note: the conversion never produces `{{`.
- The title, the type, the composer and the foot become the right fields.
- The header of the tune carries the key and the meter.
- The tempo becomes the `Q:` field, and a bar line becomes `[|:`.
- A token that is not in the dictionary is kept.
- A whole file is converted.

## Out of Scope

- The reverse conversion, from ABC to BMW. The dictionary has the data for it
  — the `bmw_token` column of the ABC dictionary — but the BMW writer needs
  more than a token for a note.
- The MIDI and frequency mappings, which are carried over as comments.
