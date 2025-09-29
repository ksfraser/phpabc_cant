# QA Plan for ABC Parser Refactor

## Test Scenarios
- Parse single and multi-tune ABC files, verify correct tune splitting.
- Validate header parsing: all standard fields (X, T, C, M, L, Q, K, etc.), voice meta (name, sname, etc.).
- Validate text line parsing: comments, annotations, and non-music lines.
- Validate body parsing: bars, notes, lyrics, and correct class instantiation.
- Ensure Bagpipe voice receives canntaireachd lyrics (w:) line below notation.
- Ensure Tune Header Voices render name, sname, etc. only in header, not in body.
- Ensure voices are rendered in correct order.
- Round-trip rendering: parsed and re-rendered file matches original ABC structure.
- Regression tests for ABC spec compliance (v2.1).
- Cross-platform file handling: verify case sensitivity and file operations on Linux and Windows.

## Regression Checks
- No loss of tune, header, or body data after parsing and rendering.
- No accidental clobbering of files with case-sensitive names.
- No duplicate or missing voice meta in output.
- No missing canntaireachd lyrics for Bagpipe voice.

## ABC Spec Compliance
- All parsing and rendering logic must conform to ABC Notation Standard v2.1.
- Header, body, and voice handling must match spec requirements.

## QA Artifacts
- Test case matrix mapping requirements to tests.
- PHPUnit test suite for all parsing and rendering logic.
- Manual test checklist for edge cases and platform-specific issues.

## References
- ABC Notation Standard v2.1: https://abcnotation.com/wiki/abc:standard:v2.1
- Project Requirements and Architecture Documents
