# ABC Processor Configuration Guide

## Overview
The ABC Processor supports flexible configuration through multiple file formats and sources. This allows you to set default processing options without repeating CLI arguments every time.

## Configuration File Formats

The system supports three configuration file formats:

### 1. JSON (Recommended for programmatic use)
- **Files**: `abc_processor_config.json`
- **Pros**: Standard format, easy to parse, widely supported
- **Cons**: No comments support (use `_comment` keys as workaround)
- **Example**: See `config/abc_processor_config.json`

### 2. YAML (Recommended for human editing)
- **Files**: `abc_processor_config.yml` or `abc_processor_config.yaml`
- **Pros**: Human-friendly, supports comments, clean syntax
- **Cons**: Requires YAML parser library
- **Example**: See `config/abc_processor_config.yml`

### 3. INI (Simple key-value format)
- **Files**: `abc_processor_config.ini`
- **Pros**: Simple, native PHP support, familiar format
- **Cons**: Limited nesting, less expressive
- **Example**: See `config/abc_processor_config.ini`

## Configuration Locations

Configurations are loaded in order of precedence (highest to lowest):

1. **CLI Options** (highest priority)
   - Command-line arguments override all configuration files
   - Example: `--voice_output_style=interleaved`

2. **Custom Config File**
   - Specified via `--config=path/to/config.yml`
   - Overrides all default configuration locations

3. **Project Config**
   - Files in current working directory:
     - `./abc_config.json`
     - `./abc_config.yml`
     - `./abc_config.ini`

4. **User Config**
   - User-specific configuration in home directory:
     - `~/.abc_processor_config.json`
     - `~/.abc_processor_config.yml`
     - `~/.abc_processor_config.ini`

5. **Global Config**
   - System-wide defaults in `config/` directory:
     - `config/abc_processor_config.json`
     - `config/abc_processor_config.yml`
     - `config/abc_processor_config.ini`

6. **Hardcoded Defaults** (lowest priority)
   - Built into `AbcProcessorConfig` class

## Configuration Sections

### Processing Options
Controls ABC output formatting:
```yaml
processing:
  voice_output_style: grouped       # 'grouped' or 'interleaved'
  interleave_bars: 1                # Bars per voice (interleaved mode)
  bars_per_line: 4                  # Bars per ABC line
  join_bars_with_backslash: false   # Join bars with \ character
  tune_number_width: 5              # X: number width (left-padded zeros)
```

### Transpose Settings
Controls instrument transposition:
```yaml
transpose:
  mode: midi                        # 'midi', 'bagpipe', or 'orchestral'
  overrides:
    Bagpipes: 0                     # Per-voice transpose overrides
    Piano: 2
```

**Transpose Modes**:
- `midi`: All voices transpose=0 (absolute pitch from MIDI)
- `bagpipe`: Concert pitch instruments transpose up 2 semitones to match Highland bagpipes
- `orchestral`: Standard transpositions (Bb instruments +2, Eb +9, F +7, etc.)

### Voice Ordering
Controls voice order in output:
```yaml
voice_ordering:
  mode: source                      # 'source', 'orchestral', or 'custom'
  custom_order:
    - Melody
    - Bagpipes
    - Guitar
    - Drums
```

**Ordering Modes**:
- `source`: Preserve voice order from input file
- `orchestral`: Standard orchestral score order (woodwinds, brass, percussion, strings)
- `custom`: User-defined order (specify in `custom_order` array)

### Canntaireachd Processing
Controls canntaireachd generation:
```yaml
canntaireachd:
  convert: false                    # Generate canntaireachd for Bagpipes voices
  generate_diff: false              # Create diff file showing changes
```

### Output Files
Specifies output file paths:
```yaml
output:
  output_file: null                 # Output ABC file (null = auto-generate)
  error_file: null                  # Error log file (null = stderr)
  cannt_diff_file: null             # Canntaireachd diff file
```

### Database Settings
Controls database usage:
```yaml
database:
  use_midi_defaults: true           # Load MIDI settings from database
  use_voice_order_defaults: true    # Use database voice ordering
```

### Validation Settings
Controls validation behavior:
```yaml
validation:
  timing_validation: true           # Validate bar timing
  strict_mode: false                # Fail on warnings
```

