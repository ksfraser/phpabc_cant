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

## Official ABC Specification Reference
- **ABC Standard v2.1**: https://abcnotation.com/wiki/abc:standard:v2.1
- All parsing and processing must comply with the official ABC notation standard
- Token parsing must cover all valid ABC directives and information fields as defined in the standard

- Must be compatible with PHP 7.3 and later (no typed properties, no PHP 7.4+ syntax).
- All public APIs must be documented.
- The project must include a UML diagram of class relationships and message flow.

## Database Management
- All database access must use the central `Ksfraser\Database\DbManager` class.
- DbManager loads config from `config/db_config.php` and/or Symfony secrets, provides a singleton PDO connection, and helper methods for queries.
- CLI and WP code must not access `db_config.php` directly; use DbManager for all DB actions.

## Test Requirements for Database
- Test that `DbManager::getConfig()` loads config correctly from file and secrets.
- Test that `DbManager::getPdo()` returns a working PDO connection.
- Test all helper methods (`fetchAll`, `fetchOne`, `fetchValue`, `execute`) for correct query execution and error handling.
- Test that CLI and WP modules use DbManager for DB access.

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
| PHP 7.3 Compatibility | ✓        | ✓       | ✓         | ✓       |   ✓    | ✓   |
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
| AbcCanntaireachdPass |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| ParseContext         |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| AbcProcessorConfig   |      ✓        |      ✓       |      -         |     ✓       |   ✓    | ✓   |
| CanntGenerator       |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |
| TokenDictionary      |      ✓        |      ✓       |      ✓         |     ✓       |   ✓    | ✓   |

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
Uses both tables and config/db_config.php for DSN
Uses both tables and config/db_config.php for DSN
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

# Automatic Canntaireachd Generation Requirements

## Feature Overview
- Automatic generation of canntaireachd text from ABC notation for bagpipe tunes
- Detects bagpipe tunes based on key signatures (typically D major, A major, etc.)
- Automatically adds "Bagpipes" voice if missing from multi-voice tunes
- Copies melody notes from primary voice to Bagpipes voice
- Converts ABC note tokens to canntaireachd text using token dictionary
- Adds generated canntaireachd as w: (lyrics) lines in the Bagpipes voice

## Processing Logic
- **Tune Detection**: Identifies bagpipe tunes by examining K: (key) headers for common bagpipe keys
- **Voice Management**: 
  - If single voice tune, processes the melody voice
  - If multi-voice tune without "Bagpipes" voice, creates new Bagpipes voice
  - Copies melody content from primary voice to Bagpipes voice
- **Token Conversion**: Uses `abc_dict.php` token dictionary to convert ABC notes to canntaireachd syllables
- **Output Integration**: Inserts canntaireachd text as w: lines aligned with note timing

## Implementation Classes
- `AbcCanntaireachdPass`: Dedicated pass class for canntaireachd generation logic
- `TokenDictionary`: Handles ABC to canntaireachd token mapping lookups
- Integration with `AbcProcessor` multi-pass pipeline

## Configuration Options
- Enable/disable automatic canntaireachd generation via `AbcProcessorConfig`
- Configurable bagpipe key detection patterns
- Option to preserve existing w: lines or replace with generated canntaireachd

## Error Handling
- Logs warnings for tunes where canntaireachd generation fails
- Continues processing other tunes in multi-tune files
- Validates token dictionary availability before processing

## Test Requirements
- **Unit Tests**:
  - Test bagpipe tune detection for various key signatures
  - Test voice creation and melody copying logic
  - Test token conversion accuracy using sample ABC/canntaireachd pairs
  - Test w: line insertion and alignment
  - Test error handling for missing tokens or invalid ABC

- **Integration Tests**:
  - End-to-end processing of complete ABC files
  - Verification of generated canntaireachd against expected outputs
  - Multi-voice tune processing with Bagpipes voice creation
  - Diff logging for canntaireachd changes

- **Edge Cases**:
  - Single voice vs multi-voice tunes
  - Tunes with existing Bagpipes voices
  - Non-bagpipe tunes (should not generate canntaireachd)
  - ABC files with complex rhythms and embellishments
  - Token dictionary gaps or missing mappings

