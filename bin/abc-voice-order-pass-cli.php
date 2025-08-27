#!/usr/bin/env php
<?php
// Pass 4: Voice order reordering

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;

// Support --output option
$outputFile = null;
foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if (preg_match('/^--output=(.+)$/', $arg, $m)) {
        $outputFile = $m[1];
    } elseif (!isset($file)) {
        $file = $arg;
    } elseif (!isset($xnum)) {
        $xnum = $arg;
    }
}
if (!isset($file) || !isset($xnum)) {
    echo "Usage: php bin/abc-voice-order-pass-cli.php <abcfile> <tune_number> [--output=out.txt]\n";
    exit(1);
}
if (!file_exists($file)) {
    $bagpipes = [];
    $melody = [];
    $harmony = [];
    $snare = [];
    $tenor = [];
    $bass = [];
    $other = [];
    // Dynamically extract voice order from voice order defaults table
    $voiceOrder = [];
    $orderFile = __DIR__ . '/../sql/abc_voice_order_defaults_schema.sql';
    if (file_exists($orderFile)) {
        $orderSchema = file_get_contents($orderFile);
        if (preg_match_all("/'(.+?)',\s*(\d+)/", $orderSchema, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $voice = $m[1];
                $order = (int)$m[2];
                $voiceOrder[$voice] = $order;
            }
        }
    }
    // Exclude percussion and pipes for 'other' sorting
    $exclude = ['Bagpipes', 'Melody', 'Harmony', 'Drums', 'Snare', 'Tenor', 'Bass'];

// Custom voice order logic
function reorderVoices($lines, $voiceOrder, $exclude) {
    $bagpipes = [];
    $melody = [];
    $harmony = [];
    $snare = [];
    $tenor = [];
    $bass = [];
    $other = [];
    foreach ($lines as $line) {
        if (preg_match('/^V:.*Bagpipe/i', $line)) {
            $bagpipes[] = $line;
        } elseif (preg_match('/^V:.*Melody/i', $line)) {
            $melody[] = $line;
        } elseif (preg_match('/^V:.*Harmony/i', $line)) {
            $harmony[] = $line;
        } elseif (preg_match('/^V:.*Snare/i', $line)) {
            $snare[] = $line;
        } elseif (preg_match('/^V:.*Tenor/i', $line)) {
            $tenor[] = $line;
        } elseif (preg_match('/^V:.*Bass/i', $line)) {
            $bass[] = $line;
        } else {
            // Try to extract voice name
            if (preg_match('/^V:\s*([^\s]+)/', $line, $m)) {
                $voice = $m[1];
                $other[] = ['line' => $line, 'voice' => $voice];
            } else {
                $other[] = ['line' => $line, 'voice' => null];
            }
        }
    }
    // Sort 'other' by voice order, excluding main and percussion voices
    $other = array_filter($other, function($v) use ($exclude) {
        return !in_array($v['voice'], $exclude, true);
    });
    usort($other, function($a, $b) use ($voiceOrder) {
        $aOrder = isset($voiceOrder[$a['voice']]) ? $voiceOrder[$a['voice']] : 99;
        $bOrder = isset($voiceOrder[$b['voice']]) ? $voiceOrder[$b['voice']] : 99;
        return $aOrder <=> $bOrder;
    });
    $otherLines = array_map(function($v) { return $v['line']; }, $other);
    return array_merge($bagpipes, $melody, $harmony, $otherLines, $snare, $tenor, $bass);
}

$result = reorderVoices($lines, $voiceOrder, $exclude);

$output = implode("\n", $result) . "\n";
if ($outputFile) {
    CliOutputWriter::write($output, $outputFile);
    echo "Voice order output written to $outputFile\n";
} else {
    echo $output;
}
