<?php
/**
 * Config File Test for Transpose Mode
 * Tests that transpose configuration can be loaded from files
 */

$phpPath = 'C:\\php\\php.exe';
$script = 'bin\\abc-cannt-cli.php';

$testAbc = <<<'ABC'
X:1
T:Config Test
M:4/4
L:1/4
K:C
V:Piano
C D E F|
V:Trumpet
G A B c|
ABC;

file_put_contents('test_config_temp.abc', $testAbc);

$tests = array();
$passed = 0;
$failed = 0;

echo "=== Transpose Config File Tests ===\n\n";

/* Test 1: Load transpose config from JSON */
echo "Test 1: JSON Config File\n";
$cmd = "$phpPath $script --file test_config_temp.abc --config=config/examples/transpose_test.json --show-config 2>&1";
$output = shell_exec($cmd);
$hasOrchestraMode = (strpos($output, '"mode": "orchestral"') !== false || strpos($output, "'mode' => 'orchestral'") !== false);
$hasOverrides = (strpos($output, 'overrides') !== false || strpos($output, 'Piano') !== false);

if ($hasOrchestraMode && $hasOverrides) {
    echo "  PASS: Config loaded (orchestral mode + overrides)\n";
    $passed++;
} else {
    echo "  FAIL: Config not loaded correctly\n";
    echo "  Has orchestral mode: " . ($hasOrchestraMode ? 'yes' : 'no') . "\n";
    echo "  Has overrides: " . ($hasOverrides ? 'yes' : 'no') . "\n";
    $failed++;
}

/* Test 2: CLI override of config file */
echo "\nTest 2: CLI Override of Config\n";
$cmd = "$phpPath $script --file test_config_temp.abc --config=config/examples/transpose_test.json --transpose-mode=bagpipe --show-config 2>&1";
$output = shell_exec($cmd);
if (strpos($output, '"mode": "bagpipe"') !== false || strpos($output, "'mode' => 'bagpipe'") !== false) {
    echo "  PASS: CLI overrides config file (bagpipe mode)\n";
    $passed++;
} else {
    echo "  FAIL: CLI did not override config file\n";
    $failed++;
}

/* Test 3: Save config with transpose settings */
echo "\nTest 3: Save Config File\n";
$saveFile = 'test_saved_config.json';
$cmd = "$phpPath $script --save-config=$saveFile --transpose-mode=bagpipe --transpose-override=Piano:3 2>&1";
$output = shell_exec($cmd);
if (file_exists($saveFile)) {
    $content = file_get_contents($saveFile);
    $hasBagpipe = (strpos($content, 'bagpipe') !== false);
    $hasOverride = (strpos($content, 'Piano') !== false && strpos($content, '3') !== false);
    
    if ($hasBagpipe && $hasOverride) {
        echo "  PASS: Config saved with transpose settings\n";
        $passed++;
    } else {
        echo "  FAIL: Config file missing transpose settings\n";
        echo "  Has bagpipe: " . ($hasBagpipe ? 'yes' : 'no') . "\n";
        echo "  Has Piano:3: " . ($hasOverride ? 'yes' : 'no') . "\n";
        $failed++;
    }
    unlink($saveFile);
} else {
    echo "  FAIL: Config file not created\n";
    $failed++;
}

/* Cleanup */
unlink('test_config_temp.abc');

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