## Test Matrix (Canntaireachd Generation)
| Feature                     | CLI | WP | Config | Error Handling | Test Coverage |
|-----------------------------|:---:|:--:|:------:|:--------------:|:-------------:|
| Bagpipe Tune Detection      | ✓   | ✓  | ✓      | ✓              | ✓             |
| Voice Creation & Copying    | ✓   | ✓  | ✓      | ✓              | ✓             |
| Token Conversion            | ✓   | ✓  | ✓      | ✓              | ✓             |
| w: Line Integration         | ✓   | ✓  | ✓      | ✓              | ✓             |
| Multi-tune Processing       | ✓   | ✓  | ✓      | ✓              | ✓             |
| Diff Logging                | ✓   | ✓  | ✓      | ✓              | ✓             |

## Example Test Cases
1. **Basic Generation**: Process simple bagpipe tune, verify canntaireachd w: lines added
2. **Voice Creation**: Multi-voice tune without Bagpipes, verify new voice created and populated
3. **Melody Copying**: Verify melody notes correctly copied to Bagpipes voice
4. **Token Accuracy**: Compare generated canntaireachd against known correct translations
5. **Non-Bagpipe Skip**: Process non-bagpipe tune, verify no canntaireachd added
6. **Existing Preservation**: Tune with existing w: lines, verify proper handling based on config
7. **Error Recovery**: Tune with unmappable tokens, verify processing continues with warnings

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

# BABOK Work Products

## Business Requirements
- **BR1**: The system must process ABC notation files to generate canntaireachd lyrics for bagpipe tunes.
- **BR2**: Support automatic detection of bagpipe tunes based on key signatures (e.g., D major, A major).
- **BR3**: Enable melody copying from primary voices to Bagpipes voices when Bagpipes voice is missing.
- **BR4**: Validate time signatures and ensure complete bars in ABC files.
- **BR5**: Log exceptions for out-of-spec bars/voices for correction.
- **BR6**: Provide CLI and WordPress interfaces for processing ABC files.
- **BR7**: Store settings, translations, and header fields in a database.
- **BR8**: Translate between ABC, canntaireachd, and BMW representations.

## Functional Requirements
- **FR1**: Parse ABC files and detect tunes, voices, and music lines.
- **FR2**: For Bagpipes voices, generate canntaireachd w: lines using token dictionary.
- **FR3**: Copy melody from primary voice to Bagpipes voice if needed.
- **FR4**: Validate timing and bar completeness, marking errors.
- **FR5**: Output processed ABC, diff, and error logs.
- **FR6**: Support interleaved/grouped voice output styles.
- **FR7**: Preserve voice headers and lyrics in output.
- **FR8**: Use Trie for efficient token matching in canntaireachd generation.
- **FR9**: Load dictionary from file or database.
- **FR10**: CLI options for conversion, output, and validation.
- **FR11**: Provide CLI interface (abc-cannt-cli.php) for ABC file processing with options like --convert and --output.
- **FR12**: Support voice ordering with three modes: source file order (default), orchestral score order, and custom user-defined order.
- **FR13**: Implement instrument family classification and voice name mapping for orchestral ordering.
- **FR14**: Provide configuration system for custom voice ordering rules (JSON, database, or UI).
- **FR15**: Support three transpose modes for ABC output: MIDI import (transpose=0), modern bagpipe (transpose=2), and orchestral (written pitch).
- **FR16**: Manage MIDI channel and program defaults per instrument via database (abc_midi_defaults table).
- **FR17**: Map percussion/drum parts to MIDI channel 10 with appropriate percussion programs.
- **FR18**: Load instrument voice defaults (MIDI channel, program, transpose, octave) from configuration or database.
- **FR19**: Support configuration files (JSON/PHP) with all CLI options having config file equivalents.
- **FR20**: Implement configuration precedence: CLI options > custom config > user config > global config > defaults.
- **FR21**: Provide configuration save/load functionality for CLI and WordPress interfaces.
- **FR22**: Support configuration presets in WordPress with export/import capabilities.

