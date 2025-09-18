# Comprehensive Test Plan: PHPABC Canntaireachd

## Overview
This test plan covers all aspects of the PHPABC Canntaireachd system, including ABC parsing, canntaireachd generation, multi-pass processing, CLI tools, WordPress integration, and database operations. The plan ensures 100% unit test coverage and comprehensive integration testing.

## Test Strategy

### Unit Testing (PHPUnit)
- **Target Coverage**: 100% class instantiation, 80%+ method coverage for core logic
- **Framework**: PHPUnit with code coverage reporting
- **Location**: `tests/` directory with class-specific test files

### Integration Testing
- **End-to-End**: Complete ABC file processing pipelines
- **Component Integration**: Multi-pass processor interactions
- **External Dependencies**: Database operations, file I/O

### System Testing
- **CLI Tools**: Command-line interface validation
- **WordPress Plugin**: Admin interface and file processing
- **Performance**: Large file processing and memory usage

## Test Categories

### 1. Core Parsing and Tokenization

#### AbcParser Tests
- **Instantiation**: Verify parser creates without errors
- **Basic Parsing**: Parse simple ABC tunes with headers and notes
- **Multi-Tune Files**: Handle files with multiple X: headers
- **Voice Parsing**: Correctly identify and separate V: voices
- **Error Recovery**: Continue parsing after syntax errors
- **Edge Cases**: Empty files, malformed headers, special characters

#### TokenDictionary Tests
- **Dictionary Loading**: Load and validate token mappings from abc_dict.php
- **ABC to Canntaireachd**: Verify accurate token conversions
- **BMW Support**: Handle BMW token mappings
- **Missing Tokens**: Graceful handling of unmappable tokens
- **Case Sensitivity**: Proper handling of token case variations

### 2. Canntaireachd Generation

#### AbcCanntaireachdPass Tests
- **Bagpipe Detection**: Identify bagpipe tunes by key signatures (D, A, etc.)
- **Voice Creation**: Add "Bagpipes" voice to multi-voice tunes when missing
- **Melody Copying**: Correctly copy notes from primary voice to Bagpipes
- **Token Conversion**: Convert ABC notes to canntaireachd syllables
- **W: Line Integration**: Insert canntaireachd as properly aligned lyrics
- **Existing Preservation**: Handle tunes with pre-existing canntaireachd

#### Edge Cases
- **Single Voice Tunes**: Process tunes without explicit voices
- **Non-Bagpipe Tunes**: Skip canntaireachd generation for non-bagpipe keys
- **Complex Rhythms**: Handle embellishments, grace notes, and tuplets
- **Multi-Part Tunes**: Process tunes with repeats and variations
- **Error Handling**: Continue processing when token conversion fails

### 3. Multi-Pass Processing Pipeline

#### AbcProcessor Tests
- **Pipeline Execution**: Verify all passes execute in correct order
- **Pass Interactions**: Ensure passes don't interfere with each other
- **Configuration**: Test configurable options (voice ordering, canntaireachd generation)
- **Error Propagation**: Handle and log errors from individual passes

#### Individual Pass Tests
- **Voice Detection Pass**: Identify and validate voice structure
- **Voice Reordering Pass**: Sort voices by channel with drums last
- **Timing Validation Pass**: Detect and mark timing errors
- **Output Generation**: Produce correct ABC, diff, and error files

### 4. Voice and Header Management

#### Voice Processing Tests
- **Voice Header Preservation**: Maintain all V: lines in output
- **Voice Reordering**: Correctly reorder voices according to configuration
- **Voice Creation**: Add missing voices during processing
- **Voice Validation**: Check voice structure and content

#### Header Field Tests
- **Header Parsing**: Extract and validate all ABC headers
- **Header Preservation**: Maintain headers in processed output
- **Header Validation**: Check required headers (X:, T:, K:)
- **Multi-Tune Headers**: Handle headers across multiple tunes

### 5. Database Operations

#### DbManager Tests
- **Connection**: Establish database connections via config
- **Query Execution**: Test all helper methods (fetchAll, fetchOne, etc.)
- **Error Handling**: Handle connection failures and query errors
- **Configuration**: Load config from files and environment

#### Schema Tests
- **Table Creation**: Verify schema creation from SQL files
- **Data Population**: Prepopulate token and header tables
- **Migration**: Handle schema updates and data migration
- **Constraints**: Validate foreign keys and data integrity

