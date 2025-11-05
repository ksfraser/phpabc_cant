# LLM.MD — Living Library of Methods & Meta-Development Principles

## 0. Requirements, Traceability, and Documentation

- **Automated Documentation Generation:**
  - Use phpDocumentor to generate HTML documentation for the project.
  - **IN EACH CLASS AND FUNCTION WE SHOULD HAVE PHPDOC'S UML CODE** - All classes must include UML diagrams describing the class structure, message flows, and variable passing.
  - **WE WANT TO HAVE A CTAGS TYPE RELATIONSHIP DIAGRAM OF CLASSES. THIS CAN BE IN UML** - Include class relationship diagrams showing inheritance, composition, and dependencies.
  - UML and message flow diagrams should be included in phpdoc blocks or as referenced images in the documentation output.
  - Ensure documentation is updated as code changes, and that generated HTML docs are reviewed for completeness and accuracy.
  - **WE NEED A SCRIPT TO RUN THE PHPDOC PROCESS SO THAT ALL CODE AND THEIR DOCUMENTATION ARTIFACTS ARE GENERATED INTO THE HTML WITH JPG/PNG** - Use the `generate-docs.sh` script to automate documentation generation.

- **Architectural Document Maintenance:**
  - **AS WE WRITE CLASSES AND FUNCTIONS, WE NEED TO UPDATE A 2ND VERSION OF THE ARCHITECTURAL DOCUMENTS TO REFERENCE THESE CLASSES AND FCNS SO THAT MESSAGE FLOW CAN BE EASILY REFERENCED IN THE DOCS**.
  - Maintain dual documentation approach:
    - **Version 1**: High-level architectural documents (System Architecture, Process Models, etc.)
    - **Version 2**: Implementation-specific documents that map classes/functions to architectural components
  - Update architectural documents whenever new classes or functions are added
  - Include message flow diagrams showing how classes interact within the architectural framework
  - Reference specific class names, methods, and their locations in the codebase
  - Ensure traceability from architectural diagrams to actual implementation

- **Requirements Capture:**
  - All discussions and instructions in our chat should result in either functional or non-functional requirements.
  - Each requirement must be recorded in a traceability matrix, listing which class/function/module fulfills it.
  - **ALL CODE GENERATED MUST REFERENCE THE REQUIREMENT IT IS DESIGNED TO TAKE CARE OF** - Include requirement ID/reference in phpdoc blocks and comments.

- **Traceability Matrix:**
  - Maintain a matrix mapping requirements to implementation (class/function/file).
  - Update the matrix as code evolves to ensure all requirements are covered and testable.
  - All unit tests must map back to specific requirements in the traceability matrix.

- **Project Documentation Standards:**
  - Maintain the following documentation for every project:
    - Requirements Traceability Matrix
    - QA Test Plan (manual and automated)
    - UAT (User Acceptance Test) checklist for manual validation
    - PHPUnit (or equivalent) automated test suite
    - Architectural Design Documents
    - UML Class Diagrams
    - ERD (Entity Relationship Diagram) for data models
    - Message Flow and Program Flow diagrams
  - Keep documentation up to date as the project evolves.

- **Unit Testing Requirements:**
  - **WE MUST HAVE UNIT TESTS FOR ALL CODE** - Every class, method, and function must have corresponding unit tests.
  - **ALL UNIT TESTS SHOULD BE ABLE TO MAP INTO OUR TESTING DOCUMENTS** - Tests must reference requirement IDs and be traceable.
  - Use PHPUnit or equivalent testing framework.
  - Tests should cover happy path, edge cases, and error conditions.
  - Implement Test-Driven Development (TDD) where possible.


## 1. Core Principles

- **Open/Closed Principle (OCP):**
  - Classes should be open for extension but closed for modification. Use interfaces, abstract classes, and event hooks to allow new features without changing existing code.
- **Interface Segregation Principle (ISP):**
  - Prefer several small, specific interfaces over large, general-purpose ones.
- **Liskov Substitution Principle (LSP):**
  - Subclasses should be substitutable for their base classes without altering the correctness of the program.
- **Separation of Concerns (SoC):**
  - Each module or layer should have a distinct responsibility (e.g., data access, business logic, presentation).
- **Immutability:**
  - Use immutable value objects for data that should not change after creation.
- **Graceful Degradation & Progressive Enhancement:**
  - Design UI and APIs to degrade gracefully if features are unavailable, and enhance when possible.
