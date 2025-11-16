# ABC Notation Object Model and Processing Requirements

## Date Updated: 2025-11-16

## ABC Notation Structure

### Document Structure
```
ABC Document
  ├── Tune 1
  │   ├── Headers (X:, T:, K:, M:, L:, etc.)
  │   ├── Directives (%%, I:)
  │   └── Voices
  │       ├── Voice 1 (Metadata + Bars)
  │       ├── Voice 2 (Metadata + Bars)
  │       └── Voice N (Metadata + Bars)
  ├── Tune 2
  └── Tune N
```

### Tune Object Model

#### AbcTune
**Responsibility**: Container for a single ABC tune with all its components

**Properties**:
- `headers`: Array of header objects (X:, T:, K:, M:, L:, etc.)
- `voices`: Array of voice metadata keyed by voice ID
- `voiceBars`: Array of Bar arrays keyed by voice ID
- `directives`: Array of directives (%%, I:)
- `comments`: Array of comment lines

**Methods**:
- `parse(string $abcText): AbcTune` - Parse text into structured object
- `renderSelf(): string` - Render object back to ABC text
- `hasVoice(string $voiceId): bool` - Check if voice exists
- `getBarsForVoice(string $voiceId): ?array` - Get bars for a voice
- `addVoice(string $voiceId, array $metadata, array $bars): void` - Add new voice
- `copyVoiceBars(string $fromId, string $toId): void` - Copy bars between voices

**UML**:
```
@startuml
class AbcTune {
  - headers: array
  - voices: array
  - voiceBars: array
  + parse(abcText): AbcTune
  + renderSelf(): string
  + hasVoice(voiceId): bool
  + getBarsForVoice(voiceId): array
  + addVoice(voiceId, metadata, bars)
  + copyVoiceBars(fromId, toId)
}
@enduml
```

#### AbcVoice
**Responsibility**: Container for voice metadata and music content

**Properties**:
- `voiceIndicator`: string (voice ID: M, Melody, Bagpipes, etc.)
- `name`: string (full name for display)
- `sname`: string (short name for display)
- `bars`: array of AbcBar objects
- `clef`: string (optional)
- `stem`: string (optional)
- `octave`: int (transpose by octaves)
- `lyricsLines`: array of w: lines

**Methods**:
- `addBar(AbcBar $bar): void` - Add a bar to this voice
- `getBars(): array` - Get all bars
- `addLyricsLine(string $lyrics): void` - Add w: line
- `renderLyrics(): array` - Render all w: lines

**UML**:
```
@startuml
class AbcVoice {
  - voiceIndicator: string
  - name: string
  - sname: string
  - bars: array
  + addBar(bar)
  + getBars(): array
  + addLyricsLine(lyrics)
  + renderLyrics(): array
}
@enduml
```

#### AbcBar
**Responsibility**: Container for music within a single bar

**Properties**:
- `barNumber`: int
- `barLine`: string (|, ||, |:, :|, etc.)
- `notes`: array of note tokens
- `timeSignature`: ?string (if changes mid-tune)
- `keySignature`: ?string (if changes mid-tune)

**Constraints**:
- A bar belongs to exactly one voice
- Voice changes CANNOT occur mid-bar
- Inline voice markers `[V:id]` mark the START of a bar for that voice

**UML**:
```
@startuml
class AbcBar {
  - barNumber: int
  - barLine: string
  - notes: array
  - timeSignature: string
  - keySignature: string
}
@enduml
```

## Voice Markers in ABC Notation

### Two Forms of Voice Markers

#### 1. Voice Header (V:)
- Standalone line defining voice metadata
- Format: `V:id name="Name" sname="ShortName" clef=treble`
- Occurs in header section or before music
- Example: `V:M name="Melody" sname="Melody"`

#### 2. Inline Voice Marker ([V:])
- Embedded at start of music line to switch voices
- Format: `[V:id]music notes | more notes`
- Changes current voice for that line
- Marks the START of a bar sequence for that voice
- Example: `[V:M]{g}A3B {g}ce3 | {g}B3A {g}c{d}B3 |`

#### Parsing Rules
1. `V:id` headers define voice metadata
2. `[V:id]` markers switch active voice for subsequent music
3. Music following `[V:id]` belongs to that voice until next voice marker
4. Voice switches occur at bar boundaries only
5. Bars are split by `|` characters

## Melody-to-Bagpipes Copy Requirement

