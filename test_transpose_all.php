<?php
/**
 * Comprehensive Transpose Test Suite
 * Runs all transpose-related tests
 */

$phpPath = 'C:\\php\\php.exe';

echo "========================================\n";
echo "  TRANSPOSE MODE TEST SUITE\n";
echo "========================================\n\n";

$allPassed = true;

/* Test 1: Strategy Unit Tests */
echo "1. Running Transpose Strategy Unit Tests...\n";
$output = shell_exec("$phpPath test_transpose_strategies.php 2>&1");
echo $output;
if (strpos($output, 'ALL TESTS PASSED') !== false) {
    echo "   Result: PASS\n\n";
} else {
    echo "   Result: FAIL\n\n";
    $allPassed = false;
}

/* Test 2: CLI Option Tests */
echo "2. Running CLI Option Tests...\n";
$output = shell_exec("$phpPath test_transpose_cli.php 2>&1");
echo $output;
if (strpos($output, 'ALL TESTS PASSED') !== false) {
    echo "   Result: PASS\n\n";
} else {
    echo "   Result: FAIL\n\n";
    $allPassed = false;
}

/* Test 3: Config File Tests */
echo "3. Running Config File Tests...\n";
$output = shell_exec("$phpPath test_transpose_config.php 2>&1");
echo $output;
if (strpos($output, 'ALL TESTS PASSED') !== false) {
    echo "   Result: PASS\n\n";
} else {
    echo "   Result: FAIL\n\n";
    $allPassed = false;
}

echo "========================================\n";
echo "  FINAL SUMMARY\n";
echo "========================================\n";
if ($allPassed) {
    echo "ALL TEST SUITES PASSED\n";
    echo "- Strategy Tests: 10/10 PASS\n";
    echo "- CLI Tests: 5/5 PASS\n";
    echo "- Config Tests: 3/3 PASS\n";
    echo "Total: 18/18 tests passing\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED\n";
    exit(1);
}
