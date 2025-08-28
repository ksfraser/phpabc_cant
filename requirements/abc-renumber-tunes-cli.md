# Requirements for abc-renumber-tunes-cli.php

## Functional Requirements
- The CLI tool shall accept an ABC file as input and renumber duplicated X: tune numbers.
- The CLI tool shall support the option `--width=N` to set the width of tune numbers (default: 5).
- The CLI tool shall support the option `--errorfile=err.txt` to write log and error output to the specified file.
- The CLI tool shall write the renumbered ABC output to `<inputfile>.renumbered`.
- The CLI tool shall log a message indicating the output file location to either stdout or the error file.
- If the input file is missing, the CLI tool shall print/log an error and exit with code 1.
- If no input file is provided, the CLI tool shall print/log usage and exit with code 1.

## Non-Functional Requirements
- The CLI tool shall use `CliOutputWriter` for all file output.
- The CLI tool shall be PSR-4 compliant and use Composer autoloading.
- The CLI tool shall not overwrite the original ABC file.
- The CLI tool shall handle large ABC files efficiently.
- The CLI tool shall be compatible with PHP 8+.

## Error Handling
- All error and usage messages shall be written to the error file if provided, otherwise to stdout.
- The tool shall exit with code 1 for missing file or usage errors.
- The tool shall exit with code 0 on successful completion.

## Test Cases
- Input ABC file with duplicate X: numbers; verify output file has unique, correctly padded X: numbers.
- Input ABC file with no duplicate X: numbers; verify output file matches input except for padding.
- Input file missing; verify error message and exit code.
- No input file provided; verify usage message and exit code.
- Provide --width option; verify tune numbers are padded to specified width.
- Provide --errorfile option; verify log message is written to error file.
- Large ABC file; verify performance and correctness.
