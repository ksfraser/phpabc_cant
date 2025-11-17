<?php
/**
 * Unit Tests for Transpose Strategies
 * 
 * Tests the three transpose strategies: MIDI, Bagpipe, and Orchestral
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\Transpose\MidiTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\BagpipeTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\OrchestralTransposeStrategy;
use Ksfraser\PhpabcCanntaireachd\Transpose\InstrumentTransposeMapper;

$testResults = [];
$totalTests = 0;
$passedTests = 0;

echo "=== Transpose Strategy Tests ===\n\n";

/* Test 1: MIDI Strategy - All Zeros */
$totalTests++;
echo "Test 1: MIDI Strategy - All instruments transpose=0\n";
$strategy = new MidiTransposeStrategy();
$instruments = ['Piano', 'Trumpet', 'Clarinet', 'Alto Sax', 'French Horn', 'Bagpipes'];
$allZero = true;
foreach ($instruments as $inst) {
    if ($strategy->getTranspose($inst) !== 0) {
        $allZero = false;
        break;
    }
}
if ($allZero && $strategy->getName() === 'midi') {
    echo "  ✅ PASS: All instruments at concert pitch (transpose=0)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Expected all instruments transpose=0\n";
}
echo "\n";

/* Test 2: Bagpipe Strategy - Bagpipes vs Concert Pitch */
$totalTests++;
echo "Test 2: Bagpipe Strategy - Transpose values\n";
$strategy = new BagpipeTransposeStrategy();
$bagpipeTranspose = $strategy->getTranspose('Bagpipes');
$pianoTranspose = $strategy->getTranspose('Piano');
$trumpetTranspose = $strategy->getTranspose('Trumpet');

if ($bagpipeTranspose === 0 && $pianoTranspose === 2 && $trumpetTranspose === 2) {
    echo "  ✅ PASS: Bagpipes=0, Piano=2, Trumpet=2\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Expected Bagpipes=0, Piano=2, Trumpet=2\n";
    echo "  Got: Bagpipes=$bagpipeTranspose, Piano=$pianoTranspose, Trumpet=$trumpetTranspose\n";
}
echo "\n";

/* Test 3: Orchestral Strategy - Bb Instruments */
$totalTests++;
echo "Test 3: Orchestral Strategy - Bb Instruments\n";
$strategy = new OrchestralTransposeStrategy();
$trumpet = $strategy->getTranspose('Trumpet');
$clarinet = $strategy->getTranspose('Clarinet');
$tenorSax = $strategy->getTranspose('Tenor Sax');

if ($trumpet === 2 && $clarinet === 2 && $tenorSax === 2) {
    echo "  ✅ PASS: Trumpet=2, Clarinet=2, Tenor Sax=2\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Bb instruments should transpose=2\n";
    echo "  Got: Trumpet=$trumpet, Clarinet=$clarinet, Tenor Sax=$tenorSax\n";
}
echo "\n";

/* Test 4: Orchestral Strategy - Eb Instruments */
$totalTests++;
echo "Test 4: Orchestral Strategy - Eb Instruments\n";
$strategy = new OrchestralTransposeStrategy();
$altoSax = $strategy->getTranspose('Alto Sax');
$baritoneSax = $strategy->getTranspose('Baritone Sax');

if ($altoSax === 9 && $baritoneSax === 9) {
    echo "  ✅ PASS: Alto Sax=9, Baritone Sax=9\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Eb instruments should transpose=9\n";
    echo "  Got: Alto Sax=$altoSax, Baritone Sax=$baritoneSax\n";
}
echo "\n";

/* Test 5: Orchestral Strategy - F Instruments */
$totalTests++;
echo "Test 5: Orchestral Strategy - F Instruments\n";
$strategy = new OrchestralTransposeStrategy();
$horn = $strategy->getTranspose('French Horn');
$englishHorn = $strategy->getTranspose('English Horn');

if ($horn === 7 && $englishHorn === 7) {
    echo "  ✅ PASS: French Horn=7, English Horn=7\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: F instruments should transpose=7\n";
    echo "  Got: French Horn=$horn, English Horn=$englishHorn\n";
}
echo "\n";

