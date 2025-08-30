# Requirements: Barline Rendering

## Functional Requirements
- The parser must recognize all ABC barline types: |, ||, |:, :|, [:, :].
- Each barline type must be represented by a dedicated renderer class (SimpleBarLineRenderer, DoubleBarLineRenderer, etc.).
- The AbcBar class must use the correct renderer for its barline type.
- The main tune render must call the barline renderer for each bar.
- Output must match the ABC standard for barline symbols.

## Non-Functional Requirements
- Renderer classes must be PSR-4 compliant and autoloadable.
- Barline renderer classes must be unit tested for correct output.
- The parser must be extensible for future barline types.

## Test Cases
- Parse a tune with each barline type and verify the correct renderer is used.
- Render each barline type and verify output matches expected ABC symbol.
- Changing a barline type in AbcBar should change the renderer and output.