## Voice Ordering Requirements
### Current Implementation Status
- **Current Behavior**: Voice order from source ABC file is preserved (no reordering)
- **Implementation**: `AbcProcessor::reorderVoices()` is currently a stub that returns input unchanged
- **Status**: Requires implementation to support orchestral and custom ordering modes

### Voice Ordering Options (REQUIRED IMPLEMENTATION)
1. **Source File Order (Default)** ✓
   - Preserves the order of V: voice headers as they appear in the input ABC file
   - No processing required
   - Recommended for files already organized by composer/arranger
   - Currently implemented (stub returns unchanged)

2. **Orchestral Score Order (REQUIRED)**
   - Reorder voices according to standard orchestral score layout
   - Standard orchestral order:
     - **Woodwinds**: Piccolo, Flute, Oboe, Clarinet, Bassoon
     - **Brass**: French Horn, Trumpet, Trombone, Tuba
     - **Percussion**: Timpani, other percussion
     - **Strings**: Violin I, Violin II, Viola, Cello, Double Bass
     - **Keyboard/Harp**: Piano, Organ, Harp (if present)
     - **Bagpipes**: Highland Bagpipes, Uilleann Pipes (treated as woodwind)
     - **Vocals**: Soprano, Alto, Tenor, Bass (if present)
   - Implementation requirements:
     - Voice family classification system (InstrumentFamily enum)
     - Instrument name to family mapping (via configuration or database)
     - Orchestral position ordering rules
     - Support for voice name variations ("Violin 1", "Vln I", "V1")
     - Fallback: unrecognized instruments placed at end in source order

3. **Custom Order (REQUIRED)**
   - User-specified voice ordering via configuration
   - Support for domain-specific ordering (e.g., bagpipe band: Pipes, Tenor Drums, Bass Drum, Snare)
   - Configuration format: ordered array of voice name patterns or instrument families
   - CLI support: `--voice-order-config=filename.json`
   - WordPress UI: Custom ordering editor with drag-and-drop interface
   - Fallback: voices not in custom order appear at end in source order

### Configuration Requirements
- **Config Option**: `voice_ordering` with values: `source` (default), `orchestral`, `custom`
- **Config Option**: `voice_order_custom` - array or JSON file path for custom ordering rules
- **CLI Options**: 
  - `--voice-order=source|orchestral|custom` - select ordering mode
  - `--voice-order-config=filename.json` - path to custom ordering configuration
- **WordPress UI**: 
  - Radio buttons for voice ordering mode (Source/Orchestral/Custom)
  - Custom ordering editor with drag-and-drop interface
  - Preview of current voice order before processing

### Implementation Classes
- **AbcVoiceOrderPass**: Update to implement all three ordering modes (currently stub)
- **VoiceOrderingStrategy** (new): Interface for ordering strategies
  - `SourceOrderStrategy`: Returns voices in original order
  - `OrchestralOrderStrategy`: Implements orchestral score ordering
  - `CustomOrderStrategy`: Implements user-defined ordering
- **InstrumentFamily** (new): Enum or class for instrument classification
- **InstrumentMapper** (new): Maps voice names to instrument families
- **VoiceOrderConfig** (new): Configuration container for ordering rules

### Test Requirements
- **VR1**: Test that source order is preserved when `voice_ordering=source`
- **VR2**: Test orchestral ordering produces correct sequence for standard orchestra
- **VR3**: Test orchestral ordering handles bagpipe ensembles correctly
- **VR4**: Test custom ordering follows user-defined rules from configuration
- **VR5**: Test voice ordering with single-voice tunes (no reordering needed)
- **VR6**: Test voice ordering with multi-tune files (each tune ordered independently)
- **VR7**: Test fallback behavior for unrecognized instrument names
- **VR8**: Test voice name variations ("Violin I", "Vln 1", "V1") map to same position
- **VR9**: Test configuration validation (invalid custom order, missing instruments)
- **VR10**: Test orchestral ordering with partial orchestras (some instrument families missing)

