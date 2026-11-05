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

### FR-PBS-4 — Exactly two notes required

If a snare voice does not use exactly two distinct pitches, the voice is
left unchanged (no conversion, no `w:` line generated).

### FR-PBS-5 — Drumline canntaireachd

For each converted music line, generate a `w:` line beneath it:
- `C` notes → `la` (Left)
- `D` notes → `ra` (Right)

Syllables are space-separated, matching the bar structure of the ABC line.

### FR-PBS-6 — Line preservation

All non-snare lines, headers, comments, `%%` directives, and blank lines
are emitted unchanged. Only snare voice music lines are modified.

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
