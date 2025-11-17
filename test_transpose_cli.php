<?php
/**
 * CLI Test for Transpose Mode
 * Tests that transpose-mode CLI options work correctly
 */

$phpPath = 'C:\\php\\php.exe';
$script = 'bin\\abc-cannt-cli.php';

/* Test ABC file with multiple instruments */
$testAbc = <<<'ABC'
X:1
T:Orchestra Test
M:4/4
L:1/4
K:C
V:Piano
C D E F|
V:Trumpet
G A B c|
V:Alto Sax
d e f g|
V:French Horn
a b c' d'|
ABC;

file_put_contents('test_transpose_temp.abc', $testAbc);

$tests = array();
$passed = 0;
$failed = 0;

echo "=== Transpose Mode CLI Tests ===\n\n";

/* Test 1: MIDI Mode (default) */
echo "Test 1: MIDI Mode (default)\n";
$cmd = "$phpPath $script --file test_transpose_temp.abc --transpose-mode=midi --show-config 2>&1";
$output = shell_exec($cmd);
if (strpos($output, '"mode": "midi"') !== false || strpos($output, "'mode' => 'midi'") !== false) {
    echo "  PASS: MIDI mode configured\n";
    $passed++;
} else {
    echo "  FAIL: MIDI mode not found in config\n";
    $failed++;
}

/* Test 2: Bagpipe Mode */
echo "\nTest 2: Bagpipe Mode\n";
$cmd = "$phpPath $script --file test_transpose_temp.abc --transpose-mode=bagpipe --show-config 2>&1";
$output = shell_exec($cmd);
if (strpos($output, '"mode": "bagpipe"') !== false || strpos($output, "'mode' => 'bagpipe'") !== false) {
    echo "  PASS: Bagpipe mode configured\n";
    $passed++;
} else {
    echo "  FAIL: Bagpipe mode not found in config\n";
    $failed++;
}

/* Test 3: Orchestral Mode */
echo "\nTest 3: Orchestral Mode\n";
$cmd = "$phpPath $script --file test_transpose_temp.abc --transpose-mode=orchestral --show-config 2>&1";
$output = shell_exec($cmd);
if (strpos($output, '"mode": "orchestral"') !== false || strpos($output, "'mode' => 'orchestral'") !== false) {
    echo "  PASS: Orchestral mode configured\n";
    $passed++;
} else {
    echo "  FAIL: Orchestral mode not found in config\n";
    $failed++;
}

/* Test 4: Transpose Override */
echo "\nTest 4: Transpose Override\n";
$cmd = "$phpPath $script --file test_transpose_temp.abc --transpose-override=Piano:5 --transpose-override=Trumpet:0 --show-config 2>&1";
$output = shell_exec($cmd);
if (strpos($output, 'Piano') !== false && strpos($output, '5') !== false) {
    echo "  PASS: Transpose override configured\n";
    $passed++;
} else {
    echo "  FAIL: Transpose override not found in config\n";
    $failed++;
}

/* Test 5: Help shows transpose options */
echo "\nTest 5: Help Documentation\n";
$cmd = "$phpPath $script --help 2>&1";
$output = shell_exec($cmd);
if (strpos($output, '--transpose-mode') !== false && strpos($output, '--transpose-override') !== false) {
    echo "  PASS: Transpose options documented in help\n";
    $passed++;
} else {
    echo "  FAIL: Transpose options not in help\n";
    $failed++;
}

/* Cleanup */
unlink('test_transpose_temp.abc');

/* Summary */
echo "\n=== Summary ===\n";
$total = $passed + $failed;
echo "Total: $total\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($failed === 0) {
    echo "ALL TESTS PASSED\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED\n";
    exit(1);
}
