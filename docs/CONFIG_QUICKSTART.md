# ABC Processor - Configuration Quick Start Guide

## Overview

All ABC Processor CLI scripts now support configuration files in **JSON**, **YAML**, or **INI** format. This allows you to save your preferred settings and reuse them across multiple runs.

## Configuration Options

### Load Configuration
```bash
# Load configuration from a file
php bin/abc-cannt-cli.php --file tune.abc --config=myconfig.yml

# Configuration files are searched in this order:
# 1. Custom file (--config option)
# 2. Project: ./abc_config.{json|yml|ini}
# 3. User: ~/.abc_processor_config.{json|yml|ini}
# 4. Global: config/abc_processor_config.{json|yml|ini}
```

### View Current Configuration
```bash
# Display the effective configuration
php bin/abc-cannt-cli.php --show-config

# With custom config file
php bin/abc-cannt-cli.php --config=myconfig.yml --show-config
```

### Save Configuration
```bash
# Save current settings to a file
php bin/abc-cannt-cli.php --save-config=mysettings.json \
    --transpose-mode=bagpipe \
    --voice-order=orchestral \
    --bars_per_line=8
```

### Override Configuration
```bash
# CLI options always override config file settings
php bin/abc-cannt-cli.php --file tune.abc \
    --config=bagpipe_ensemble.yml \
    --transpose-mode=orchestral \
    --bars_per_line=4
```

## Configuration Precedence

Settings are applied in this order (highest to lowest priority):

1. **CLI Options** (highest) - `--transpose-mode=orchestral`
2. **Custom Config** - `--config=myconfig.json`
3. **Project Config** - `./abc_config.{json|yml|ini}`
4. **User Config** - `~/.abc_processor_config.{json|yml|ini}`
5. **Global Config** - `config/abc_processor_config.{json|yml|ini}`
6. **Hardcoded Defaults** (lowest) - Built into the application

## Example Configuration Files

### Bagpipe Ensemble (YAML)
```yaml
# config/examples/bagpipe_ensemble.yml
processing:
  voice_output_style: grouped
  interleave_bars: 1
  bars_per_line: 4

transpose:
  mode: bagpipe
  overrides:
    Bagpipes: 0
    Piano: 2
    Guitar: 2
    Bass: 2

voice_ordering:
  mode: custom
  custom_order:
    - Bagpipes
    - Harmony
    - Tenor
    - Snare
    - Bass
    - Piano
    - Guitar

canntaireachd:
  convert: true
  generate_diff: true

output:
  error_file: bagpipe_errors.log
  cannt_diff_file: cannt_diff.txt
```

### Orchestral Score (YAML)
```yaml
# config/examples/orchestral_score.yml
processing:
  voice_output_style: grouped
  bars_per_line: 8

transpose:
  mode: orchestral

voice_ordering:
  mode: orchestral

validation:
  timing_validation: true
  strict_mode: true
```

### MIDI Import (YAML)
```yaml
# config/examples/midi_import.yml
processing:
  voice_output_style: interleaved
  interleave_bars: 1

transpose:
  mode: midi

voice_ordering:
  mode: source

database:
  use_midi_defaults: true
```

### JSON Format
```json
{
  "processing": {
    "voice_output_style": "grouped",
    "interleave_bars": 1,
    "bars_per_line": 4
  },
  "transpose": {
    "mode": "bagpipe",
    "overrides": {
      "Bagpipes": 0,
      "Piano": 2
    }
  }
}
```

### INI Format
```ini
[processing]
voice_output_style = grouped
interleave_bars = 1
bars_per_line = 4

[transpose]
mode = bagpipe

[voice_ordering]
mode = orchestral
```

## Supported CLI Scripts

All major CLI scripts support configuration:

- ✅ `abc-cannt-cli.php` - Main processing tool
- ✅ `abc-voice-pass-cli.php` - Voice assignment
- ✅ `abc-timing-validator-pass-cli.php` - Timing validation
- ✅ `abc-renumber-tunes-cli.php` - Tune renumbering
- ✅ `abc-midi-defaults-cli.php` - MIDI defaults
- ✅ `abc-lyrics-pass-cli.php` - Lyrics processing

## Configuration Sections

### Processing Options
- `voice_output_style`: "grouped" | "interleaved" | "separate"
- `interleave_bars`: Number of bars to interleave (default: 1)
- `bars_per_line`: Bars per line in output (default: 4)
- `join_bars_with_backslash`: Join bars with backslash (default: false)
- `width`: Width for padding (default: 5)

### Transpose Options
- `mode`: "midi" | "bagpipe" | "orchestral"
- `overrides`: Per-voice transpose settings (e.g., "Bagpipes": 0)

### Voice Ordering Options
- `mode`: "source" | "orchestral" | "custom"
- `custom_order`: Array of voice names in desired order

### Canntaireachd Options
- `convert`: Enable canntaireachd conversion (default: false)
- `generate_diff`: Generate canntaireachd diff output (default: false)

### Output Options
- `output_file`: Path to output ABC file (default: null/stdout)
- `error_file`: Path to error log file (default: null/stderr)
- `cannt_diff_file`: Path to canntaireachd diff file (default: null)

### Database Options
- `use_midi_defaults`: Use MIDI defaults from database (default: true)
- `use_voice_order_defaults`: Use voice order from database (default: true)

### Validation Options
- `timing_validation`: Enable timing validation (default: true)
- `strict_mode`: Enable strict validation mode (default: false)

## Common Workflows

### 1. Highland Bagpipe Processing
```bash
# Use pre-configured bagpipe settings
php bin/abc-cannt-cli.php --file tune.abc \
    --config=config/examples/bagpipe_ensemble.yml \
    --convert --output=processed.abc
```

### 2. Orchestral Score Processing
```bash
# Use orchestral configuration
php bin/abc-cannt-cli.php --file score.abc \
    --config=config/examples/orchestral_score.yml \
    --output=orchestral.abc
```

### 3. MIDI File Import
```bash
# Import from MIDI with no transposition
php bin/abc-cannt-cli.php --file midi_import.abc \
    --config=config/examples/midi_import.yml \
    --output=imported.abc
```

### 4. Create Your Own Configuration
```bash
# 1. Start with example config
cp config/examples/bagpipe_ensemble.yml myproject.yml

# 2. Edit the file to your preferences
# 3. Test it
php bin/abc-cannt-cli.php --config=myproject.yml --show-config

# 4. Use it
php bin/abc-cannt-cli.php --file tune.abc --config=myproject.yml
```

### 5. Quick Override
```bash
# Use config but override specific settings
php bin/abc-cannt-cli.php --file tune.abc \
    --config=myproject.yml \
    --bars_per_line=8 \
    --transpose-mode=orchestral
```

## Tips

1. **Start with Examples**: Copy an example config and modify it for your needs
2. **Use --show-config**: Verify your configuration before processing
3. **Use Project Config**: Place `abc_config.yml` in your project root for automatic loading
4. **CLI Overrides**: CLI options always override file settings - useful for one-off changes
5. **Format Choice**: Use YAML for comments and readability, JSON for tools, INI for simplicity

## Getting Help

Each CLI script includes configuration help:
```bash
php bin/abc-cannt-cli.php --help
```

See `config/README.md` for detailed configuration documentation.
