# Project Requirements: PHPABC Canntaireachd

## Overview
This project provides a PSR-4 compliant PHP library for working with ABC notation and canntaireachd, including utilities for parsing, simplifying, and converting musical notation. The codebase is designed for correctness, completeness, and maintainability.

## Functional Requirements

### 1. PSR-4 Compliance
- All classes must use proper namespaces and directory structure.
- Composer autoloading must work for all classes.

### 2. ABC Notation Support
- Parse ABC notation files and strings.
- Represent musical elements: notes, barlines, beats, voices, keys, embellishments, gracenotes, etc.
- Provide utilities for simplifying and converting ABC notation.
- Support dictionary-based conversions (Dict2php).

### 3. Canntaireachd Support
- Support conversion between ABC notation and canntaireachd (bagpipe vocal notation).
- Provide extensible base classes for future musical features.

### 4. File Handling
- Integrate with `ksf-file` package for robust file operations.

### 5. Testing & Documentation
- All classes must have PHPDoc blocks for IDE and documentation support.
- All classes must have unit tests verifying instantiation and basic behavior.
- Test coverage must be tracked and reported.
- Requirements, test plan, and test matrix must be documented in the repository.

## Non-Functional Requirements
- Code must be readable, maintainable, and follow modern PHP best practices.
- All dependencies must be managed via Composer.
- The codebase must be compatible with PHP 8.0+.
- All public APIs must be documented.
- The project must include a UML diagram of class relationships and message flow.

---

# Test Plan

## 1. Unit Testing
- Use PHPUnit for all unit tests.
- Each class must have a test verifying:
  - Instantiation
  - Basic method behavior (where applicable)
- Edge cases and error handling must be tested for core logic classes (e.g., AbcParser, Dict2php).

## 2. Integration Testing
- Test that Composer autoloading works for all classes.
- Test that ABC parsing and simplification work end-to-end.
- Test file operations using ksf-file integration.

## 3. Documentation Testing
- Verify that all classes have PHPDoc blocks.
- Verify that UML diagram is up to date and matches codebase.

## 4. Coverage Reporting
- Use PHPUnit's coverage tools to ensure all classes and methods are covered.
- Target: 100% class instantiation coverage, 80%+ method coverage for core logic.

---

# Test Matrix

| Class                | Instantiation | Core Methods | Error Handling | Integration | PHPDoc | UML |
|----------------------|:-------------:|:------------:|:--------------:|:-----------:|:------:|:---:|
| AbcParser            |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| AbcTuneBase          |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| AbcNote              |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| AbcBarline           |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| AbcBeat              |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| AbcEmbellishment     |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| AbcFile              |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| AbcGracenote         |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| AbcKey               |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| AbcVoice             |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| AspdTune             |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| BaseConverter        |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| BmwFileByToken       |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| BuildDictionaries    |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| Dict2php             |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| LineByLine           |      ✓        |      -       |      -         |     ✓       |   ✓    | ✓   |
| SimplifyAbc          |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |

Legend: ✓ = Covered, - = Not applicable

---

# Coverage Reporting
- Run `vendor/bin/phpunit --coverage-html coverage` to generate coverage report.
- Review `coverage/index.html` for gaps and improve tests as needed.

---

# Additional Notes
- All requirements, test plans, and test matrix must be kept up to date as the codebase evolves.
- All new features must include corresponding tests and documentation.

---

# ABC Canntaireachd Requirements

## MIDI Defaults Table
- Table name: `abc_midi_defaults` (used by both WordPress and CLI)
- Columns: `id`, `voice_name`, `midi_channel`, `midi_program`
- Example defaults:
  - Drums: channel 10
  - Bagpipes: channel 0, program 110
  - Flute, Tenor, Clarinet, Trombone, Tuba, Alto, Trumpet, Guitar, Piano, BassGuitar (sequential channels)

## WordPress Admin
- Admin screen to list/add/edit/delete MIDI defaults
- Uses the same table
- Validates and processes ABC files in multiple passes
- Shows links to output files (ABC, diff, error log)

## CLI Tool
- Options: `--midi_channel`, `--midi_program`, `--list`, `--add`, `--edit`, `--delete`, `--validate`, `--save`
- Uses the same table and config_db.php for DSN
- Lists output files after processing

## AbcProcessor (shared)
- Multi-pass ABC file processing:
  1. Detect voices
  2. Copy Melody to Bagpipes if needed
  3. Handle w:/W: lyrics/canntaireachd
  4. Validate canntaireachd and log differences
  5. Reorder voices by channel, drums last
- Returns processed lines and diff log

## Output
- ABC file, cannt_diff.txt, error log
- CLI lists files, WP shows links

## Tests
- Test MIDI defaults table creation and CRUD
- Test CLI tool options and output files
- Test WordPress admin screen (list, add, edit, delete, output links)
- Test AbcProcessor for multi-pass logic, voice reordering, diff logging, lyrics handling

# Multi-pass ABC processing includes timing validation
- Timing validation is performed as the last pass using AbcTimingValidator
- Bars with incorrect timing (except pickup/last) are marked with 'TIMING' and errors are logged
- Errors are written to abc_errors.txt by CLI
- AbcTimingValidator is a dedicated class for SRP/SOLID
- CLI output files: validated ABC, cannt_diff.txt, abc_errors.txt
- All passes are unit tested

# Requirements Update
- CLI supports multiple ABC files via wildcards for validate/save
- WP module supports multiple concurrent uploads, stores files in uploads directory, provides download links
- ABC files may contain multiple songs (tunes), detected by X: header
- Blank line is auto-inserted before X: header if missing
- Blank lines within tunes are preserved for hidden voices/data
- ABC parsing builds AbcTune/AbcLine/AbcBar objects for validation and rendering
- Validation pipeline uses pass classes for SRP/SOLID
- Bagpipe style checks: bar count, repeats, volta/2nd endings
- All output files (ABC, diff, errors) are saved and listed per input file
- Unit tests cover multi-song parsing, timing validation, style checks
