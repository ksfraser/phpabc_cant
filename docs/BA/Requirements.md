# ABC Parser Refactor Requirements

## Overview
This document outlines the requirements for refactoring the ABC file parser to comply with the ABC notation standard (https://abcnotation.com/wiki/abc:standard:v2.1) and project goals.

## Functional Requirements
- Parse one or more ABC files, splitting content into individual Tunes.
- For each Tune, subdivide into:
  - Header (metadata, voices, etc.)
  - Text lines (comments, annotations)
  - Body (music notation)
- Parse the Body into class objects (bars, notes, lyrics, etc.)
- Render Bagpipe voice with canntaireachd "lyrics" (w:) line below music notation.
- Render Tune Header Voices with name, sname, etc. if set (not in body).
- Render voices in the order specified in the source or requirements.
- Support round-trip rendering (parsed and re-rendered file matches original structure).

## Non-Functional Requirements
- Modular, maintainable codebase (PSR-4, Composer autoload).
- Unit test coverage for all parsing and rendering logic.
- Documentation for requirements, architecture, QA, and test coverage.
- Compatible with Linux and Windows (case sensitivity, file handling).

## Out of Scope
- ABC playback, MIDI synthesis, or UI features.

## References
- ABC Notation Standard v2.1: https://abcnotation.com/wiki/abc:standard:v2.1
- Project source code and existing documentation.
