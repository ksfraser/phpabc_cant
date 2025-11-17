<?php
/**
 * Test CLI Configuration Integration
 * Tests that CLI options properly load, merge, and override configurations
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\CLIOptions;
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;

echo "=== CLI Configuration Integration Tests ===\n\n";

$passCount = 0;
$failCount = 0;

// Test 1: CLI options parsing with new config options
echo "Test 1: Parse CLI options with config flags\n";
$testArgv = [
    'script.php',
    '--file=test.abc',
    '--config=myconfig.json',
    '--voice-order=orchestral',
    '--transpose-mode=bagpipe',
    '--transpose-override=Piano:2',
    '--transpose-override=Guitar:2',
    '--strict',
    '--show-config'
];

$cli = new CLIOptions($testArgv);

$configCorrect = $cli->configFile === 'myconfig.json';
$voiceOrderCorrect = $cli->voiceOrderMode === 'orchestral';
$transposeModeCorrect = $cli->transposeMode === 'bagpipe';
$transposeOverridesCorrect = (
    isset($cli->transposeOverride['Piano']) && $cli->transposeOverride['Piano'] === 2 &&
    isset($cli->transposeOverride['Guitar']) && $cli->transposeOverride['Guitar'] === 2
);
$strictCorrect = $cli->strictMode === true;
$showConfigCorrect = $cli->showConfig === true;

if ($configCorrect && $voiceOrderCorrect && $transposeModeCorrect && $transposeOverridesCorrect && $strictCorrect && $showConfigCorrect) {
    echo "  ✅ PASS - CLI options parsed correctly\n";
    echo "    - Config file: {$cli->configFile}\n";
    echo "    - Voice order: {$cli->voiceOrderMode}\n";
    echo "    - Transpose mode: {$cli->transposeMode}\n";
    echo "    - Transpose overrides: " . count($cli->transposeOverride) . " voices\n";
    echo "    - Strict mode: " . ($cli->strictMode ? 'YES' : 'NO') . "\n";
    $passCount++;
} else {
    echo "  ❌ FAIL - CLI options not parsed correctly\n";
    $failCount++;
}

// Test 2: Apply CLI options to config
echo "\nTest 2: Apply CLI options to AbcProcessorConfig\n";
$config = new AbcProcessorConfig();
$config->transposeMode = 'midi';  // Default
$config->voiceOrderingMode = 'source';  // Default

$testArgv2 = [
    'script.php',
    '--transpose-mode=orchestral',
    '--voice-order=custom',
    '--bars_per_line=8',
    '--strict'
];

$cli2 = new CLIOptions($testArgv2);
$cli2->applyToConfig($config);

$transposeModeApplied = $config->transposeMode === 'orchestral';
$voiceOrderApplied = $config->voiceOrderingMode === 'custom';
$barsPerLineApplied = $config->barsPerLine === 8;
$strictApplied = $config->strictMode === true;

if ($transposeModeApplied && $voiceOrderApplied && $barsPerLineApplied && $strictApplied) {
    echo "  ✅ PASS - CLI options applied to config\n";
    echo "    - Transpose mode: {$config->transposeMode}\n";
    echo "    - Voice ordering: {$config->voiceOrderingMode}\n";
    echo "    - Bars per line: {$config->barsPerLine}\n";
    echo "    - Strict mode: " . ($config->strictMode ? 'YES' : 'NO') . "\n";
    $passCount++;
} else {
    echo "  ❌ FAIL - CLI options not applied correctly\n";
    $failCount++;
}

// Test 3: Config file + CLI override precedence
echo "\nTest 3: Configuration precedence (config file + CLI override)\n";
$config3 = new AbcProcessorConfig();

// Simulate loading from config file
$config3->transposeMode = 'midi';
$config3->barsPerLine = 4;
$config3->voiceOrderingMode = 'source';

// Apply CLI overrides
$testArgv3 = [
    'script.php',
    '--transpose-mode=bagpipe',  // Override
    '--bars_per_line=6',         // Override
    // voiceOrderingMode not specified - should keep config file value
];

$cli3 = new CLIOptions($testArgv3);
$cli3->applyToConfig($config3);

$transposeOverridden = $config3->transposeMode === 'bagpipe';
$barsOverridden = $config3->barsPerLine === 6;
$voiceOrderPreserved = $config3->voiceOrderingMode === 'source';

if ($transposeOverridden && $barsOverridden && $voiceOrderPreserved) {
    echo "  ✅ PASS - Precedence working correctly\n";
    echo "    - Transpose mode (overridden): {$config3->transposeMode}\n";
    echo "    - Bars per line (overridden): {$config3->barsPerLine}\n";
    echo "    - Voice ordering (preserved): {$config3->voiceOrderingMode}\n";
    $passCount++;
} else {
    echo "  ❌ FAIL - Precedence not working correctly\n";
    $failCount++;
}

// Test 4: Load config file via CLI
echo "\nTest 4: Load configuration file via --config option\n";
$configPath = __DIR__ . '/config/abc_processor_config.json';
if (file_exists($configPath)) {
    $testArgv4 = [
        'script.php',
        '--file=test.abc',
        '--config=' . $configPath
    ];
    
    $cli4 = new CLIOptions($testArgv4);
    
    try {
        $config4 = AbcProcessorConfig::loadFromFile($cli4->configFile);
        $cli4->applyToConfig($config4);
        
        // Check that config was loaded
        $hasDefaults = (
            $config4->voiceOutputStyle === 'grouped' &&
            $config4->interleaveBars === 1 &&
            $config4->transposeMode === 'midi'
        );
        
        if ($hasDefaults) {
            echo "  ✅ PASS - Config file loaded via CLI\n";
            echo "    - Config file: {$cli4->configFile}\n";
            echo "    - Voice output style: {$config4->voiceOutputStyle}\n";
            echo "    - Transpose mode: {$config4->transposeMode}\n";
            $passCount++;
        } else {
            echo "  ❌ FAIL - Config not loaded correctly\n";
            $failCount++;
        }
    } catch (Exception $e) {
        echo "  ❌ FAIL - Exception: " . $e->getMessage() . "\n";
        $failCount++;
    }
} else {
    echo "  ⚠️  SKIP - Config file not found: $configPath\n";
}

// Test 5: Multiple transpose overrides
echo "\nTest 5: Multiple transpose overrides\n";
$testArgv5 = [
    'script.php',
    '--transpose-override=Bagpipes:0',
    '--transpose-override=Piano:2',
    '--transpose-override=Guitar:2',
    '--transpose-override=Trumpet:2'
];

$cli5 = new CLIOptions($testArgv5);
$overrideCount = count($cli5->transposeOverride);
$hasExpected = (
    $cli5->transposeOverride['Bagpipes'] === 0 &&
    $cli5->transposeOverride['Piano'] === 2 &&
    $cli5->transposeOverride['Guitar'] === 2 &&
    $cli5->transposeOverride['Trumpet'] === 2
);

if ($overrideCount === 4 && $hasExpected) {
    echo "  ✅ PASS - Multiple transpose overrides parsed\n";
    echo "    - Overrides count: $overrideCount\n";
    foreach ($cli5->transposeOverride as $voice => $transpose) {
        echo "      - $voice: $transpose\n";
    }
    $passCount++;
} else {
    echo "  ❌ FAIL - Multiple overrides not working\n";
    $failCount++;
}

// Test 6: toArray() includes new options
echo "\nTest 6: CLIOptions::toArray() includes new fields\n";
$testArgv6 = [
    'script.php',
    '--config=test.json',
    '--voice-order=orchestral',
    '--transpose-mode=bagpipe',
    '--strict'
];

$cli6 = new CLIOptions($testArgv6);
$array = $cli6->toArray();

$hasNewFields = (
    isset($array['configFile']) &&
    isset($array['voiceOrderMode']) &&
    isset($array['transposeMode']) &&
    isset($array['strictMode'])
);

$valuesCorrect = (
    $array['configFile'] === 'test.json' &&
    $array['voiceOrderMode'] === 'orchestral' &&
    $array['transposeMode'] === 'bagpipe' &&
    $array['strictMode'] === true
);

if ($hasNewFields && $valuesCorrect) {
    echo "  ✅ PASS - toArray() includes new fields\n";
    echo "    - Fields present: " . count($array) . "\n";
    $passCount++;
} else {
    echo "  ❌ FAIL - toArray() missing fields\n";
    $failCount++;
}

// Summary
echo "\n=== SUMMARY ===\n";
$total = $passCount + $failCount;
echo "Tests run: $total\n";
echo "Passed: $passCount\n";
echo "Failed: $failCount\n";

if ($failCount === 0) {
    echo "\n✅ ALL TESTS PASSED\n";
    exit(0);
} else {
    echo "\n❌ SOME TESTS FAILED\n";
    exit(1);
}
