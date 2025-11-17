<?php
/**
 * Debug Voice Ordering Integration
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;

echo "=== Voice Ordering Debug ===\n\n";

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

echo "Input ABC:\n";
echo $orchestralAbc . "\n\n";

// Test with orchestral ordering
$config = new AbcProcessorConfig();
$config->voiceOrderingMode = 'orchestral';
$pass = new AbcVoiceOrderPass(null, $config);
$lines = explode("\n", $orchestralAbc);

echo "Processing with orchestral order...\n\n";
$result = $pass->process($lines);

echo "Output ABC:\n";
foreach ($result as $line) {
    echo $line . "\n";
}
