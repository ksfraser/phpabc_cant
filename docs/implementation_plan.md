Project implementation plan and summary

Overview
- Goal: parse one or more ABC files (each file may contain multiple tunes), tokenize tunes into notes/barlines/voices/lyrics/canntaireachd (BMW), fix formatting, reorder voices according to DB order, ensure bagpipe voice has canntaireachd (generate from melody when missing), and output validated ABC.

## Completed Work

### 1) ParseContext Class ✅
- Introduced ParseContext class to hold parsing state (current voice, current bar, voice bars reference)
- Implements ArrayAccess for compatibility with existing handlers
- Provides helpers: getOrCreateVoice(), incrementBar(), etc.

### 2) AbcTune::parseBodyLines Update ✅
- Updated to use ParseContext for centralized state management
- Ensures default melody voice is created when notes appear prior to any V: header
- Eliminates null-offset errors and centralizes parsing state

### 3) Bagpipe Voice Management ✅
- Implemented via AbcCanntaireachdPass in the multi-pass processing pipeline
- Detects bagpipe tunes based on key signatures (D major, A major, etc.)
- Automatically creates "Bagpipes" voice if missing from multi-voice tunes
- Copies melody content from primary voice to Bagpipes voice

### 4) Voice Header Management ✅
- Implemented AbcTune::getLines() to return header/voice header objects for modification
- Fixed fixVoiceHeaders functionality operating over returned lines
- Proper voice header preservation and reordering

### 5) Canntaireachd Generation ✅
- Implemented AbcCanntaireachdPass for automatic canntaireachd generation
- Tokenizes note sequences and maps ABC tokens to canntaireachd using dictionary (longest-match-first)
- Converts ABC notes to canntaireachd syllables and adds as w: lines
- Provides validation and diff logging for canntaireachd changes

### 6) Comprehensive Testing ✅
- Unit tests for ParseContext, AbcCanntaireachdPass, and all new functionality
- Integration tests for parsing tunes with/without voices, multi-voice processing
- End-to-end CLI tests on test files including test-multi.abc
- Test coverage for bagpipe detection, voice creation, token conversion, and error handling

## Current Architecture

### Multi-Pass Processing Pipeline (AbcProcessor)
1. **Voice Detection Pass**: Identifies and validates voice structure
2. **Canntaireachd Generation Pass**: Generates canntaireachd from ABC notes for bagpipe tunes
3. **Voice Reordering Pass**: Reorders voices by channel with drums last
4. **Timing Validation Pass**: Validates bar timing and marks errors
5. **Output Generation**: Produces validated ABC, canntaireachd diff, and error logs

### Key Classes
- **AbcCanntaireachdPass**: Handles automatic canntaireachd generation and voice management
- **ParseContext**: Centralizes parsing state across handlers
- **TokenDictionary**: Manages ABC to canntaireachd token mappings
- **AbcProcessor**: Orchestrates multi-pass processing pipeline
- **AbcProcessorConfig**: Configurable processing options

### Features Implemented
- ✅ Automatic bagpipe tune detection
- ✅ Bagpipes voice creation and melody copying
- ✅ ABC to canntaireachd token conversion
- ✅ w: line integration with proper timing alignment
- ✅ Multi-tune file processing
- ✅ Comprehensive error handling and logging
- ✅ CLI and WordPress plugin support
- ✅ Full unit test coverage

## Design Principles Maintained
- Small, single-responsibility handlers (barline, lyrics, canntaireachd, notes)
- Dictionary-driven tokenization for ABC→canntaireachd mapping
- Configurable fixes (auto-apply vs report-only)
- Comprehensive logging of all automated changes
- SOLID principles with dedicated pass classes

## Remaining Work

### Voice Ordering Implementation (Phase 4A)
**Status**: Stub implementation exists (preserves source order only)
**Priority**: High - Required feature per REQUIREMENTS.md

**Tasks**:
1. **Design Voice Ordering Architecture** (2h)
   - Create VoiceOrderingStrategy interface
   - Design InstrumentFamily classification system
   - Plan InstrumentMapper for voice name → family mapping
   - Design VoiceOrderConfig for configuration management

2. **Implement Source Order Strategy** (1h)
   - Extract existing stub logic into SourceOrderStrategy class
   - Add tests for source order preservation

3. **Implement Orchestral Order Strategy** (4h)
   - Define orchestral instrument families and ordering rules
   - Create InstrumentMapper with voice name patterns
   - Implement OrchestralOrderStrategy with family-based ordering
   - Handle edge cases: unrecognized instruments, partial orchestras
   - Add comprehensive tests (standard orchestra, bagpipe ensemble, mixed groups)

