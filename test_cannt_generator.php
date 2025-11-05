<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\CanntGenerator;

echo "Testing CanntGenerator with abc_dict.php\n";

$gen = new CanntGenerator();
$result = $gen->generateForNotes('D2 E2 | F2 G2');
echo "Input: D2 E2 | F2 G2\n";
echo "Output: $result\n";

// Test a few more tokens
$result2 = $gen->generateForNotes('A B C');
echo "Input: A B C\n";
echo "Output: $result2\n";

$result3 = $gen->generateForNotes('z |');
echo "Input: z |\n";
echo "Output: $result3\n";

echo "Test completed.\n";