# Master TODO List for ABC Canntaireachd Refactor

**Status**: Phase 2 Complete - 44% Done  
**Created**: 2025-11-16  
**Estimated**: 21.5 hours  
**Actual So Far**: 9.5 hours  
**Target Completion**: TBD

---

## ✅ Phase 1: Environment Setup (1.5h COMPLETE)

### Step 1.1: Environment Verification
- [x] Verified PHP 8.4.14 with mbstring extension enabled
- [x] Resolved merge conflicts in AbcCanntaireachdPassTest.php
- [x] Fixed syntax error in AbcBar.php (missing closing brace)
- [x] Ran test suite: 354 tests, 46 errors, 49 failures (pre-existing)

### Step 1.2: Requirements Documentation
- [x] Created OBJECT_MODEL_REQUIREMENTS.md
- [x] Documented voice markers (V: headers, [V:id] inline)
- [x] Documented voice ID variations (M/Melody, Bagpipes/Pipes/P)
- [x] Documented edge cases (no Melody, existing Bagpipes, empty bars)

### Step 1.3: Test Coverage Audit
- [x] Created test_coverage_audit.md
- [x] Identified 125 existing test files
- [x] Documented gaps: VoiceCopyTransform, CanntaireachdTransform, integration tests
- [ ] Run coverage report (deferred - not blocking)

---

## ✅ Phase 2: Transform Implementation (6h COMPLETE)

### Step 2.1: Transform Interface Design
- [x] Created AbcTransform interface
- [x] Defined transform(AbcTune): AbcTune contract
- [x] Added comprehensive PHPDoc with UML
- [x] Documented usage patterns

### Step 2.2: VoiceCopyTransform Implementation
- [x] Created VoiceCopyTransform class (172 lines)
- [x] Implemented transform() method
- [x] Added deep copy of bars to prevent object sharing
- [x] Support for M/Melody → Bagpipes/Pipes/P (case-insensitive)
- [x] Created VoiceCopyTransformTest (14 tests)
  - [x] Test: Copy when Melody exists with bars, no Bagpipes
  - [x] Test: No copy when Bagpipes exists with bars
  - [x] Test: No copy when Melody has no bars
  - [x] Test: No copy when no Melody voice
  - [x] Test: Multiple bars copied correctly
  - [x] Test: Bar order preserved
  - [x] Test: Metadata set correctly
  - [x] Test: Voice variations (M, Melody)
  - [x] Test: Case-insensitive matching
  - [x] Test: Real-world test-Suo.abc file
- [x] All 14 tests passing

### Step 2.3: CanntaireachdTransform Implementation
- [x] Created CanntaireachdTransform class (181 lines)
- [x] Implemented transform() method
- [x] Uses existing CanntGenerator for syllable generation
- [x] ONLY adds canntaireachd to Bagpipes-family voices
- [x] Does NOT add to Melody (critical business rule)
- [x] Created CanntaireachdTransformTest (12 tests)
  - [x] Test: Bagpipes voice gets canntaireachd
  - [x] Test: Melody voice does NOT get canntaireachd
  - [x] Test: Pipes voice gets canntaireachd
  - [x] Test: P voice gets canntaireachd
  - [x] Test: Case-insensitive voice matching
  - [x] Test: Multi-voice tune (M + Bagpipes)
  - [x] Test: Tune with no Bagpipes (no-op)
  - [x] Test: Tune with empty bars
  - [x] Test: Multiple bars get canntaireachd
  - [x] Test: Real-world test-Suo.abc structure
- [x] Created integration test scripts
  - [x] test_canntaireachd_transform.php (3 tests, all passing)
  - [x] test_integration_transforms.php (full pipeline, passing)

### Step 2.4: Parser Enhancements
- [x] Enhanced AbcTune::parse() to handle V: headers
- [x] Enhanced HeaderLineHandler to create voice objects
- [x] Enhanced BarLineHandler to handle [V:id] inline markers
- [x] Fixed voice assignment for bars
- [x] Removed incorrect default Bagpipes creation

### Integration Test Results
- [x] ✅ M voice (13 bars) → Bagpipes voice (13 bars) copied
- [x] ✅ Melody: NO canntaireachd
- [x] ✅ Bagpipes: HAS canntaireachd (26/27 notes)
- [x] ✅ Deep copy prevents object sharing bug

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

#### Phase 2: Test Creation 🔄 IN PROGRESS (2.5h actual so far)
- [x] ✅ Created VoiceCopyTransformTest.php (14 test methods)
- [x] ✅ Created AbcTransform interface
- [x] ✅ Created VoiceCopyTransform implementation  
- [x] ✅ Enhanced AbcTune::parse() to properly parse V: headers and [V:] inline markers
- [x] ✅ Made all 14 VoiceCopyTransform tests pass (100% success!)
- [x] ✅ Verified with test-Suo.abc (M voice bars successfully copied to Bagpipes)
- [ ] ⬜ Create CanntaireachdTransformTest (refactor existing)
- [ ] ⬜ Create ObjectPipelineIntegrationTest

**Current Status**:
- Tests written first (TDD) ✅
- Transform interface defined ✅
- Transform implementation created ✅
- AbcTune::parse() enhanced for voice parsing ✅
- **All 14 tests passing** ✅
- **Real-world test (test-Suo.abc) working** ✅
- Ready for next transform (Canntaireachd)
