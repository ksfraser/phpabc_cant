<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\Transform\VoiceCopyTransform;

// Read test-Suo.abc
$abcText = file_get_contents(__DIR__ . '/test-Suo.abc');

echo "===== ORIGINAL FILE =====\n";
echo $abcText;
echo "\n\n";

// Parse the tune
echo "===== PARSING =====\n";
$tune = AbcTune::parse($abcText);
echo "Parsed tune successfully\n";

// Show what voices were found
$voices = $tune->getVoices();
echo "Found " . count($voices) . " voices:\n";
foreach ($voices as $voiceId => $voice) {
    $bars = $tune->getBarsForVoice($voiceId);
    $barCount = $bars ? count($bars) : 0;
    echo "  - $voiceId: $barCount bars\n";
}
echo "\n";

// Apply the VoiceCopyTransform
echo "===== APPLYING VOICE COPY TRANSFORM =====\n";
$transform = new VoiceCopyTransform();
$result = $transform->transform($tune);
echo "Transform applied successfully\n";

// Show voices after transform
$voicesAfter = $result->getVoices();
echo "Found " . count($voicesAfter) . " voices after transform:\n";
foreach ($voicesAfter as $voiceId => $voice) {
    $bars = $result->getBarsForVoice($voiceId);
    $barCount = $bars ? count($bars) : 0;
    echo "  - $voiceId: $barCount bars\n";
}
echo "\n";

// Check if Bagpipes voice was created
if ($result->hasVoice('Bagpipes')) {
    echo "✓ SUCCESS: Bagpipes voice was created!\n";
    $bagpipesBars = $result->getBarsForVoice('Bagpipes');
    echo "  Bagpipes has " . count($bagpipesBars) . " bars\n";
} else {
    echo "✗ FAIL: Bagpipes voice was NOT created\n";
}

// Render the result
echo "\n===== RENDERED OUTPUT =====\n";
$output = $result->renderSelf();
echo $output;
echo "\n";
