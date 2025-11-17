# Transpose Modes - User Guide

## Overview

The transpose mode system automatically adds transpose values to ABC notation voices based on the instrument type. This ensures that:

- **Transposing instruments** (like Bb trumpet) show their written pitch
- **Concert pitch instruments** (like piano) show actual sounding pitch
- **MIDI/audio imports** can maintain absolute pitch
- **Bagpipe ensembles** account for bagpipes sounding differently than written

---

## Three Transpose Modes

### 1. MIDI Mode (Default)
**Use when**: Importing from MIDI files or audio

All instruments are set to concert pitch (transpose=0). What you write is what sounds.

**Example**: A trumpet plays middle C (C4), and the ABC notation shows C.

### 2. Bagpipe Mode
**Use when**: Writing for Highland bagpipe ensembles

- Bagpipes: transpose=0 (written pitch)
- All other instruments: transpose=2 (+2 semitones)

**Rationale**: Highland bagpipes sound Bb when written in A (up a whole step). To accompany bagpipes, other instruments must transpose up 2 semitones.

**Example**: 
- Bagpipes play written A → sounds Bb
- Piano plays written B → sounds C (to match)

### 3. Orchestral Mode
**Use when**: Writing traditional orchestral or band scores

Each instrument uses its standard transposition:

| Instrument Type | Transpose | Examples |
|----------------|-----------|----------|
| Concert Pitch | 0 | Piano, Flute, Violin, Trombone |
| Bb Instruments | +2 | Trumpet, Clarinet, Tenor Sax |
| Eb Instruments | +9 | Alto Sax, Baritone Sax |
| F Instruments | +7 | French Horn, English Horn |

**Example**: A Bb trumpet reading C will sound Bb (down 2 semitones, but written up 2 to compensate).

---

## Quick Start

### Command Line

```bash
# Use orchestral mode
php bin/abc-cannt-cli.php --file myscore.abc --transpose-mode=orchestral

# Use bagpipe mode
php bin/abc-cannt-cli.php --file pipes.abc --transpose-mode=bagpipe

# Use MIDI mode (default)
php bin/abc-cannt-cli.php --file imported.abc --transpose-mode=midi
```

### Configuration File

Create `my_config.json`:
```json
{
  "transpose": {
    "mode": "orchestral"
  }
}
```

Use it:
```bash
php bin/abc-cannt-cli.php --file score.abc --config=my_config.json
```

### WordPress Admin

1. Go to: **ABC Canntaireachd → Transpose**
2. Select your mode (MIDI/Bagpipe/Orchestral)
3. Click **Save Transpose Settings**
4. All future processing uses this mode

---

## Per-Voice Overrides

Override the transpose value for specific instruments.

### Command Line

```bash
php bin/abc-cannt-cli.php --file score.abc \
  --transpose-mode=orchestral \
  --transpose-override=Piano:0 \
  --transpose-override=Trumpet:5
```

This uses orchestral defaults, but Piano stays at 0 and Trumpet goes to 5.

### Configuration File

```json
{
  "transpose": {
    "mode": "orchestral",
    "overrides": {
      "Piano": 0,
      "Trumpet": 5,
      "Violin": -2
    }
  }
}
```

### WordPress Admin

1. Go to: **ABC Canntaireachd → Transpose**
2. Select your base mode
3. In the voice table, enter override values
4. Leave blank to use mode default
5. Click **Save**

---

## Common Scenarios

### Scenario 1: Orchestra Score with Soloist

You're writing an orchestra piece with a piano soloist. You want orchestral transpositions but the piano part to stay at concert pitch.

**Solution**:
```bash
php bin/abc-cannt-cli.php --file concerto.abc \
  --transpose-mode=orchestral \
  --transpose-override=Piano:0
```

### Scenario 2: Bagpipe Band with Drummer

Writing for Highland bagpipes with snare drums. Bagpipes sound Bb in A, drums are non-pitched.

**Solution**:
```bash
php bin/abc-cannt-cli.php --file march.abc \
  --transpose-mode=bagpipe
```