### Traceability
- **Related Classes**: `AbcVoiceOrderPass`, `AbcProcessor::reorderVoices()`, `AbcProcessor::reorderVoicesInTune()`
- **Related Tests**: `AbcVoiceOrderPassTest`
- **Related Requirements**: FR7 (Preserve voice headers), BR6 (CLI/WP interfaces)

---

## Transpose Mode Requirements
### Overview
Different use cases require different transpose settings for ABC notation output. The system must support three transpose modes to accommodate MIDI imports, modern bagpipe tuning, and orchestral score conventions.

### Transpose Modes (REQUIRED IMPLEMENTATION)

#### 1. MIDI Import Mode (transpose=0 for all voices)
- **Use Case**: ABC files imported from MIDI files or created from audio
- **Behavior**: All voices have `transpose=0` and `octave=0`
- **Rationale**: MIDI uses absolute pitch (C=C, D=D), no transposition needed
- **Default**: This is the current behavior when importing from MIDI
- **Output Example**: `V:Trumpet transpose=0 octave=0`

#### 2. Modern Bagpipe Mode (transpose=2 for concert pitch instruments)
- **Use Case**: Bagpipe ensemble music for modern Highland bagpipes
- **Behavior**: 
  - Bagpipe voices: `transpose=0` (written pitch = sounding pitch)
  - Concert pitch instruments (Piano, Guitar, etc.): `transpose=2` (up one whole step)
  - Reason: Highland bagpipes sound at approximately Bb major when written in A major
  - Modern chanters are typically tuned 480Hz (slightly sharp of Bb)
