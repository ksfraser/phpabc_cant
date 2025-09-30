<?php
require 'vendor/autoload.php';


if ($argc < 2) {
	fwrite(STDERR, "Usage: php test_cannt.php <abc_file>\n");
	exit(1);
}
$abcFile = $argv[1];
if (!file_exists($abcFile)) {
	fwrite(STDERR, "File not found: $abcFile\n");
	exit(1);
}
$abc = file_get_contents($abcFile);
echo "Input:\n$abc\n\n";

$result = Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abc, ['cannt' => 1]);
echo "Output:\n" . implode("\n", $result['lines']) . "\n";
