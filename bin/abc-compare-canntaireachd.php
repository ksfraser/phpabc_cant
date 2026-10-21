#!/usr/bin/env php
<?php
/**
 * ABC Canntaireachd Comparison Tool
 *
 * Converts the target voices of every ABC file in a directory and compares the
 * generated canntaireachd with the canntaireachd already written in the file.
 *
 * The score corpus holds hand-written canntaireachd, so the comparison shows
 * where the two disagree. It is a report, not a gate: the two sources use
 * different word conventions for some notes, and the hand-written lines
 * contain errors of their own.
 *
 * The "words that differ" table aligns the two sequences note by note. Once
 * one word differs the alignment drifts, so the counts for the words after
 * the first difference of a line are not reliable.
 *
 * Usage:
 *   php abc-compare-canntaireachd.php <directory> [--details] [--limit=N]
 *
 * Options:
 *   --details     Show the individual disagreements
 *   --limit=N     Only look at the first N files
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @since 2.1.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use ksfraser\PhpabcCanntaireachd\CanntGenerator;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;

$args = array_slice($argv, 1);
$directory = $args[0] ?? '';
$details = in_array('--details', $args, true);
$limit = 0;
foreach ($args as $arg) {
    if (preg_match('/^--limit=(\d+)$/', $arg, $matches) === 1) {
        $limit = (int)$matches[1];
    }
}

if ($directory === '' || !is_dir($directory)) {
    fwrite(STDERR, "Usage: php " . basename($argv[0]) . " <directory> [--details] [--limit=N]\n");
    exit(2);
}

$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'abc') {
        $files[] = $file->getPathname();
    }
}
sort($files);

$pass = new AbcCanntaireachdPass(new TokenDictionary());
$generator = new CanntGenerator(new TokenDictionary());

/**
 * The syllables of a canntaireachd line, bar lines removed.
 *
 * @param string $line
 * @return string[]
 */
function syllables(string $line): array
{
    $line = preg_replace('/^w:\s*/', '', $line);
    $parts = preg_split('/\s+/', trim($line));
    $out = [];
    foreach ($parts as $part) {
        if ($part === '' || $part === '|') {
            continue;
        }
        $out[] = $part;
    }
    return $out;
}

$counters = [
    'files' => 0,
    'withCannt' => 0,
    'lines' => 0,
    'exact' => 0,
    'caseOnly' => 0,
    'countMismatch' => 0,
    'tokenMismatch' => 0,
];
$byToken = [];
$examples = [];

foreach ($files as $path) {
    if ($limit > 0 && $counters['files'] >= $limit) {
        break;
    }
    $counters['files']++;
    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }
    $lines = preg_split('/\r?\n/', $content);
    if ($lines === false) {
        continue;
    }

    // The canntaireachd already in the file, keyed by the music line it
    // belongs to. A lower case "w:" is canntaireachd; an upper case "W:" is
    // English lyrics and is not compared.
    $existing = [];
    $lastMusic = null;
    foreach ($lines as $line) {
        $text = trim($line);
        if (preg_match('/^w:/', $text) === 1) {
            if ($lastMusic !== null && !isset($existing[$lastMusic])) {
                $existing[$lastMusic] = $text;
            }
            continue;
        }
        if (preg_match('/^W:/', $text) === 1) {
            continue;
        }
        if ($text === '' || str_starts_with($text, '%') || preg_match('/^[A-Za-z]:/', $text) === 1) {
            continue;
        }
        $lastMusic = $line;
    }
    if ($existing === []) {
        continue;
    }
    $counters['withCannt']++;

    // What this project generates for the same file.
    $result = $pass->process($lines);
    $generated = [];
    $previous = null;
    foreach ($result['lines'] as $line) {
        $text = trim($line);
        if (preg_match('/^w:/i', $text) === 1) {
            if ($previous !== null) {
                $generated[$previous] = $text;
            }
            continue;
        }
        $previous = $line;
    }

    foreach ($generated as $music => $mine) {
        if (!isset($existing[$music])) {
            continue;
        }
        $counters['lines']++;
        $theirs = syllables($existing[$music]);
        $mineSyllables = syllables($mine);

        if ($theirs === $mineSyllables) {
            $counters['exact']++;
            continue;
        }
        if (array_map('strtolower', $theirs) === array_map('strtolower', $mineSyllables)) {
            $counters['caseOnly']++;
            continue;
        }

        // Align the two sequences note by note, so that the disagreement can
        // be reported against the dictionary key that caused it.
        $notes = [];
        foreach ($generator->getItems($music) as $item) {
            if ($item[0] === 'note') {
                $notes[] = $item;
            }
        }
        if (count($theirs) !== count($notes)) {
            $counters['countMismatch']++;
            if ($details && count($examples) < 20) {
                $examples[] = [basename($path), $existing[$music], $mine, 'different number of syllables'];
            }
            continue;
        }
        $counters['tokenMismatch']++;
        foreach ($notes as $index => $note) {
            if (strcasecmp($note[1], $theirs[$index]) === 0) {
                continue;
            }
            $token = $note[2];
            if (!isset($byToken[$token])) {
                $byToken[$token] = ['generated' => $note[1], 'count' => 0, 'scores' => []];
            }
            $byToken[$token]['count']++;
            $byToken[$token]['scores'][$theirs[$index]] = ($byToken[$token]['scores'][$theirs[$index]] ?? 0) + 1;
        }
    }
}

printf("files scanned                : %d\n", $counters['files']);
printf("files with canntaireachd    : %d\n", $counters['withCannt']);
printf("lines compared               : %d\n", $counters['lines']);
printf("  identical                  : %d (%.1f%%)\n", $counters['exact'],
    $counters['lines'] ? $counters['exact'] * 100 / $counters['lines'] : 0);
printf("  identical apart from case  : %d\n", $counters['caseOnly']);
printf("  different number of words  : %d\n", $counters['countMismatch']);
printf("  different words             : %d\n", $counters['tokenMismatch']);

uasort($byToken, static fn ($a, $b) => $b['count'] <=> $a['count']);
if ($byToken !== []) {
    echo "\n=== words that differ, by dictionary key ===\n";
    printf("%-14s %-12s %-7s %s\n", 'token', 'generated', 'count', 'in the scores');
    foreach ($byToken as $token => $entry) {
        $scores = $entry['scores'];
        arsort($scores);
        $list = [];
        foreach ($scores as $word => $count) {
            $list[] = "$word($count)";
        }
        printf("%-14s %-12s %-7d %s\n", $token, $entry['generated'], $entry['count'], implode(' ', $list));
    }
}

if ($details && $examples !== []) {
    echo "\n=== examples ===\n";
    foreach ($examples as $example) {
        printf("\n--- %s (%s)\n  existing : %s\n  generated: %s\n", $example[0], $example[3], $example[1], $example[2]);
    }
}

exit(0);
