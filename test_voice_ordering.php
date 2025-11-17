<?php
/**
 * Voice Ordering Strategy Tests
 *
 * Tests the three voice ordering strategies: Source, Orchestral, Custom
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\SourceOrderStrategy;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\OrchestralOrderStrategy;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\CustomOrderStrategy;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\InstrumentMapper;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\InstrumentFamily;

$testResults = [];
$totalTests = 0;
$passedTests = 0;

echo "=== Voice Ordering Strategy Tests ===\n\n";

// Helper function to create test voices
function createVoices(array $voiceNames): array {
    $voices = [];
    foreach ($voiceNames as $name) {
        $voices[] = new AbcVoice($name);
    }
    return $voices;
}

// Helper function to get voice names from voices array
function getVoiceNames(array $voices): array {
    return array_map(function($voice) {
        return $voice->getVoiceIndicator();
    }, $voices);
}

// Test 1: SourceOrderStrategy preserves original order
$totalTests++;
echo "Test 1: SourceOrderStrategy preserves original order\n";
$strategy = new SourceOrderStrategy();
$voices = createVoices(['Piano', 'Trumpet', 'Violin', 'Flute']);
$ordered = $strategy->orderVoices($voices);
$names = getVoiceNames($ordered);
$expected = ['Piano', 'Trumpet', 'Violin', 'Flute'];
if ($names === $expected) {
    echo "  ✅ PASS: " . implode(', ', $names) . "\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Expected " . implode(', ', $expected) . ", got " . implode(', ', $names) . "\n";
}
echo "\n";

// Test 2: OrchestralOrderStrategy - Standard Orchestra
$totalTests++;
echo "Test 2: OrchestralOrderStrategy - Standard Orchestra\n";
$strategy = new OrchestralOrderStrategy();
$voices = createVoices(['Cello', 'Trumpet', 'Violin', 'Flute', 'Timpani']);
$ordered = $strategy->orderVoices($voices);
$names = getVoiceNames($ordered);
echo "  Input:  " . implode(', ', ['Cello', 'Trumpet', 'Violin', 'Flute', 'Timpani']) . "\n";
echo "  Output: " . implode(', ', $names) . "\n";
// Expected: Flute (woodwinds), Trumpet (brass), Timpani (percussion), Violin, Cello (strings)
if ($names[0] === 'Flute' && $names[1] === 'Trumpet' && $names[2] === 'Timpani') {
    echo "  ✅ PASS: Correct orchestral order (woodwinds, brass, percussion, strings)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Incorrect orchestral order\n";
}
echo "\n";

// Test 3: OrchestralOrderStrategy - Bagpipe Ensemble
$totalTests++;
echo "Test 3: OrchestralOrderStrategy - Bagpipe Ensemble\n";
$strategy = new OrchestralOrderStrategy();
$voices = createVoices(['Bass Drum', 'Bagpipes', 'Snare', 'Piano', 'Tenor']);
$ordered = $strategy->orderVoices($voices);
$names = getVoiceNames($ordered);
echo "  Input:  " . implode(', ', ['Bass Drum', 'Bagpipes', 'Snare', 'Piano', 'Tenor']) . "\n";
echo "  Output: " . implode(', ', $names) . "\n";
// Expected orchestral order: Percussion (priority 3), Keyboards (priority 5), Bagpipes (priority 7)
// So: Snare/Tenor/Bass Drum (percussion), then Piano (keyboards), then Bagpipes
$percussionFirst = in_array($names[0], ['Snare', 'Tenor', 'Bass Drum']);
$pianoBeforeBagpipes = array_search('Piano', $names) < array_search('Bagpipes', $names);
if ($percussionFirst && $pianoBeforeBagpipes) {
    echo "  ✅ PASS: Correct order (percussion, keyboards, bagpipes)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Incorrect bagpipe ensemble order\n";
}
echo "\n";

// Test 4: CustomOrderStrategy - Bagpipe Band Custom Order
$totalTests++;
echo "Test 4: CustomOrderStrategy - Bagpipe Band Custom Order\n";
$customOrder = ['Bagpipes', 'Harmony', 'Tenor', 'Snare', 'Bass', 'Piano'];
$strategy = new CustomOrderStrategy($customOrder);
$voices = createVoices(['Piano', 'Snare', 'Bagpipes', 'Bass', 'Tenor']);
$ordered = $strategy->orderVoices($voices);
$names = getVoiceNames($ordered);
$expected = ['Bagpipes', 'Tenor', 'Snare', 'Bass', 'Piano'];
echo "  Custom Order: " . implode(', ', $customOrder) . "\n";
echo "  Input:  " . implode(', ', ['Piano', 'Snare', 'Bagpipes', 'Bass', 'Tenor']) . "\n";
echo "  Output: " . implode(', ', $names) . "\n";
if ($names === $expected) {
    echo "  ✅ PASS: Voices ordered according to custom configuration\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Expected " . implode(', ', $expected) . "\n";
}
echo "\n";

// Test 5: InstrumentMapper - Various Instruments
$totalTests++;
echo "Test 5: InstrumentMapper - Various Instruments\n";
$mappings = [
    'Violin' => InstrumentFamily::STRINGS,
    'Trumpet' => InstrumentFamily::BRASS,
    'Flute' => InstrumentFamily::WOODWINDS,
    'Piano' => InstrumentFamily::KEYBOARDS,
    'Snare' => InstrumentFamily::PERCUSSION,
    'Bagpipes' => InstrumentFamily::BAGPIPES,
    'Soprano' => InstrumentFamily::VOCALS,
];
$allCorrect = true;
foreach ($mappings as $instrument => $expectedFamily) {
    $family = InstrumentMapper::mapToFamily($instrument);
    if ($family !== $expectedFamily) {
        echo "  ❌ $instrument mapped to $family (expected $expectedFamily)\n";
        $allCorrect = false;
    }
}
if ($allCorrect) {
    echo "  ✅ PASS: All instruments mapped correctly\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Some instruments incorrectly mapped\n";
}
echo "\n";

// Test 6: InstrumentMapper - Variations and Abbreviations
$totalTests++;
echo "Test 6: InstrumentMapper - Variations and Abbreviations\n";
$variations = [
    'Violin I' => InstrumentFamily::STRINGS,
    'Vln 1' => InstrumentFamily::STRINGS,
    'V1' => InstrumentFamily::STRINGS,
    'Tpt' => InstrumentFamily::BRASS,
    'Fl' => InstrumentFamily::WOODWINDS,
    'Pno' => InstrumentFamily::KEYBOARDS,
];
$allCorrect = true;
foreach ($variations as $instrument => $expectedFamily) {
    $family = InstrumentMapper::mapToFamily($instrument);
    if ($family !== $expectedFamily) {
        echo "  ❌ $instrument mapped to $family (expected $expectedFamily)\n";
        $allCorrect = false;
    }
}
if ($allCorrect) {
    echo "  ✅ PASS: All variations mapped correctly\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Some variations incorrectly mapped\n";
}
echo "\n";

// Test 7: OrchestralOrderStrategy - String Section Order
$totalTests++;
echo "Test 7: OrchestralOrderStrategy - String Section Order\n";
$strategy = new OrchestralOrderStrategy();
$voices = createVoices(['Cello', 'Double Bass', 'Violin II', 'Viola', 'Violin I']);
$ordered = $strategy->orderVoices($voices);
$names = getVoiceNames($ordered);
echo "  Input:  " . implode(', ', ['Cello', 'Double Bass', 'Violin II', 'Viola', 'Violin I']) . "\n";
echo "  Output: " . implode(', ', $names) . "\n";
// Expected: Violin I, Violin II, Viola, Cello, Double Bass
if ($names[0] === 'Violin I' && $names[1] === 'Violin II' && $names[4] === 'Double Bass') {
    echo "  ✅ PASS: String section correctly ordered\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: String section incorrectly ordered\n";
}
echo "\n";

// Test 8: CustomOrderStrategy - Pattern Matching
$totalTests++;
echo "Test 8: CustomOrderStrategy - Pattern Matching\n";
$customOrder = ['Bagpipes', 'Tenor', 'Snare'];
$strategy = new CustomOrderStrategy($customOrder);
$voices = createVoices(['Tenor Drum', 'Snare Drum', 'Highland Bagpipes']);
$ordered = $strategy->orderVoices($voices);
$names = getVoiceNames($ordered);
echo "  Custom Order: " . implode(', ', $customOrder) . "\n";
echo "  Input:  " . implode(', ', ['Tenor Drum', 'Snare Drum', 'Highland Bagpipes']) . "\n";
echo "  Output: " . implode(', ', $names) . "\n";
// Should match patterns: Bagpipes -> Highland Bagpipes, Tenor -> Tenor Drum, Snare -> Snare Drum
if ($names[0] === 'Highland Bagpipes' && $names[1] === 'Tenor Drum' && $names[2] === 'Snare Drum') {
    echo "  ✅ PASS: Pattern matching works correctly\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Pattern matching failed\n";
}
echo "\n";

// Test 9: CustomOrderStrategy - Unmatched Voices Go Last
$totalTests++;
echo "Test 9: CustomOrderStrategy - Unmatched Voices Go Last\n";
$customOrder = ['Bagpipes', 'Snare'];
$strategy = new CustomOrderStrategy($customOrder);
$voices = createVoices(['Piano', 'Snare', 'Bagpipes', 'Guitar']);
$ordered = $strategy->orderVoices($voices);
$names = getVoiceNames($ordered);
echo "  Custom Order: " . implode(', ', $customOrder) . "\n";
echo "  Input:  " . implode(', ', ['Piano', 'Snare', 'Bagpipes', 'Guitar']) . "\n";
echo "  Output: " . implode(', ', $names) . "\n";
// Expected: Bagpipes, Snare, then Piano and Guitar (unmatched) in original order
if ($names[0] === 'Bagpipes' && $names[1] === 'Snare' && 
    in_array('Piano', [$names[2], $names[3]]) && in_array('Guitar', [$names[2], $names[3]])) {
    echo "  ✅ PASS: Unmatched voices placed at end\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Unmatched voices not handled correctly\n";
}
echo "\n";

// Summary
echo str_repeat('=', 60) . "\n";
echo "Test Summary\n";
echo str_repeat('=', 60) . "\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";

if ($passedTests === $totalTests) {
    echo "✅ ALL TESTS PASSED\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED\n";
    exit(1);
}
