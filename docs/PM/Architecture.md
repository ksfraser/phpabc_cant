# ABC Parser Refactor Architecture

## Overview
This document describes the architecture for the refactored ABC file parser, including class structure, parsing flow, rendering logic, and canntaireachd integration.

## High-Level Design
- **AbcFileParser**: Entry point for parsing ABC files. Splits file into tunes, delegates parsing to `AbcTune`.
- **AbcTune**: Represents a single tune. Holds header, text lines, and body. Provides methods for parsing and rendering.
- **AbcHeader**: Represents tune header fields (X, T, C, M, L, Q, K, etc.). Handles voice meta (name, sname, etc.).
- **AbcVoice**: Represents a voice, including meta and ordering.
- **AbcBar**: Represents a bar/measure in the body.
- **AbcNote**: Represents a note or rest.
- **AbcLyrics**: Represents lyrics lines (w:), including canntaireachd for Bagpipe voice.
- **CanntGenerator**: Generates canntaireachd text for Bagpipe voice.

## Parsing Flow
1. **File Split**: `AbcFileParser` splits file into tunes using ABC spec markers (X: field, blank lines, etc.).
2. **Tune Parsing**: For each tune:
   - Parse header fields into `AbcHeader` objects.
   - Parse text lines (comments, annotations).
   - Parse body into `AbcBar`, `AbcNote`, and `AbcLyrics` objects.
3. **Voice Handling**:
   - Header voices rendered with name, sname, etc. if set.
   - Body voices rendered in specified order.
4. **Canntaireachd Integration**:
   - For Bagpipe voice, generate and insert canntaireachd lyrics (w:) below notation.

## Rendering Logic
- Each class (`AbcTune`, `AbcHeader`, `AbcBar`, etc.) provides a `render()` method to output ABC text.
- Round-trip rendering: Parsed and re-rendered file matches original structure.
- Header voices rendered with meta only in header, not in body.
- Voices rendered in correct order.

## Extensibility
- Modular class design for easy extension (e.g., new header fields, voice types).
- Canntaireachd generator can be replaced or extended.

## References
- ABC Notation Standard v2.1: https://abcnotation.com/wiki/abc:standard:v2.1
- Project Requirements Document
