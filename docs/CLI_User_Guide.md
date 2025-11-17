# CLI User Guide

**ABC Canntaireachd Converter - Command-Line Interface**  
**Version**: 2.0  
**Updated**: 2025-11-17

---

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Quick Start](#quick-start)
4. [Main Converter](#main-converter)
5. [Processing Passes](#processing-passes)
6. [Validation Tools](#validation-tools)
7. [Database Tools](#database-tools)
8. [Configuration Files](#configuration-files)
9. [Common Options](#common-options)
10. [Examples](#examples)
11. [Troubleshooting](#troubleshooting)

---

## Overview

The ABC Canntaireachd Converter provides 12+ command-line tools for processing ABC music notation files. Each tool follows a consistent interface and supports configuration files for repeatable workflows.

### Available Tools

| Tool | Purpose | Phase |
|------|---------|-------|
| `abc-cannt-cli.php` | Main converter with all features | Core |
| `abc-canntaireachd-pass-cli.php` | Canntaireachd generation only | Core |
| `abc-voice-pass-cli.php` | Voice copying (Melody → Bagpipes) | Core |
| `abc-voice-order-pass-cli.php` | Voice ordering (Source/Orchestral/Custom) | 4A |
| `abc-lyrics-pass-cli.php` | Lyrics processing and filtering | Core |
| `abc-timing-validator-pass-cli.php` | Bar timing validation | Core |
| `abc-tune-number-validator-cli.php` | Tune number (X:) validation | Core |
| `abc-renumber-tunes-cli.php` | Renumber tune X: headers | Core |
| `abc-reorder-tunes-cli.php` | Reorder tunes in file | Core |
| `abc-header-fields-cli.php` | Header field processing | Core |
| `abc-midi-defaults-cli.php` | MIDI directive defaults | Core |
| `run-migrations.php` | Database schema migrations | 4B |

---

## Installation

### Prerequisites
- PHP 7.3 or higher
- Composer (for dependencies)
- MySQL/MariaDB (for database features)

### Setup
```bash
# Install dependencies
composer install

# Verify installation
php bin/abc-cannt-cli.php --version

# Run database migrations (if using database features)
php bin/run-migrations.php
```

---

## Quick Start

### Convert ABC to Canntaireachd

```bash
# Basic conversion
php bin/abc-cannt-cli.php input.abc output.abc

# With transpose (orchestral mode)
php bin/abc-cannt-cli.php --transpose-mode=orchestral input.abc output.abc

# With voice ordering
php bin/abc-cannt-cli.php --voice-order=orchestral input.abc output.abc
```

### Using Configuration Files

```bash
# Save configuration
php bin/abc-cannt-cli.php --save-config=myconfig.json --transpose-mode=orchestral --voice-order=orchestral

# Use configuration (CLI options override file settings)
php bin/abc-cannt-cli.php --config=myconfig.json input.abc output.abc
```

---

## Main Converter

**File**: `bin/abc-cannt-cli.php`  
**Purpose**: Full-featured ABC to canntaireachd conversion with all processing passes

### Syntax
```bash
php bin/abc-cannt-cli.php [OPTIONS] <input_file> <output_file>
```

### Options

#### Voice Processing
- `--voice-copy`: Copy Melody voice to Bagpipes (default: enabled)
- `--no-voice-copy`: Disable voice copying

#### Canntaireachd Generation
- `--cannt`: Generate canntaireachd syllables (default: enabled)
- `--no-cannt`: Disable canntaireachd generation

#### Transpose Modes (Phase 4B)
- `--transpose-mode=MODE`: Set transpose mode
  - `midi`: All instruments at concert pitch (0)
  - `bagpipe`: Bagpipes=0, others=+2
  - `orchestral`: Bb=+2, Eb=+9, F=+7, concert=0
- `--transpose-override=VOICE:SEMITONES`: Override specific voice
  - Example: `--transpose-override="Clarinet:0"`
  - Multiple: `--transpose-override="Clarinet:0" --transpose-override="Trumpet:-2"`

#### Voice Ordering (Phase 4A)
- `--voice-order=MODE`: Set voice ordering mode
  - `source`: Preserve original order
  - `orchestral`: Standard orchestral sections order
  - `custom`: Use custom order (requires --custom-order)
- `--custom-order=LIST`: Comma-separated voice list
  - Example: `--custom-order="Melody,Bagpipes,Drums"`

#### Validation
- `--timing`: Validate bar timing (default: enabled)
- `--no-timing`: Disable timing validation
- `--tune-numbers`: Validate X: headers (default: enabled)
- `--no-tune-numbers`: Disable tune number validation

#### Configuration (Phase 4C)
- `--config=FILE`: Load configuration from JSON/YAML file
- `--save-config=FILE`: Save current options to configuration file

#### Output Control
- `--verbose`: Show detailed processing information
- `--quiet`: Suppress all output except errors
- `--diff`: Show canntaireachd differences

### Examples

```bash
# Full conversion with all features
php bin/abc-cannt-cli.php input.abc output.abc

# Orchestral score (transpose + ordering)
php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --voice-order=orchestral \
  orchestra.abc output.abc

# Bagpipe-centric (bagpipe mode + no ordering)
php bin/abc-cannt-cli.php \
  --transpose-mode=bagpipe \
  --voice-order=source \
  pipes.abc output.abc

# MIDI import (no transpose, preserve order)
php bin/abc-cannt-cli.php \
  --transpose-mode=midi \
  --voice-order=source \
  import.abc output.abc

# Custom configuration
php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --transpose-override="Clarinet:0" \
  --transpose-override="Horn:-1" \
  --custom-order="Flute,Clarinet,Horn,Strings" \
  complex.abc output.abc
```

---

## Processing Passes

### Canntaireachd Pass

**File**: `bin/abc-canntaireachd-pass-cli.php`  
**Purpose**: Generate canntaireachd syllables for Bagpipes voices only

```bash
php bin/abc-canntaireachd-pass-cli.php input.abc output.abc

# With validation
php bin/abc-canntaireachd-pass-cli.php --validate input.abc output.abc

# Show differences
php bin/abc-canntaireachd-pass-cli.php --diff input.abc output.abc
```

**Features**:
- Generates canntaireachd for Bagpipes/Pipes/P voices only
- Does NOT add canntaireachd to Melody voice
- Uses unified token dictionary
- Validates against existing canntaireachd

### Voice Copy Pass

**File**: `bin/abc-voice-pass-cli.php`  
**Purpose**: Copy Melody voice bars to Bagpipes voice

```bash
php bin/abc-voice-pass-cli.php input.abc output.abc

# Verbose mode
php bin/abc-voice-pass-cli.php --verbose input.abc output.abc
```

**Rules**:
- Only copies if Melody voice exists with bars
- Only copies if Bagpipes voice doesn't exist or has no bars
- Creates new Bagpipes voice with metadata
- Deep copy prevents object sharing

### Voice Order Pass

**File**: `bin/abc-voice-order-pass-cli.php`  
**Purpose**: Reorder voices in multi-voice ABC files

```bash
# Orchestral order
php bin/abc-voice-order-pass-cli.php --mode=orchestral input.abc output.abc

# Custom order
php bin/abc-voice-order-pass-cli.php \
  --mode=custom \
  --custom-order="Melody,Bagpipes,Drums" \
  input.abc output.abc

# Preserve source order
php bin/abc-voice-order-pass-cli.php --mode=source input.abc output.abc

# With configuration file
php bin/abc-voice-order-pass-cli.php \
  --config=voice_config.json \
  input.abc output.abc
```

**Orchestral Order Sections**:
1. Woodwinds (Flute, Clarinet, Oboe, Bassoon)
2. Brass (Trumpet, Horn, Trombone, Tuba)
3. Percussion (Drums, Timpani, Cymbals)
4. Strings (Violin, Viola, Cello, Bass)

### Lyrics Pass

**File**: `bin/abc-lyrics-pass-cli.php`  
**Purpose**: Process and filter w:/W: lyrics lines

```bash
php bin/abc-lyrics-pass-cli.php input.abc output.abc

# With filtering
php bin/abc-lyrics-pass-cli.php --filter input.abc output.abc
```

### Timing Validator Pass

**File**: `bin/abc-timing-validator-pass-cli.php`  
**Purpose**: Validate bar timing against meter signature

```bash
php bin/abc-timing-validator-pass-cli.php input.abc

# With detailed output
php bin/abc-timing-validator-pass-cli.php --verbose input.abc

# Output to file
php bin/abc-timing-validator-pass-cli.php input.abc errors.txt
```

**Validation**:
- Checks note durations against M: meter
- Reports bars with incorrect timing
- Marks errors with %timing-error: comments
- Supports complex meters (2/4, 3/4, 4/4, 6/8, 9/8, 12/8)

---

## Validation Tools

### Tune Number Validator

**File**: `bin/abc-tune-number-validator-cli.php`  
**Purpose**: Validate X: tune numbers for uniqueness and sequence

```bash
php bin/abc-tune-number-validator-cli.php input.abc

# Strict mode (must be sequential 1,2,3...)
php bin/abc-tune-number-validator-cli.php --strict input.abc
```

**Checks**:
- Duplicate X: numbers
- Missing X: headers
- Non-sequential numbering (in strict mode)

### Renumber Tunes

**File**: `bin/abc-renumber-tunes-cli.php`  
**Purpose**: Renumber all X: headers sequentially

```bash
php bin/abc-renumber-tunes-cli.php input.abc output.abc

# Starting from different number
php bin/abc-renumber-tunes-cli.php --start=10 input.abc output.abc
```

### Reorder Tunes

**File**: `bin/abc-reorder-tunes-cli.php`  
**Purpose**: Reorder tunes within a multi-tune file

```bash
# By title alphabetically
php bin/abc-reorder-tunes-cli.php --by=title input.abc output.abc

# By key signature
php bin/abc-reorder-tunes-cli.php --by=key input.abc output.abc

# By meter
php bin/abc-reorder-tunes-cli.php --by=meter input.abc output.abc
```

---

## Database Tools

### Run Migrations

**File**: `bin/run-migrations.php`  
**Purpose**: Apply database schema migrations

```bash
# Apply all pending migrations
php bin/run-migrations.php

# Check migration status
php bin/run-migrations.php --status

# Rollback last migration
php bin/run-migrations.php --rollback

# Dry run (show SQL without executing)
php bin/run-migrations.php --dry-run
```

**Features**:
- Transactional migrations
- Idempotent (safe to run multiple times)
- Tracking table for migration history
- Rollback support

### Load Schemas

**File**: `bin/abc-load-schemas-cli.php`  
**Purpose**: Load initial database schemas

```bash
# Load all schemas
php bin/abc-load-schemas-cli.php

# Load specific schema
php bin/abc-load-schemas-cli.php --schema=abc_dict

# Force reload (drop and recreate)
php bin/abc-load-schemas-cli.php --force
```

**Schemas**:
- `abc_dict`: Token dictionary (ABC/canntaireachd/BMW)
- `abc_midi_defaults`: MIDI directive defaults
- `abc_voice_names`: Instrument names and transpose values
- `abc_voice_order_defaults`: Voice ordering rules
- `abc_header_field_defaults`: Header field default values

---

## Configuration Files

### Format

Configuration files support JSON and YAML formats.

**JSON Example** (`myconfig.json`):
```json
{
  "transposeMode": "orchestral",
  "voiceOrder": "orchestral",
  "transposeOverrides": {
    "Clarinet": 0,
    "Horn": -1
  },
  "customVoiceOrder": ["Flute", "Clarinet", "Horn", "Strings"],
  "enableCannt": true,
  "enableVoiceCopy": true,
  "enableTiming": true
}
```

**YAML Example** (`myconfig.yaml`):
```yaml
transposeMode: orchestral
voiceOrder: orchestral
transposeOverrides:
  Clarinet: 0
  Horn: -1
customVoiceOrder:
  - Flute
  - Clarinet
  - Horn
  - Strings
enableCannt: true
enableVoiceCopy: true
enableTiming: true
```

### Precedence

When using configuration files with CLI options:

1. **CLI Options** (highest priority)
2. **Configuration File**
3. **Default Values** (lowest priority)

**Example**:
```bash
# Config says transpose=midi, CLI says transpose=orchestral
# Result: orchestral (CLI wins)
php bin/abc-cannt-cli.php --config=config.json --transpose-mode=orchestral input.abc output.abc
```

### Creating Configuration Files

```bash
# Method 1: Save current options
php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --voice-order=orchestral \
  --save-config=orchestra.json

# Method 2: Create manually
echo '{"transposeMode":"orchestral","voiceOrder":"orchestral"}' > config.json
```

### Loading Configuration Files

```bash
# Basic usage
php bin/abc-cannt-cli.php --config=myconfig.json input.abc output.abc

# Override specific options
php bin/abc-cannt-cli.php \
  --config=myconfig.json \
  --transpose-mode=midi \
  input.abc output.abc

# Use with multiple tools
php bin/abc-voice-order-pass-cli.php --config=myconfig.json input.abc output.abc
```

---

## Common Options

All CLI tools support these standard options:

### Help
```bash
php bin/TOOL.php --help
php bin/TOOL.php -h
```

### Version
```bash
php bin/TOOL.php --version
php bin/TOOL.php -v
```

### Verbose Output
```bash
php bin/TOOL.php --verbose input.abc output.abc
php bin/TOOL.php -V input.abc output.abc
```

### Quiet Mode
```bash
php bin/TOOL.php --quiet input.abc output.abc
php bin/TOOL.php -q input.abc output.abc
```

### Dry Run (Preview Only)
```bash
php bin/TOOL.php --dry-run input.abc output.abc
```

---

## Examples

### Example 1: Basic Canntaireachd Generation

```bash
# Input: Simple ABC tune with Melody
# Output: ABC tune with Bagpipes voice and canntaireachd

php bin/abc-cannt-cli.php tune.abc output.abc
```

### Example 2: Orchestral Score Processing

```bash
# Input: Multi-voice orchestral score
# Output: Properly ordered and transposed for performance

php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --voice-order=orchestral \
  --timing \
  symphony.abc symphony_processed.abc
```

### Example 3: MIDI Import Workflow

```bash
# Step 1: Import from MIDI (all concert pitch)
php bin/abc-cannt-cli.php \
  --transpose-mode=midi \
  --voice-order=source \
  import.abc step1.abc

# Step 2: Add bagpipe voice with canntaireachd
php bin/abc-voice-pass-cli.php step1.abc step2.abc
php bin/abc-canntaireachd-pass-cli.php step2.abc final.abc
```

### Example 4: Batch Processing with Configuration

```bash
# Create reusable configuration
php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --voice-order=orchestral \
  --save-config=batch_config.json

# Process multiple files
for file in tunes/*.abc; do
  output="processed/$(basename "$file")"
  php bin/abc-cannt-cli.php --config=batch_config.json "$file" "$output"
done
```

### Example 5: Custom Instrument Configuration

```bash
# Create configuration with custom transpose values
cat > custom.json <<EOF
{
  "transposeMode": "orchestral",
  "transposeOverrides": {
    "Clarinet in A": 3,
    "Piccolo Trumpet in A": 3,
    "Alto Flute": -5
  },
  "voiceOrder": "orchestral"
}
EOF

# Use configuration
php bin/abc-cannt-cli.php --config=custom.json unusual.abc output.abc
```

### Example 6: Validation Pipeline

```bash
# Step 1: Validate tune numbers
php bin/abc-tune-number-validator-cli.php --strict input.abc

# Step 2: Renumber if needed
php bin/abc-renumber-tunes-cli.php input.abc step1.abc

# Step 3: Validate timing
php bin/abc-timing-validator-pass-cli.php step1.abc

# Step 4: Process if valid
if [ $? -eq 0 ]; then
  php bin/abc-cannt-cli.php step1.abc final.abc
fi
```

---

## Troubleshooting

### Common Issues

#### 1. "PHP Fatal error: Class not found"

**Cause**: Composer autoloader not generated  
**Solution**:
```bash
composer dump-autoload
```

#### 2. "Database connection failed"

**Cause**: Database credentials not configured  
**Solution**: Check `config/db_config.php`:
```php
return [
    'host' => 'localhost',
    'database' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password'
];
```

#### 3. "Migration failed"

**Cause**: Migration already applied or schema conflict  
**Solution**:
```bash
# Check migration status
php bin/run-migrations.php --status

# If needed, manually rollback
php bin/run-migrations.php --rollback
```

#### 4. "No canntaireachd generated"

**Cause**: Voice name not recognized as Bagpipes family  
**Solution**: Check voice names. Valid: Bagpipes, Pipes, P (case-insensitive)

```abc
V:Bagpipes  ✓ Works
V:bagpipes  ✓ Works  
V:Pipes     ✓ Works
V:P         ✓ Works
V:Melody    ✗ Won't generate canntaireachd
```

#### 5. "Transpose not working"

**Cause**: Voice name not in database or wrong mode  
**Solution**:
```bash
# Check database for instrument
# Add to abc_voice_names table if missing

# Or use override
php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --transpose-override="YourInstrument:2" \
  input.abc output.abc
```

#### 6. "Configuration file not found"

**Cause**: Wrong path or file doesn't exist  
**Solution**: Use absolute or relative path:
```bash
# Relative to current directory
php bin/abc-cannt-cli.php --config=./config/myconfig.json input.abc output.abc

# Absolute path
php bin/abc-cannt-cli.php --config=/full/path/to/config.json input.abc output.abc
```

### Getting Help

1. **Check help documentation**:
   ```bash
   php bin/TOOL.php --help
   ```

2. **Use verbose mode** to see detailed processing:
   ```bash
   php bin/TOOL.php --verbose input.abc output.abc
   ```

3. **Check logs** in `src/logs/` directory

4. **Run tests** to verify installation:
   ```bash
   vendor/bin/phpunit
   php test_transpose_master.php
   ```

---

## Best Practices

### 1. Always Validate First
```bash
# Validate before processing
php bin/abc-tune-number-validator-cli.php input.abc
php bin/abc-timing-validator-pass-cli.php input.abc

# Then process
php bin/abc-cannt-cli.php input.abc output.abc
```

### 2. Use Configuration Files for Repeated Workflows
```bash
# Save configuration once
php bin/abc-cannt-cli.php --save-config=workflow.json --transpose-mode=orchestral

# Reuse many times
php bin/abc-cannt-cli.php --config=workflow.json file1.abc out1.abc
php bin/abc-cannt-cli.php --config=workflow.json file2.abc out2.abc
```

### 3. Backup Before Batch Processing
```bash
# Create backup
cp -r tunes tunes_backup

# Process files
for file in tunes/*.abc; do
  php bin/abc-cannt-cli.php --config=config.json "$file" "processed/$(basename "$file")"
done
```

### 4. Test with Small Files First
```bash
# Extract first tune for testing
head -n 20 large_file.abc > test.abc

# Test configuration
php bin/abc-cannt-cli.php --config=config.json test.abc test_out.abc

# Review output
cat test_out.abc

# Apply to full file if satisfied
php bin/abc-cannt-cli.php --config=config.json large_file.abc output.abc
```

### 5. Use Dry Run for Preview
```bash
# Preview changes without writing
php bin/abc-cannt-cli.php --dry-run input.abc output.abc

# Review and confirm
php bin/abc-cannt-cli.php input.abc output.abc
```

---

**For more information, see**:
- [Transpose User Guide](Transpose_User_Guide.md)
- [WordPress Admin Guide](WordPress_Admin_Guide.md)
- [Configuration File Guide](Configuration_File_Guide.md)
- [README.md](../README.md)

---

*Last Updated: 2025-11-17*
