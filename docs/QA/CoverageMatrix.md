# Unit Test Coverage Matrix for ABC Parser Refactor

| Requirement/Test Case                                 | Test File(s)                        | Status |
|------------------------------------------------------|--------------------------------------|--------|
| Parse single/multi-tune ABC files                    | AbcFileTest, AbcRenumberTunesTest    | Exists |
| Header parsing (fields, voice meta)                  | AbcHeaderFieldTableTest, AbcVoiceTest| Exists |
| Text line parsing (comments, annotations)            | AbcFileTest                          | Exists |
| Body parsing (bars, notes, lyrics)                   | AbcBarlineTest, AbcNoteTest, AbcLyricsTest | Exists |
| Canntaireachd lyrics for Bagpipe voice               | TuneServiceBagpipeTest, AbcCanntaireachdPassTest | Exists |
| Header voices render meta only in header             | AbcVoiceOrderPassTest                | Exists |
| Voice ordering in output                             | AbcVoiceOrderPassTest                | Exists |
| Round-trip rendering (parse & re-render)             | AbcFileTest, AbcProcessorConfigTest  | Exists |
| ABC spec compliance (v2.1)                           | AbcValidatorTest, AbcSanityCheckerTest| Exists |
| Cross-platform file handling (case sensitivity)       | Manual/QA Checklist                  | Manual |

## Notes
- Manual tests/checklist required for platform-specific issues.
- Add new tests as refactor progresses.
- Update matrix as coverage expands.
