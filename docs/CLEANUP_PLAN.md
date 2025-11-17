# Code Cleanup Plan

**Date**: 2025-11-17  
**Status**: Phase 5 - Cleanup & Deprecation  
**Purpose**: Organize test files, remove debug code, prepare for production

---

## Files to Clean Up

### Test Files in Root Directory (to organize)

#### Keep (Integration/Manual Tests):
- `test_pipeline_refactor.php` - Keep (validates Phase 3)
- `test_transpose_master.php` - Keep (validates Phase 4B - runs all 28 tests)
- `test_transpose_e2e.php` - Keep (validates end-to-end transpose)
- `test_integration_transforms.php` - Keep (validates core transforms)
- `test_canntaireachd_transform.php` - Keep (validates canntaireachd)
- `test_voice_copy_transform.php` - Keep (validates voice copying)
- `test_voice_ordering_integration.php` - Keep (validates Phase 4A)

#### Move to tests/Integration/:
- `test_transpose_strategies.php` → `tests/Integration/TransposeStrategiesTest.php`
- `test_transpose_cli.php` → `tests/Integration/TransposeCliTest.php`
- `test_transpose_config.php` → `tests/Integration/TransposeConfigTest.php`
- `test_config_system.php` → `tests/Integration/ConfigSystemTest.php`
- `test_all_cli_config.php` → `tests/Integration/AllCliConfigTest.php`
- `test_voice_ordering.php` → `tests/Integration/VoiceOrderingTest.php`

#### Delete (Debug/Obsolete):
- `debug_*.php` (11 files) - Debug scripts no longer needed
- `run_test.php` - Obsolete test runner
- `run_test_fixed.php` - Obsolete test runner
- `test_autoload.php` - Replaced by proper PHPUnit bootstrap
- `test_dict.php` - Debug script
- `test_regex.php` - Debug script
- `test_trie.php` - Debug script
- `test_timing*.php` (6 files) - Performance debug scripts
- `test_cannt_simple.php` - Superseded by proper tests
- `test_cannt_generator.php` - Superseded by proper tests
- `test_melody_copy.php` - Superseded by transforms
- `test_gen.php` - Debug script
- `test_final.php` - Debug script
- `test_simple_pipeline.php` - Superseded by test_pipeline_refactor.php
- `test_voice_debug.php` - Debug script
- `test_voice_pass.php` - Superseded by transforms
- `test_transpose_all.php` - Superseded by test_transpose_master.php
- `test_transpose_integration.php` - Superseded by test_transpose_e2e.php
- `test_debug_transforms.php` - Debug script
- `test_include.php` - Debug script
- `test_cli_config.php` - Superseded by test_all_cli_config.php
- `test_token_normalizer.php` - Should be in tests/ if still needed
- `test_cannt.php` - Superseded by proper tests
- `test_new_midi_parsers.php` - Debug script

### Debug Output Files (to delete):
- `debug_output*.abc` (3 files)
- `test_output*.abc` (10+ files)
- `test_output*.txt` (1 file)
- `xresult.abc`
- `out.abc`
- `test_custom_out.abc`
- `test_Suo_fixed.abc`
- `timing_debug.txt`
- `test_results.txt`

### Log Files (to delete):
- `debug.log`
- `stderr.log`
- `stderr2.log`

### Temporary Files (to delete):
- `composer.json~`
- `php.zip`

### ABC Test Data Files (to keep):
- `test-Suo.abc` - Keep (main test file)
- `test-simple.abc` - Keep (simple test case)
- `test-multi.abc` - Keep (multi-tune test)
- `test-multi-out.abc` - Keep (expected output)
- `test-output.abc` - Keep (if used for validation)
- `test-midi-voices.abc` - Keep (MIDI test)
- `test_orchestra.abc` - Keep (orchestral test)
- `test_bad_formatting.abc` - Keep (edge case)
- `test_formatted.abc` - Keep (formatting test)
- `test_formatting.abc` - Keep (formatting test)
- `test_midi_directives.abc` - Keep (MIDI test)

---

## Deprecation Plan

### Classes to Mark @deprecated

#### AbcVoicePass (if text-based)
- Check if still using text manipulation
- If yes, mark @deprecated
- Point to VoiceCopyTransform + CanntaireachdTransform
- Add migration guide

#### Text-Based Methods in AbcProcessor
- Review all methods for text manipulation
- Mark as @deprecated if superseded by transforms
- Document transform-based alternatives

---

## CLI Scripts Audit

