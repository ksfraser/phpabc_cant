# ABC to Canntaireachd Refactor Plan

## Date: 2025-11-16

## Current State Analysis

### Architecture Issues
1. **Mixed Abstraction Levels**: Pipeline works with text lines, but `AbcTune::parse()` creates structured objects (Tune → Voice → Bar → Notes)
2. **Incomplete Object Model**: Parsing creates objects, but passes work on text lines
3. **Copy Logic**: Melody-to-Bagpipes copying works at wrong level (text manipulation instead of object copying)
4. **Multiple Parse/Render Cycles**: Each pass that needs structure does its own parse/render

### Current Data Flow
```
Input Lines (text[])
  ↓
Pipeline Pass 1 (text[] → text[])
  ↓
Pipeline Pass 2 (text[] → text[])
  ↓
...
  ↓
Output Lines (text[])
```

### Desired Data Flow
```
Input Lines (text[])
  ↓
Parse (text[] → AbcTune)
  ↓
Transform Pass 1 (AbcTune → AbcTune)
  ↓
Transform Pass 2 (AbcTune → AbcTune)
  ↓
...
  ↓
Render (AbcTune → text[])
  ↓
Output Lines (text[])
```

## Requirements Updates Needed

### Current Requirements Issues
1. Voice copying requirement not documented at object level
2. Bar structure not fully specified
3. Inline voice markers `[V:id]` not documented as separate from `V:id` headers
4. Canntaireachd placement (only on Bagpipes voice) not explicit

### Requirements to Add/Update
1. **Voice Structure**: Each voice must have:
   - Metadata (name, sname, clef, etc.)
   - Bars array (ordered collection of Bar objects)
   - Bars cannot span voice boundaries

2. **Melody-to-Bagpipes Copy**:
   - IF Melody voice exists with bars (music content)
   - AND Bagpipes voice does NOT exist OR has no bars
   - THEN copy all bars from Melody to new Bagpipes voice
   - Maintain bar order and structure

3. **Canntaireachd Generation**:
   - ONLY add canntaireachd lyrics to Bagpipes-family voices
   - Bagpipes family = {Bagpipes, Pipes, P}
   - Do NOT add canntaireachd to Melody voice
   - Each bar's notes → canntaireachd syllables → w: line

4. **Parsing Requirements**:
   - Lines → Headers + Voices
   - Voice music lines → Bars
   - Bars → Notes/Tokens
   - Preserve all non-music content (comments, directives, etc.)

## SOLID Principles Application

### Single Responsibility Principle (SRP)
- **AbcTune**: Container for tune structure
- **AbcVoice**: Container for voice metadata and bars
- **AbcBar**: Container for bar content (notes, tokens)
- **AbcParser**: Parse text → objects
- **AbcRenderer**: Render objects → text
- **VoiceTransform**: Copy melody to bagpipes
- **CanntaireachdTransform**: Add canntaireachd lyrics

### Open/Closed Principle
- Pipeline accepts any transform implementing `AbcTransform` interface
- New transforms can be added without modifying pipeline

### Liskov Substitution Principle
- All transforms implement same interface
- Can substitute any transform in pipeline

### Interface Segregation Principle
- Separate interfaces for parsing, rendering, transformation
- Clients only depend on methods they use

### Dependency Inversion Principle
- Pipeline depends on `AbcTransform` interface, not concrete classes
- Transforms injected via constructor (DI)

## Test-Driven Development (TDD) Plan

### Phase 1: Establish Test Coverage for Current Code
1. **Audit existing tests** (see tests/ directory)
2. **Identify gaps** in test coverage
3. **Create missing unit tests** for:
   - AbcTune::parse()
   - AbcTune::renderSelf()
   - AbcTune::getBarsForVoice()
   - AbcVoice structure
   - AbcBar structure
   - Current pipeline passes

### Phase 2: Create Tests for Refactored Code
1. **Parser tests**: Text → AbcTune object validation
2. **Renderer tests**: AbcTune → Text validation
3. **Transform tests**: Each transform in isolation
4. **Integration tests**: Full pipeline with multiple transforms
5. **Regression tests**: Ensure existing files still process correctly

### Phase 3: Red-Green-Refactor
1. Write failing test
2. Implement minimum code to pass
3. Refactor while keeping tests green

## Refactor Steps (Master TODO)

### Step 1: Requirements Documentation ✓ (This file)
- [ ] Update `REQUIREMENTS.md` with voice/bar structure
- [ ] Document Melody→Bagpipes copy rules
- [ ] Document canntaireachd placement rules
- [ ] Add parsing requirements
- [ ] Add UML diagrams for object model

### Step 2: Test Coverage Audit
- [ ] Run coverage analysis on existing code
- [ ] Document current test coverage percentage
- [ ] List all classes without tests
- [ ] List all methods without tests
- [ ] Prioritize critical paths for testing

### Step 3: Create Missing Unit Tests (Current Code)
- [ ] Test: `AbcTune::parse()` basic functionality
- [ ] Test: `AbcTune::parse()` with multiple voices
- [ ] Test: `AbcTune::parse()` with inline voice markers
- [ ] Test: `AbcTune::renderSelf()` output format
- [ ] Test: `AbcTune::getBarsForVoice()` returns correct bars
- [ ] Test: `AbcTune::hasVoice()` detection
- [ ] Test: `AbcVoice` constructor and methods
- [ ] Test: `AbcBar` structure
- [ ] Test: Current `AbcVoicePass` (text-based)
- [ ] Test: Current `AbcCanntaireachdPass`
- [ ] Test: End-to-end with test-Suo.abc