- **Internationalization (i18n) & Localization (l10n):**
  - Plan for multi-language support in UI and data models.
- **Accessibility (a11y):**
  - Ensure UI components are accessible to all users, including those using assistive technologies.
- **Performance Optimization:**
  - Profile and optimize code for speed and memory usage, especially in data-heavy or real-time applications.
- **Error Handling Strategy:**
  - Use consistent error handling patterns (exceptions, error objects, etc.) and document expected error flows.
- **Extensibility & Plugin Architecture:**
  - Design core systems to allow third-party plugins or modules to extend functionality without modifying core code.
- **Backward Compatibility:**
  - When possible, avoid breaking changes to public APIs and document any that are necessary.
- **Automated Code Quality Tools:**
  - Use static analysis, code sniffers, and linters (e.g., PHPStan, PHPCS) as part of CI/CD.

- **KISS (Keep It Simple, Stupid):**
  - Favor simple, clear solutions over clever or complex ones.
- **YAGNI (You Aren’t Gonna Need It):**
  - Don’t implement features until they are actually needed.
- **Fail Fast & Defensive Programming:**
  - Validate inputs early, throw exceptions on invalid state, and fail fast to catch bugs early.
- **Consistent Naming Conventions:**
  - Use clear, descriptive, and consistent names for classes, functions, and variables.
- **Code Reviews & Pair Programming:**
  - All significant changes should be reviewed by another developer.
- **Automated CI/CD:**
  - Set up continuous integration and deployment pipelines (e.g., GitHub Actions) to run tests and deploy on push.
- **Security by Design:**
  - Sanitize all inputs, use prepared statements, and follow least privilege principles.
- **Logging & Monitoring:**
  - Implement structured logging with configurable levels (error, warning, info, debug).
  - Logging level should be settable via config file or MySQL DB table.
- **Version Control Best Practices:**
  - Use feature branches, meaningful commit messages, and regular merges to main.
- **Documentation as Code:**
  - Keep documentation in version control, update alongside code changes.

- **Single Responsibility Principle (SRP):**
  - **ALL CLASSES ARE TO BE SRP WHERE POSSIBLE** - Each class or module should do one thing only.
  - Keep classes small, focused, and easy to test.
- **Minimalism:**
  - Use minimal variables and concise functions.
  - Avoid unnecessary complexity.
- **Dependency Injection:**
  - **WE WANT SOLID AND DRY CODE THAT USES DI** - Pass dependencies into classes/functions instead of hard-coding them.
  - Enables easier testing and flexibility.
- **SOLID Principles:**
  - **S**ingle Responsibility - Each class has one reason to change
  - **O**pen/Closed - Open for extension, closed for modification
  - **L**iskov Substitution - Subtypes must be substitutable for their base types
  - **I**nterface Segregation - Clients should not be forced to depend on interfaces they don't use
  - **D**ependency Inversion - Depend on abstractions, not concretions
