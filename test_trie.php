<?php
require_once 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\CanntGenerator;

$dict = new TokenDictionary();
// Simulate prepopulate
$dict->prepopulate([
    'A' => ['cannt_token' => 'dar', 'bmw_token' => null, 'description' => 'Test A'],
    'B' => ['cannt_token' => 'dod', 'bmw_token' => null, 'description' => 'Test B'],
    'C' => ['cannt_token' => 'hid', 'bmw_token' => null, 'description' => 'Test C'],
    'D' => ['cannt_token' => 'dar', 'bmw_token' => null, 'description' => 'Test D']
]);

$generator = new CanntGenerator($dict);

$input = 'A B C D';
$result = $generator->generateForNotes($input);

echo "Input: $input\n";
echo "Output: $result\n";
echo "Expected: dar dod hid dar\n";
?>