Bagpipes get transpose=0, drums get transpose=2 (but it doesn't affect non-pitched instruments).

### Scenario 3: MIDI Import

You imported an ABC file from a MIDI recording. All pitches are absolute.

**Solution**:
```bash
php bin/abc-cannt-cli.php --file imported.abc --transpose-mode=midi
```

Everything stays at concert pitch (transpose=0).

### Scenario 4: Mixed Ensemble

You have piano, trumpet, and bagpipes together. You want the bagpipes at written pitch, trumpet transposed, piano at concert pitch.

**Solution**:
```json
{
  "transpose": {
    "mode": "orchestral",
    "overrides": {
      "Bagpipes": 0
    }
  }
}
```

- Piano: 0 (orchestral default for concert pitch)
- Trumpet: 2 (orchestral default for Bb)
- Bagpipes: 0 (overridden from default)

---

## How Transpose Values Work

The `transpose` field in ABC notation tells software/players how to adjust the written notes:

```abc
V:Trumpet transpose=2
```

This means: "written C sounds as Bb" (transpose up 2 semitones to compensate).

### Positive Values (Transposing Instruments)
- `transpose=2` (Bb instruments): Written C → Sounds Bb
- `transpose=9` (Eb instruments): Written C → Sounds Eb
- `transpose=7` (F instruments): Written C → Sounds F

### Zero (Concert Pitch)
- `transpose=0`: Written C → Sounds C

### Negative Values (Rare)
- `transpose=-2`: Written C → Sounds D (down 2 semitones)

---

## Supported Instruments

### Concert Pitch (0)
Bagpipes, Piano, Flute, Violin, Viola, Cello, Double Bass, Oboe, Bassoon, Trombone, Tuba, Strings, Percussion, Guitar

### Bb Instruments (+2)
Trumpet, Clarinet, Tenor Sax, Soprano Sax, Bass Clarinet

### Eb Instruments (+9)
Alto Sax, Baritone Sax, Eb Clarinet

### F Instruments (+7)
French Horn, English Horn

### Special
Piccolo: transpose=0, octave=1 (sounds octave higher)

---

## Abbreviations

The system recognizes common abbreviations:

- Tpt, Trp → Trumpet
- Cl → Clarinet
- Fl → Flute
- Hn → Horn
- Sax → Saxophone
- Vln → Violin
- Vla → Viola
- Vc, Cello → Cello
- Pno, Pf → Piano

**Example**: "Tpt" gets transpose=2 (Bb instrument)

---

## Troubleshooting

### Problem: Instrument not recognized

**Symptom**: Unknown instrument defaults to transpose=0

**Solution**: 
1. Check spelling: "Trumpt" won't match, use "Trumpet"
2. Use full name: "Bb Trumpet" works
3. Add to database via WordPress admin
4. Or use override: `--transpose-override=MyInstrument:2`

### Problem: Wrong transpose value

**Symptom**: Voice has incorrect transpose

**Solution**:
1. Check mode: MIDI mode sets everything to 0
2. Use override: `--transpose-override=Voice:N`
3. Update database default in WordPress admin

### Problem: Settings not persisting

**Symptom**: Mode resets after restarting

**Solution**:
1. WordPress: Check you clicked "Save"
2. CLI: Use config file instead of command-line options
3. Verify WordPress options table has `abc_transpose_mode`

### Problem: Database errors

**Symptom**: Can't save to database

**Solution**:
1. Run migration: `php bin/run-migrations.php`
2. Check database connection in `config/db_config.php`
3. Verify `abc_voice_names` table has `transpose` column

---

## Best Practices

### 1. Choose the Right Mode

- **MIDI mode**: Only for imported files or all-electronic ensembles
- **Bagpipe mode**: Only for Highland bagpipe ensembles
- **Orchestral mode**: Most traditional acoustic ensembles

### 2. Use Config Files for Projects

Instead of repeating command-line options:

```bash
# Create once
cat > project_config.json << EOF
{
  "transpose": {
    "mode": "orchestral",
    "overrides": {
      "Piano": 0
    }
  },
  "voice_ordering": {
    "mode": "orchestral"
  }
}
EOF

# Use everywhere
php bin/abc-cannt-cli.php --file tune1.abc --config=project_config.json
php bin/abc-cannt-cli.php --file tune2.abc --config=project_config.json
```

### 3. Set WordPress Defaults

For regular use, set defaults in WordPress admin:
1. ABC Canntaireachd → Transpose
2. Set your preferred mode
3. Add common overrides
4. Save

All processing uses these defaults unless overridden.

### 4. Document Custom Overrides

If you use non-standard transpose values, add comments:

```json
{
  "transpose": {
    "mode": "orchestral",
    "overrides": {
      "CustomInstrument": 5
    }
  },
  "_comment": "CustomInstrument transposes 5 semitones for special tuning"
}
```

---

## Advanced Usage

### Save Current Configuration

```bash
php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --transpose-override=Piano:0 \
  --save-config=my_orchestra.json
```

Now `my_orchestra.json` contains your settings.

### Show Current Configuration

```bash
php bin/abc-cannt-cli.php --show-config
```

Displays all current settings (from config files + WordPress + defaults).

### Precedence Order

Settings are applied in this order (last wins):

1. Default values (midi mode, no overrides)
2. Global config file
3. User config file  
4. Project config file
5. `--config` file
6. Command-line options (highest priority)

**Example**:
- WordPress has orchestral mode
- Config file has bagpipe mode
- CLI has `--transpose-mode=midi`
- Result: **MIDI mode** (CLI wins)

---

## Examples

### Example 1: String Quartet

All concert pitch, use orchestral mode (will set all to 0):

```bash
php bin/abc-cannt-cli.php --file quartet.abc --transpose-mode=orchestral
```

### Example 2: Jazz Combo

Piano (0), Alto Sax (Eb = 9), Trumpet (Bb = 2), Bass (0):

```bash
php bin/abc-cannt-cli.php --file jazz.abc --transpose-mode=orchestral
```

### Example 3: Brass Band

All Bb/Eb instruments:

```bash
php bin/abc-cannt-cli.php --file brass.abc --transpose-mode=orchestral
```

### Example 4: Symphony Orchestra

Full orchestra with all instrument types:

```bash
php bin/abc-cannt-cli.php --file symphony.abc --transpose-mode=orchestral
```

---

## API Integration

### PHP Code

```php
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;
use Ksfraser\PhpabcCanntaireachd\Transpose\OrchestralTransposeStrategy;

$config = new AbcProcessorConfig();
$config->transposeMode = 'orchestral';
$config->transposeOverrides = ['Piano' => 0, 'Trumpet' => 5];

$strategy = new OrchestralTransposeStrategy();
$transpose = $strategy->getTranspose('Trumpet'); // Returns 2

// With override:
$finalTranspose = isset($config->transposeOverrides['Trumpet']) 
    ? $config->transposeOverrides['Trumpet'] 
    : $transpose;
// Returns 5
```

---

## Reference Tables

### Transpose Values by Mode

| Instrument | MIDI | Bagpipe | Orchestral |
|------------|------|---------|------------|
| Bagpipes   | 0    | 0       | 0          |
| Piano      | 0    | 2       | 0          |
| Trumpet    | 0    | 2       | 2          |
| Alto Sax   | 0    | 2       | 9          |
| French Horn| 0    | 2       | 7          |
| Violin     | 0    | 2       | 0          |

### Semitone Intervals

| Transpose | Interval |
|-----------|----------|
| 0         | Unison (C→C) |
| 1         | Minor 2nd (C→Db) |
| 2         | Major 2nd (C→D) |
| 3         | Minor 3rd (C→Eb) |
| 7         | Perfect 5th (C→G) |
| 9         | Major 6th (C→A) |
| 12        | Octave (C→C) |

---

## Support

### Get Help

1. Check this guide first
2. Run tests: `php test_transpose_master.php`
3. View config: `php bin/abc-cannt-cli.php --show-config`
4. Check logs in WordPress debug log

### Report Issues

When reporting problems, include:
- Transpose mode being used
- Instrument names involved
- Expected vs actual transpose values
- Command or config file used
- Output of `--show-config`

---

## Version History

- **v1.0**: Initial release with 3 modes, 80+ instruments
- WordPress UI, CLI integration, database storage
- 28/28 tests passing (100%)
