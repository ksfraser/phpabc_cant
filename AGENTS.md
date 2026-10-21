# AGENTS.md — phpabc_cant

> **All shared standards live in the master file:**
> `~/Documents/AGENTS.md` and `~/Documents/AGENTS_ARCH.md`
>
> This file contains **only** the project-specific parts: what applies here, what
> deliberately does not, and the deviation backlog. Do not duplicate the shared
> documents — reference them by section number.

---

## 1. What this project is

`ksfraser/phpabc_cant` — Composer package `ksfraser/phpabc_canntaireachd`.

Parses ABC (notation) files and converts bagpipe tunes to **canntaireachd**
(the vocalised syllable notation used by pipers). Ships a PHP library, a CLI
tool suite under `bin/`, and a WordPress admin plugin under
`wp-abc-canntaireachd/`.

The core conversion is *not* solfège: `abc_dict.php` maps ABC tokens to real
canntaireachd syllables (`{g}A` → `hen`, `{d}B` → `do`, `A` → `en`, …), including
doublings, taorluath, shakes, grips and birls.

## 2. Namespace

```
ksfraser\PhpabcCanntaireachd\
ksfraser\origin\
```

Lowercase vendor segment, per `AGENTS_ARCH.md` §2.1. **This was `Ksfraser\`
(capital K) until 2026-10-20** — the rename was a breaking change to the
public API of the package. The on-disk tree is `src/ksfraser/…`.

`ksfraser\origin\` is the vendored FrontAccounting `Origin` base class. It is
legacy: it is FA-specific and carries a `defines.inc.php` that declares two
classes (`IException`, `CustomException`) inside a `.inc` file, so Composer
cannot autoload them. Nothing in this repo uses them; they exist only because
`Origin.php` extends the FA base class.

## 3. PHP platform

**PHP 8.1+.** The shared `AGENTS_ARCH.md` §1 floor of 7.3 does **not** apply
here — this package is not a FrontAccounting module and does not run on the FA
webserver. The floor is declared in `composer.json` (`"php": "^8.1"`) and
pinned with `config.platform.php = 8.1.18`.

Consequences: typed properties, `?Type` parameters and `match` are all allowed.
`declare(strict_types=1);` is required in every new file (see §5).

## 4. Which shared standards apply

| `AGENTS_ARCH.md` section | Applies? | Note |
|---|---|---|
| §2 Core design principles (SOLID, DRY, SRP, DI) | **Yes** | |
| §2.1 Namespace convention | **Yes** | Lowercase vendor segment, as above. |
| §2.2 One owner per capability | **Yes** | `AbcCanntaireachdPass` owns canntaireachd generation. |
| §3 Standard module layout | Partly | No `sql/`, no `includes/`, no `hooks.php` — this is a library, not an FA module. |
| §4 Namespace conventions | **Yes** | |
| §5 Coding standards | **Yes** | `strict_types`, naming, docblocks, SemVer, commit style, no `vendor/`, no `composer.lock`. |
| §6 Testing / TDD | **Yes** | See the backlog — the suite is not yet green. |
| §7 Dev/deploy workflow | Partly | No FA deploy tree, no pod. Branch/commit rules apply. |
| §8–§11 FA security areas, hooks, tables, RBAC | **No** | No FrontAccounting runtime here. |
| §12 FA module packaging (`_init/config`, `install.sql`) | **No** | Not an FA extension. |
| §13 RBAC | **No** | |

Two rules from `AGENTS_APPENDIX.md` **do** apply, because they are about PHP
and Composer rather than about FA:

- **One class, one load mechanism.** Never `require_once` a class file that
  Composer can autoload, and never declare the same FQCN in two files. Both
  were violated here and both caused real fatals (see §6).
- **Do not add a second autoloader.** Composer only.

## 5. Coding standards for this repo

- `declare(strict_types=1);` at the top of every PHP file. **Not yet done
  repo-wide** — see the backlog. New and modified files must have it.
- PSR-4: one class per file, file name == class name. Composer autoloads; do
  not hand-roll a prefix matcher.
- Docblocks carry `@param`, `@return`, `@throws`, `@since`.
- Exceptions: `ksfraser\PhpabcCanntaireachd\Exceptions\*`.
- Logging: `ksfraser\PhpabcCanntaireachd\Log\{CanntLog,DebugLog,FlowLog,GeneralLog}`.
  Do not call `error_log()` directly from library code — the CLI scripts are
  the only place that may write to stderr.
- The token dictionary has exactly **one** home:
  `src/ksfraser/PhpabcCanntaireachd/abc_dict.php`. It is a data file, not a
  class; it populates `$abc` in the including scope and returns it.

## 6. Dev commands

```bash
composer install                # vendor/ is not tracked; composer.lock is not tracked
vendor/bin/phpunit              # full suite
vendor/bin/phpunit tests/X.php  # one file
php -l <file>                   # lint before commit
php composer.phar validate      # composer.json + lock sanity
```

The suite is currently **red** (see §7), so a green/red count alone is not
enough to judge a change: capture a per-test status baseline before and after
and diff it. The CI does the same thing against a recorded list of known
failures:

```bash
vendor/bin/phpunit --log-junit junit.xml
php bin/check-test-baseline.php junit.xml      # fails on a NEW failure
php bin/check-test-baseline.php junit.xml --update   # after fixing one
```

To check the generated canntaireachd against the hand-written canntaireachd in
the score corpus:

```bash
php bin/abc-compare-canntaireachd.php ~/Bagpipes/Scores
```

To tidy up the tunes of a score — the header fields in one order, the include
block, the voice definitions, the pipe band drums, the pipe scale, and the
bars four to a line:

```bash
php bin/abc-tune-cleanup-cli.php --dir ~/Bagpipes/Scores --dry-run
```

## 7. Deviation backlog

Ordered by risk. Each item is a deliberate, recorded deviation from the
shared standards, not an accident.

| # | Item | State |
|---|---|---|
| 1 | **Test suite is red.** 32 errors + 51 failures out of 407 at the 2026-10-20 baseline. `docs/PROJECT_COMPLETION_CERTIFICATE.md` claims "100% complete, production ready" — that claim is false. | open |
| 2 | **`declare(strict_types=1)` missing** in ~360 of 363 source files. A blanket sweep is *not* safe: it changes scalar coercion at every call boundary. Do it file-by-file, with the suite as a gate. | open |
| 3 | **Legacy code in `legacy/`** — the original pre-PSR-4 scripts, moved out of `src/` on 2026-10-20. Not autoloaded, not tested, uses CWD-relative `require_once`. Do not add features there. | quarantined |
| 4 | `ksfraser\origin\Origin` — vendored FA base class with FA-specific code. Two of its classes live in a `.inc` file and are therefore not autoloadable. | accepted |
| 5 | `AbcFileParser::parse()` drops the `X:` header — `AbcTune::parse()` fills `$tune->headerLines` but never populates `$tune->headers` — so `bin/abc-canntaireachd-pass-cli.php` cannot locate a tune by its number. `AbcFileParser::parseRecursive()` does populate it. | open |
| 6 | The `AbcFormattingPass` rewrites headers as `X: 1` / `V: Bagpipes` (with a space after the colon). That is the project's documented house style — see `tests/AbcFormattingPassTest.php` — but it is *not* the ABC 2.1 standard (`X:1`). Do not "fix" it without changing those tests too. | accepted |
| 7 | `src/ksfraser/PhpabcCanntaireachd/InformationFields/` was removed: 27/27 files failed to parse and the directory name did not match the namespace. Nothing referenced them. | resolved |
| 8 | The package is absent from `MODULE_DIRECTORY.md` / `PACKAGIST.md` (by decision, 2026-10-20 — it is not part of the FA/WP ecosystem). | accepted |
| 9 | `AbcLyricsPass` / `AbcProcessor::handleLyrics()` are stubs — the lyrics pass does nothing. | open |

## 8. The clean up

`src/ksfraser/PhpabcCanntaireachd/Cleanup/` holds the clean up of a tune: a
parser of its own (`Parser/`, `Model/`), and one class per consideration
(`HeaderOrderNormaliser`, `IncludeBlockWriter`, `VoiceAttributeNormaliser`,
`DrumVoiceNormaliser`, `BagpipeScaleChecker`, `BarReflower`), all injected
into `TuneCleanupPipeline`.

The rule the clean up follows: **it never changes the music.** It changes the
headers, the voice definitions, the include block and the layout. The notes,
the grace notes, the decorations, the spacing and the lyrics are carried
through as they were written.

The requirements are in `requirements/tune-cleanup.md`.

## 9. The BMW converter

`src/ksfraser/PhpabcCanntaireachd/Bmw/` converts a Bagpipe Music Writer file
into ABC: `Bmw/BmwDictionary` reads the dictionary, and
`Bmw/BmwToAbcConverter` converts the file. The tokens are matched one at a
time, longest first — the original converter replaced over the whole line,
which turned a doubling into `{{d}e}`.

The dictionary is a data file, `src/ksfraser/PhpabcCanntaireachd/bmw_dict.php`,
next to the ABC one. The requirements are in `requirements/bmw-to-abc.md`.

## 10. The bagpipe fingering

`src/ksfraser/PhpabcCanntaireachd/Bagpipe/` holds the fingering of the Highland
chanter: `BagpipeFingering` has the table — the scale from the low G to the
high D, and the holes that are closed for each note — and
`BagpipeFingeringWriter` writes the abc2ps format file that draws it.

The words for the embellishments come from the token dictionary, so the chart
and the dictionary cannot disagree. Write the file again with
`bin/abc-bagpipe-fingering-cli.php` whenever the dictionary changes.

## 11. Known traps

- **`w:` is a lyrics field, not a line-break.** In this project a `w:` line
  *under* a music line carries the canntaireachd for that line. Do not confuse
  it with the ABC `w:` (line break) semantics.
- **The dictionary is not solfège.** `A` → `en`, `B` → `o`, `c` → `o`, `d` → `a`,
  `e` → `e`, `f` → `ve`, `g` → `di`. Do not "fix" these to `dar`/`dod`/`hid`;
  those are the solfège readings and are wrong for canntaireachd.
- **Grace notes are single tokens.** `{g}A` is one dictionary key, not `g`
  followed by `A`. The generator must keep the grace-note prefix attached.
- **Two `CanntGenerator` implementations existed** — one in
  `CanntGenerator.php`, one appended to `AbcCanntaireachdPass.php`. The
  appended copy won by accident of autoload order. Only the PSR-4 file remains.
- **The pipeline is line-preserving now, but the object model is not.**
  `AbcVoicePass` and `AbcVoiceOrderPass` were rewritten to work on lines
  rather than round-tripping the tune through `AbcTune`. `AbcTune::renderSelf()`
  still loses spacing and drops the `X:` header — do not use it to re-render
  a tune.
- **`AbcCanntaireachdPass` is the only place that writes `w:` lines.**
  `LyricsGenerator` and `CanntaireachdTransform` still contain a second,
  older implementation of the same idea; they are not wired into the
  pipeline. Prefer the pass.

---

*Shared standards: `~/Documents/AGENTS.md`, `~/Documents/AGENTS_ARCH.md`,
`~/Documents/AGENTS_APPENDIX.md`. Ecosystem maps hardlinked into this repo:
`MODULE_DIRECTORY.md`, `PACKAGIST.md`, `APP_TAB_ARCHITECTURE.md`.*