4. **Implement Custom Order Strategy** (3h)
   - Design JSON configuration format for custom ordering
   - Implement CustomOrderStrategy with user-defined rules
   - Add configuration validation
   - Support pattern matching for voice names
   - Add tests for various custom configurations

5. **Update AbcVoiceOrderPass** (2h)
   - Replace stub with strategy pattern implementation
   - Integrate with AbcProcessorConfig for mode selection
   - Update AbcProcessor::reorderVoices() to use strategies
   - Maintain backward compatibility

6. **CLI and Configuration Integration** (2h)
   - Add --voice-order and --voice-order-config CLI options
   - Implement configuration file loading (JSON)
   - Add validation and error reporting
   - Update CLI help documentation

7. **WordPress UI Integration** (3h)
   - Add voice ordering mode selector (radio buttons)
   - Implement custom ordering editor interface
   - Add voice order preview before processing
   - Save/load custom ordering configurations

8. **Testing and Documentation** (3h)
   - Implement all test cases (VR1-VR10) from requirements
   - Add integration tests for all three modes
   - Update UML diagrams
   - Update README and user documentation

**Total Estimated Time**: 20 hours
**Dependencies**: None (can start immediately)

---

### Transpose Mode Implementation (Phase 4B)
**Status**: Infrastructure exists (VoiceFactory has transpose/octave), needs mode strategies
**Priority**: High - Required feature per REQUIREMENTS.md FR15-FR18

**Tasks**:
1. **Database Schema Enhancement** (1h)
   - Add `transpose` and `octave` columns to `abc_midi_defaults` table
   - Update default data with standard transpose values per instrument
   - Fix percussion instruments to use MIDI channel 10 (Snare, Bass drum, Tenor drum)
   - Test schema migration and data integrity

2. **Design Transpose Architecture** (2h)
   - Create TransposeMode enum (MIDI, Bagpipe, Orchestral)
   - Design TransposeStrategy interface
   - Plan InstrumentTransposeMapper with instrument family → transpose mapping
   - Design DrumMapper for percussion MIDI note mapping

3. **Implement Transpose Strategies** (4h)
   - Implement MidiTransposeStrategy (transpose=0 for all)
   - Implement BagpipeTransposeStrategy (bagpipes=0, concert pitch=2)
   - Implement OrchestralTransposeStrategy (per-instrument standard transpositions)
   - Add tests for each strategy with various instrument combinations

4. **Instrument Transpose Mapping** (3h)
   - Create InstrumentTransposeMapper with orchestral transpose rules
   - Map instrument families to transpose values:
     - Concert pitch: 0 (Piano, Flute, Strings, etc.)
     - Bb instruments: 2 (Trumpet, Clarinet, Tenor Sax)
     - Eb instruments: 9 (Alto Sax, Bari Sax)
     - F instruments: 7 (French Horn, English Horn)
   - Support instrument name variations and aliases
   - Add comprehensive tests (all instrument families)

5. **Percussion/Drum Mapping** (2h)
   - Create DrumMapper class for MIDI channel 10 note mapping
   - Map drum types to MIDI note numbers (Bass=35-36, Snare=38/40, etc.)
   - Implement percussion voice detection (name, clef=perc, channel 10)
   - Auto-configure percussion voices (channel 10, transpose=0)
   - Add tests for drum detection and mapping

6. **Update Voice Creation** (3h)
   - Enhance InstrumentVoiceFactory with transpose mode support
   - Load default transpose/octave from abc_midi_defaults table
   - Apply transpose strategy based on mode configuration
   - Allow per-voice transpose override
   - Maintain backward compatibility with existing code

7. **CLI and Configuration Integration** (2h)
   - Add --transpose-mode CLI option (midi|bagpipe|orchestral)
   - Add transpose mode to AbcProcessorConfig
   - Update abc-midi-defaults-cli.php to manage transpose/octave
   - Add validation and error reporting
   - Update CLI help documentation

8. **WordPress UI Integration** (2h)
   - Add transpose mode selector (radio buttons: MIDI/Bagpipe/Orchestral)
   - Display per-instrument transpose values in voice editor
   - Save/load transpose mode with tune processing settings
   - Add tooltip explanations for each mode

