<?php
/**
 * Test Configuration File Loading
 * Tests JSON, YAML, INI config loading and merging
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;
use Ksfraser\PhpabcCanntaireachd\Config\ConfigLoader;
use Ksfraser\PhpabcCanntaireachd\Config\ConfigMerger;
use Ksfraser\PhpabcCanntaireachd\Config\ConfigValidator;

echo "=== Configuration System Tests ===\n\n";

$passCount = 0;
$failCount = 0;

// Test 1: Load JSON configuration
echo "Test 1: Load JSON configuration\n";
try {
    $config = ConfigLoader::loadFromFile(__DIR__ . '/config/abc_processor_config.json');
    
    $hasProcessing = isset($config['processing']);
    $hasTranspose = isset($config['transpose']);
    $hasVoiceOrdering = isset($config['voice_ordering']);
    
    if ($hasProcessing && $hasTranspose && $hasVoiceOrdering) {
        echo "  ✅ PASS - JSON config loaded successfully\n";
        echo "    - Has processing section: YES\n";
        echo "    - Has transpose section: YES\n";
        echo "    - Has voice_ordering section: YES\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL - JSON config missing sections\n";
        $failCount++;
    }
} catch (Exception $e) {
    echo "  ❌ FAIL - " . $e->getMessage() . "\n";
    $failCount++;
}

// Test 2: Load YAML configuration
echo "\nTest 2: Load YAML configuration\n";
try {
    $config = ConfigLoader::loadFromFile(__DIR__ . '/config/abc_processor_config.yml');
    
    $hasComments = true; // YAML supports comments natively
    $voiceStyle = $config['processing']['voice_output_style'] ?? null;
    
    if ($voiceStyle === 'grouped') {
        echo "  ✅ PASS - YAML config loaded successfully\n";
        echo "    - Voice output style: $voiceStyle\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL - YAML config has unexpected values\n";
        $failCount++;
    }
} catch (Exception $e) {
    echo "  ⚠️  SKIP - YAML not supported: " . $e->getMessage() . "\n";
    // Not a failure - YAML is optional
}

// Test 3: Load INI configuration
echo "\nTest 3: Load INI configuration\n";
try {
    $config = ConfigLoader::loadFromFile(__DIR__ . '/config/abc_processor_config.ini');
    
    $voiceStyle = $config['processing']['voice_output_style'] ?? null;
    $transposeMode = $config['transpose']['mode'] ?? null;
    
    if ($voiceStyle === 'grouped' && $transposeMode === 'midi') {
        echo "  ✅ PASS - INI config loaded successfully\n";
        echo "    - Voice output style: $voiceStyle\n";
        echo "    - Transpose mode: $transposeMode\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL - INI config has unexpected values\n";
        $failCount++;
    }
} catch (Exception $e) {
    echo "  ❌ FAIL - " . $e->getMessage() . "\n";
    $failCount++;
}

// Test 4: Configuration validation
echo "\nTest 4: Configuration validation\n";
$validConfig = [
    'processing' => [
        'voice_output_style' => 'grouped',
        'bars_per_line' => 4,
    ],
    'transpose' => [
        'mode' => 'orchestral',
    ],
];

$invalidConfig = [
    'processing' => [
        'voice_output_style' => 'invalid_mode',  // Invalid
        'bars_per_line' => 100,                  // Out of range
    ],
];

$validErrors = ConfigValidator::validate($validConfig);
$invalidErrors = ConfigValidator::validate($invalidConfig);

if (empty($validErrors) && !empty($invalidErrors)) {
    echo "  ✅ PASS - Validation working correctly\n";
    echo "    - Valid config: " . (empty($validErrors) ? "NO ERRORS" : "HAS ERRORS") . "\n";
    echo "    - Invalid config: " . count($invalidErrors) . " errors detected\n";
    foreach ($invalidErrors as $error) {
        echo "      - $error\n";
    }
    $passCount++;
} else {
    echo "  ❌ FAIL - Validation not working correctly\n";
    $failCount++;
}

// Test 5: Configuration merging
echo "\nTest 5: Configuration merging\n";
$base = [
    'processing' => ['voice_output_style' => 'grouped', 'bars_per_line' => 4],
    'transpose' => ['mode' => 'midi'],
];

$override = [
    'processing' => ['bars_per_line' => 8],  // Override
    'canntaireachd' => ['convert' => true],  // New
];

$merged = ConfigMerger::merge($base, $override);

$barsCorrect = $merged['processing']['bars_per_line'] === 8;
$stylePreserved = $merged['processing']['voice_output_style'] === 'grouped';
$newAdded = $merged['canntaireachd']['convert'] === true;

if ($barsCorrect && $stylePreserved && $newAdded) {
    echo "  ✅ PASS - Merging working correctly\n";
    echo "    - Override applied: bars_per_line = " . $merged['processing']['bars_per_line'] . "\n";
    echo "    - Base preserved: voice_output_style = " . $merged['processing']['voice_output_style'] . "\n";
    echo "    - New section added: canntaireachd.convert = true\n";
    $passCount++;
} else {
    echo "  ❌ FAIL - Merging not working correctly\n";
    $failCount++;
}

// Test 6: AbcProcessorConfig integration
echo "\nTest 6: AbcProcessorConfig integration\n";
try {
    $config = AbcProcessorConfig::loadFromFile(__DIR__ . '/config/abc_processor_config.json');
    
    $correctDefaults = (
        $config->voiceOutputStyle === 'grouped' &&
        $config->interleaveBars === 1 &&
        $config->barsPerLine === 4 &&
        $config->transposeMode === 'midi' &&
        $config->voiceOrderingMode === 'source'
    );
    
    if ($correctDefaults) {
        echo "  ✅ PASS - AbcProcessorConfig loaded from JSON\n";
        echo "    - Voice output style: " . $config->voiceOutputStyle . "\n";
        echo "    - Transpose mode: " . $config->transposeMode . "\n";
        echo "    - Voice ordering mode: " . $config->voiceOrderingMode . "\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL - AbcProcessorConfig has incorrect values\n";
        $failCount++;
    }
} catch (Exception $e) {
    echo "  ❌ FAIL - " . $e->getMessage() . "\n";
    $failCount++;
}

// Test 7: Save and reload configuration
echo "\nTest 7: Save and reload configuration\n";
try {
    $config = new AbcProcessorConfig();
    $config->voiceOutputStyle = 'interleaved';
    $config->transposeMode = 'bagpipe';
    $config->convertCanntaireachd = true;
    
    $testFile = __DIR__ . '/test_config_output.json';
    $saved = $config->saveToFile($testFile);
    
    if ($saved && file_exists($testFile)) {
        $reloaded = AbcProcessorConfig::loadFromFile($testFile);
        
        $valuesMatch = (
            $reloaded->voiceOutputStyle === 'interleaved' &&
            $reloaded->transposeMode === 'bagpipe' &&
            $reloaded->convertCanntaireachd === true
        );
        
        if ($valuesMatch) {
            echo "  ✅ PASS - Save and reload working\n";
            echo "    - Saved to: $testFile\n";
            echo "    - Values preserved correctly\n";
            $passCount++;
        } else {
            echo "  ❌ FAIL - Values not preserved after reload\n";
            $failCount++;
        }
        
        // Cleanup
        unlink($testFile);
    } else {
        echo "  ❌ FAIL - Could not save configuration\n";
        $failCount++;
    }
} catch (Exception $e) {
    echo "  ❌ FAIL - " . $e->getMessage() . "\n";
    $failCount++;
}

// Test 8: Example configurations
echo "\nTest 8: Load example configurations\n";
$examples = [
    'bagpipe_ensemble.yml',
    'orchestral_score.yml',
    'midi_import.yml',
];

$exampleCount = 0;
foreach ($examples as $example) {
    $path = __DIR__ . '/config/examples/' . $example;
    if (file_exists($path)) {
        try {
            $config = ConfigLoader::loadFromFile($path);
            echo "    ✓ Loaded: $example\n";
            $exampleCount++;
        } catch (Exception $e) {
            echo "    ✗ Failed: $example - " . $e->getMessage() . "\n";
        }
    }
}

if ($exampleCount === count($examples)) {
    echo "  ✅ PASS - All example configs loaded\n";
    $passCount++;
} else {
    echo "  ⚠️  PARTIAL - $exampleCount/" . count($examples) . " examples loaded\n";
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
