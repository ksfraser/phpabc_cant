# Tune Number Renumbering & Width
## Voice Header Preservation
- Output files must support both grouped and interleaved voice layouts, configurable via AbcProcessorConfig or CLI option.
- Tests must assert correct rendering for both grouped and interleaved styles.
| Voice Output Style     | ✓   | ✓  | ✓      | ✓              | ✓             |
- All CLI and WP output files must preserve and render all V: (voice) header lines from input ABC files.
- Multi-tune and multi-voice files must retain all V: lines in output, in correct order.
- Tests must assert that output files contain all expected V: lines for each tune and voice.
| Feature                | CLI | WP | Config | Error Handling | Test Coverage |
|------------------------|:---:|:--:|:------:|:--------------:|:-------------:|
| Voice Header Output    | ✓   | ✓  | ✓      | ✓              | ✓             |


## Features
- All CLI commands support `--output=filename` and use a common output writer class (`CliOutputWriter`) for file output
- CLI and WP support renumbering duplicated X: tune numbers
- Config option for tune number width (default 5, left-filled with zeros)
- CLI: `abc-renumber-tunes-cli.php <abcfile> [--width=N]` sets width
- WP: Option to set width for renumbering in admin UI
- All renumbered X: numbers are unique and formatted as X: 00001, X: 00002, etc.

## Test Matrix (Renumbering)
| Feature                | CLI | WP | Config | Error Handling | Test Coverage |
|------------------------|:---:|:--:|:------:|:--------------:|:-------------:|
| Renumber Duplicates    | ✓   | ✓  | ✓      | ✓              | ✓             |
| Tune Number Width      | ✓   | ✓  | ✓      | ✓              | ✓             |
| Left-filled Zeros      | ✓   | ✓  | ✓      | ✓              | ✓             |
| Validation Errors      | ✓   | ✓  | ✓      | ✓              | ✓             |
| Undefined Variable Fix | ✓   | ✓  | -      | ✓              | ✓             |

## Error Handling
- All validation errors (missing X:, T:, K:) are reported per tune
- Undefined variable notices (e.g., $params) are fixed and tested
- CLI and WP output errors to screen/log

---
# Project Requirements: PHPABC Canntaireachd

- All public APIs must be documented.
- The project must include a UML diagram of class relationships and message flow.

---


# Test Plan
## 6. Voice Output Style Testing
- Add/expand tests to assert correct output for both grouped and interleaved voice layouts.
## 5. Voice Header Output Testing
- Add/expand tests to assert that output files contain all expected V: lines for multi-tune and multi-voice cases.

## 1. Unit Testing
- Use PHPUnit for all unit tests.
- Each class must have a test verifying:
  - Instantiation
  - Basic method behavior (where applicable)
- Edge cases and error handling must be tested for core logic classes (e.g., AbcParser, Dict2php).
- All CLI output is tested via `CliOutputWriterTest`.
- Voice order pass is tested via `AbcVoiceOrderPassTest`.

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
| AbcTune            | ✓        | ✓       | ✓         | ✓       |   ✓    | ✓   |
| AbcLine            | ✓        | ✓       | ✓         | ✓       |   ✓    | ✓   |


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
| AbcVoiceOrderPass    |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| CliOutputWriter      |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |

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

## Token Dictionary Table
Table name: `abc_dict_tokens` (used by both WordPress and CLI)
Columns: `id`, `abc_token`, `cannt_token`, `bmw_token`, `description`
Prepopulated from `abc_dict.php` for ABC/canntaireachd/BMW mappings
Logic:
  - If a BMW token is added and the ABC token already exists, update the BMW token in that row
  - If a new ABC token is added, insert with all provided values
  - Retain all ABC/canntaireachd mappings, and ensure BMW tokens are filled where appropriate

## Header Field Table & Matching
Table name: `abc_header_fields` (used by both WordPress and CLI)
Columns: `id`, `field_name`, `field_value`
Stores unique values for header fields (e.g., composer, book)
Logic:
  - When processing ABC files, compare tune header fields against stored values
  - If match score is low, add new value to table
  - If match score is high but not exact, add a comment to the tune for review
  - Supports future matching, suggestions, and corrections

## WordPress Admin
- Admin screen to list/add/edit/delete MIDI defaults
- Admin screen to list/add/edit/delete token dictionary entries (ABC/canntaireachd/BMW)
- Admin screen to list/add/edit/delete header field values (composer, book, etc.)
- On add, checks for existing ABC token and updates BMW token if present
- Validates and processes ABC files in multiple passes
- Shows links to output files (ABC, diff, error log)

## CLI Tool
- Options: `--midi_channel`, `--midi_program`, `--list`, `--add`, `--edit`, `--delete`, `--validate`, `--save`
- Uses both tables and config_db.php for DSN
- Lists output files after processing
- Supports header field CRUD and matching

## Test Requirements for Token & Header Field Management
- Test that the token table is correctly prepopulated from `abc_dict.php`
- Test that adding a BMW token updates the correct row if the ABC token exists
- Test CRUD operations via the admin screen (add, edit, delete)
- Test conversion logic using the unified token table for ABC/canntaireachd/BMW
- Test that header field table stores unique values for composer, book, etc.
- Test that processing a tune with a new header field adds it to the table
- Test that high-but-not-exact matches add a comment for review
- Test CRUD operations for header fields via admin and CLI
- Test matching and suggestion logic for header fields

## WordPress Admin
- Admin screen to list/add/edit/delete MIDI defaults
- Admin screen to list/add/edit/delete token dictionary entries (ABC/canntaireachd/BMW)
- On add, checks for existing ABC token and updates BMW token if present
- Validates and processes ABC files in multiple passes
- Shows links to output files (ABC, diff, error log)

## CLI Tool
- Options: `--midi_channel`, `--midi_program`, `--list`, `--add`, `--edit`, `--delete`, `--validate`, `--save`
- Uses both tables and config_db.php for DSN
- Lists output files after processing

## Test Requirements for Token Management
- Test that the token table is correctly prepopulated from `abc_dict.php`
- Test that adding a BMW token updates the correct row if the ABC token exists
- Test CRUD operations via the admin screen (add, edit, delete)
- Test conversion logic using the unified token table for ABC/canntaireachd/BMW

### Example Test Cases
1. **Prepopulation**
   - Assert that all ABC/canntaireachd/BMW mappings from `abc_dict.php` exist in the table after schema creation.
2. **BMW Token Update**
   - Add a BMW token for an existing ABC token via admin UI/CLI, assert only the BMW token and description are updated.
3. **Add New Token**
   - Add a new ABC token with canntaireachd and BMW values, assert a new row is created.
4. **Edit Token**
   - Edit any field for an existing token via admin UI, assert changes are persisted.
5. **Delete Token**
   - Delete a token via admin UI, assert it is removed from the table.
6. **Conversion Logic**
   - Use the token table for ABC->canntaireachd and BMW->ABC conversions, assert correct mapping is returned.
7. **Edge Cases**
   - Add tokens with special characters, long values, or empty fields, assert proper handling and validation.
8. **Bulk Operations**
   - Import multiple tokens at once, assert all are added/updated correctly.

### Test Coverage Targets
- 100% coverage for CRUD operations on token table (add, edit, delete, update BMW)
- 100% coverage for conversion logic using token table
- 100% coverage for admin UI actions (form submission, validation, error handling)
- 100% coverage for CLI options related to token management
- 100% coverage for schema prepopulation and migration

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
