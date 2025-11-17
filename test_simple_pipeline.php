<?php
/**
 * Simple debug test for processWithTransforms
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline;
use Ksfraser\PhpabcCanntaireachd\Transform\VoiceCopyTransform;
use Ksfraser\PhpabcCanntaireachd\Transform\CanntaireachdTransform;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

$simpleAbc = <<<ABC
X:1
T:Test Tune
M:4/4
L:1/4
K:D
V:M name="Melody"
A B c d|

ABC;

// Create minimal dictionary
$dict = new TokenDictionary();
$dict->prepopulate([
    'A' => ['chin'],
    'B' => ['hin'],
    'c' => ['ho'],
    'd' => ['dro']
]);

// Create transforms
$transforms = [
    new VoiceCopyTransform(),
    new CanntaireachdTransform($dict)
];

// Create pipeline and process
$pipeline = new AbcProcessingPipeline([]);
$result = $pipeline->processWithTransforms($simpleAbc, $transforms, false);

echo "=== ERRORS ===\n";
print_r($result['errors']);

echo "\n=== RESULT TEXT ===\n";
echo $result['text'];
echo "\n";

// Check specific patterns
echo "\n=== VALIDATION ===\n";
$text = $result['text'];
echo "Has V:M: " . (strpos($text, 'V:M') !== false ? 'YES' : 'NO') . "\n";
echo "Has V:Bagpipes: " . (strpos($text, 'V:Bagpipes') !== false ? 'YES' : 'NO') . "\n";
echo "Has w: lines: " . (strpos($text, 'w:') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'chin': " . (strpos($text, 'chin') !== false ? 'YES' : 'NO') . "\n";
