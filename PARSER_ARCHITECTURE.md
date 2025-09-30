# Parser Architecture and Testing (2025 Refactor)

## Overview
This project now uses a modular, extensible ABC/canntaireachd parser inspired by EasyABC. All element parsers (notes, decorators, accidentals, etc.) provide dynamic regex patterns via static `getRegex()` methods, enabling robust ambiguity resolution and maintainability.

## Architecture
- **Element Parsers:** Each ABC element (note, decorator, accidental, chord, grace, etc.) is represented by a dedicated parser class in `src/Ksfraser/PhpabcCanntaireachd/Parser/`.
    - Each parser exposes a static `getRegex()` method for its matching pattern.
    - Parsing logic in `AbcNote.php` uses these dynamic regexes for element extraction.
- **DecoratorLoader:** Dynamically loads all decorator classes and generates a composite regex for all shortcuts and latin names, filtering out empty keys.
- **Ambiguity Resolution:** Ambiguity is resolved by prioritized regex matching and pattern order, not manual logic. The longest match wins.
- **Extensibility:** Adding new element types or shortcuts only requires updating the relevant parser or loader class.

## Testing
- **Unit Tests:** All element parsers and ambiguity logic are covered by PHPUnit tests in `tests/Parser/` and `tests/AbcNoteAmbiguityTest.php`.
    - Tests validate correct parsing, ambiguity resolution, and pitch extraction for various ABC strings.
    - Decorator extraction and shortcut handling are robustly tested.
- **Debug Logging:** Parsing steps and test assertions are logged to `debug.log` for traceability and troubleshooting.
- **How to Run:**
    - Run all tests: `vendor/bin/phpunit`
    - Run a specific test: `vendor/bin/phpunit tests/AbcNoteAmbiguityTest.php`

## Migration Notes
- Legacy manual ambiguity logic has been removed in favor of regex-based resolution.
- All element extraction is now centralized and maintainable via parser classes.

## Extending the Parser
- To add a new decorator, create a new `*Decorator.php` class and implement `getLatinName()` and `getShortcuts()`.
- To add a new element type, create a parser class with a static `getRegex()` and update `AbcNote.php` to use it.

## References
- Inspired by EasyABC: https://github.com/jwdj/EasyABC
- See `src/Ksfraser/PhpabcCanntaireachd/AbcNote.php` for main parsing logic.
- See `src/Ksfraser/PhpabcCanntaireachd/Decorator/DecoratorLoader.php` for decorator management.
