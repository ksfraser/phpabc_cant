# Functional and Nonfunctional Requirements Document

## 1. Functional Requirements

### 1.1 ABC/Canntaireachd Parsing
- The system shall parse ABC notation files and canntaireachd lyrics, supporting all elements defined in the ABC spec.
- The parser shall split files into tunes, tunes into voices, voices into bars, and bars into notes using recursive descent.
- The parser shall support round-trip rendering (parse → model → output) for ABC and canntaireachd.
- The parser shall support Bagpipe voice and canntaireachd lyrics mapping.
- The parser shall support user-defined symbols via the U: field.
- The parser shall detect and resolve ambiguities in decorator shortcuts and note elements.
- The parser shall support composite elements (e.g., chords, directives, annotations).
- The parser shall handle invalid characters and report errors.

### 1.2 Modular Architecture
- Each parser class (file, tune, voice, bar, note) shall only handle its scope and delegate parsing to child elements.
- Decorator and note element shortcut maps shall be dynamically built and injected.
- All ABC spec elements shall have dedicated parser/model classes.

### 1.3 Extensibility
- The system shall allow for easy addition of new ABC elements and decorators.
- Regex patterns for parsing shall be auditable and extendable.

### 1.4 Testing and Validation
- The system shall provide unit tests for all parser/model classes.
- The system shall provide integration tests for parsing pipelines.
- The system shall support acceptance and UAT testing for real-world ABC/canntaireachd files.

## 2. Nonfunctional Requirements

### 2.1 Performance
- The parser shall process typical ABC files (<10,000 lines) in under 2 seconds.

### 2.2 Compatibility
- The system shall be compatible with PHP 7.3+.
- The system shall use Composer PSR-4 autoloading.

### 2.3 Documentation
- All public classes and methods shall have PHPDoc blocks.
- UML diagrams shall be provided for major parser/model classes.
- Project documentation shall describe parser architecture, class responsibilities, and ambiguity handling.

### 2.4 Error Handling
- The system shall log and report all parsing errors, warnings, and ambiguities.

### 2.5 Maintainability
- The codebase shall be modular, readable, and follow best practices for PHP projects.

### 2.6 Security
- The system shall validate and sanitize all input files to prevent code injection or parsing exploits.

---

# Testing Traceability Matrix

| Req ID | Requirement Description | Unit Test | Integration Test | Acceptance Test | UAT |
|--------|------------------------|-----------|------------------|-----------------|-----|
| FR-1.1 | Parse ABC/canntaireachd files | ✓ | ✓ | ✓ | ✓ |
| FR-1.2 | Recursive descent parsing | ✓ | ✓ | ✓ | ✓ |
| FR-1.3 | Round-trip rendering | ✓ | ✓ | ✓ | ✓ |
| FR-1.4 | Bagpipe voice/canntaireachd mapping | ✓ | ✓ | ✓ | ✓ |
| FR-1.5 | User-defined symbols (U:) | ✓ | ✓ | ✓ | ✓ |
| FR-1.6 | Ambiguity resolution | ✓ | ✓ | ✓ | ✓ |
| FR-1.7 | Composite elements | ✓ | ✓ | ✓ | ✓ |
| FR-1.8 | Invalid character handling | ✓ | ✓ | ✓ | ✓ |
| FR-2.1 | Performance (<2s parse) |   | ✓ | ✓ | ✓ |
| FR-2.2 | PHP 7.3+ compatibility | ✓ | ✓ | ✓ | ✓ |
| FR-2.3 | Composer autoloading | ✓ | ✓ | ✓ | ✓ |
| FR-2.4 | PHPDoc/UML documentation | ✓ |   | ✓ | ✓ |
| FR-2.5 | Error logging/reporting | ✓ | ✓ | ✓ | ✓ |
| FR-2.6 | Maintainable codebase | ✓ | ✓ | ✓ | ✓ |
| FR-2.7 | Input validation/sanitization | ✓ | ✓ | ✓ | ✓ |

Legend: ✓ = Test coverage planned/implemented

---

## Notes
- Unit tests: parser/model class methods, shortcut/ambiguity logic, error handling.
- Integration tests: end-to-end parsing pipeline, round-trip rendering, file splitting.
- Acceptance tests: real-world ABC/canntaireachd files, spec compliance.
- UAT: user scenarios, Bagpipe/canntaireachd mapping, extensibility, error reporting.
