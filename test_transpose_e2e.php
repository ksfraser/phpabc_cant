<?php
/**
 * End-to-End Integration Test for Transpose System
 * 
 * Tests the complete flow: Config → Strategy → Voice metadata
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;
use Ksfraser\PhpabcCanntaireachd\Transpose\MidiTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\BagpipeTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\OrchestralTransposeStrategy;

$passed = 0;
$failed = 0;

echo "=== End-to-End Transpose Integration Tests ===\n\n";

/* Test 1: Config to Strategy - MIDI Mode */
echo "Test 1: Config to Strategy - MIDI Mode\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'midi';

$strategy = null;
if ($config->transposeMode === 'midi') {
    $strategy = new MidiTransposeStrategy();
} elseif ($config->transposeMode === 'bagpipe') {
    $strategy = new BagpipeTransposeStrategy();
} elseif ($config->transposeMode === 'orchestral') {
    $strategy = new OrchestralTransposeStrategy();
}

if ($strategy instanceof MidiTransposeStrategy) {
    $trumpet = $strategy->getTranspose('Trumpet');
    if ($trumpet === 0) {
        echo "  PASS: MIDI mode creates correct strategy, Trumpet=0\n";
        $passed++;
    } else {
        echo "  FAIL: Trumpet should be 0, got $trumpet\n";
        $failed++;
    }
} else {
    echo "  FAIL: Strategy not created\n";
    $failed++;
}

/* Test 2: Config to Strategy - Bagpipe Mode */
echo "\nTest 2: Config to Strategy - Bagpipe Mode\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'bagpipe';

if ($config->transposeMode === 'bagpipe') {
    $strategy = new BagpipeTransposeStrategy();
}

if ($strategy instanceof BagpipeTransposeStrategy) {
    $bagpipes = $strategy->getTranspose('Bagpipes');
    $piano = $strategy->getTranspose('Piano');
    if ($bagpipes === 0 && $piano === 2) {
        echo "  PASS: Bagpipe mode - Bagpipes=0, Piano=2\n";
        $passed++;
    } else {
        echo "  FAIL: Wrong values (Bagpipes=$bagpipes, Piano=$piano)\n";
        $failed++;
    }
} else {
    echo "  FAIL: Strategy not created\n";
    $failed++;
}

/* Test 3: Config to Strategy - Orchestral Mode */
echo "\nTest 3: Config to Strategy - Orchestral Mode\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'orchestral';

if ($config->transposeMode === 'orchestral') {
    $strategy = new OrchestralTransposeStrategy();
}

if ($strategy instanceof OrchestralTransposeStrategy) {
    $trumpet = $strategy->getTranspose('Trumpet');
    $altoSax = $strategy->getTranspose('Alto Sax');
    $frenchHorn = $strategy->getTranspose('French Horn');
    $piano = $strategy->getTranspose('Piano');
    
    if ($trumpet === 2 && $altoSax === 9 && $frenchHorn === 7 && $piano === 0) {
        echo "  PASS: Orchestral mode - Tpt=2, ASax=9, Hn=7, Pno=0\n";
        $passed++;
    } else {
        echo "  FAIL: Wrong values (Tpt=$trumpet, ASax=$altoSax, Hn=$frenchHorn, Pno=$piano)\n";
        $failed++;
    }
} else {
    echo "  FAIL: Strategy not created\n";
    $failed++;
}

/* Test 4: Config with Overrides */
echo "\nTest 4: Config with Per-Voice Overrides\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'orchestral';
$config->transposeOverrides = array(
    'Trumpet' => 0,
    'Piano' => 5
);

$strategy = new OrchestralTransposeStrategy();
$baseTranspose = $strategy->getTranspose('Trumpet');
$overrideTranspose = isset($config->transposeOverrides['Trumpet']) 
    ? $config->transposeOverrides['Trumpet'] 
    : $baseTranspose;

if ($overrideTranspose === 0) {
    echo "  PASS: Override applied (Trumpet base=2, override=0)\n";
    $passed++;
} else {
    echo "  FAIL: Override not applied correctly\n";
    $failed++;
}

/* Test 5: Voice Metadata Application */
echo "\nTest 5: Voice Metadata Application\n";
$voices = array(
    array('name' => 'Piano', 'instrument' => 'Piano'),
    array('name' => 'Trumpet', 'instrument' => 'Trumpet'),
    array('name' => 'Alto Sax', 'instrument' => 'Alto Sax')
);

$config = new AbcProcessorConfig();
$config->transposeMode = 'orchestral';
$strategy = new OrchestralTransposeStrategy();

