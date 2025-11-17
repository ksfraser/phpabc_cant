<?php
/**
 * Test the refactored AbcProcessingPipeline::processWithTransforms() method
 * 
 * This script tests the new object-based pipeline that uses:
 * Parse → Transform* → Render pattern
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline;
use Ksfraser\PhpabcCanntaireachd\Transform\VoiceCopyTransform;
use Ksfraser\PhpabcCanntaireachd\Transform\CanntaireachdTransform;
use Ksfraser\PhpabcCanntaireachd\CanntGenerator;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

// Test 1: Simple ABC with Melody voice → should create Bagpipes + canntaireachd
echo "=== Test 1: Simple ABC with Melody voice ===\n";

$simpleAbc = <<<ABC
X:1
T:Test Tune
M:4/4
L:1/4
K:D
V:M name="Melody"
A B c d|

ABC;

// Load the real ABC dictionary
include __DIR__ . '/src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
$dict = new TokenDictionary();
$dict->prepopulate($abc);

// Create transforms
$transforms = [
    new VoiceCopyTransform(),
    new CanntaireachdTransform($dict)
];

// Create pipeline and process
$pipeline = new AbcProcessingPipeline([]);
$result = $pipeline->processWithTransforms($simpleAbc, $transforms, true);

echo "\n--- Result ---\n";
if (!empty($result['errors'])) {
    echo "ERRORS:\n";
    foreach ($result['errors'] as $error) {
        echo "  - $error\n";
    }
}

echo "\nProcessed ABC:\n";
echo $result['text'];
echo "\n";

// Verify expected behavior
$hasVoiceM = strpos($result['text'], 'V:M') !== false || strpos($result['text'], 'V:Melody') !== false;
$hasVoiceBagpipes = strpos($result['text'], 'V:Bagpipes') !== false;
$hasCanntInMelody = preg_match('/V:M.*?w:.*?(chin|hin|ho|dro)/s', $result['text']);
$hasCanntInBagpipes = preg_match('/V:Bagpipes.*?w:.*?(chin|hin|ho|dro)/s', $result['text']);

echo "\n--- Validation ---\n";
echo "✓ Has V:M voice: " . ($hasVoiceM ? 'YES' : 'NO') . "\n";
echo "✓ Has V:Bagpipes voice: " . ($hasVoiceBagpipes ? 'YES' : 'NO') . "\n";
echo "✓ Canntaireachd in Melody: " . ($hasCanntInMelody ? 'YES (BAD!)' : 'NO (GOOD!)') . "\n";
echo "✓ Canntaireachd in Bagpipes: " . ($hasCanntInBagpipes ? 'YES (GOOD!)' : 'NO (BAD!)') . "\n";

$test1Pass = $hasVoiceM && $hasVoiceBagpipes && !$hasCanntInMelody && $hasCanntInBagpipes;
echo "\nTest 1: " . ($test1Pass ? "✅ PASS" : "❌ FAIL") . "\n";

// Test 2: ABC with existing Bagpipes → should NOT copy, but add canntaireachd
echo "\n\n=== Test 2: ABC with existing Bagpipes voice ===\n";

$existingBagpipesAbc = <<<ABC
X:2
T:Test with Bagpipes
M:4/4
L:1/4
K:D
V:M name="Melody"
A B c d|
V:Bagpipes name="Bagpipes"
E F G A|

ABC;

$result2 = $pipeline->processWithTransforms($existingBagpipesAbc, $transforms, false);

echo "\n--- Result ---\n";
echo $result2['text'];

$melodyHasABcd = strpos($result2['text'], 'A B c d') !== false;
$bagpipesHasEFGA = strpos($result2['text'], 'E F G A') !== false;
$bagpipesHasCanntEFGA = preg_match('/V:Bagpipes.*E F G A.*?w:.*?/s', $result2['text']);

echo "\n--- Validation ---\n";
echo "✓ Melody has 'A B c d': " . ($melodyHasABcd ? 'YES' : 'NO') . "\n";
echo "✓ Bagpipes has 'E F G A': " . ($bagpipesHasEFGA ? 'YES' : 'NO') . "\n";
echo "✓ Bagpipes has canntaireachd: " . ($bagpipesHasCanntEFGA ? 'YES' : 'NO') . "\n";

$test2Pass = $melodyHasABcd && $bagpipesHasEFGA && $bagpipesHasCanntEFGA;
echo "\nTest 2: " . ($test2Pass ? "✅ PASS" : "❌ FAIL") . "\n";

// Test 3: Real-world test-Suo.abc
echo "\n\n=== Test 3: Real-world test-Suo.abc ===\n";

if (file_exists(__DIR__ . '/test-Suo.abc')) {
    $suoAbc = file_get_contents(__DIR__ . '/test-Suo.abc');
    $result3 = $pipeline->processWithTransforms($suoAbc, $transforms, false);
    
    echo "\n--- Result (first 1000 chars) ---\n";
    echo substr($result3['text'], 0, 1000) . "...\n";
    
    // Count bars in each voice
    preg_match_all('/\|/', $result3['text'], $barMatches);
    $totalBars = count($barMatches[0]);
    
    preg_match('/V:M.*?((?:\|[^|]*)+)\|/s', $result3['text'], $melodyMatch);
    $melodyBars = isset($melodyMatch[1]) ? substr_count($melodyMatch[1], '|') + 1 : 0;
    
    preg_match('/V:Bagpipes.*?((?:\|[^|]*)+)\|/s', $result3['text'], $bagpipesMatch);
    $bagpipesBars = isset($bagpipesMatch[1]) ? substr_count($bagpipesMatch[1], '|') + 1 : 0;
    
    $melodyHasCannt = preg_match('/V:M.*?w:.*?(chin|hin|ho|dro)/s', $result3['text']);
    $bagpipesHasCannt = preg_match('/V:Bagpipes.*?w:.*?(chin|hin|ho|dro)/s', $result3['text']);
    
    echo "\n--- Validation ---\n";
    echo "✓ Melody bars: $melodyBars\n";
    echo "✓ Bagpipes bars: $bagpipesBars\n";
    echo "✓ Melody has canntaireachd: " . ($melodyHasCannt ? 'YES (BAD!)' : 'NO (GOOD!)') . "\n";
    echo "✓ Bagpipes has canntaireachd: " . ($bagpipesHasCannt ? 'YES (GOOD!)' : 'NO (BAD!)') . "\n";
    
    $test3Pass = $melodyBars > 0 && $bagpipesBars > 0 && !$melodyHasCannt && $bagpipesHasCannt;
    echo "\nTest 3: " . ($test3Pass ? "✅ PASS" : "❌ FAIL") . "\n";
} else {
    echo "test-Suo.abc not found, skipping Test 3\n";
    $test3Pass = true; // Don't fail if file not present
}

// Summary
echo "\n\n=== SUMMARY ===\n";
$allPass = $test1Pass && $test2Pass && $test3Pass;
echo "Test 1 (Simple): " . ($test1Pass ? "✅" : "❌") . "\n";
echo "Test 2 (Existing Bagpipes): " . ($test2Pass ? "✅" : "❌") . "\n";
echo "Test 3 (Real-world): " . ($test3Pass ? "✅" : "❌") . "\n";
echo "\nOverall: " . ($allPass ? "✅ ALL TESTS PASSED" : "❌ SOME TESTS FAILED") . "\n";

exit($allPass ? 0 : 1);
