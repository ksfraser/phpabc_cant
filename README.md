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

## Available Classes

- `AbcParser` - Parse ABC notation files
- `AbcNote` - Represent individual musical notes  
- `AbcTuneBase` - Base class for tune components
- `AbcToken`, `AbcComment`, `AbcChord` - Parser tokens
- And more...

## Development

To regenerate autoloader after adding new classes:
```bash
composer dump-autoload
```