- **DRY (Don't Repeat Yourself):**
  - Reuse code, avoid duplication, and extract common logic.
- **Replace If-Then-Else with Polymorphism:**
  - **THERE SHOULD BE FEW IF/THEN/ELSE BLOCKS NOR SWITCH STATEMENTS** - Where possible, refactor if-then-else or switch-case logic into class hierarchies (Strategy, State, Command, etc.) to eliminate the need for else statements.
  - **INSTEAD USE CLASSES (PER FOWLER) WHERE IT RETURNS THE OUTPUT THE IF'D FUNCTION WOULD HAVE, OR RETURN NOTHING (NULL)**.
  - This leads to more maintainable, extensible, and testable code, as advocated by Martin Fowler.

## 2. Architectural Patterns

- **Event-Driven Architecture:**
  - For all but the simplest apps, use an event-driven architecture to decouple components and enable extensibility.
  - Design modules/classes to register themselves for the events or actions they handle.
  - The system should automatically discover and include new modules/classes, and remove those that are deleted, without manual wiring.
  - Modules should declare their responsibilities and event hooks at registration.
  - Where possible, mimic or interoperate with WordPress's event (hook/action/filter) system to maximize compatibility with WP and similar frameworks.
  - Ensure the event system can coexist with other frameworks (e.g., WP, FrontAccounting) and does not interfere with their core event handling.

- **REST/SOAP API Layer:**
  - Build REST (and/or SOAP) APIs for data access on top of DAOs and Models.
  - APIs should be framework-agnostic and reusable.
- **Database Abstraction Layer:**
  - Implement a DB layer that can interface with multiple frameworks (standalone, WordPress, FrontAccounting, etc.).
  - Design for drop-in replacement and easy integration.
- **PSR Compliance:**
  - Follow PHP-FIG PSRs (e.g., PSR-1, PSR-2, PSR-4, PSR-12, PSR-7, PSR-11, PSR-17) as closely as possible for code, autoloading, HTTP, containers, etc.
- **Database Migration/Update Scripts:**
  - Implement or reuse migration scripts (like FA/SuiteCRM) that compare current schema to new and apply changes safely.

- **MVC (Model-View-Controller):**
  - **SEPARATION OF CONCERNS WHETHER MVC OR SOME OTHER FRAMEWORK PATTERN LIKE SYMFONY**.
  - Use for clear separation of concerns.
  - Applies to standalone apps, WordPress plugins, and FrontAccounting modules.
  - Controllers handle requests, Models manage data/business logic, Views handle presentation.
- **Plugin/Extension Design:**
  - Place generic, reusable code in separate modules or Composer packages.
  - Keep app-specific business logic and UI separate from reusable libraries.
- **Framework-Agnostic Code:**
  - Write code that can be reused across different frameworks and projects.

## 3. Code Organization & Reuse

- **Composer Packages:**
  - **USE WELL MAINTAINED AND TESTED COMPOSER PLUGINS WHERE POSSIBLE INSTEAD OF WRITING OUR OWN CODE**.
  - Extract reusable components into Composer packages for sharing and maintenance.
  - Research and evaluate existing packages before implementing custom solutions.
  - Prefer packages with active maintenance, good test coverage, and community adoption.
  - Document package choices and rationale in the project README.
- **Clear Boundaries:**
  - Distinguish between generic libraries and application-specific code.
- **Documentation:**
  - Document patterns, decisions, and reusable modules for onboarding and consistency.

## 4. Practical Guidelines

- **Custom Exceptions:**
  - Define and use custom exception classes for different error conditions instead of relying solely on generic exceptions.
  - Use exception hierarchies to represent different error types and enable precise error handling.
- **Replace If-Then-Else with Polymorphism:**
  - **THERE SHOULD BE FEW IF/THEN/ELSE BLOCKS NOR SWITCH STATEMENTS** - Where possible, refactor if-then-else or switch-case logic into class hierarchies (Strategy, State, Command, etc.) to eliminate the need for else statements.
  - **INSTEAD USE CLASSES (PER FOWLER) WHERE IT RETURNS THE OUTPUT THE IF'D FUNCTION WOULD HAVE, OR RETURN NOTHING (NULL)**.
  - This leads to more maintainable, extensible, and testable code, as advocated by Martin Fowler.
  - Example: Instead of `if ($type === 'pdf') { return new PDFGenerator(); } elseif ($type === 'excel') { return new ExcelGenerator(); }`, use a factory or strategy pattern with dedicated classes.

- **Test-Driven Development (TDD):**
  - Write the unit test for new functionality first. The test should initially fail.
  - Implement the new code so that the test passes.
  - Each code unit should indicate (in comments or docblocks) which requirement it fulfills.
- **UI/View Code Generation:**
  - Generate UI/View code as classes with `render()` functions for output.
  - Keep rendering logic separate from business logic.
- **Business Logic Composition:**
  - Business logic classes should compose (hold) other classes representing elemental types (e.g., int, double, string).
  - Elemental classes should have sanity checks in their setters to validate values.
  - The existing `ORIGIN` class with a `set(field, value, enforce)` signature can be reused or extended for these elemental types.

- **Requirement Traceability in Code:**
  - **ALL CODE GENERATED MUST REFERENCE THE REQUIREMENT IT IS DESIGNED TO TAKE CARE OF**.
  - Include requirement ID/reference in phpdoc blocks using custom tags like `@requirement REQ-001` or `@covers-requirement REQ-001`.
  - Add comments in code indicating which requirement is being fulfilled.
  - Ensure all classes and methods can be traced back to specific requirements in the traceability matrix.

- **PHP Documentation Standards:**
  - **WE WILL USE PHPDOCUMENT BLOCKS AND TAGS** - All classes, methods, and functions must have comprehensive phpdoc blocks.
  - **AS PART OF THAT BLOCK SHOULD BE THE REFERENCE TO THE REQUIREMENTS** - Include requirement IDs using custom tags like `@requirement REQ-001` or `@covers-requirement REQ-001`.
  - Include @param, @return, @throws, @author, @since, @package tags as appropriate.
  - Reference requirement IDs in @covers or custom @requirement tags.
  - Include UML diagrams in docblocks where relevant.
  - **IN EACH CLASS AND FUNCTION WE SHOULD HAVE PHPDOC'S UML CODE** - Embed UML class diagrams and sequence diagrams in phpdoc blocks.

- **UML and Class Relationship Diagrams:**
  - **WE WANT TO HAVE A CTAGS TYPE RELATIONSHIP DIAGRAM OF CLASSES. THIS CAN BE IN UML** - Create comprehensive class relationship diagrams showing:
    - Inheritance hierarchies (extends relationships)
    - Interface implementations (implements relationships)
    - Composition and aggregation relationships
    - Dependency relationships between classes
    - Package/namespace organization
  - Use tools like phpDocumentor with GraphViz to automatically generate relationship diagrams
  - Include both high-level architectural diagrams and detailed class-level diagrams
  - Update diagrams whenever class relationships change
  - Embed relevant UML snippets in phpdoc blocks for individual classes

- **Composer Package Usage:**
  - **USE WELL MAINTAINED AND TESTED COMPOSER PLUGINS WHERE POSSIBLE INSTEAD OF WRITING OUR OWN CODE**.
  - Research and evaluate existing packages before implementing custom solutions.
  - Prefer packages with active maintenance, good test coverage, and community adoption.
  - Document package choices and rationale in the project README.

- **Refactoring and Code Migration:**
  - When refactoring, do not delete code from the old class until it is fully implemented in a new class with passing unit tests.
  - In the old class, leave a comment where the code was, explaining where the code has moved to.
  - This ensures traceability and safe migration of logic.
- **Documentation and Comment Preservation:**
  - Minimize changes to comments and documentation unless the existing information is obsolete or incorrect.
  - Do not alter phpdoc blocks for variables or methods unless the variables/methods themselves have changed.
  - Do not remove UML diagrams or other architectural documentation unless they are no longer accurate or relevant.
  - Always update or annotate documentation to reflect code movement or deprecation, but preserve historical context where possible.

- **Documentation Generation Process:**
  - **WE NEED A SCRIPT TO RUN THE PHPDOC PROCESS** - Use the `generate-docs.sh` script located in the project root.
  - Run `./generate-docs.sh` to generate complete HTML documentation with UML diagrams and images.
  - The script automatically:
    - Checks for required dependencies (phpDocumentor, GraphViz, PHP)
    - Generates PHPDoc configuration if needed
    - Creates HTML documentation with embedded UML diagrams
    - Generates class relationship diagrams
    - Validates the generated documentation
    - Creates a summary report
  - Run this script after any significant code changes to keep documentation current.

- **Architectural Documentation Updates:**
  - **AS WE WRITE CLASSES AND FUNCTIONS, WE NEED TO UPDATE A 2ND VERSION OF THE ARCHITECTURAL DOCUMENTS**.
  - Maintain implementation mapping documents that connect architectural components to actual code:
    - Update class reference documents when new classes are created
    - Map functions to architectural processes and workflows
    - Include message flow diagrams showing class interactions
    - Reference specific file paths and line numbers where relevant
  - Ensure all architectural diagrams can be traced to actual implementation

- **Class Design:**
  - One class = one responsibility.
  - Limit the number of methods and properties.
- **Function Design:**
  - Functions should do one thing and do it well.
  - Prefer pure functions where possible.
- **Testing:**
  - Write unit tests for all logic.
  - Use dependency injection to facilitate mocking and testing.
- **MVC in Plugins:**
  - For WordPress/FrontAccounting, keep controllers thin, models reusable, and views simple.
- **Business Logic:**
  - Place business rules in service classes, not in controllers or views.

## 5. Example Structure

```
/your-app
  /src
    /Core        # Generic, reusable code (Composer package candidates)
    /App         # App-specific business logic
    /UI          # User interface components
  /plugins       # WordPress/FA plugins (MVC structure)
  /tests         # Unit and integration tests
```

## 6. References
- Martin Fowler, Refactoring & Patterns
- SOLID Principles (Uncle Bob)
- DRY, KISS, YAGNI
- Composer documentation

---

*This document is a living reference. Update as new best practices and patterns emerge across projects.*
