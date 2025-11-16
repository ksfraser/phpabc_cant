# Quick Reference - Next Session Start

**Date**: 2025-11-16  
**Context**: Refactoring ABC→Canntaireachd from text-based to object-based architecture

---

## Current Status
✅ **Phase 0 Complete**: All planning and documentation done  
⏳ **Phase 1 Ready**: Waiting for PHP mbstring fix

---

## Immediate Actions (First 15 Minutes)

### 1. Fix PHP Environment
```powershell
# Check if mbstring is available
php -m | Select-String mbstring

# If not found, enable in php.ini
# Edit: C:\php-8\php.ini or C:\php\php.ini
# Uncomment: extension=mbstring

# Verify after restart
php -m | Select-String mbstring
```

### 2. Run Coverage Report
```powershell
cd c:\Users\prote\phpabc_cant
vendor\bin\phpunit --coverage-text --coverage-html coverage
```

### 3. Review Coverage Numbers
- Open `coverage/index.html` in browser
- Document actual percentage in `test_coverage_audit.md`
- Identify classes with 0% coverage

---

## Documents to Review (5 Minutes)

| Document | Purpose | Key Sections |
|----------|---------|--------------|
| PROGRESS_SUMMARY.md | Session overview | Problem Statement, Solution Approach, Next Actions |
| OBJECT_MODEL_REQUIREMENTS.md | Complete spec | Melody-to-Bagpipes Copy, Canntaireachd Rules |
| TODO.md | Master checklist | Phase 1 tasks, Phase 2 test list |
| test_coverage_audit.md | Test inventory | Missing Tests section |

---

## What We're Building

### Problem
- Canntaireachd appearing under `V:M` (Melody) ❌
- No `V:Bagpipes` section created ❌
- Should be: Melody bars → copied to Bagpipes → canntaireachd ONLY under Bagpipes ✅

### Solution
1. **VoiceCopyTransform**: Copy Melody bars to Bagpipes (if Bagpipes doesn't have bars)
2. **CanntaireachdTransform**: Add syllables ONLY to Bagpipes-family voices
3. **Refactored Pipeline**: Parse once → Transform → Render once

### Architecture Change
```
OLD: Lines → Pass1(text) → Pass2(text) → Lines
NEW: Lines → Parse → Transform1(Tune) → Transform2(Tune) → Render → Lines
```

---

## Next Tasks (Phase 1 - 2h)

### Step 1.1: Requirements Documentation ✅ DONE
- [x] Created OBJECT_MODEL_REQUIREMENTS.md
- [ ] Review with stakeholder (optional)

### Step 1.2: Test Coverage Audit (1h) ⬅️ START HERE
1. Fix PHP mbstring (15m)
2. Run coverage report (5m)
3. Document actual coverage % (10m)
4. Update test_coverage_audit.md with:
   - Actual coverage numbers
   - Classes with 0% coverage
   - Critical gaps confirmed

### Step 1.3: Architecture Documentation (30m)
- [ ] Document current pipeline flow (text-based)
- [ ] Document desired pipeline flow (object-based)
- [ ] Create UML class diagram for major classes
- [ ] Document public API methods for AbcTune

---

## Key Files to Know

### Source Code
```
src/Ksfraser/.../
  AbcTune.php              # Core object model
  AbcVoice.php             # Voice container
  AbcBar.php               # Bar container
  AbcProcessingPipeline.php # Current text-based pipeline
  AbcCanntaireachdPass.php # Current canntaireachd (text-based)
```

### Tests to Create (Phase 2)
```
tests/
  Transform/
    VoiceCopyTransformTest.php      # NEW - 12+ test methods
    CanntaireachdTransformTest.php  # REFACTOR existing
    AbcTransformInterfaceTest.php   # NEW - interface contract
  Integration/
    ObjectPipelineIntegrationTest.php # NEW - full pipeline
```

### Test Input File
```
test-Suo.abc  # Has V:M with inline [V:M] markers
```

---

## Expected Test Output (Success)

```abc
V:M name="Melody" clef=treble
[V:M] {g}A3B {g}ce3 | {g}B3A {g}c{d}B3 |

V:Bagpipes name="Bagpipes" clef=treble
[V:Bagpipes] {g}A3B {g}ce3 | {g}B3A {g}c{d}B3 |
w: hen o ho e | ho en ho do |
```

✅ V:Bagpipes section created  
✅ Melody bars copied to Bagpipes  
✅ Canntaireachd ONLY under V:Bagpipes (NOT under V:M)

---

## Commands Cheat Sheet

### Test Execution
```powershell
# Run all tests
vendor\bin\phpunit

# Run with coverage
vendor\bin\phpunit --coverage-text

# Run specific test file
vendor\bin\phpunit tests/Tune/AbcTuneTest.php

# Run with testdox (readable output)
vendor\bin\phpunit --testdox
```

### Code Search
```powershell
# Find test files
Get-ChildItem -Path tests -Filter "*Test.php" -Recurse

# Search for class definition
Select-String -Path "src\**\*.php" -Pattern "class AbcTune"

# Search for method
Select-String -Path "src\**\*.php" -Pattern "public function parse"
```

---

## Key Decisions (Don't Forget!)

1. **Use existing AbcTune::parse()** - Don't create new parser
2. **Use AbcTune::addVoice()** - Don't access protected properties directly
3. **TDD approach** - Write tests FIRST, then implement
4. **Bar-level operations** - Work on Bar objects, not text lines
5. **Transform interface** - `transform(AbcTune): AbcTune`

---

## Questions for Stakeholder (If Needed)

1. Immutable vs mutable transforms?
2. Error handling strategy?
3. Custom transform ordering?
4. Cache parsed tunes?

---

## Success Criteria Checklist

### Functional
- [ ] Melody bars copied to Bagpipes when needed
- [ ] Canntaireachd ONLY on Bagpipes voice
- [ ] Melody voice has NO canntaireachd
- [ ] test-Suo.abc produces correct output

### Code Quality
- [ ] SOLID principles followed
- [ ] Test coverage ≥ 80%
- [ ] All classes have PHPDoc with UML
- [ ] All tests pass

---

## Timeline Remaining

| Phase | Time | Status |
|-------|------|--------|
| ✅ 0. Planning | 2h | DONE |
| 1. Analysis | 2h | ← YOU ARE HERE |
| 2. Tests | 4h | - |
| 3. Design | 3h | - |
| 4. Implementation | 6h | - |
| 5. Cleanup | 2h | - |
| 6. Validation | 2h | - |
| 7. Review | 0.5h | - |
| **Remaining** | **19.5h** | 9% complete |

---

## Critical Path

1. Fix mbstring → Run coverage → Document gaps
2. Create VoiceCopyTransformTest (write all tests)
3. Create AbcTransform interface
4. Implement VoiceCopyTransform (make tests pass)
5. Refactor CanntaireachdTransform (update for objects)
6. Update Pipeline (accept transforms array)
7. Validate with test-Suo.abc

---

## Don't Get Sidetracked!

❌ Don't refactor parsers (already working)  
❌ Don't refactor renderers (already working)  
❌ Don't add new features  
✅ DO focus on: Melody→Bagpipes copy + Canntaireachd placement  
✅ DO follow TDD cycle: Test → Implement → Refactor  
✅ DO run full test suite after each change

---

**Ready to Start**: Fix PHP mbstring, run coverage, begin Phase 1