/* Test 6: Orchestral Strategy - Concert Pitch Instruments */
$totalTests++;
echo "Test 6: Orchestral Strategy - Concert Pitch Instruments\n";
$strategy = new OrchestralTransposeStrategy();
$piano = $strategy->getTranspose('Piano');
$flute = $strategy->getTranspose('Flute');
$violin = $strategy->getTranspose('Violin');
$trombone = $strategy->getTranspose('Trombone');

if ($piano === 0 && $flute === 0 && $violin === 0 && $trombone === 0) {
    echo "  ✅ PASS: Piano=0, Flute=0, Violin=0, Trombone=0\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Concert pitch instruments should transpose=0\n";
    echo "  Got: Piano=$piano, Flute=$flute, Violin=$violin, Trombone=$trombone\n";
}
echo "\n";

/* Test 7: InstrumentMapper - Abbreviations */
$totalTests++;
echo "Test 7: InstrumentMapper - Abbreviations\n";
$tpt = InstrumentTransposeMapper::getTranspose('Tpt');
$cl = InstrumentTransposeMapper::getTranspose('Cl');
$fl = InstrumentTransposeMapper::getTranspose('Fl');
$hn = InstrumentTransposeMapper::getTranspose('Hn');

if ($tpt === 2 && $cl === 2 && $fl === 0 && $hn === 7) {
    echo "  ✅ PASS: Abbreviations mapped correctly (Tpt=2, Cl=2, Fl=0, Hn=7)\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Abbreviations not mapped correctly\n";
    echo "  Got: Tpt=$tpt, Cl=$cl, Fl=$fl, Hn=$hn\n";
}
echo "\n";

/* Test 8: InstrumentMapper - Variations */
$totalTests++;
echo "Test 8: InstrumentMapper - Instrument Name Variations\n";
$trumpet1 = InstrumentTransposeMapper::getTranspose('Trumpet');
$trumpet2 = InstrumentTransposeMapper::getTranspose('Trumpet I');
$trumpet3 = InstrumentTransposeMapper::getTranspose('Bb Trumpet');

if ($trumpet1 === 2 && $trumpet2 === 2 && $trumpet3 === 2) {
    echo "  ✅ PASS: Trumpet variations all map to 2\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Trumpet variations should all map to 2\n";
    echo "  Got: Trumpet=$trumpet1, Trumpet I=$trumpet2, Bb Trumpet=$trumpet3\n";
}
echo "\n";

/* Test 9: InstrumentMapper - Unknown Instruments Default to 0 */
$totalTests++;
echo "Test 9: InstrumentMapper - Unknown Instruments\n";
$unknown1 = InstrumentTransposeMapper::getTranspose('Theremin');
$unknown2 = InstrumentTransposeMapper::getTranspose('Kazoo');

if ($unknown1 === 0 && $unknown2 === 0) {
    echo "  ✅ PASS: Unknown instruments default to transpose=0\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: Unknown instruments should default to transpose=0\n";
    echo "  Got: Theremin=$unknown1, Kazoo=$unknown2\n";
}
echo "\n";

/* Test 10: Bagpipe Strategy - Bagpipe Name Variations */
$totalTests++;
echo "Test 10: Bagpipe Strategy - Bagpipe Name Variations\n";
$strategy = new BagpipeTransposeStrategy();
$bagpipes = $strategy->getTranspose('Bagpipes');
$pipes = $strategy->getTranspose('Pipes');
$highlandBagpipe = $strategy->getTranspose('Highland Bagpipe');
$chanter = $strategy->getTranspose('Chanter');

if ($bagpipes === 0 && $pipes === 0 && $highlandBagpipe === 0 && $chanter === 0) {
    echo "  ✅ PASS: All bagpipe variations transpose=0\n";
    $passedTests++;
} else {
    echo "  ❌ FAIL: All bagpipe variations should transpose=0\n";
    echo "  Got: Bagpipes=$bagpipes, Pipes=$pipes, Highland=$highlandBagpipe, Chanter=$chanter\n";
}
echo "\n";

/* Summary */
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
