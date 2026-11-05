# Voice Line Prefix Requirements

> Feature-level requirements for `--voice-lines` CLI switch that prefixes
> every music line with voice identifier, time signature, and note length.
>
> Implemented by `ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass`
> and driven by `bin/abc-cannt-cli.php --voice-lines`.

## Problem

ABC music lines don't consistently carry voice/time/unit context on every
line. For pipe band scoring with multiple drum voices and alignment
requirements, each line needs explicit `[V:...][M:...][L:...]` markers plus
bar line markers for visual alignment across staves.

## Functional Requirements

### FR-VLP-1 — CLI switch

`--voice-lines` enables per-line voice/time/unit prefixing. When absent,
music lines are emitted unchanged.

### FR-VLP-2 — Voice line prefix format

Every music line is prefixed with:

```
[V:<voice>][M:<meter>][L:<unit>] <bar> <music> |
```

- `<voice>` = voice identifier (e.g. `Snare`, `Bagpipes`)
- `<meter>` = `M:` time signature declared for that voice (default `4/4`)
- `<unit>` = `L:` note-length declared for that voice (default `1/8`)
- `<bar>` = `|` for first music line after voice header (anacrusis), `x|`
  for subsequent lines (continuation marker)
- Every music line ends with a trailing `|` bar line

### FR-VLP-3 — Voice/M/L tracking

- A `V:<id>` header sets the current voice
- An inline `[V:<id>]` marker switches the current voice
- `M:` and `L:` headers are tracked per voice
- Default meter `4/4`, default unit `1/8` when not declared

### FR-VLP-4 — Anacrusis handling

The first music line after a voice header uses `|` (anacrusis/pick-up
marker). Subsequent music lines use `x|` (continuation marker) for vertical
alignment across voices.

### FR-VLP-5 — Line preservation

Non-music lines (headers, comments, `%%`, blank lines) are emitted
unchanged. Only music lines receive the prefix/trailing-bar treatment.

## Non-Functional Requirements

- Usable independently via `AbcCanntaireachdPass` constructed with
  `voiceLines=true`
- Must not depend on CWD, `include_path`, or second autoloader

## Test Cases

- `V:Snare` with `M:2/4`, `L:1/8`, music `A B` →
  `[V:Snare][M:2/4][L:1/8] | A B |`
- First music line after voice header gets `|`, next gets `x|`
- Music lines without declared M:/L: use defaults `4/4`/`1/8`
- Non-music lines (X:, T:, K:, comments) emitted unchanged