### Step 4: Design Refactored Architecture
- [ ] Define `AbcTransform` interface
- [ ] Design new pipeline (parse-transform-render)
- [ ] Design `VoiceCopyTransform` class
- [ ] Design `CanntaireachdTransform` class
- [ ] Update UML diagrams
- [ ] Document class responsibilities

### Step 5: Create Tests for Refactored Code (TDD - Red Phase)
- [ ] Test: `AbcTransform` interface contract
- [ ] Test: `AbcPipeline::process()` with empty transforms
- [ ] Test: `AbcPipeline::process()` with single transform
- [ ] Test: `AbcPipeline::process()` with multiple transforms
- [ ] Test: `VoiceCopyTransform::transform()` copies bars
- [ ] Test: `VoiceCopyTransform::transform()` skips if exists
- [ ] Test: `CanntaireachdTransform::transform()` only Bagpipes
- [ ] Test: `CanntaireachdTransform::transform()` skips Melody
- [ ] Test: Integration test with test-Suo.abc

### Step 6: Implement Refactored Code (TDD - Green Phase)
- [ ] Implement `AbcTransform` interface
- [ ] Refactor `AbcProcessingPipeline` to:
  - Parse once at start
  - Run transforms on AbcTune object
  - Render once at end
- [ ] Implement `VoiceCopyTransform`:
  - Check for Melody voice with bars
  - Check for Bagpipes voice existence
  - Copy bars from Melody to Bagpipes
  - Add proper PHPDoc and UML
- [ ] Implement `CanntaireachdTransform`:
  - Process only Bagpipes-family voices
  - Convert bars → notes → canntaireachd
  - Add w: lines to voice
  - Add proper PHPDoc and UML
- [ ] Update pass registration in main processor

### Step 7: Refactor Existing Code (TDD - Refactor Phase)
- [ ] Refactor `AbcVoicePass` → `VoiceCopyTransform`
- [ ] Refactor `AbcCanntaireachdPass` → `CanntaireachdTransform`
- [ ] Remove old text-based logic from `AbcProcessor`
- [ ] Clean up unused methods
- [ ] Ensure all tests still pass

### Step 8: Add Public API Methods to AbcTune
- [ ] `addVoice(voiceId, metadata, bars)` - already exists
- [ ] `copyVoiceBars(fromVoiceId, toVoiceId)`
- [ ] `getVoiceIds()`
- [ ] `hasVoiceWithBars(voiceId)`
- [ ] Add tests for each method

### Step 9: Documentation
- [ ] Update all PHPDoc comments
- [ ] Generate UML diagrams from code
- [ ] Update `README.md` with new architecture
- [ ] Update `PARSER_ARCHITECTURE.md`
- [ ] Create `TRANSFORM_ARCHITECTURE.md`
- [ ] Document transform interface and usage

### Step 10: Validation & Cleanup
- [ ] Run all unit tests
- [ ] Run integration tests
- [ ] Test with all example files
- [ ] Check code coverage (aim for >80%)
- [ ] Remove commented-out code
- [ ] Remove unused imports
- [ ] Run static analysis (psalm/phpstan if available)

### Step 11: Performance Testing
- [ ] Benchmark current implementation
- [ ] Benchmark refactored implementation
- [ ] Compare memory usage
- [ ] Optimize if needed

## Success Criteria

### Must Have
1. ✅ All existing tests pass
2. ✅ All new tests pass
3. ✅ Code coverage ≥ 80%
4. ✅ test-Suo.abc produces correct output:
   - V:Bagpipes voice created
   - Melody bars copied to Bagpipes
   - Canntaireachd only under Bagpipes
   - Melody voice has no canntaireachd
5. ✅ No regressions in other test files

### Should Have
1. ✅ All classes have PHPDoc with UML
2. ✅ SOLID principles followed
3. ✅ DRY violations removed
4. ✅ Clear separation: Parse → Transform → Render

### Nice to Have
1. Performance improvement over current implementation
2. Extensible transform system for future enhancements
3. Better error messages and logging

## Risk Assessment

### High Risk
- Breaking existing functionality during refactor
- Incomplete test coverage causing missed regressions

**Mitigation**: Comprehensive test suite before refactoring

### Medium Risk
- Performance degradation from parsing overhead
- Complex voice/bar relationships causing bugs

**Mitigation**: Benchmark before/after, thorough integration tests

### Low Risk
- API changes requiring updates elsewhere
- Documentation becoming outdated

**Mitigation**: Update docs as part of each step

## Timeline Estimate

- **Step 1-2**: Requirements & Audit (2 hours)
- **Step 3**: Missing tests (4 hours)
- **Step 4-5**: Design & test specs (3 hours)
- **Step 6-7**: Implementation (6 hours)
- **Step 8-9**: API & docs (2 hours)
- **Step 10-11**: Validation & performance (2 hours)

**Total**: ~19 hours of focused work

## Next Immediate Actions

1. ✅ Create this plan
2. Update REQUIREMENTS.md
3. Audit test coverage
4. Begin creating missing tests

---

**Note**: This plan follows TDD and SOLID principles. Each step builds on the previous one. Do not proceed to implementation until tests are in place.