### Business Rule
When processing ABC tunes for canntaireachd output:

**IF** Melody voice exists with bars (music content)  
**AND** Bagpipes voice does NOT exist OR has no bars  
**THEN** Copy all bars from Melody voice to new Bagpipes voice

### Voice Identification

#### Melody Voice IDs (case-insensitive):
- `M`
- `Melody`

#### Bagpipes Voice IDs (case-insensitive):
- `Bagpipes`
- `Pipes`  
- `P`

### Copy Semantics
1. **Metadata Copy**:
   - Create new voice with ID: `Bagpipes`
   - Set name: `"Bagpipes"`
   - Set sname: `"Bagpipes"`

2. **Bar Copy**:
   - Copy all Bar objects from Melody voice
   - Maintain bar order
   - Preserve bar numbers
   - Preserve all bar content (notes, grace notes, timing)

3. **Independence**:
   - Copied bars are independent of original
   - Changes to Bagpipes bars don't affect Melody
   - Canntaireachd added to Bagpipes doesn't affect Melody

### When Copy Does NOT Occur
- Bagpipes voice already has bars (music content)
- Melody voice has no bars (only header, no music)
- Melody voice doesn't exist

## Canntaireachd Generation Requirement

### Business Rule
Canntaireachd syllables are ONLY added to Bagpipes-family voices:

**Target Voices** (case-insensitive):
- `Bagpipes`
- `Pipes`
- `P`

**Excluded Voices**:
- `M` (Melody) - NEVER gets canntaireachd
- `Melody` - NEVER gets canntaireachd
- All other voices

### Generation Process
1. For each bar in Bagpipes voice:
   - Extract notes (ignore grace notes for canntaireachd)
   - Look up each note in token dictionary
   - Generate canntaireachd syllable for each note
   - Preserve bar line separators (`|`)

2. Add canntaireachd as `w:` line:
   - Format: `w: syllable1 syllable2 | syllable3 syllable4 |`
   - One w: line per music line
   - Syllables separated by spaces
   - Bar lines preserved in lyrics

3. Place w: line immediately after corresponding music line

### Example
```abc
V:Bagpipes name="Bagpipes" sname="Bagpipes"
{g}A3B {g}ce3 | {g}B3A {g}c{d}B3 |
w: hen o ho e | ho en ho do |
```

## Parsing Requirements

### Parse Phase: Text → Objects

#### Input
- Array of text lines from ABC file

#### Process
1. **Split into tunes**: Separate by X: headers
2. **Parse each tune**:
   - Extract headers (X:, T:, K:, M:, L:, etc.)
   - Extract directives (%%, I:)
   - Parse voice definitions (V: headers)
   - Parse music lines into bars
   - Assign bars to correct voices

3. **Voice-to-Bar Assignment**:
   - Track current active voice
   - When `V:id` header seen, set active voice to id
   - When `[V:id]` marker seen, set active voice to id
   - Split music by `|` into bars
   - Assign bars to current active voice

4. **Bar Parsing**:
   - Split on bar line markers: `|`, `||`, `|:`, `:|`, `|]`, `[|`
   - Parse notes, grace notes, chords, rests
   - Preserve timing information
   - Handle inline directives (time, key changes)

#### Output
- AbcTune object with complete structure

### Render Phase: Objects → Text

#### Input
- AbcTune object

#### Process
1. **Render headers** in standard order
2. **Render voice headers** (V: lines)
3. **Render music** for each voice:
   - Output voice marker if needed
   - Output bars with bar lines
   - Output any w: lines (lyrics/canntaireachd)
4. **Maintain formatting**:
   - Preserve blank lines
   - Preserve comments
   - Preserve directives

#### Output
- Array of text lines (ABC notation)

## Transform Pipeline Architecture

### Pipeline Flow
```
Text Lines (input)
      ↓
  Parse Phase (AbcParser)
      ↓
  AbcTune Object
      ↓
  Transform 1 (e.g., VoiceCopyTransform)
      ↓
  AbcTune Object (modified)
      ↓
  Transform 2 (e.g., CanntaireachdTransform)
      ↓
  AbcTune Object (modified)
      ↓
  Transform N...
      ↓
  Render Phase (AbcRenderer)
      ↓
Text Lines (output)
```

