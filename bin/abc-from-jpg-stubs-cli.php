#!/usr/bin/env php
<?php
/**
 * ABC-from-JPG Stub Generator
 *
 * IMPORTANT: This tool does NOT perform optical music recognition (OMR).
 * It cannot read musical notes from JPG scans.
 *
 * What it does:
 * - Recursively scans a tunes directory for .jpg/.jpeg files
 * - Creates a corresponding .abc file stub for each image (same basename)
 * - Injects a “movement guide” (throws/doublings/grip/taorluath/etc.) extracted
 *   from the legacy dictionary at src/Ksfraser/phpabc_canntaireachd/abc_dict.php
 *
 * Usage:
 *   php bin/abc-from-jpg-stubs-cli.php --tunes-dir tunes [--overwrite]
 *
 * Options:
 *   --tunes-dir <dir>   Root directory to scan (default: tunes)
 *   --overwrite         Overwrite existing .abc files
 *   -h, --help          Show help
 */

require __DIR__ . '/../vendor/autoload.php';

function showUsage(): void {
    $script = basename($_SERVER['argv'][0] ?? 'abc-from-jpg-stubs-cli.php');
    echo "Usage: php $script --tunes-dir <dir> [--overwrite]\n";
}

$args = $_SERVER['argv'];
$tunesDir = 'tunes';
$overwrite = false;

for ($i = 1; $i < count($args); $i++) {
    $arg = $args[$i];
    if ($arg === '-h' || $arg === '--help') {
        showUsage();
        exit(0);
    }
    if ($arg === '--overwrite') {
        $overwrite = true;
        continue;
    }
    if ($arg === '--tunes-dir' && isset($args[$i + 1])) {
        $tunesDir = $args[++$i];
        continue;
    }
}

if (!is_dir($tunesDir)) {
    fwrite(STDERR, "Error: tunes dir not found: $tunesDir\n");
    exit(1);
}

$dictPath = __DIR__ . '/../src/Ksfraser/phpabc_canntaireachd/abc_dict.php';
$movementGuide = is_file($dictPath) ? parseLegacyMovementDictionary($dictPath) : [];

$images = findImages($tunesDir);
if (!$images) {
    echo "No .jpg/.jpeg files found under $tunesDir\n";
    exit(0);
}

echo "Found " . count($images) . " image(s) under $tunesDir\n";

$created = 0;
$skipped = 0;
$written = 0;

$x = 1;
foreach ($images as $imgPath) {
    $abcPath = preg_replace('/\.(jpe?g)$/i', '.abc', $imgPath);
    if (!$abcPath) {
        $skipped++;
        continue;
    }

    if (is_file($abcPath) && !$overwrite) {
        $skipped++;
        $x++;
        continue;
    }

    $title = basename($imgPath);
    $title = preg_replace('/\.(jpe?g)$/i', '', $title);
    $title = str_replace('_', ' ', $title);

    $relImg = normalizePath($imgPath);

    $abc = [];
    $abc[] = 'X:' . $x;
    $abc[] = 'T:' . $title . ' (from scan)';
    $abc[] = 'C:Unknown';
    $abc[] = 'M:4/4';
    $abc[] = 'L:1/16';
    $abc[] = 'Q:1/4=60';
    $abc[] = 'K:Hp';
    $abc[] = '% Source image: ' . $relImg;
    $abc[] = '% NOTE: This file is a stub. Transcribe notes from the image.';

    if ($movementGuide) {
        $abc[] = '%';
        $abc[] = '% Movement guide (from src/Ksfraser/phpabc_canntaireachd/abc_dict.php):';
        foreach (formatMovementGuide($movementGuide, 6) as $line) {
            $abc[] = '% ' . $line;
        }
    }

    $abc[] = '';
    $abc[] = 'V:1 name="Bagpipes" snm="Pipes"';
    $abc[] = '[V:1] z4 z4 z4 z4 | z4 z4 z4 z4 |]';

    ensureDir(dirname($abcPath));
    file_put_contents($abcPath, implode("\n", $abc) . "\n");

    $written++;
    if (!is_file($abcPath)) {
        $created++;
    }
    $x++;
}

echo "Wrote $written .abc stub(s). Skipped $skipped existing file(s).\n";

// ---- helpers ----

/** @return list<string> */
function findImages(string $root): array {
    $paths = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        /** @var SplFileInfo $file */
        if (!$file->isFile()) {
            continue;
        }
        $name = $file->getFilename();
        if (preg_match('/\.(jpe?g)$/i', $name)) {
            $paths[] = $file->getPathname();
        }
    }
    sort($paths);
    return $paths;
}

function ensureDir(string $dir): void {
    if ($dir === '' || $dir === '.' || is_dir($dir)) {
        return;
    }
    @mkdir($dir, 0777, true);
}

function normalizePath(string $path): string {
    return str_replace('\\', '/', $path);
}

/**
 * Parse the legacy dictionary file by using its section comments as “movement” buckets.
 *
 * @return array<string, list<array{token:string, cannt:string}>>
 */
function parseLegacyMovementDictionary(string $filePath): array {
    $sections = [];
    $currentSection = 'Tokens';

    $lines = @file($filePath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return [];
    }

    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '') {
            continue;
        }

        if (preg_match('/^\s*\/\/\s*(.+)\s*$/', $line, $m)) {
            $currentSection = trim($m[1]);
            continue;
        }

            // Match examples:
            // - $abc["{Gdc}d"]['cannt'] = 'tra';
            // - $abc['G']['cannt'] = 'em';
            // - $abc["{gGd}G"]['cannt'] = 'hemtem';
            if (preg_match('/\$abc\s*\[\s*(["\'])\s*(.+?)\s*\1\s*\]\s*\[\s*(["\'])\s*([^"\']+)\s*\3\s*\]\s*=\s*(["\'])\s*(.*?)\s*\5\s*;/', $line, $m)) {
                $token = $m[2];
                $field = $m[4];
                $value = $m[6];

                if ($field !== 'cannt' && $field !== 'cannt_token') {
                    continue;
                }

                $sections[$currentSection] ??= [];
                $sections[$currentSection][] = ['token' => $token, 'cannt' => $value];
            }
    }

    // Keep only the sections that look like “movements” (reduce noise)
    $wanted = [
        'Birl',
        'Double Echo',
        'Doublings',
        'Thumb Dbl',
        'Half  Dbl',
        'Throws',
        'Grip',
        'Taorluath',
        'Crunluath',
    ];

    $filtered = [];
    foreach ($wanted as $name) {
        if (!empty($sections[$name])) {
            $filtered[$name] = $sections[$name];
        }
    }

    // If no known sections matched (file changed), fall back to everything found.
    return $filtered ?: $sections;
}

/**
 * @param array<string, list<array{token:string, cannt:string}>> $guide
 * @return list<string>
 */
function formatMovementGuide(array $guide, int $maxPerSection): array {
    $lines = [];
    foreach ($guide as $section => $items) {
        $pairs = [];
        $count = 0;
        foreach ($items as $row) {
            if (!isset($row['token'], $row['cannt'])) {
                continue;
            }
            $token = trim((string)$row['token']);
            $cannt = trim((string)$row['cannt']);
            if ($token === '' || $cannt === '') {
                continue;
            }
            $pairs[] = $token . '=' . $cannt;
            $count++;
            if ($count >= $maxPerSection) {
                break;
            }
        }
        if ($pairs) {
            $lines[] = $section . ': ' . implode(' ; ', $pairs);
        }
    }
    return $lines;
}
