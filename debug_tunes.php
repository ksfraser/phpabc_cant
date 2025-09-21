<?php
$content = file_get_contents('test-multi.abc');
$lines = explode("\n", $content);
$tunes = [];
$currentTune = [];
foreach ($lines as $line) {
    if (preg_match('/^X:/', $line)) {
        if (!empty($currentTune)) {
            $tunes[] = $currentTune;
        }
        $currentTune = [$line];
    } elseif (!empty($currentTune)) {
        $currentTune[] = $line;
    }
}
if (!empty($currentTune)) {
    $tunes[] = $currentTune;
}
echo 'Number of tunes: ' . count($tunes) . "\n";
foreach ($tunes as $i => $tune) {
    echo "Tune " . ($i+1) . ": " . $tune[0] . "\n";
    $hasMelody = false;
    $hasBagpipes = false;
    foreach ($tune as $line) {
        if (preg_match('/name="Melody"/', $line)) {
            $hasMelody = true;
        }
        if (preg_match('/^V:Bagpipes/', $line)) {
            $hasBagpipes = true;
        }
    }
    echo "  Has Melody: " . ($hasMelody ? 'YES' : 'NO') . "\n";
    echo "  Has Bagpipes: " . ($hasBagpipes ? 'YES' : 'NO') . "\n";
    echo "  Should copy: " . (($hasMelody && !$hasBagpipes) ? 'YES' : 'NO') . "\n";
}
?>