### 6. CLI Tool Testing

#### Command Execution Tests
- **File Processing**: Process single and multiple ABC files
- **Output Generation**: Verify all output files are created
- **Option Handling**: Test all CLI options and flags
- **Error Reporting**: Check error output and logging
- **Wildcard Support**: Handle file glob patterns

#### Specific CLI Tests
- **abc-canntaireachd-cli.php**: Canntaireachd generation and validation
- **abc-renumber-tunes-cli.php**: Tune number renumbering
- **abc-voice-pass-cli.php**: Voice processing and reordering
- **Database CLIs**: Token and header field management

### 7. WordPress Integration

#### Plugin Tests
- **File Upload**: Handle ABC file uploads
- **Processing Integration**: Use AbcProcessor in WordPress context
- **Admin Interface**: Test admin screens for configuration
- **Output Display**: Show processing results and download links
- **Error Handling**: WordPress-specific error reporting

#### Admin Interface Tests
- **Token Management**: CRUD operations for token dictionary
- **Header Fields**: Manage header field values and matching
- **MIDI Defaults**: Configure MIDI settings
- **File Processing**: Process uploaded files and show results

### 8. File I/O and Output

#### Output Writer Tests
- **File Creation**: Generate output files in correct locations
- **Content Accuracy**: Verify processed ABC content
- **Diff Generation**: Create accurate canntaireachd diff files
- **Error Logging**: Log processing errors and warnings
- **Encoding**: Handle UTF-8 and special characters

### 9. Performance and Scalability

#### Large File Tests
- **Memory Usage**: Monitor memory consumption during processing
- **Processing Time**: Measure performance with large ABC files
- **Multi-Tune Handling**: Process files with many tunes efficiently
- **Concurrent Processing**: Handle multiple simultaneous requests

### 10. Error Handling and Recovery

#### Error Condition Tests
- **Malformed ABC**: Handle syntax errors gracefully
- **Missing Files**: Report missing input files
- **Permission Errors**: Handle file permission issues
- **Database Errors**: Continue processing when DB operations fail
- **Token Gaps**: Handle missing token mappings

## Test Execution

### Automated Test Suite
```bash
# Run all unit tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage

# Run specific test suites
vendor/bin/phpunit --testsuite UnitTests
vendor/bin/phpunit --testsuite IntegrationTests
```

### Manual Testing Checklist
- [ ] CLI tool execution with sample files
- [ ] WordPress plugin installation and configuration
- [ ] Database schema creation and population
- [ ] Large file processing performance
- [ ] Error condition handling

## Coverage Targets

### Unit Test Coverage
- **Core Classes**: 100% instantiation coverage
- **Business Logic**: 80%+ method coverage
- **Error Paths**: All error conditions tested
- **Edge Cases**: Boundary conditions and unusual inputs

### Integration Coverage
- **End-to-End Flows**: All major use cases
- **Component Interactions**: Pass pipeline interactions
- **External Systems**: Database and file system operations

### System Coverage
- **CLI Tools**: All command-line functionality
- **WordPress Plugin**: All admin and processing features
- **Configuration**: All configuration options

## Test Data

### Sample ABC Files
- `test-multi.abc`: Multi-tune, multi-voice file
- `test_new_midi_parsers.php`: Test file for MIDI processing
- `test-midi-voices.abc`: Voice-specific test cases
- Bagpipe-specific test files for canntaireachd generation

### Expected Outputs
- Validated ABC files with correct formatting
- Canntaireachd diff files showing changes
- Error logs with appropriate warnings
- Database state after processing

## Maintenance

### Test Updates
- Update tests when code changes
- Add tests for new features
- Review and update test data regularly
- Maintain test documentation

### Coverage Monitoring
- Regular coverage report generation
- Identify and address coverage gaps
- Set coverage thresholds in CI/CD
- Monitor performance regression

## Success Criteria

### Code Quality
- ✅ All classes have unit tests
- ✅ 80%+ code coverage achieved
- ✅ No critical bugs in production
- ✅ Comprehensive error handling

### Feature Completeness
- ✅ All requirements implemented
- ✅ All edge cases handled
- ✅ Backward compatibility maintained
- ✅ Performance requirements met

### Documentation
- ✅ Test plan kept current
- ✅ Test cases documented
- ✅ Coverage reports generated
- ✅ CI/CD integration maintained
