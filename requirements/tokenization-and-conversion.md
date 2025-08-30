# Tokenization and Conversion Requirements

## Functional Requirements
- The parser must load files in ABC, canntaireachd, or BMW format.
- The parser must tokenize all formats into a common set of tokens (using AbcNote as the master token).
- The parser must support format-specific header parsing.
- The converter must output ABC, canntaireachd, or BMW from token arrays.
- AbcNote must support lyrics, canntaireachd, solfege, and BMW tokens.
- Decorator classes must exist for all valid ABC decorators.
- The parser must throw a custom error for invalid note lengths (three or more slashes).
- The parser must log a warning if multiple invalid note lengths are found in a bar.
- The token dictionary must include BMW tokens for all basic notes and gracenotes, and populate them during prepopulation and conversion.

## Non-Functional Requirements
- All classes must be PSR-4 compliant.
- All conversion logic must be extensible for future formats.
- All errors and warnings must be logged for review.

## Test Cases
- Parse ABC, canntaireachd, and BMW files and verify correct tokenization.
- Convert token arrays to ABC, canntaireachd, and BMW and verify output.
- Verify AbcNote properties and rendering for all supported fields.
- Verify decorator classes render correct symbols.
- Verify custom error is thrown for three or more slashes in note length.
- Verify warning is logged if multiple invalid note lengths are found in a bar.
- Verify that BMW tokens are present for all basic notes and gracenotes in the token dictionary.
- Verify that BMW tokens are correctly populated during prepopulation and conversion.
- Verify conversion logic for ABC <-> BMW <-> canntaireachd for all dictionary entries.
