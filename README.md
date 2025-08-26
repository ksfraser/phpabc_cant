# PHP ABC Canntaireachd

A PHP library for working with ABC notation and canntaireachd (bagpipe vocal music notation).

## Installation

```bash
composer install
```

## PSR-4 Structure

This package follows PSR-4 autoloading standards with the namespace `Ksfraser\PhpabcCanntaireachd`.

### Directory Structure
```
src/
  Ksfraser/
    PhpabcCanntaireachd/
      AbcParser.php         - Main ABC file parser
      AbcTuneBase.php       - Base class for ABC tune components  
      AbcNote.php           - ABC note representation
      Defines.php           - Package constants
      ... (other classes)
```

### Dependencies

- **ksfraser/ksf-file**: File handling utilities (from GitHub repository)

## Usage

```php
<?php

require_once 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcNote;
use Ksfraser\PhpabcCanntaireachd\AbcParser;
use ksfraser\origin\KsfFile;

// Create an ABC note
$note = new AbcNote('G', '', '', '1');
echo $note->get_body_out(); // Outputs: G1

// Use file utilities
$file = new KsfFile("tune.abc", __DIR__);
```

## Migration from Previous Structure

The package has been updated to be PSR-4 compliant:

- **Old**: `class.abcparser.php` with `require_once` statements
- **New**: `AbcParser.php` in proper namespace with autoloading

- **Old**: `require_once 'class.abc_note.php'`  
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

## Available Classes

- `AbcParser` - Parse ABC notation files
- `AbcNote` - Represent individual musical notes  
- `AbcTuneBase` - Base class for tune components
- `AbcToken`, `AbcComment`, `AbcChord` - Parser tokens
- And more...

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

## Development

To regenerate autoloader after adding new classes:
```bash
composer dump-autoload
```
