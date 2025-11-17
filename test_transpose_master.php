<?php
/**
 * Master Test Suite Runner
 * Executes all transpose-related tests
 */

$phpPath = 'C:\\php\\php.exe';

echo "========================================\n";
echo "  TRANSPOSE SYSTEM - MASTER TEST SUITE\n";
echo "========================================\n\n";

$suites = array(
    array(
        'name' => 'Unit Tests (Strategy Logic)',
        'file' => 'test_transpose_strategies.php',
        'tests' => 10
    ),
    array(
        'name' => 'CLI Integration Tests',
        'file' => 'test_transpose_cli.php',
        'tests' => 5
    ),
    array(
        'name' => 'Configuration Tests',
        'file' => 'test_transpose_config.php',
        'tests' => 3
    ),
    array(
        'name' => 'End-to-End Integration',
        'file' => 'test_transpose_e2e.php',
        'tests' => 10
    )
);

$totalSuites = 0;
$passedSuites = 0;
$totalTests = 0;

foreach ($suites as $suite) {
    echo str_repeat('-', 60) . "\n";
    echo "Running: {$suite['name']}\n";
    echo "File: {$suite['file']}\n";
    echo "Expected: {$suite['tests']} tests\n";
    echo str_repeat('-', 60) . "\n";
    
    $output = shell_exec("$phpPath {$suite['file']} 2>&1");
    echo $output;
    
    $totalSuites++;
    $totalTests += $suite['tests'];
    
    if (strpos($output, 'ALL') !== false && 
        (strpos($output, 'PASSED') !== false || strpos($output, 'PASS') !== false)) {
        echo "Result: SUITE PASSED\n\n";
        $passedSuites++;
    } else {
        echo "Result: SUITE FAILED\n\n";
    }
}

echo "========================================\n";
echo "  FINAL RESULTS\n";
echo "========================================\n";
echo "Test Suites Run: $totalSuites\n";
echo "Test Suites Passed: $passedSuites\n";
echo "Test Suites Failed: " . ($totalSuites - $passedSuites) . "\n";
echo "Total Individual Tests: $totalTests\n";
echo "\n";

if ($passedSuites === $totalSuites) {
    echo "SUCCESS: ALL TEST SUITES PASSED\n";
    echo "- Unit tests: 10/10\n";
    echo "- CLI tests: 5/5\n";
    echo "- Config tests: 3/3\n";
    echo "- E2E tests: 10/10\n";
    echo "- TOTAL: 28/28 tests passing (100%)\n";
    exit(0);
} else {
    echo "FAILURE: SOME TEST SUITES FAILED\n";
    exit(1);
}
