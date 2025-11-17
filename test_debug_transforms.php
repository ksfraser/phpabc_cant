<?php
/**
 * Debug version with verbose logging
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline;
use Ksfraser\PhpabcCanntaireachd\Transform\VoiceCopyTransform;
use Ksfraser\PhpabcCanntaireachd\Transform\CanntaireachdTransform;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

$simpleAbc = <<<ABC
X:1
T:Test Tune
M:4/4
L:1/4
K:D
V:M name="Melody"
A B c d|

ABC;

echo "=== Parsing ABC ===\n";
$tune = AbcTune::parse($simpleAbc);
echo "Parsed tune has " . count($tune->getVoices()) . " voices\n";

foreach ($tune->getVoices() as $voiceId => $voice) {
    echo "Voice: $voiceId\n";
    $bars = $tune->getBarsForVoice($voiceId);
    echo "  Bars: " . count($bars) . "\n";
    foreach ($bars as $idx => $bar) {
        $noteCount = isset($bar->notes) ? count($bar->notes) : 0;
        $contentText = isset($bar->contentText) ? $bar->contentText : 'N/A';
        echo "  Bar $idx: notes=$noteCount, contentText=$contentText\n";
    }
}

echo "\n=== Applying VoiceCopyTransform ===\n";
$voiceCopy = new VoiceCopyTransform();
$tune = $voiceCopy->transform($tune);
echo "After VoiceCopyTransform: " . count($tune->getVoices()) . " voices\n";

foreach ($tune->getVoices() as $voiceId => $voice) {
    echo "Voice: $voiceId\n";
    $bars = $tune->getBarsForVoice($voiceId);
    echo "  Bars: " . count($bars) . "\n";
}

echo "\n=== Applying CanntaireachdTransform ===\n";
// Load the real ABC dictionary
include __DIR__ . '/src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
$dict = new TokenDictionary();
$dict->prepopulate($abc);

$canntTransform = new CanntaireachdTransform($dict);
$tune = $canntTransform->transform($tune);

echo "\n=== Rendering Result ===\n";
$result = $tune->renderSelf();
echo $result;
echo "\n";

echo "\n=== Analysis ===\n";
echo "Has V:Bagpipes: " . (strpos($result, 'V:Bagpipes') !== false ? 'YES' : 'NO') . "\n";
echo "Has w: lines: " . (strpos($result, 'w:') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'chin': " . (strpos($result, 'chin') !== false ? 'YES' : 'NO') . "\n";
