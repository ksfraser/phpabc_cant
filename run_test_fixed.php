<?php
require 'vendor/autoload.php';

$test = new TestAbcProcessor();
try {
    $test->testCanntDiff();
    echo "testCanntDiff passed\n";
} catch (Exception $e) {
    echo "testCanntDiff failed: " . $e->getMessage() . "\n";
}
?>
