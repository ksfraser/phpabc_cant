<?php
// Integration test: VoiceCopyTransform + CanntaireachdTransform

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\Transform\VoiceCopyTransform;
use Ksfraser\PhpabcCanntaireachd\Transform\CanntaireachdTransform;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

echo "Integration Test: VoiceCopyTransform + CanntaireachdTransform\n\n";
echo "Testing with test-Suo.abc...\n\n";

// Read the file
$abcContent = file_get_contents(__DIR__ . '/test-Suo.abc');
if (!$abcContent) {
    echo "❌ FAIL: Could not read test-Suo.abc\n";
    exit(1);
}

// Parse the ABC
$tune = AbcTune::parse($abcContent);
if (!$tune) {
    echo "❌ FAIL: Could not parse test-Suo.abc\n";
    exit(1);
}

echo "✅ Parsed test-Suo.abc\n";

// Check initial state
$initialM = $tune->hasVoice('M') ? count($tune->getBarsForVoice('M')) : 0;
$initialBagpipes = $tune->hasVoice('Bagpipes') ? count($tune->getBarsForVoice('Bagpipes')) : 0;

echo "Initial state:\n";
echo "  M voice: " . ($tune->hasVoice('M') ? "exists with $initialM bars" : "does not exist") . "\n";
echo "  Bagpipes voice: " . ($tune->hasVoice('Bagpipes') ? "exists with $initialBagpipes bars" : "does not exist") . "\n\n";

// Apply VoiceCopyTransform
echo "Step 1: Applying VoiceCopyTransform...\n";
$voiceCopyTransform = new VoiceCopyTransform();
$tune = $voiceCopyTransform->transform($tune);

$afterCopyM = $tune->hasVoice('M') ? count($tune->getBarsForVoice('M')) : 0;
$afterCopyBagpipes = $tune->hasVoice('Bagpipes') ? count($tune->getBarsForVoice('Bagpipes')) : 0;

echo "After VoiceCopyTransform:\n";
echo "  M voice: " . ($tune->hasVoice('M') ? "exists with $afterCopyM bars" : "does not exist") . "\n";
echo "  Bagpipes voice: " . ($tune->hasVoice('Bagpipes') ? "exists with $afterCopyBagpipes bars" : "does not exist") . "\n\n";

if ($afterCopyBagpipes > 0 && $afterCopyBagpipes === $afterCopyM) {
    echo "✅ VoiceCopyTransform: Copied $afterCopyM bars from M to Bagpipes\n\n";
} else {
    echo "⚠️ VoiceCopyTransform: Unexpected bar counts\n\n";
}

// Apply CanntaireachdTransform
echo "Step 2: Applying CanntaireachdTransform...\n";
$dict = new TokenDictionary();
$canntTransform = new CanntaireachdTransform($dict);
$tune = $canntTransform->transform($tune);

echo "After CanntaireachdTransform:\n";

// Check Melody voice - should have NO canntaireachd
$melodyBars = $tune->getBarsForVoice('M');
$melodyHasCannt = false;
$melodyCanntCount = 0;
if ($melodyBars) {
    foreach ($melodyBars as $bar) {
        if (isset($bar->notes) && is_array($bar->notes)) {
            foreach ($bar->notes as $note) {
                if (method_exists($note, 'getCanntaireachd')) {
                    $cannt = $note->getCanntaireachd();
                    if (!empty($cannt)) {
                        $melodyHasCannt = true;
                        $melodyCanntCount++;
                    }
                }
            }
        }
    }
}

echo "  M voice: " . ($melodyHasCannt ? "❌ HAS canntaireachd ($melodyCanntCount notes)" : "✅ NO canntaireachd") . "\n";

// Check Bagpipes voice - should have canntaireachd
$bagpipesBars = $tune->getBarsForVoice('Bagpipes');
$bagpipesHasCannt = false;
$bagpipesCanntCount = 0;
$bagpipesNoteCount = 0;
if ($bagpipesBars) {
    foreach ($bagpipesBars as $bar) {
        if (isset($bar->notes) && is_array($bar->notes)) {
            foreach ($bar->notes as $note) {
                $bagpipesNoteCount++;
                if (method_exists($note, 'getCanntaireachd')) {
                    $cannt = $note->getCanntaireachd();
                    if (!empty($cannt)) {
                        $bagpipesHasCannt = true;
                        $bagpipesCanntCount++;
                    }
                }
            }
        }
    }
}

echo "  Bagpipes voice: " . ($bagpipesHasCannt ? "✅ HAS canntaireachd ($bagpipesCanntCount of $bagpipesNoteCount notes)" : "❌ NO canntaireachd") . "\n\n";

// Final verdict
if (!$melodyHasCannt && $bagpipesHasCannt) {
    echo "🎉 SUCCESS: Integration test passed!\n";
    echo "   - Melody bars were copied to Bagpipes\n";
    echo "   - Canntaireachd ONLY added to Bagpipes (NOT to Melody)\n";
    
    // Render to show the result
    echo "\n--- Rendered Output ---\n";
    echo $tune->renderSelf();
} else {
    echo "❌ FAIL: Integration test failed\n";
    echo "   Melody has cannt: " . ($melodyHasCannt ? "YES (should be NO)" : "NO") . "\n";
    echo "   Bagpipes has cannt: " . ($bagpipesHasCannt ? "YES" : "NO (should be YES)") . "\n";
}
