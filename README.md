# PHP ABC Canntaireachd

A PHP library for working with ABC notation and canntaireachd (bagpipe vocal music notation).

## Features

- **ABC Parsing**: Parse ABC notation files with support for headers, voices, notes, barlines, and lyrics
- **Canntaireachd Generation**: Automatically generate vocal instructions for bagpipe music from ABC notes
- **Voice Management**: Handle multi-voice ABC files with automatic bagpipe voice creation and reordering
- **Header Field Processing**: Support for all ABC header fields (A-Z) with validation and matching
- **Multi-pass Processing**: Comprehensive validation pipeline with timing checks, voice reordering, and canntaireachd validation
- **Database Integration**: Token dictionary and header field management with WordPress admin UI
- **CLI Tools**: Command-line utilities for batch processing and validation

## Installation

```bash
composer install
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcProcessor;

// Process an ABC file with canntaireachd generation
$abcContent = file_get_contents('tune.abc');
$result = AbcProcessor::process($abcContent, ['cannt' => 1]);

// Output processed ABC with generated canntaireachd
echo implode("\n", $result['lines']);

// Check for canntaireachd differences
if (!empty($result['canntDiff'])) {
    echo "\nCanntaireachd changes:\n";
    foreach ($result['canntDiff'] as $diff) {
        echo "- $diff\n";
    }
}
```

## Canntaireachd Generation

When processing ABC files, the system automatically:

1. **Detects Voices**: Identifies melody and bagpipe voices in the ABC file
2. **Creates Bagpipe Voice**: If a melody voice exists but no bagpipe voice, automatically creates one
3. **Copies Content**: Copies ABC notes and lyrics from melody to bagpipe voice
4. **Generates Canntaireachd**: Converts ABC notes to vocal instructions using the token dictionary
5. **Adds Lyrics Lines**: Inserts `w:` lines with canntaireachd text below bagpipe music

### Example Input:
```
X:1
T:Test Tune
V:Melody
|A B C D|
```

### Example Output:
```
X:1
T:Test Tune
V:Bagpipes name="Bagpipes" sname="Bagpipes"
%canntaireachd: <add your canntaireachd here>
w: en o [C] [D]
|A B C D|
V:Melody
|A B C D|
```

## Processing Pipeline

The ABC processor uses a multi-pass architecture for comprehensive validation and transformation:

1. **Tune Number Validation**: Validates X: headers and renumbers duplicates
2. **Voice Detection**: Identifies melody and bagpipe voices
3. **Voice Copying**: Creates bagpipe voices from melody when missing
4. **Lyrics Processing**: Handles w:/W: lines and dictionary-based filtering
5. **Canntaireachd Generation**: Generates vocal instructions for bagpipe voices
6. **Voice Reordering**: Orders voices by MIDI channel (Bagpipes first, drums last)
7. **Timing Validation**: Checks bar timing and marks errors

## PSR-4 Structure

This package follows PSR-4 autoloading standards with the namespace `Ksfraser\PhpabcCanntaireachd`.

### Directory Structure
```
src/
  Ksfraser/
    PhpabcCanntaireachd/
      AbcProcessor.php           - Main processing engine with multi-pass pipeline
      AbcCanntaireachdPass.php   - Canntaireachd generation and validation
      AbcVoicePass.php           - Voice detection and copying
      AbcLyricsPass.php          - Lyrics processing and filtering
      AbcVoiceOrderPass.php      - Voice reordering by MIDI channel
      AbcTimingValidator.php     - Bar timing validation
      CanntGenerator.php         - ABC to canntaireachd conversion
      TokenDictionary.php        - Token mapping management
      AbcParser.php              - Main ABC file parser
      AbcTuneBase.php            - Base class for ABC tune components
      AbcNote.php                - ABC note representation
      Defines.php                - Package constants
      Header/                    - Header field classes
      ... (other classes)
```

### Dependencies

- **ksfraser/ksf-file**: File handling utilities (from GitHub repository)
- **symfony/console**: CLI command framework
- **symfony/secrets**: Configuration management

## Migration from Previous Structure

The package has been updated to be PSR-4 compliant:

- **Old**: `class.abcparser.php` with `require_once` statements
- **New**: `AbcParser.php` in proper namespace with autoloading

- **Old**: `require_once 'class.abc_note.php`
- **New**: `use Ksfraser\PhpabcCanntaireachd\AbcNote;`

## Token Dictionary Table and Admin UI

### Token Table
- Table: `abc_dict_tokens`
- Columns: `id`, `abc_token`, `cannt_token`, `bmw_token`, `description`
- Prepopulated from `abc_dict.php` for ABC/canntaireachd/BMW mappings
- Unified lookup for ABC, canntaireachd, and BMW tokens

### Admin CRUD
- WordPress admin screen for managing token dictionary
- Add, edit, delete tokens
- If a BMW token is added and the ABC token exists, the BMW token is updated in that row
- If a new ABC token is added, all values are inserted

### Usage in Conversion
- All ABC/canntaireachd/BMW conversions use the unified token table

## Header Field Architecture

All ABC header fields (A–Z) are represented by dedicated classes in `src/Ksfraser/PhpabcCanntaireachd/Header/`. Each class inherits from a common superclass, ensuring consistent set/get/render methods. Multi-value fields (e.g., C:, B:) use a dedicated subclass for proper handling.

- Single-value: Inherit from `AbcHeaderField`
- Multi-value: Inherit from `AbcHeaderMultiField`
- Field label is a static property for DRY rendering

### Header Field Coverage

- All ABC header fields (A–Z) are supported
- Multi-value fields: C, B
- Single-value fields: All others

### Parser Configuration

- `AbcFileParser` supports a config variable `singleHeaderPolicy` (`first` or `last`) to control which instance of a single-value header is kept when duplicates are encountered.

## Testing

Unit tests cover:
- Parsing ABC files with all header fields
- Multi-value and single-value header handling
- Configurable singleHeaderPolicy (first/last)
- Rendering tunes with missing/empty headers
- PSR-4 compliance and autoloading
- Voice detection and copying
- Canntaireachd generation and validation
- Multi-pass processing pipeline
- Timing validation and error reporting

## Development

To regenerate autoloader after adding new classes:
```bash
composer dump-autoload
```

To run tests:
```bash
vendor/bin/phpunit
```

To generate coverage report:
```bash
vendor/bin/phpunit --coverage-html coverage
```