## Example Configurations

The `config/examples/` directory contains example configurations for common use cases:

### 1. Bagpipe Ensemble (`bagpipe_ensemble.yml`)
For Highland bagpipe band music with modern chanters:
- Transpose mode: `bagpipe` (concert pitch +2)
- Custom voice order: Bagpipes, Harmony, Tenor, Snare, Bass
- Canntaireachd generation enabled

### 2. Orchestral Score (`orchestral_score.yml`)
For traditional orchestral/concert band music:
- Transpose mode: `orchestral` (standard transpositions)
- Voice order: `orchestral` (woodwinds, brass, percussion, strings)
- Strict validation enabled

### 3. MIDI Import (`midi_import.yml`)
For ABC files imported from MIDI:
- Transpose mode: `midi` (no transposition)
- Voice order: `source` (preserve MIDI track order)
- Interleaved output for better readability

## Using Configuration Files

### From Command Line

```bash
# Use default configuration (searches standard locations)
php bin/abc-cannt-cli.php input.abc

# Specify custom configuration file
php bin/abc-cannt-cli.php input.abc --config=config/examples/bagpipe_ensemble.yml

# Override config settings with CLI options
php bin/abc-cannt-cli.php input.abc --config=myconfig.yml --voice_output_style=interleaved

# Save current settings to configuration file
php bin/abc-cannt-cli.php --save-config=myconfig.json

# Display current configuration
php bin/abc-cannt-cli.php --show-config
```

### From PHP Code

```php
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;

// Load from default locations (with precedence)
$config = AbcProcessorConfig::loadWithPrecedence();

// Load from specific file
$config = AbcProcessorConfig::loadFromFile('config/examples/bagpipe_ensemble.yml');

// Load and merge multiple sources
$config = new AbcProcessorConfig();
$config->mergeFromFile('config/abc_processor_config.yml');
$config->mergeFromArray(['transpose' => ['mode' => 'orchestral']]);

// Override with CLI options
$config->applyFromCLI($cliOptions);

// Save configuration
$config->saveToFile('my_config.json');
```

## Creating Your Own Configuration

1. **Start with an example**:
   ```bash
   cp config/examples/bagpipe_ensemble.yml my_config.yml
   ```

2. **Edit the configuration**:
   - Change settings to match your needs
   - Add comments to document your choices
   - Remove unused sections (defaults will be used)

3. **Test the configuration**:
   ```bash
   php bin/abc-cannt-cli.php --config=my_config.yml --show-config
   ```

4. **Use the configuration**:
   ```bash
   php bin/abc-cannt-cli.php input.abc --config=my_config.yml
   ```

## Configuration Validation

The system validates configuration files on load:
- **Type checking**: Ensures values are correct type (int, bool, string, array)
- **Range validation**: Checks numeric values are in valid ranges
- **Enum validation**: Verifies mode values are valid ('source'/'orchestral'/'custom', etc.)
- **File path validation**: Checks output paths are writable

Invalid configurations will produce clear error messages indicating the problem.

## WordPress Configuration

In WordPress, configuration is stored in the `wp_options` table and managed through the admin interface:

1. Navigate to **ABC Processor Settings**
2. Configure options in the UI tabs:
   - Processing Settings
   - Voice Ordering
   - Transpose Settings
   - Output Settings
3. Save as named preset for reuse
4. Export configuration as JSON file
5. Import configuration from JSON file

## Troubleshooting

### Configuration not loading
- Check file exists: `ls -la config/abc_processor_config.yml`
- Check file permissions: Must be readable
- Validate JSON/YAML syntax: Use online validator or `php -l`

### CLI options not overriding config
- Ensure CLI option comes after `--config` flag
- Check option name matches exactly (use `--show-config` to verify)

### Invalid configuration errors
- Read error message carefully (indicates which setting is invalid)
- Check data types (strings in quotes, numbers without, booleans as true/false)
- Verify enum values (e.g., 'source' not 'Source')

## See Also
- [Requirements Document](../REQUIREMENTS.md) - Full configuration requirements
- [Implementation Plan](../docs/implementation_plan.md) - Implementation details
- [CLI Documentation](../bin/README.md) - Command-line interface guide
