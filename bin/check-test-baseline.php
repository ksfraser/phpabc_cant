<?php

declare(strict_types=1);

/**
 * Check a PHPUnit JUnit report against the known failures.
 *
 * The suite is red: it has a known set of failing and erroring tests that are
 * recorded in tests/known-failures.txt. This script compares a JUnit report
 * with that list:
 *
 *  - a test that is failing now but is not in the list is a REGRESSION, and
 *    the script exits 1;
 *  - a test in the list that now passes is reported as fixed, so the list can
 *    be trimmed;
 *  - a test that fails in a different way than recorded is also a regression.
 *
 * Usage:
 *   php bin/check-test-baseline.php <junit-report.xml> [--update]
 *
 * With --update the known-failures list is rewritten from the report.
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @since 2.1.0
 */

const BASELINE_FILE = __DIR__ . '/../tests/known-failures.txt';

/**
 * @param string $file
 * @return array<string, string> Test name => status.
 */
function readBaseline(string $file): array
{
    $out = [];
    if (!is_file($file)) {
        return $out;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = preg_split('/\s+/', $line, 2);
        $out[$parts[0]] = $parts[1] ?? '';
    }
    return $out;
}

/**
 * @param string $file
 * @param array<string, string> $tests
 * @return void
 */
function writeBaseline(string $file, array $tests): void
{
    ksort($tests);
    $lines = [
        '# Known failing and erroring tests.',
        '#',
        '# The suite is red: these tests fail at the recorded commit. The CI',
        '# compares a JUnit report with this list and fails the build only when',
        '# a test that is not listed here starts failing.',
        '#',
        '# Format: <test name> <status>',
        '# Regenerate with: php bin/check-test-baseline.php <report.xml> --update',
        '',
    ];
    foreach ($tests as $name => $status) {
        $lines[] = $name . ' ' . $status;
    }
    file_put_contents($file, implode("\n", $lines) . "\n");
}

/**
 * @param string $file
 * @return array<string, string> Test name => status.
 */
function readReport(string $file): array
{
    $xml = @simplexml_load_file($file);
    if ($xml === false) {
        fwrite(STDERR, "Could not read the JUnit report: $file\n");
        exit(2);
    }
    $out = [];
    foreach ($xml->xpath('//testcase') as $testCase) {
        $name = (string)$testCase['class'] . '::' . (string)$testCase['name'];
        if (isset($testCase->error)) {
            $out[$name] = 'ERROR';
        } elseif (isset($testCase->failure)) {
            $out[$name] = 'FAIL';
        } elseif (isset($testCase->warning)) {
            $out[$name] = 'WARN';
        }
    }
    return $out;
}

$args = array_slice($argv, 1);
$reportFile = $args[0] ?? '';
$update = in_array('--update', $args, true);

if ($reportFile === '' || !is_file($reportFile)) {
    fwrite(STDERR, "Usage: php bin/check-test-baseline.php <junit-report.xml> [--update]\n");
    exit(2);
}

$report = readReport($reportFile);
$baseline = readBaseline(BASELINE_FILE);

if ($update) {
    writeBaseline(BASELINE_FILE, $report);
    printf("Known failures written to %s (%d tests)\n", BASELINE_FILE, count($report));
    exit(0);
}

$regressions = [];
$changed = [];
foreach ($report as $name => $status) {
    if (!isset($baseline[$name])) {
        $regressions[] = "NEW  $name ($status)";
        continue;
    }
    if (strtoupper($baseline[$name]) !== $status) {
        $changed[] = sprintf('CHANGED %s (%s -> %s)', $name, $baseline[$name], $status);
    }
}

$fixed = [];
foreach ($baseline as $name => $status) {
    if (!isset($report[$name])) {
        $fixed[] = "FIXED $name ($status)";
    }
}

printf("tests in report : %d\n", count($report) + count(array_filter($report, fn ($s) => $s === 'PASS')));
printf("known failures  : %d\n", count($baseline));
printf("still failing   : %d\n", count(array_intersect_key($report, $baseline)));
printf("newly failing   : %d\n", count($regressions));
printf("newly passing   : %d\n", count($fixed));

foreach ($regressions as $line) {
    echo "  $line\n";
}
foreach ($changed as $line) {
    echo "  $line\n";
}
if ($fixed !== []) {
    echo "\nThese are no longer failing — remove them from " . BASELINE_FILE . ":\n";
    foreach ($fixed as $line) {
        echo "  $line\n";
    }
}

if ($regressions !== [] || $changed !== []) {
    fwrite(STDERR, "\nThe test suite has regressed.\n");
    exit(1);
}

echo "\nNo new failures.\n";
exit(0);
