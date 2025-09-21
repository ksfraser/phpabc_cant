<?php
$content = file_get_contents("test-multi.abc");
$lines = explode("\n", $content);
foreach ($lines as $line) {
    if (preg_match('/^V:M\s/', $line)) {
        echo "MATCH: $line\n";
    }
    if (preg_match('/name="Melody"/', $line)) {
        echo "NAME MATCH: $line\n";
    }
}
?>
