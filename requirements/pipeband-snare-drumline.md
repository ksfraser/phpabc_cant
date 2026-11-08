# Pipe Band Snare Drumline Requirements

> Feature-level requirements for `--pipebandsnare` CLI switch that converts
> V:Snare voices to drumline C/D notation (C=Left/la, D=Right/ra).
>
> Implemented by `ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass`
> and driven by `bin/abc-cannt-cli.php --pipebandsnare`.

## Problem

Pipe band snare drum parts in ABC use regular note pitches (A, B, C, etc.).
Drumline notation uses only two strokes: Left (lower-pitched) and Right
(higher-pitched/raised). The conversion must:
1. Identify the two distinct pitches used in a snare voice.
2. Map the higher pitch → D (Right, `ra`), lower pitch → C (Left, `la`).
3. Replace all notes in the snare voice with C or D accordingly.
4. Generate canntaireachd `w:` lines using `la` for C and `ra` for D.

## Functional Requirements

### FR-PBS-1 — CLI switch

`--pipebandsnare` enables drumline conversion for snare voices. When absent,
no conversion occurs and the pass behaves as before.

### FR-PBS-2 — Snare voice detection

A voice is a *snare voice* when either:
- the voice identifier matches `snare` (case-insensitive, e.g. `V:Snare`,
  `V:SNARE`, `V:Snare II`);
- the declared voice name matches `snare` (case-insensitive, e.g.
  `V:M name="Snare Drum"`).

Non-snare voices are never modified when `--pipebandsnare` is active.

### FR-PBS-3 — Two-pass conversion

**Pass 1:** Scan all lines of each snare voice and collect the distinct
note pitches used (e.g. `A`, `B`).

**Pass 2:** Sort the two pitches by pitch value (uppercase = lower octave,
lowercase = higher octave; within same case C<D<E<F<G<A<B).
- Lower pitch → `C` (Left)
- Higher pitch → `D` (Right)

Replace every note matching the lower pitch with `C`, every note matching
the higher pitch with `D`.

### FR-PBS-4 — Voice interaction rules

The conversion interacts with existing voices as follows:

- **Snare + Drumline both present:** The Drumline voice is **replaced** with
  converted Snare content (C/D notation). The original Snare voice is
  preserved unchanged.
- **Drumline only (no Snare):** A new V:Snare voice is created from the
  Drumline content, reverse-converting C/D back to A/c notation.
- **Snare only:** A new V:Drumline voice is appended with converted
  Snare content (C/D notation).

### FR-PBS-5 — Exactly two notes required

If a snare voice does not use exactly two distinct pitches, the conversion
is an error: lines are returned unchanged, no `V:Drumline` voice is created,
and an error message is recorded.

### FR-PBS-6 — Drumline canntaireachd

For each converted music line, generate a `w:` line beneath it:
- `C` notes → `la` (Left)
- `D` notes → `ra` (Right)

Syllables are space-separated, matching the bar structure of the ABC line.

### FR-PBS-7 — Reverse canntaireachd (Drumline → Snare)

When creating a Snare voice from Drumline (reverse conversion):
- `C` notes → `A` → `en`
- `D` notes → `c` → `o`

### FR-PBS-8 — Line preservation

All non-snare/non-drumline lines, headers, comments, `%%` directives, and
blank lines are emitted unchanged. When replacing Drumline, the original
Drumline header and music lines are removed.

### FR-PBS-9 — Processing order

Pipebandsnare runs **before** knock-on so that all voices (including newly
created Drumline/Snare voices) receive prepended knock-on rests.

### FR-PBS-10 — MuseScore Marching Snare compatibility (--musescore)

When `--musescore` is combined with `--pipebandsnare`, additional conversions
are applied to the drumline voice for MuseScore XML import compatibility:

- **ALL notes → C:** MuseScore's Marching Snare does not sound D, so every
  note (A, B, D, E, F, G) is converted to C. The `!accent!` character is
  preserved.
- **Flams {C} and drags {CC} → !accent!:** MuseScore does not handle grace-note
  dividers correctly, so grace-note groups are replaced with `!accent!`
  (accent mark). Using `!accent!` rather than `V` avoids collision with ABC
  `V:` voice headers.
- **L:-aware roll conversion:**
  - **L:1/8:** `(RCn` → `(RC-RC-...` (n hits), bare `r` → `(R.`
  - **L:1/16:** `(rcn` → `(rc-rc-...` (n hits, lowercase), bare `r` stays `r`
  - **L:1/8:** `(RC` → `(RC.`, **L:1/16:** `(rC` stays `(rC`
- **Note duration subdivisions:** `Cn` → `C` repeated `n` times (e.g. `C4` → `CCCC`)
- **Canntaireachd:** `C` → `la`, `!accent!` → `va`

Processing order: `--pipebandsnare` → `--musescore` → `--knock-on`, so the
drumline voice receives musescore-compatible notation before knock-on rests
are prepended.

## Non-Functional Requirements

- The conversion must be usable independently via `AbcCanntaireachdPass`
  constructed with `pipebandsnare=true`.
- The pass must not depend on CWD, `include_path`, or a second autoloader.
- The drumline dictionary (`drumline_dict.php`) follows the same data-file
  pattern as `abc_dict.php`.

## Test Cases

- `V:Snare` with notes `A B` → `C D`, `w: la ra`
- `V:Snare` with notes `A b` (lowercase b higher pitch) → `C D`, `w: la ra`
- `V:Snare` with 3+ distinct pitches → unchanged
- `V:Bagpipes` with `--pipebandsnare` → unaffected
- `V:Snare name="Snare Drum"` → detected and converted
- Case-insensitive matching on voice identifier and name