- **Configuration**: `transpose_mode=bagpipe` or `--transpose-mode=bagpipe`
- **Example**:
  - `V:Bagpipes transpose=0` (A on paper sounds Bb)
  - `V:Piano transpose=2` (written C sounds D, matching bagpipe's written A = sounding Bb)
  - `V:Guitar transpose=2`

#### 3. Orchestral Score Mode (written pitch for transposing instruments)
- **Use Case**: Traditional orchestral/concert band scores
- **Behavior**: Each instrument uses its standard orchestral transpose setting
- **Transpose Settings by Instrument Family**:
  - **Concert Pitch** (transpose=0): Piano, Guitar, Flute, Oboe, Bassoon, Trombone, Tuba, Strings, Percussion
  - **Bb Instruments** (transpose=2): Trumpet, Clarinet, Tenor Sax, Soprano Sax
  - **Eb Instruments** (transpose=9): Alto Sax, Baritone Sax, Eb Clarinet
  - **F Instruments** (transpose=7): French Horn, English Horn
  - **Bagpipes** (transpose=0 or 2 depending on convention)
- **Configuration**: `transpose_mode=orchestral` or `--transpose-mode=orchestral`
- **Note**: In orchestral mode, each part shows the notes the musician reads, not concert pitch

### Configuration Requirements
- **Config Option**: `transpose_mode` with values: `midi` (default), `bagpipe`, `orchestral`
- **CLI Option**: `--transpose-mode=midi|bagpipe|orchestral`
- **WordPress UI**: Radio buttons for transpose mode selection
- **Per-Voice Override**: Allow manual transpose override via configuration or ABC header
- **Database Storage**: `abc_midi_defaults` table should include default `transpose` column

### Implementation Requirements

#### Database Schema Enhancement
```sql
ALTER TABLE abc_midi_defaults 
ADD COLUMN transpose INT DEFAULT 0,
ADD COLUMN octave INT DEFAULT 0;
```

#### New/Modified Classes
- **TransposeMode** (new): Enum or class for transpose mode types
- **TransposeStrategy** (new): Interface for transpose calculation strategies
  - `MidiTransposeStrategy`: Returns transpose=0 for all instruments
  - `BagpipeTransposeStrategy`: Applies bagpipe ensemble transposition rules
  - `OrchestralTransposeStrategy`: Applies orchestral transposition by instrument
- **InstrumentTransposeMapper** (new): Maps instrument names to standard transpose values
- **AbcVoiceFactory** (enhance): Apply transpose mode when creating voices
- **InstrumentVoiceFactory** (enhance): Include default transpose values per instrument

#### Existing Infrastructure (COMPLETE ✓)
- **VoiceFactory**: Already supports `transpose` and `octave` parameters
- **AbcVoice**: Already stores and renders `transpose=N` in V: headers
- **MidiInstrumentMapper**: Maps 128 MIDI programs to instrument names
- **abc_midi_defaults table**: Stores voice_name, midi_channel, midi_program
- **abc-midi-defaults-cli.php**: CLI tool for managing MIDI defaults

### Percussion/Drum Instrument Mapping

#### MIDI Channel 10 Requirement
- **Standard**: MIDI channel 10 is reserved for percussion/drums (General MIDI spec)
- **Database**: `abc_midi_defaults` already maps Drums to channel 10
- **Current Entries**:
  ```sql
  ('Drums', 10, 0),
  ('Snare', 11, 0),  -- Should be channel 10
  ('Bass', 11, 0),   -- If this is bass drum, should be channel 10
  ('Tenor', 11, 0)   -- If this is tenor drum, should be channel 10
  ```
- **Fix Required**: Update drum/percussion instruments to use channel 10

#### Percussion Instrument Mapping
- **Clef**: Percussion uses `clef=perc` in ABC notation
- **MIDI Programs for Percussion** (channel 10):
  - Program numbers are ignored on channel 10 (uses note numbers instead)
  - Different drum sounds mapped to different MIDI note numbers:
    - Bass Drum: MIDI note 35-36
    - Snare Drum: MIDI note 38, 40
    - Tenor Drum: MIDI note 47-48 (low-mid toms)
    - Hi-Hat: MIDI note 42, 44, 46
    - Cymbals: MIDI note 49, 51, 52, 55, 57, 59
- **Implementation**: Create DrumMapper class for note number mapping

#### Percussion Voice Detection
- **Detection Rules**:
  - Voice name contains "Drum", "Snare", "Bass" (drum context), "Tenor" (drum context), "Perc", "Cymbal"
  - Voice has `clef=perc` attribute
  - Voice mapped to MIDI channel 10 in database
- **Auto-Configuration**: Percussion voices automatically get:
  - `midi_channel=10`
  - `clef=perc`
  - `transpose=0` (percussion doesn't transpose)
  - `stafflines=1` (optional, for visual clarity)

### Test Requirements
- **TR1**: Test MIDI mode sets transpose=0 for all instruments
- **TR2**: Test bagpipe mode sets transpose=2 for concert pitch instruments, 0 for bagpipes
- **TR3**: Test orchestral mode applies correct transpose per instrument family
- **TR4**: Test transpose override via configuration works
- **TR5**: Test percussion instruments map to MIDI channel 10
- **TR6**: Test drum voice detection (name, clef, database)
- **TR7**: Test transpose mode persistence (save/load configurations)
- **TR8**: Test mixed ensembles (bagpipes + orchestra)
- **TR9**: Test abc_midi_defaults table loads transpose/octave values
- **TR10**: Test CLI --transpose-mode flag applies correct settings

### Traceability
- **Related Classes**: 
  - Existing: `VoiceFactory`, `AbcVoice`, `InstrumentVoiceFactory`, `MidiInstrumentMapper`
  - Database: `abc_midi_defaults`, `abc-midi-defaults-cli.php`
  - New: `TransposeMode`, `TransposeStrategy`, `InstrumentTransposeMapper`, `DrumMapper`
- **Related Requirements**: FR15-FR18, BR6 (CLI/WP interfaces)
- **Database Schema**: `sql/abc_midi_defaults_schema.sql` (needs transpose/octave columns)

---

## Configuration File Requirements
### Overview
All CLI options must have corresponding default settings in a configuration file to enable reusable processing configurations. Users should be able to save, load, and share processing configurations without repeating CLI arguments.

### Configuration File Support (REQUIRED IMPLEMENTATION)

#### Configuration File Format
- **Primary Format**: JSON (human-readable, version-controllable, widely supported)
- **Secondary Formats**: 
  - YAML (more human-friendly, supports comments)
  - INI (simple key-value, consistent with header_defaults.txt pattern)
  - PHP array (for backward compatibility with existing config/db_config.php pattern)
- **Format Detection**: Automatic based on file extension (.json, .yml/.yaml, .ini, .php)
- **File Location Options**:
  1. `config/abc_processor_config.json` (default global config - JSON)
  2. `config/abc_processor_config.yml` (alternative global config - YAML)
  3. `config/abc_processor_config.ini` (alternative global config - INI)
  4. `~/.abc_processor_config.json` (user-specific config)
  5. Project-specific: `./abc_config.json` or `./abc_config.yml` or `./abc_config.ini` (current directory)
  6. Custom path via `--config=path/to/config.{json|yml|ini|php}`
- **Precedence**: CLI options > custom config file > project config > user config > global config > hardcoded defaults
- **Recommendation**: Use JSON for programmatic generation/parsing, YAML for human editing, INI for simple cases

#### Configuration File Structure (JSON)
```json
{
  "processing": {
    "voice_output_style": "grouped",
    "interleave_bars": 1,
    "bars_per_line": 4,
    "join_bars_with_backslash": false,
    "tune_number_width": 5
  },
  "transpose": {
    "mode": "midi",
    "overrides": {
      "Bagpipes": 0,
      "Piano": 2
    }
  },
  "voice_ordering": {
    "mode": "source",
    "custom_order": ["Melody", "Bagpipes", "Guitar", "Piano", "Drums"]
  },
  "canntaireachd": {
    "convert": true,
    "generate_diff": true
  },
  "output": {
    "output_file": null,
    "error_file": "errors.log",
    "cannt_diff_file": "cannt_diff.txt"
  },
  "database": {
    "use_midi_defaults": true,
    "use_voice_order_defaults": true
  },
  "validation": {
    "timing_validation": true,
    "strict_mode": false
  }
}
```

#### Current State Analysis
**Existing** ✓:
- `AbcProcessorConfig` class with 5 properties:
  - `voiceOutputStyle`, `interleaveBars`, `barsPerLine`, `joinBarsWithBackslash`, `tuneNumberWidth`
- `CLIOptions` class parses CLI arguments (but doesn't load from config files)
- `config/db_config.php` for database settings only

**Missing** (REQUIRED):
- Config file loading (JSON/PHP)
- Merging CLI options with config file defaults
- Config precedence chain (CLI > custom > user > global)
- Save configuration from CLI/WordPress
- Voice ordering configuration
- Transpose mode configuration
- Canntaireachd processing configuration
- Validation settings configuration

### Enhanced AbcProcessorConfig Requirements

#### Expand AbcProcessorConfig Class
Add the following properties to `AbcProcessorConfig`:

```php
class AbcProcessorConfig {
    // Existing properties
    public $voiceOutputStyle = 'grouped';
    public $interleaveBars = 1;
    public $barsPerLine = 4;
    public $joinBarsWithBackslash = false;
    public $tuneNumberWidth = 5;
    
    // NEW: Voice ordering
    public $voiceOrderingMode = 'source'; // 'source'|'orchestral'|'custom'
    public $customVoiceOrder = []; // array of voice names/patterns
    
    // NEW: Transpose settings
    public $transposeMode = 'midi'; // 'midi'|'bagpipe'|'orchestral'
    public $transposeOverrides = []; // ['VoiceName' => transposeValue]
    
    // NEW: Canntaireachd processing
    public $convertCanntaireachd = false;
    public $generateCanntDiff = false;
    
    // NEW: Output file paths
    public $outputFile = null;
    public $errorFile = null;
    public $canntDiffFile = null;
    
    // NEW: Database usage
    public $useMidiDefaults = true;
    public $useVoiceOrderDefaults = true;
    
    // NEW: Validation settings
    public $timingValidation = true;
    public $strictMode = false;
    
    // NEW: Config file management
    public static function loadFromFile(string $path): self;
    public static function loadWithPrecedence(array $paths): self;
    public function saveToFile(string $path): bool;
    public function mergeFromArray(array $config): void;
    public function toArray(): array;
    public function toJSON(): string;
}
```

#### Configuration Loading Strategy

**Implementation Pattern**:
```php
// 1. Start with defaults
$config = new AbcProcessorConfig();

// 2. Load from config files (lowest to highest precedence)
$configPaths = [
    __DIR__ . '/../config/abc_processor_config.json',  // global
    $_SERVER['HOME'] . '/.abc_processor_config.json',   // user
    getcwd() . '/abc_config.json',                      // project
];
$config = AbcProcessorConfig::loadWithPrecedence($configPaths);

// 3. Apply custom config if specified
if ($cli->opts['config'] ?? false) {
    $customConfig = AbcProcessorConfig::loadFromFile($cli->opts['config']);
    $config->merge($customConfig);
}

// 4. Override with CLI options (highest precedence)
$config->applyFromCLI($cli);
```

### CLI Configuration Options

#### New CLI Options Required
- `--config=path/to/config.json` - Load configuration from file
- `--save-config=path/to/config.json` - Save current settings to config file
- `--show-config` - Display current configuration (for debugging)
- `--voice-order=source|orchestral|custom` - Voice ordering mode
- `--voice-order-config=file.json` - Custom voice ordering configuration
- `--transpose-mode=midi|bagpipe|orchestral` - Transpose mode
- `--transpose-override=Voice:N` - Per-voice transpose override (e.g., `--transpose-override=Piano:2`)
- `--no-midi-defaults` - Don't load MIDI defaults from database
- `--strict` - Enable strict validation mode

#### Mapping CLI Options to Config Properties

| CLI Option | Config Property | Config Section |
|------------|-----------------|----------------|
| `--voice_output_style=X` | `voiceOutputStyle` | `processing.voice_output_style` |
| `--interleave_bars=N` | `interleaveBars` | `processing.interleave_bars` |
| `--bars_per_line=N` | `barsPerLine` | `processing.bars_per_line` |
| `--join_bars_with_backslash` | `joinBarsWithBackslash` | `processing.join_bars_with_backslash` |
| `--width=N` | `tuneNumberWidth` | `processing.tune_number_width` |
| `--voice-order=X` | `voiceOrderingMode` | `voice_ordering.mode` |
| `--voice-order-config=F` | `customVoiceOrder` | `voice_ordering.custom_order` |
| `--transpose-mode=X` | `transposeMode` | `transpose.mode` |
| `--transpose-override=V:N` | `transposeOverrides[V]` | `transpose.overrides.V` |
| `--convert` | `convertCanntaireachd` | `canntaireachd.convert` |
| `--output=F` | `outputFile` | `output.output_file` |
| `--errorfile=F` | `errorFile` | `output.error_file` |
| `--canntdiff=F` | `canntDiffFile` | `output.cannt_diff_file` |
| `--no-midi-defaults` | `useMidiDefaults=false` | `database.use_midi_defaults` |
| `--strict` | `strictMode=true` | `validation.strict_mode` |

### WordPress Configuration Support

#### WordPress Settings Storage
- Store configuration as WordPress options (wp_options table)
- Key: `abc_processor_config` with serialized JSON value
- WordPress UI allows editing and saving configuration
- Export/import configuration as JSON file

#### WordPress Admin UI Requirements
1. **Processing Settings Tab**
   - Voice output style (grouped/interleaved)
   - Bars per line, interleave bars
   - Tune number width

2. **Voice Ordering Tab**
   - Mode selector (source/orchestral/custom)
   - Custom order editor (drag-and-drop)
   - Preview of current order

3. **Transpose Settings Tab**
   - Mode selector (MIDI/bagpipe/orchestral)
   - Per-instrument transpose overrides table
   - Visual diagram showing transpose relationships

4. **Output Settings Tab**
   - Default output file patterns
   - Error logging preferences
   - Canntaireachd diff generation

5. **Configuration Management**
   - Save current settings as named preset
   - Load preset
   - Export as JSON file
   - Import from JSON file
   - Reset to defaults

### Implementation Requirements

#### New Classes/Files
- **ConfigLoader** (new): Loads configuration from JSON/PHP files
- **ConfigMerger** (new): Merges configurations with precedence rules
- **ConfigValidator** (new): Validates configuration structure and values
- **config/abc_processor_config.json** (new): Default global configuration file
- **docs/configuration.md** (new): Configuration file documentation

#### Modified Classes
- **AbcProcessorConfig** (enhance): Add properties, file loading/saving methods
- **CLIOptions** (enhance): Add config file option, integration with AbcProcessorConfig
- All CLI scripts in `bin/` (enhance): Load config before CLI parsing

### Test Requirements
- **CR1**: Test loading configuration from JSON file
- **CR2**: Test loading configuration from PHP array file
- **CR3**: Test configuration precedence (CLI > custom > user > global)
- **CR4**: Test CLI options override config file settings
- **CR5**: Test saving configuration to JSON file
- **CR6**: Test invalid configuration file handling (malformed JSON, missing keys)
- **CR7**: Test configuration validation (invalid values, out-of-range)
- **CR8**: Test merging multiple configuration sources
- **CR9**: Test WordPress configuration save/load
- **CR10**: Test configuration export/import in WordPress
- **CR11**: Test all CLI options map correctly to config properties
- **CR12**: Test backward compatibility with existing AbcProcessorConfig usage

### Traceability
- **Related Classes**: 
  - Existing: `AbcProcessorConfig`, `CLIOptions`
  - New: `ConfigLoader`, `ConfigMerger`, `ConfigValidator`
- **Related Files**: 
  - Existing: `config/db_config.php`
  - New: `config/abc_processor_config.json`, `docs/configuration.md`
- **Related Requirements**: All CLI-based requirements (FR10, BR6, voice ordering, transpose modes)
- **Related Tests**: `AbcProcessorConfigTest` (needs expansion)

## CLI Interfaces
### ABC Processing CLI (abc-cannt-cli.php)
- **Options**:
  - `--convert`: Enable canntaireachd generation for ABC files.
  - `--output=<filename>`: Specify output file for processed ABC.
  - Supports multiple ABC files via wildcards for batch processing.
- **Functionality**: Processes ABC files through multi-pass pipeline, generates canntaireachd, validates timing, and outputs results.
- **Output**: Lists processed files, diff logs, and error logs.

### Database Management CLI
- **Options**: --midi_channel, --midi_program, --list, --add, --edit, --delete, --validate, --save.
- **Functionality**: Manages MIDI defaults, token dictionaries, and header fields in the database.

## Use Cases
- **UC1**: User uploads ABC file via WordPress; system processes and generates canntaireachd.
- **UC2**: CLI user runs command to convert ABC file with canntaireachd generation.
- **UC3**: System detects missing Bagpipes voice and copies melody.
- **UC4**: System validates timing and logs errors for manual correction.
- **UC5**: User queries translations between ABC, canntaireachd, and BMW.

## Test Plan
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

## QA (Quality Assurance)
- **Code Review Checklist**:
  - Ensure all classes follow SRP (Single Responsibility Principle).
  - Verify SOLID principles: S (SRP), O (Open/Closed), L (Liskov Substitution), I (Interface Segregation), D (Dependency Inversion).
  - Check for DRY (Don't Repeat Yourself) violations.
  - Confirm Dependency Injection (DI) is used where appropriate.
  - Validate PHPDoc blocks are present and complete for all classes, methods, and functions.
  - Ensure UML diagrams are included in PHPDoc blocks.
  - Check that unit tests exist for all classes and methods.
  - Verify traceability to requirements in code comments and PHPDoc.
- **Automated QA**:
  - Run PHPUnit tests with coverage reporting.
  - Use static analysis tools (e.g., PHPStan) for code quality.
  - Generate documentation with phpDocumentor and verify completeness.
- **Manual QA**:
  - Test CLI and WordPress interfaces for correct output.
  - Validate canntaireachd generation against known examples.
  - Check error logging and exception handling.

## Traceability Matrix
- Map requirements to classes/functions (e.g., BR1 -> AbcCanntaireachdPass::process).
- Ensure all requirements are covered by code and tests.

## Project Direction from LLM.md
- Follow guidelines in LLM.md for PHPDoc, UML, unit tests, SRP/SOLID/DRY/DI.
- Maintain architectural documents and update with code changes.
- Use automated documentation generation scripts.
- Ensure all code references requirements and is traceable.
