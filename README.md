# PHP ABC Canntaireachd

A PHP library for working with ABC notation and canntaireachd (bagpipe vocal music notation).

## Features

### Core Processing
- **ABC Parsing**: Parse ABC notation files with support for headers, voices, notes, barlines, and lyrics
- **Canntaireachd Generation**: Automatically generate vocal instructions for bagpipe music from ABC notes
- **Voice Management**: Handle multi-voice ABC files with automatic bagpipe voice creation and copying
- **Header Field Processing**: Support for all ABC header fields (A-Z) with validation and matching
- **Multi-pass Processing**: Comprehensive validation pipeline with timing checks, voice reordering, and canntaireachd validation

### Voice Ordering (Phase 4A)
- **Source Order Mode**: Preserve original voice order from ABC file
- **Orchestral Order Mode**: Reorder voices by standard orchestral sections (woodwinds → brass → percussion → strings)
- **Custom Order Mode**: Define your own voice ordering rules
- **Database-driven**: Configurable voice order defaults stored in `abc_voice_order_defaults` table
- **WordPress Admin UI**: Manage voice ordering settings through admin interface

### Transpose Modes (Phase 4B) 🎵
- **MIDI Mode**: All instruments at concert pitch (0 semitones) for MIDI/audio imports
- **Bagpipe Mode**: Bagpipes at written pitch, all other instruments transposed +2 semitones
- **Orchestral Mode**: Instrument-specific transposition (Bb=+2, Eb=+9, F=+7, concert pitch=0)
- **80+ Instruments**: Comprehensive instrument mapping including orchestral families
- **Per-Voice Overrides**: Fine-grained control with voice-specific transpose settings
- **Strategy Pattern**: Clean, extensible architecture for transpose calculations
- **Database Integration**: Transpose values stored in `abc_voice_names` table
- **WordPress Admin UI**: Configure transpose modes and per-voice overrides

### Configuration System (Phase 4C)
- **JSON/YAML Support**: Save and load processing configurations from files
- **CLI Override Precedence**: Command-line options override config file settings
- **Shareable Configurations**: Export settings for team workflows
- **All CLI Scripts**: Configuration file support across all processing tools

### Database & WordPress Integration
- **Token Dictionary**: Unified ABC/canntaireachd/BMW token mappings with CRUD admin UI
- **Header Field Defaults**: Database-backed default values with migration support
- **Voice Name Management**: Instrument names, transpose values, and abbreviations
- **Voice Order Defaults**: Configurable orchestral ordering rules
- **Migration System**: Automated database schema updates with rollback support

### CLI Tools
- **Comprehensive CLI Suite**: 12+ command-line utilities for batch processing
- **Consistent Interface**: All scripts support config files, help documentation, and proper error handling
- **Processing Passes**: Dedicated CLIs for voice ordering, transpose, timing validation, and more
- **Validation Tools**: Tune number validation, timing checks, header field verification

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

## Advanced Features

### Voice Ordering

Organize multi-voice ABC files with intelligent voice ordering:

```bash
# Use orchestral ordering (woodwinds, brass, percussion, strings)
php bin/abc-voice-order-pass-cli.php --mode=orchestral input.abc output.abc

# Use custom order
php bin/abc-voice-order-pass-cli.php --mode=custom --custom-order="Melody,Bagpipes,Drums" input.abc output.abc

# Preserve source order
php bin/abc-voice-order-pass-cli.php --mode=source input.abc output.abc
```

**WordPress Admin**: Configure voice ordering in *Settings → Voice Order Settings*

### Transpose Modes

Transpose instruments automatically for different contexts:

```bash
# MIDI mode (all instruments at concert pitch)
php bin/abc-cannt-cli.php --transpose-mode=midi input.abc output.abc

# Bagpipe mode (bagpipes=0, others=+2)
php bin/abc-cannt-cli.php --transpose-mode=bagpipe input.abc output.abc

# Orchestral mode (Bb=+2, Eb=+9, F=+7, concert=0)
php bin/abc-cannt-cli.php --transpose-mode=orchestral input.abc output.abc

# Per-voice override
php bin/abc-cannt-cli.php --transpose-mode=orchestral --transpose-override="Clarinet:0" input.abc output.abc
```

**Supported Instruments**: 80+ including trumpet, clarinet, flute, horn, saxophone, tuba, and more  
**WordPress Admin**: Configure transpose settings in *Settings → Transpose Settings*

### Configuration Files

Save processing settings to reusable configuration files:

```bash
# Save configuration
php bin/abc-cannt-cli.php --save-config=myconfig.json --transpose-mode=orchestral --voice-order=orchestral

# Load configuration (CLI options override file settings)
php bin/abc-cannt-cli.php --config=myconfig.json input.abc output.abc
```

**Formats**: JSON, YAML  
**Precedence**: CLI options > config file > defaults

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
