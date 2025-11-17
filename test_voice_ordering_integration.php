<?php
/**
 * Integration Test for Voice Ordering System
 *
 * Tests the complete voice ordering pipeline from ABC input to ordered output
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\OrchestralOrderStrategy;
use Ksfraser\PhpabcCanntaireachd\VoiceOrdering\CustomOrderStrategy;

$testResults = [];
$totalTests = 0;
$passedTests = 0;

echo "=== Voice Ordering Integration Tests ===\n\n";

// Test ABC content with multiple voices
$orchestralAbc = <<<'ABC'
X:1
T:Test Orchestra
M:4/4
L:1/4
K:C
V:Cello
C D E F|
V:Trumpet
G A B c|
V:Violin
d e f g|
V:Flute
a b c' d'|
V:Timpani
C, D, E, F,|
ABC;

// Test 1: Source Order (Default)
$totalTests++;
echo "Test 1: Source Order (Default)\n";
$config = new AbcProcessorConfig();
$config->voiceOrderingMode = 'source';
$pass = new AbcVoiceOrderPass(null, $config);
$lines = explode("\n", $orchestralAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);

// Check that Cello appears before Trumpet (original order preserved)
$celloPos = strpos($resultText, 'V:Cello');
$trumpetPos = strpos($resultText, 'V:Trumpet');
if ($celloPos !== false && $trumpetPos !== false && $celloPos < $trumpetPos) {
    echo "  ✅ PASS: Source order preserved (Cello before Trumpet)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Source order not preserved\n";
}
echo "\n";

// Test 2: Orchestral Order
$totalTests++;
echo "Test 2: Orchestral Order\n";
$config = new AbcProcessorConfig();
$config->voiceOrderingMode = 'orchestral';
$pass = new AbcVoiceOrderPass(null, $config);
$lines = explode("\n", $orchestralAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);

// Check orchestral order: Flute (woodwinds) < Trumpet (brass) < Timpani (percussion) < Cello/Violin (strings)
$flutePos = strpos($resultText, 'V:Flute');
$trumpetPos = strpos($resultText, 'V:Trumpet');
$timpaniPos = strpos($resultText, 'V:Timpani');
$celloPos = strpos($resultText, 'V:Cello');

if ($flutePos !== false && $trumpetPos !== false && $timpaniPos !== false && $celloPos !== false &&
    $flutePos < $trumpetPos && $trumpetPos < $timpaniPos && $timpaniPos < $celloPos) {
    echo "  ✅ PASS: Orchestral order correct (Woodwinds, Brass, Percussion, Strings)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Orchestral order incorrect\n";
    echo "  Positions: Flute=$flutePos, Trumpet=$trumpetPos, Timpani=$timpaniPos, Cello=$celloPos\n";
}
echo "\n";

// Test 3: Custom Order
$totalTests++;
echo "Test 3: Custom Order (Bagpipe Band)\n";

$bagpipeAbc = <<<'ABC'
X:1
T:Test Bagpipe Band
M:4/4
L:1/4
K:HP
V:Piano
C D E F|
V:Snare
z z z z|
V:Bagpipes
A B c d|
V:Bass
C, D, E, F,|
V:Tenor
z z z z|
ABC;

$config = new AbcProcessorConfig();
$config->voiceOrderingMode = 'custom';
$config->customVoiceOrder = ['Bagpipes', 'Tenor', 'Snare', 'Bass', 'Piano'];
$pass = new AbcVoiceOrderPass(null, $config);
$lines = explode("\n", $bagpipeAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);

// Check custom order
$bagpipesPos = strpos($resultText, 'V:Bagpipes');
$tenorPos = strpos($resultText, 'V:Tenor');
$snarePos = strpos($resultText, 'V:Snare');
$bassPos = strpos($resultText, 'V:Bass');
$pianoPos = strpos($resultText, 'V:Piano');

if ($bagpipesPos !== false && $tenorPos !== false && $snarePos !== false &&
    $bagpipesPos < $tenorPos && $tenorPos < $snarePos && $snarePos < $bassPos && $bassPos < $pianoPos) {
    echo "  ✅ PASS: Custom order correct (Bagpipes, Tenor, Snare, Bass, Piano)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Custom order incorrect\n";
    echo "  Positions: Bagpipes=$bagpipesPos, Tenor=$tenorPos, Snare=$snarePos, Bass=$bassPos, Piano=$pianoPos\n";
}
echo "\n";

// Test 4: Strategy Injection
$totalTests++;
echo "Test 4: Strategy Injection (Direct Strategy)\n";
$strategy = new OrchestralOrderStrategy();
$pass = new AbcVoiceOrderPass($strategy);
$lines = explode("\n", $orchestralAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);

// Check orchestral order again
$flutePos = strpos($resultText, 'V:Flute');
$trumpetPos = strpos($resultText, 'V:Trumpet');
if ($flutePos !== false && $trumpetPos !== false && $flutePos < $trumpetPos) {
    echo "  ✅ PASS: Strategy injection works (Flute before Trumpet)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Strategy injection failed\n";
}
echo "\n";

// Test 5: Empty/Single Voice
$totalTests++;
echo "Test 5: Single Voice (No Reordering Needed)\n";

$singleVoiceAbc = <<<'ABC'
X:1
T:Single Voice
M:4/4
L:1/4
K:C
V:Melody
C D E F|
ABC;

$config = new AbcProcessorConfig();
$config->voiceOrderingMode = 'orchestral';
$pass = new AbcVoiceOrderPass(null, $config);
$lines = explode("\n", $singleVoiceAbc);
$result = $pass->process($lines);
$resultText = implode("\n", $result);

// Should still have the voice
if (strpos($resultText, 'V:Melody') !== false) {
    echo "  ✅ PASS: Single voice preserved\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Single voice lost\n";
}
echo "\n";

// Test 6: Configuration Mode Switching
$totalTests++;
echo "Test 6: Configuration Mode Switching\n";
$config = new AbcProcessorConfig();

// Start with source order
$config->voiceOrderingMode = 'source';
$pass = new AbcVoiceOrderPass(null, $config);
$lines = explode("\n", $orchestralAbc);
$result1 = $pass->process($lines);
$result1Text = implode("\n", $result1);
$cello1Pos = strpos($result1Text, 'V:Cello');
$trumpet1Pos = strpos($result1Text, 'V:Trumpet');

// Switch to orchestral order
$config->voiceOrderingMode = 'orchestral';
$pass2 = new AbcVoiceOrderPass(null, $config);
$result2 = $pass2->process($lines);
$result2Text = implode("\n", $result2);
$flute2Pos = strpos($result2Text, 'V:Flute');
$trumpet2Pos = strpos($result2Text, 'V:Trumpet');
$cello2Pos = strpos($result2Text, 'V:Cello');

// Source order should have Cello before Trumpet, orchestral should have Flute before Cello
if ($cello1Pos < $trumpet1Pos && $flute2Pos < $cello2Pos) {
    echo "  ✅ PASS: Configuration mode switching works\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Configuration mode switching failed\n";
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