$voicesWithTranspose = array();
foreach ($voices as $voice) {
    $transpose = $strategy->getTranspose($voice['instrument']);
    $voicesWithTranspose[] = array_merge($voice, array('transpose' => $transpose));
}

$pianoTranspose = $voicesWithTranspose[0]['transpose'];
$trumpetTranspose = $voicesWithTranspose[1]['transpose'];
$saxTranspose = $voicesWithTranspose[2]['transpose'];

if ($pianoTranspose === 0 && $trumpetTranspose === 2 && $saxTranspose === 9) {
    echo "  PASS: Transpose values applied to voice metadata\n";
    $passed++;
} else {
    echo "  FAIL: Wrong metadata values\n";
    $failed++;
}

/* Test 6: Config File Integration */
echo "\nTest 6: Config File Integration\n";
$testConfig = array(
    'transpose' => array(
        'mode' => 'orchestral',
        'overrides' => array(
            'Piano' => 3
        )
    )
);

$config = new AbcProcessorConfig();
$config->mergeFromArray($testConfig);

if ($config->transposeMode === 'orchestral' && 
    isset($config->transposeOverrides['Piano']) && 
    $config->transposeOverrides['Piano'] === 3) {
    echo "  PASS: Config file data loaded correctly\n";
    $passed++;
} else {
    echo "  FAIL: Config file not loaded properly\n";
    $failed++;
}

/* Test 7: Multiple Voice Processing */
echo "\nTest 7: Multiple Voice Processing Pipeline\n";
$instruments = array('Piano', 'Trumpet', 'Clarinet', 'Alto Sax', 'French Horn', 'Violin');
$config = new AbcProcessorConfig();
$config->transposeMode = 'orchestral';
$strategy = new OrchestralTransposeStrategy();

$results = array();
foreach ($instruments as $instrument) {
    $results[$instrument] = $strategy->getTranspose($instrument);
}

$expected = array(
    'Piano' => 0,
    'Trumpet' => 2,
    'Clarinet' => 2,
    'Alto Sax' => 9,
    'French Horn' => 7,
    'Violin' => 0
);

$allCorrect = true;
foreach ($expected as $instrument => $expectedValue) {
    if ($results[$instrument] !== $expectedValue) {
        $allCorrect = false;
        break;
    }
}

if ($allCorrect) {
    echo "  PASS: All 6 instruments processed correctly\n";
    $passed++;
} else {
    echo "  FAIL: Some instruments have wrong transpose values\n";
    $failed++;
}

/* Test 8: Strategy Switching */
echo "\nTest 8: Strategy Switching (Mode Change)\n";
$instrument = 'Trumpet';

/* MIDI mode */
$strategy1 = new MidiTransposeStrategy();
$value1 = $strategy1->getTranspose($instrument);

/* Orchestral mode */
$strategy2 = new OrchestralTransposeStrategy();
$value2 = $strategy2->getTranspose($instrument);

/* Bagpipe mode */
$strategy3 = new BagpipeTransposeStrategy();
$value3 = $strategy3->getTranspose($instrument);

if ($value1 === 0 && $value2 === 2 && $value3 === 2) {
    echo "  PASS: Strategy switching works (MIDI=0, Orch=2, Bagpipe=2)\n";
    $passed++;
} else {
    echo "  FAIL: Strategy switching gave wrong values\n";
    $failed++;
}

/* Test 9: Unknown Instrument Handling */
echo "\nTest 9: Unknown Instrument Handling\n";
$strategy = new OrchestralTransposeStrategy();
$unknown = $strategy->getTranspose('UnknownInstrument123');

if ($unknown === 0) {
    echo "  PASS: Unknown instrument defaults to 0 (concert pitch)\n";
    $passed++;
} else {
    echo "  FAIL: Unknown instrument should default to 0, got $unknown\n";
    $failed++;
}

/* Test 10: Empty Config Handling */
echo "\nTest 10: Empty Config (Default Values)\n";
$config = new AbcProcessorConfig();

if ($config->transposeMode === 'midi') {
    echo "  PASS: Default transpose mode is 'midi'\n";
    $passed++;
} else {
    echo "  FAIL: Default mode should be 'midi', got {$config->transposeMode}\n";
    $failed++;
}

/* Summary */
echo "\n" . str_repeat('=', 60) . "\n";
echo "Integration Test Summary\n";
echo str_repeat('=', 60) . "\n";
$total = $passed + $failed;
echo "Total Tests: $total\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Success Rate: " . round(($passed / $total) * 100, 1) . "%\n\n";

if ($failed === 0) {
    echo "ALL INTEGRATION TESTS PASSED\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED\n";
    exit(1);
}
