# Master TODO List for ABC Canntaireachd Refactor

**Status**: Not Started  
**Created**: 2025-11-16  
**Estimated**: 19 hours  
**Target Completion**: TBD

---

## Phase 1: Documentation & Analysis (2h)

### Step 1.1: Requirements Documentation (30m)
- [x] Create OBJECT_MODEL_REQUIREMENTS.md
- [ ] Review and validate requirements with stakeholder
- [ ] Document all ABC standard voice markers
- [ ] Document edge cases (no Melody, existing Bagpipes, empty bars)

### Step 1.2: Test Coverage Audit (1h)
- [ ] Run PHPUnit with code coverage: `vendor/bin/phpunit --coverage-html coverage`
- [ ] Document current coverage percentage
- [ ] Identify classes with NO tests
- [ ] Identify classes with <50% coverage
- [ ] Create test_coverage_audit.md report

### Step 1.3: Architecture Documentation (30m)
- [ ] Document current pipeline flow (text-based)
- [ ] Document desired pipeline flow (object-based)
- [ ] Create UML class diagram for all major classes
- [ ] Document public API methods for AbcTune
- [ ] Identify protected properties that need API methods

---

## Phase 2: Test Creation (4h)

### Step 2.1: Core Model Tests (1.5h)
- [ ] Create `tests/AbcTuneTest.php` (if doesn't exist)
  - [ ] Test parse() with single voice
  - [ ] Test parse() with multiple voices
  - [ ] Test parse() with inline voice markers
  - [ ] Test hasVoice()
  - [ ] Test getBarsForVoice()
  - [ ] Test addVoice()
  - [ ] Test renderSelf() round-trip (parse → render → parse)

- [ ] Create `tests/AbcVoiceTest.php`
  - [ ] Test voice metadata initialization
  - [ ] Test addBar()
  - [ ] Test getBars()
  - [ ] Test addLyricsLine()
  - [ ] Test renderLyrics()

- [ ] Create `tests/AbcBarTest.php`
  - [ ] Test bar initialization
  - [ ] Test bar properties
  - [ ] Test note array handling

### Step 2.2: Transform Tests (1.5h)
- [ ] Create `tests/VoiceCopyTransformTest.php`
  - [ ] Test: Melody with bars, no Bagpipes → Copy occurs
  - [ ] Test: Melody with bars, Bagpipes with bars → No copy
  - [ ] Test: Melody with NO bars → No copy
  - [ ] Test: No Melody voice → No copy
  - [ ] Test: Multiple bars copied correctly
  - [ ] Test: Bar order preserved
  - [ ] Test: Metadata set correctly (name, sname)

- [ ] Create `tests/CanntaireachdTransformTest.php`
  - [ ] Test: Bagpipes voice gets canntaireachd
  - [ ] Test: Melody voice does NOT get canntaireachd
  - [ ] Test: Pipes voice gets canntaireachd
  - [ ] Test: P voice gets canntaireachd
  - [ ] Test: Other voices do NOT get canntaireachd
  - [ ] Test: Bar lines preserved in lyrics
  - [ ] Test: Syllables spaced correctly
  - [ ] Test: Unknown tokens handled gracefully

### Step 2.3: Integration Tests (1h)
- [ ] Create `tests/AbcPipelineIntegrationTest.php`
  - [ ] Test: Full pipeline with test-Suo.abc
  - [ ] Test: Parse → VoiceCopy → Canntaireachd → Render
  - [ ] Test: Multi-tune file processing
  - [ ] Test: Inline voice markers handled correctly
  - [ ] Test: Round-trip preservation (parse → render → parse)
  - [ ] Test: All voices preserved (Melody + Bagpipes both present)

---

## Phase 3: Design (3h)

### Step 3.1: Transform Interface Design (1h)
- [ ] Create `src/Ksfraser/Transform/AbcTransform.php` interface
  - [ ] Define transform(AbcTune $tune): AbcTune
  - [ ] Add PHPDoc with usage examples
  - [ ] Define error handling strategy

- [ ] Design Transform base class (optional)
  - [ ] Common validation logic
  - [ ] Error handling
  - [ ] Logging/debugging support

### Step 3.2: Pipeline Refactor Design (1h)
- [ ] Design new AbcProcessingPipeline API
  - [ ] Constructor: `__construct(AbcTransform[] $transforms)`
  - [ ] Method: `process(string $abcText): string`
  - [ ] Internal: parse once, apply transforms, render once

- [ ] Design Transform registration
  - [ ] Transforms as constructor dependencies
  - [ ] Allow dynamic transform ordering
  - [ ] Support conditional transforms

### Step 3.3: API Methods Design (1h)
- [ ] Review AbcTune protected properties
  - [ ] Identify properties that need public access
  - [ ] Design getter methods
  - [ ] Design setter methods (if needed)

- [ ] Design copyVoiceBars() method
  - [ ] Signature: `copyVoiceBars(string $fromId, string $toId): void`
  - [ ] Validation: source exists, source has bars
  - [ ] Behavior: copy vs reference (prefer copy)

---

## Phase 4: TDD Implementation (6h)

### Step 4.1: Implement Transform Interface (30m)
- [ ] Create AbcTransform interface
- [ ] Add comprehensive PHPDoc
- [ ] Add UML diagram in PHPDoc
- [ ] Run tests (should all still pass)

### Step 4.2: Implement VoiceCopyTransform (2h)
- [ ] Create empty VoiceCopyTransform class
- [ ] Run tests (should fail)
- [ ] Implement transform() method:
  - [ ] Check if Melody voice exists
  - [ ] Check if Melody has bars
  - [ ] Check if Bagpipes exists and has bars
  - [ ] Copy bars using addVoice() API
  - [ ] Set metadata (name, sname)
- [ ] Run tests until all pass
- [ ] Refactor for clarity and DRY

### Step 4.3: Implement CanntaireachdTransform (2h)
- [ ] Create empty CanntaireachdTransform class
- [ ] Run tests (should fail)
- [ ] Implement transform() method:
  - [ ] Iterate over all voices
  - [ ] Filter to Bagpipes-family voices only
  - [ ] For each bar:
    - [ ] Extract notes
    - [ ] Look up syllables in dictionary
    - [ ] Build w: line
  - [ ] Add w: lines to voice
- [ ] Run tests until all pass
- [ ] Refactor for clarity and DRY

### Step 4.4: Refactor Pipeline (1.5h)
- [ ] Update AbcProcessingPipeline constructor
  - [ ] Accept array of AbcTransform
  - [ ] Store transforms as property
- [ ] Update process() method:
  - [ ] Parse once: $tune = AbcTune::parse($abcText)
  - [ ] Apply transforms: foreach ($transforms as $t) { $tune = $t->transform($tune); }
  - [ ] Render once: return $tune->renderSelf()
- [ ] Update all callers of pipeline
- [ ] Run all tests
- [ ] Fix any regressions

---

## Phase 5: Cleanup & Deprecation (2h)

### Step 5.1: Remove Old Code (1h)
- [ ] Mark AbcVoicePass as @deprecated
  - [ ] Add PHPDoc warning
  - [ ] Point to VoiceCopyTransform

- [ ] Mark text-based methods as @deprecated
  - [ ] AbcProcessor::copyMelodyToBagpipes()
  - [ ] Any other text-manipulation methods

- [ ] Update CLI scripts to use new transforms
  - [ ] bin/abc-voice-pass-cli.php
  - [ ] Any other affected scripts

### Step 5.2: Update Documentation (1h)
- [ ] Update README.md with new architecture
- [ ] Update PARSER_ARCHITECTURE.md
- [ ] Add examples of using Transform API
- [ ] Document migration path from old to new
- [ ] Add troubleshooting section

---

## Phase 6: Validation (2h)

### Step 6.1: Run All Tests (30m)
- [ ] Run full test suite: `vendor/bin/phpunit`
- [ ] Verify all tests pass
- [ ] Check code coverage: ≥80% target
- [ ] Document any gaps

### Step 6.2: Regression Testing (1h)
- [ ] Test with test-Suo.abc
  - [ ] Verify V:Bagpipes section created
  - [ ] Verify Melody bars copied
  - [ ] Verify canntaireachd ONLY under Bagpipes
  - [ ] Verify NO canntaireachd under V:M

- [ ] Test with test-simple.abc
- [ ] Test with test-multi.abc
- [ ] Test with all files in tests/ directory
- [ ] Document any issues

### Step 6.3: Manual Testing (30m)
- [ ] Run CLI scripts with sample files
- [ ] Verify output formatting
- [ ] Test edge cases:
  - [ ] File with no Melody
  - [ ] File with existing Bagpipes
  - [ ] File with empty Melody (header only, no bars)
  - [ ] Multi-tune file
- [ ] Document findings

---

## Phase 7: Final Review & Deployment (30m)

### Step 7.1: Code Review (15m)
- [ ] Check all classes have PHPDoc
- [ ] Check all classes have UML in PHPDoc
- [ ] Check SOLID principles applied
- [ ] Check DRY violations removed
- [ ] Check dependency injection used

### Step 7.2: Documentation Review (15m)
- [ ] Verify README.md updated
- [ ] Verify REQUIREMENTS.md updated
- [ ] Verify PARSER_ARCHITECTURE.md updated
- [ ] Verify test_coverage_audit.md created
- [ ] Verify all diagrams render correctly

---

## Success Metrics

### Functional
- [ ] test-Suo.abc produces correct output
- [ ] V:Bagpipes section created
- [ ] Melody bars copied to Bagpipes
- [ ] Canntaireachd ONLY under Bagpipes (NOT under V:M)
- [ ] All existing tests pass
- [ ] No regressions in other test files

### Code Quality
- [ ] Test coverage ≥ 80%
- [ ] All classes have PHPDoc
- [ ] All classes have UML diagrams
- [ ] SOLID principles applied
- [ ] DRY violations eliminated
- [ ] Dependency Injection used

### Process
- [ ] TDD followed (tests before implementation)
- [ ] All phases completed
- [ ] Documentation complete
- [ ] Stakeholder approval

---

## Notes & Blockers

### Decisions Pending
- [ ] Immutable vs mutable transforms?
- [ ] Error handling strategy (exceptions vs error objects)?
- [ ] Logging/debugging approach?

### Blockers
- None yet

### Questions
- Should we support custom transform ordering?
- Should we cache parsed tunes for performance?
- Should we validate ABC notation during parse?

---

## Progress Log

### 2025-11-16

#### Phase 0: Planning & Documentation ✅ COMPLETE (2h actual)
- [x] Created REFACTOR_PLAN.md (comprehensive 19-hour plan)
- [x] Created OBJECT_MODEL_REQUIREMENTS.md (complete specification)
- [x] Created TODO.md (this file - master checklist)
- [x] Updated test_coverage_audit.md (inventory of 125 test files)
- [x] Created PROGRESS_SUMMARY.md (session summary)
- [x] Identified PHP mbstring blocker for coverage report
- [x] Created NEXT_SESSION.md (quick-start guide)

#### Phase 1: Documentation & Analysis ✅ COMPLETE (1.5h actual)
- [x] ✅ Verified PHP mbstring extension (already enabled)
- [x] ✅ Resolved merge conflicts in AbcCanntaireachdPassTest.php
- [x] ✅ Fixed syntax error in AbcBar.php (missing closing brace)
- [x] ✅ Ran test suite (354 tests, 46 errors, 49 failures - existing issues)
- [x] ✅ Documented findings: Focus on new tests, not fixing all existing

**Key Findings**:
- Many pre-existing test failures (not related to refactor)
- AbcTune::parse() exists but needs enhancement for voice parsing
- All required API methods exist: hasVoice(), getBarsForVoice(), addVoice(), getVoices()
- Focus on NEW tests for refactor (TDD approach)

#### Phase 2: Test Creation 🔄 IN PROGRESS (1h actual so far)
- [x] ✅ Created VoiceCopyTransformTest.php (14 test methods)
- [x] ✅ Created AbcTransform interface
- [x] ✅ Created VoiceCopyTransform implementation
- [x] ✅ Ran tests (14 tests, 2 errors, 4 failures - expected in TDD)
- [ ] ⬜ Fix AbcTune::parse() to properly parse voices
- [ ] ⬜ Make VoiceCopyTransform tests pass
- [ ] ⬜ Create CanntaireachdTransformTest (refactor existing)
- [ ] ⬜ Create ObjectPipelineIntegrationTest

**Current Status**:
- Tests written first (TDD) ✅
- Transform interface defined ✅
- Transform implementation created ✅
- Tests failing as expected (need parse enhancement) ⚠️
