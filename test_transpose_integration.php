<?php
require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcTransposePass;
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;
use Ksfraser\PhpabcCanntaireachd\Transpose\OrchestralTransposeStrategy;

$testResults = array();
$totalTests = 0;
$passedTests = 0;

echo "=== Transpose Pass Integration Tests ===\n\n";

$orchestralAbc = <<<'ABC'
X:1
T:Test Orchestra
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
V:Violin
e' f' g' a'|
ABC;

$bagpipeAbc = <<<'ABC'
X:1
T:Bagpipe Ensemble
M:4/4
L:1/4
K:HP
V:Bagpipes
A B c d|
V:Piano
C D E F|
V:Guitar
G A B c|
ABC;

/* Test 1: MIDI Mode */
$totalTests++;
echo "Test 1: MIDI Mode\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'midi';
$pass = new AbcTransposePass(null, $config);
$lines = explode("\n", $orchestralAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);
$hasCorrectTranspose = !preg_match('/transpose=([^0\s]+)/', $resultText);
if ($hasCorrectTranspose) {
    echo "  PASS\n";
    $passedTests++;
} else {
    echo "  FAIL\n";
}

/* Test 2: Bagpipe Mode */
$totalTests++;
echo "Test 2: Bagpipe Mode\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'bagpipe';
$pass = new AbcTransposePass(null, $config);
$lines = explode("\n", $bagpipeAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);
$pianoHas2 = preg_match('/V:Piano[^\n]*transpose=2/', $resultText);
if ($pianoHas2) {
    echo "  PASS\n";
    $passedTests++;
} else {
    echo "  FAIL\n";
}

/* Test 3: Orchestral Mode */
$totalTests++;
echo "Test 3: Orchestral Mode\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'orchestral';
$pass = new AbcTransposePass(null, $config);
$lines = explode("\n", $orchestralAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);
$trumpetHas2 = preg_match('/V:Trumpet[^\n]*transpose=2/', $resultText);
if ($trumpetHas2) {
    echo "  PASS\n";
    $passedTests++;
} else {
    echo "  FAIL\n";
}

/* Test 4: Strategy Injection */
$totalTests++;
echo "Test 4: Strategy Injection\n";
$strategy = new OrchestralTransposeStrategy();
$pass = new AbcTransposePass($strategy);
$lines = explode("\n", $orchestralAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);
$trumpetHas2 = preg_match('/V:Trumpet[^\n]*transpose=2/', $resultText);
if ($trumpetHas2) {
    echo "  PASS\n";
    $passedTests++;
} else {
    echo "  FAIL\n";
}

/* Test 5: Per-Voice Overrides */
$totalTests++;
echo "Test 5: Per-Voice Overrides\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'orchestral';
$config->transposeOverrides = array('Piano' => 5);
$pass = new AbcTransposePass(null, $config);
$lines = explode("\n", $orchestralAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);
$pianoHas5 = preg_match('/V:Piano[^\n]*transpose=5/', $resultText);
if ($pianoHas5) {
    echo "  PASS\n";
    $passedTests++;
} else {
    echo "  FAIL\n";
}

/* Summary */
echo "\n=== Summary ===\n";
echo "Total: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
if ($passedTests === $totalTests) {
    echo "ALL TESTS PASSED\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED\n";
    exit(1);
}