### Scripts to Review:
1. `bin/abc-cannt-cli.php` - Main converter
2. `bin/abc-canntaireachd-pass-cli.php` - Canntaireachd pass
3. `bin/abc-voice-pass-cli.php` - Voice pass
4. `bin/abc-voice-order-pass-cli.php` - Voice ordering
5. `bin/abc-timing-validator-pass-cli.php` - Timing validation
6. `bin/abc-lyrics-pass-cli.php` - Lyrics pass
7. `bin/abc-renumber-tunes-cli.php` - Renumbering
8. `bin/abc-reorder-tunes-cli.php` - Reordering
9. `bin/abc-header-fields-cli.php` - Header fields
10. `bin/abc-midi-defaults-cli.php` - MIDI defaults
11. `bin/abc-tune-number-validator-cli.php` - Tune validation
12. `bin/run-migrations.php` - Database migrations

### Verification Checklist:
- [ ] All scripts follow consistent pattern
- [ ] Error handling present
- [ ] Help documentation complete
- [ ] Config file support where applicable
- [ ] Proper exit codes

---

## Documentation Files to Update

### Phase 5 Updates Required:

#### README.md
- [ ] Add Phase 4 features section
  - [ ] Voice ordering (3 modes)
  - [ ] Transpose modes (3 modes, 80+ instruments)
  - [ ] Configuration system
- [ ] Update architecture overview
- [ ] Add WordPress admin pages documentation
- [ ] Update quick start examples
- [ ] Add troubleshooting section

#### PARSER_ARCHITECTURE.md
- [ ] Document Transform pattern
- [ ] Add VoiceCopyTransform architecture
- [ ] Add CanntaireachdTransform architecture
- [ ] Add AbcVoiceOrderPass architecture
- [ ] Add AbcTransposePass architecture
- [ ] Update pipeline diagram
- [ ] Update UML class diagrams

#### Create New User Guides:
- [ ] WordPress_Admin_Guide.md - How to use admin pages
- [ ] CLI_User_Guide.md - Consolidated CLI documentation
- [ ] Configuration_File_Guide.md - JSON/YAML config examples
- [ ] Migration_Guide.md - Upgrading from text-based to transform-based

---

## Cleanup Commands

### PowerShell Script to Remove Debug Files:

```powershell
# Remove debug PHP scripts
Remove-Item debug_*.php
Remove-Item run_test*.php
Remove-Item test_autoload.php
Remove-Item test_dict.php
Remove-Item test_regex.php
Remove-Item test_trie.php
Remove-Item test_timing*.php
Remove-Item test_cannt_simple.php
Remove-Item test_cannt_generator.php
Remove-Item test_melody_copy.php
Remove-Item test_gen.php
Remove-Item test_final.php
Remove-Item test_simple_pipeline.php
Remove-Item test_voice_debug.php
Remove-Item test_voice_pass.php
Remove-Item test_transpose_all.php
Remove-Item test_transpose_integration.php
Remove-Item test_debug_transforms.php
Remove-Item test_include.php
Remove-Item test_cli_config.php
Remove-Item test_token_normalizer.php
Remove-Item test_cannt.php
Remove-Item test_new_midi_parsers.php

# Remove debug output files
Remove-Item debug_output*.abc
Remove-Item test_output*.abc
Remove-Item test_output*.txt
Remove-Item xresult.abc
Remove-Item out.abc
Remove-Item test_custom_out.abc
Remove-Item test_Suo_fixed.abc
Remove-Item timing_debug.txt
Remove-Item test_results.txt

# Remove log files
Remove-Item debug.log
Remove-Item stderr.log
Remove-Item stderr2.log

# Remove temporary files
Remove-Item composer.json~
Remove-Item php.zip
```

---

## Success Criteria

### Code Organization:
- [ ] No test_*.php files in root (except integration tests)
- [ ] No debug_*.php files
- [ ] No temporary output files
- [ ] All tests in tests/ directory structure

### Documentation:
- [ ] README.md updated with Phase 4 features
- [ ] PARSER_ARCHITECTURE.md updated
- [ ] User guides created (WordPress, CLI, Config)
- [ ] Migration guide created

### Code Quality:
- [ ] Deprecated code marked with @deprecated
- [ ] Migration paths documented
- [ ] All CLI scripts audited
- [ ] Consistent patterns throughout

---

## Execution Order

1. **Backup First**: Create git commit before cleanup
2. **Document**: Complete this cleanup plan
3. **Test**: Run test suite to ensure all passing
4. **Delete**: Remove debug and temporary files
5. **Move**: Organize test files into proper structure
6. **Deprecate**: Mark old code with @deprecated
7. **Document**: Update README, PARSER_ARCHITECTURE, create guides
8. **Test Again**: Verify nothing broken
9. **Commit**: Create clean commit with organized structure

---

*Next: Execute cleanup commands and update documentation*