### Transform Interface
```php
interface AbcTransform {
    /**
     * Transform an AbcTune object
     * @param AbcTune $tune The tune to transform
     * @return AbcTune The transformed tune
     */
    public function transform(AbcTune $tune): AbcTune;
}
```

### Transform Principles
1. **Immutability**: Consider making transforms pure (return new object)
2. **Single Responsibility**: Each transform does ONE thing
3. **Composability**: Transforms can be chained
4. **Testability**: Each transform tested in isolation

## Test Requirements

### Unit Test Coverage
All classes must have unit tests covering:
- Constructor and property initialization
- All public methods
- Edge cases and error conditions
- Boundary conditions

### Integration Test Coverage
- Full pipeline: parse → transform → render
- Multi-tune files
- Multi-voice files
- Melody-to-Bagpipes copy scenarios
- Canntaireachd generation

### Regression Test Files
- `test-Suo.abc` - Inline voice markers
- `test-simple.abc` - Basic single tune
- `test-multi.abc` - Multiple tunes
- All files in tests/ directory

### Test Assertions for Voice Copy
```php
// Given: Melody voice with bars, no Bagpipes
$tune = AbcTune::parse($abcWithMelody);

// When: VoiceCopyTransform applied
$transform = new VoiceCopyTransform();
$result = $transform->transform($tune);

// Then: Bagpipes voice exists with copied bars
assertTrue($result->hasVoice('Bagpipes'));
$bagpipesBars = $result->getBarsForVoice('Bagpipes');
$melodyBars = $result->getBarsForVoice('M');
assertEquals(count($melodyBars), count($bagpipesBars));
```

### Test Assertions for Canntaireachd
```php
// Given: Bagpipes voice with bars
$tune = AbcTune::parse($abcWithBagpipes);

// When: CanntaireachdTransform applied
$transform = new CanntaireachdTransform($dictionary);
$result = $transform->transform($tune);

// Then: Bagpipes voice has canntaireachd lyrics
$voice = $result->getVoices()['Bagpipes'];
$lyrics = $voice->renderLyrics();
assertNotEmpty($lyrics);
assertStringContainsString('w:', $lyrics[0]);

// And: Melody voice has NO canntaireachd
if ($result->hasVoice('M')) {
    $melodyVoice = $result->getVoices()['M'];
    $melodyLyrics = $melodyVoice->renderLyrics();
    assertEmpty($melodyLyrics); // No w: lines added to Melody
}
```

## Success Criteria

### Functional Requirements Met
1. ✅ Melody bars copied to Bagpipes when needed
2. ✅ Canntaireachd ONLY on Bagpipes voice
3. ✅ Melody voice has NO canntaireachd
4. ✅ All voice content preserved
5. ✅ Bar structure maintained
6. ✅ Inline voice markers handled correctly

### Code Quality Requirements Met
1. ✅ SOLID principles followed
2. ✅ DRY violations eliminated
3. ✅ Single Responsibility per class
4. ✅ Dependency Injection used
5. ✅ All classes have PHPDoc with UML
6. ✅ Test coverage ≥ 80%

### Test Requirements Met
1. ✅ All unit tests pass
2. ✅ All integration tests pass
3. ✅ All regression tests pass
4. ✅ test-Suo.abc produces correct output
5. ✅ No existing functionality broken

---

## Appendix: Object Relationships

```
@startuml
class AbcDocument {
  - tunes: array
  + addTune(tune)
  + getTunes(): array
}

class AbcTune {
  - headers: array
  - voices: array
  - voiceBars: array
  + parse(text): AbcTune
  + renderSelf(): string
  + addVoice(id, metadata, bars)
  + copyVoiceBars(from, to)
}

class AbcVoice {
  - voiceIndicator: string
  - name: string
  - bars: array
  + addBar(bar)
  + getBars(): array
}

class AbcBar {
  - barNumber: int
  - barLine: string
  - notes: array
}

interface AbcTransform {
  + transform(tune): AbcTune
}

class VoiceCopyTransform {
  + transform(tune): AbcTune
}

class CanntaireachdTransform {
  - dictionary: TokenDictionary
  + transform(tune): AbcTune
}

AbcDocument "1" *-- "many" AbcTune
AbcTune "1" *-- "many" AbcVoice
AbcVoice "1" *-- "many" AbcBar
VoiceCopyTransform ..|> AbcTransform
CanntaireachdTransform ..|> AbcTransform
CanntaireachdTransform --> TokenDictionary
@enduml
```
