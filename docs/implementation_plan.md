Project implementation plan and summary

Overview
- Goal: parse one or more ABC files (each file may contain multiple tunes), tokenize tunes into notes/barlines/voices/lyrics/canntaireachd (BMW), fix formatting, reorder voices according to DB order, ensure bagpipe voice has canntaireachd (generate from melody when missing), and output validated ABC.

Planned work (ordered)
1) Introduce a ParseContext class to hold parsing state (current voice, current bar, voice bars reference).
   - Implements ArrayAccess for compatibility with existing handlers.
   - Provide helpers: getOrCreateVoice(), incrementBar(), etc.

2) Update AbcTune::parseBodyLines to use ParseContext.
   - Ensure default melody voice is created when notes appear prior to any V: header.
   - Avoid null-offset errors and centralize state.

3) Implement TuneService with ensureBagpipeVoice(AbcTune $tune)
   - If a bagpipe voice exists (by known aliases), validate canntaireachd.
   - If missing, find 'M' or 'Melody' voice, copy bars/lyrics to a new bagpipe voice, and generate canntaireachd (skeleton CanntGenerator).

4) Fix fixVoiceHeaders/getLines
   - Implement AbcTune::getLines() to return header/voice header objects for modification.
   - Re-enable fixVoiceHeaders by operating over those returned lines.

5) Add CanntaireachdGenerator skeleton
   - Tokenize note sequences and map tokens to cannt tokens using dictionary (longest-match-first).
   - Provide a validation report and optional auto-fill.

6) Tests
   - Unit tests for ParseContext and handlers.
   - Integration tests for parsing tunes with no V: (default melody), multi-voice tune missing bagpipe, and voice reordering.
   - End-to-end CLI test on test-multi.abc.

Notes on design
- Keep handlers small and single-responsibility (barline, lyrics, canntaireachd, solfege, notes).
- Use dictionary-driven tokenizer implemented elsewhere in project for mapping notes->cannt tokens.
- Keep fixes configurable (auto-apply vs report-only) and log all automated fixes.

Next action: implement ParseContext and wire into AbcTune::parseBodyLines.