9. **Testing and Documentation** (3h)
   - Implement all test cases (TR1-TR10) from requirements
   - Test MIDI import, bagpipe ensemble, orchestral score scenarios
   - Test percussion mapping and channel 10 assignment
   - Update UML diagrams
   - Update README and user documentation

**Total Estimated Time**: 22 hours
**Dependencies**: Database schema update should happen first

---

### Configuration File Implementation (Phase 4C)
**Status**: Partial - AbcProcessorConfig exists with 5 properties, CLIOptions exists but no config file loading
**Priority**: High - Required for all CLI features (FR19-FR22)

**Tasks**:
1. **Design Configuration Architecture** (2h)
   - Design ConfigLoader for JSON/PHP file loading
   - Design ConfigMerger for precedence-based merging
   - Design ConfigValidator for validation
   - Define configuration file structure (JSON schema)

2. **Expand AbcProcessorConfig Class** (3h)
   - Add voice ordering properties (mode, customVoiceOrder)
   - Add transpose properties (mode, overrides)
   - Add canntaireachd properties (convert, generateDiff)
   - Add output file properties (outputFile, errorFile, canntDiffFile)
   - Add database usage flags (useMidiDefaults, useVoiceOrderDefaults)
   - Add validation properties (timingValidation, strictMode)
   - Maintain backward compatibility with existing code

3. **Implement Configuration Loading** (4h)
   - Implement ConfigLoader::loadFromFile() for JSON
   - Implement ConfigLoader::loadFromFile() for PHP arrays
   - Implement AbcProcessorConfig::loadWithPrecedence() for multiple sources
   - Handle file not found gracefully (use defaults)
   - Implement error handling for malformed files
   - Add comprehensive tests for all loading scenarios

4. **Implement Configuration Merging** (3h)
   - Implement ConfigMerger with precedence rules
   - Implement AbcProcessorConfig::merge() method
   - Implement AbcProcessorConfig::applyFromCLI() method
   - Ensure CLI options override all config file settings
   - Test precedence chain: CLI > custom > user > global > defaults

5. **Implement Configuration Saving** (2h)
   - Implement AbcProcessorConfig::saveToFile() for JSON output
   - Implement AbcProcessorConfig::toArray() and toJSON() methods
   - Add --save-config CLI option to all scripts
   - Add --show-config CLI option for debugging
   - Test configuration round-trip (save/load)

6. **Configuration Validation** (2h)
   - Implement ConfigValidator with JSON schema validation
   - Validate value ranges (interleaveBars > 0, tuneNumberWidth 1-10, etc.)
   - Validate enum values (voiceOutputStyle, transposeMode, voiceOrderingMode)
   - Validate file paths (exist, writable)
   - Provide helpful error messages for invalid configurations

7. **Update CLI Integration** (3h)
   - Add config file loading to all bin/*.php CLI scripts
   - Add --config, --save-config, --show-config options
   - Update CLIOptions to integrate with AbcProcessorConfig
   - Map all CLI options to config properties (per requirements table)
   - Ensure backward compatibility with existing scripts

8. **Create Default Configuration Files** (2h)
   - Create config/abc_processor_config.json with sensible defaults
   - Document all configuration options in comments/README
   - Create example configurations for common use cases:
     - `config/examples/bagpipe_ensemble.json`
     - `config/examples/orchestral_score.json`
     - `config/examples/midi_import.json`
   - Add .gitignore for user configs (~/.abc_processor_config.json)

9. **WordPress Configuration UI** (4h)
   - Implement wp_options storage for configuration
   - Create admin UI tabs (Processing, Voice Order, Transpose, Output)
   - Implement configuration presets (save/load/delete)
   - Implement export configuration as JSON (download)
   - Implement import configuration from JSON (upload)
   - Add "Reset to Defaults" button

10. **Testing and Documentation** (3h)
    - Implement all test cases (CR1-CR12) from requirements
    - Test all precedence scenarios
    - Test invalid configuration handling
    - Test WordPress save/load/export/import
    - Create docs/configuration.md with full documentation
    - Update README with configuration examples

**Total Estimated Time**: 28 hours
**Dependencies**: Should be implemented early as it affects all other CLI features

---

## Current Status
Phases 1-3 complete. The system now successfully:
- Parses ABC files with multiple tunes and voices
- Automatically generates canntaireachd for bagpipe tunes
- Validates timing and structure
- Produces validated output with diff logging
- Supports both CLI and WordPress integration
- Preserves source voice order (orchestral/custom ordering pending)

Next actions: Implement voice ordering strategies (Phase 4) per updated requirements.
