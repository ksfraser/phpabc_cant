<?php
// CLI tool for managing MIDI defaults
// Load secrets if available
if (class_exists('Symfony\Component\Runtime\Secrets\getSecret')) {
    $getSecret = \Closure::fromCallable(['Symfony\Component\Runtime\Secrets', 'getSecret']);
    $env = getenv();
    $config = [
        'mysql_user' => $env['MYSQL_USER'] ?? $getSecret('MYSQL_USER') ?? null,
        'mysql_pass' => $env['MYSQL_PASS'] ?? $getSecret('MYSQL_PASS') ?? null,
        'mysql_db'   => $env['MYSQL_DB']   ?? $getSecret('MYSQL_DB')   ?? null,
        'mysql_host' => $env['MYSQL_HOST'] ?? $getSecret('MYSQL_HOST') ?? 'localhost',
        'mysql_port' => $env['MYSQL_PORT'] ?? $getSecret('MYSQL_PORT') ?? 3306,
    ];
    // Fallback to config_db.php if any are missing
    $fallback = require __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/config_db.php';
    foreach ($fallback as $k => $v) {
        if (!isset($config[$k]) || $config[$k] === null) $config[$k] = $v;
    }
} else {
    $config = require __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/config_db.php';
}
$options = getopt('', [
    'list', 'add:', 'edit:', 'delete:', 'midi_channel:', 'midi_program:', 'validate:', 'save:',
    'voice_output_style:', 'interleave_bars:', 'bars_per_line:', 'join_bars_with_backslash:',
    'mysql_user:', 'mysql_pass:', 'mysql_db:', 'mysql_host:', 'mysql_port:'
]);
// Override config with CLI options if provided
if (isset($options['mysql_user'])) $config['mysql_user'] = $options['mysql_user'];
if (isset($options['mysql_pass'])) $config['mysql_pass'] = $options['mysql_pass'];
if (isset($options['mysql_db']))   $config['mysql_db']   = $options['mysql_db'];
if (isset($options['mysql_host'])) $config['mysql_host'] = $options['mysql_host'];
if (isset($options['mysql_port'])) $config['mysql_port'] = $options['mysql_port'];
$config['dsn'] = "mysql:host={$config['mysql_host']};port={$config['mysql_port']};dbname={$config['mysql_db']};charset=utf8mb4";
$pdo = new PDO($config['dsn'], $config['mysql_user'], $config['mysql_pass']);
// Check if table exists, create/populate if missing
$table = 'abc_midi_defaults';
$tableExists = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    $tableExists = $stmt->fetchColumn() !== false;
} catch (Exception $e) {
    $tableExists = false;
}
if (!$tableExists) {
    $schemaFile = __DIR__ . '/abc_midi_defaults_schema.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        foreach (explode(';', $sql) as $query) {
            $query = trim($query);
            if ($query) $pdo->exec($query);
        }
        echo "Created and populated $table from schema.\n";
    } else {
        echo "Schema file $schemaFile not found.\n";
        exit(1);
    }
}
$options = getopt('', [
    'list', 'add:', 'edit:', 'delete:', 'midi_channel:', 'midi_program:', 'validate:', 'save:',
    'voice_output_style:', 'interleave_bars:', 'bars_per_line:', 'join_bars_with_backslash:'
]);
// Support --errorfile option
$errorFile = null;
foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if (preg_match('/^--errorfile=(.+)$/', $arg, $m)) {
        $errorFile = $m[1];
    }
}
// Support multiple files via wildcard for validate/save
$abcFiles = [];
if (isset($options['validate'])) {
    $abcFiles = glob($options['validate']);
} elseif (isset($options['save'])) {
    $abcFiles = glob($options['save']);
}
$table = 'abc_midi_defaults';
if (isset($options['list'])) {
    $stmt = $pdo->query("SELECT * FROM $table");
    $msg = "";
    foreach ($stmt as $row) {
        $msg .= "{$row['voice_name']}: Channel {$row['midi_channel']}, Program {$row['midi_program']}\n";
    }
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} elseif (isset($options['add'])) {
    $voice = $options['add'];
    $channel = $options['midi_channel'] ?? 0;
    $program = $options['midi_program'] ?? 0;
    $stmt = $pdo->prepare("INSERT INTO $table (voice_name, midi_channel, midi_program) VALUES (?, ?, ?)");
    $stmt->execute([$voice, $channel, $program]);
    $msg = "Added $voice.\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} elseif (isset($options['edit'])) {
    $voice = $options['edit'];
    $channel = $options['midi_channel'] ?? null;
    $program = $options['midi_program'] ?? null;
    if ($channel !== null) {
        $stmt = $pdo->prepare("UPDATE $table SET midi_channel=? WHERE voice_name=?");
        $stmt->execute([$channel, $voice]);
    }
    if ($program !== null) {
        $stmt = $pdo->prepare("UPDATE $table SET midi_program=? WHERE voice_name=?");
        $stmt->execute([$program, $voice]);
    }
    $msg = "Edited $voice.\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} elseif (isset($options['delete'])) {
    $voice = $options['delete'];
    $stmt = $pdo->prepare("DELETE FROM $table WHERE voice_name=?");
    $stmt->execute([$voice]);
    $msg = "Deleted $voice.\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} elseif (!empty($abcFiles)) {
    $config = new \Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig();
    if (isset($options['voice_output_style'])) $config->voiceOutputStyle = $options['voice_output_style'];
    if (isset($options['interleave_bars'])) $config->interleaveBars = (int)$options['interleave_bars'];
    if (isset($options['bars_per_line'])) $config->barsPerLine = (int)$options['bars_per_line'];
    if (isset($options['join_bars_with_backslash'])) $config->joinBarsWithBackslash = (bool)$options['join_bars_with_backslash'];

    foreach ($abcFiles as $abcFile) {
        $abcContent = file_get_contents($abcFile);
        $dict = include __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
        $result = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abcContent, $dict);
        $output = $result['lines'];
        $canntDiff = $result['canntDiff'];
        $newFile = preg_replace('/\.abc$/', '_1.abc', $abcFile);
        file_put_contents($newFile, implode("\n", $output));
        $files = [$newFile];
        $msg = "Saved with canntaireachd: $newFile\n";
        if ($canntDiff) {
            $diffFile = 'cannt_diff.txt';
            file_put_contents($diffFile, implode("\n", $canntDiff));
            $msg .= "Canntaireachd diff written to $diffFile\n";
            $files[] = $diffFile;
        }
        if ($result['errors'] ?? false) {
            $errFile = 'abc_errors.txt';
            file_put_contents($errFile, implode("\n", $result['errors']));
            $msg .= "Errors written to $errFile\n";
            $files[] = $errFile;
        }
        $msg .= "Output files for $abcFile:\n";
        foreach ($files as $f) {
            $msg .= "  $f\n";
        }
        if ($errorFile) {
            \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
        } else {
            echo $msg;
        }
    }
    exit;
} else {
    $msg = "Usage:\n  --list\n  --add=<voice> --midi_channel=<ch> --midi_program=<prog>\n  --edit=<voice> [--midi_channel=<ch>] [--midi_program=<prog>]\n  --delete=<voice>\n  --validate=<file.abc>\n  --save=<file.abc>\n  [--errorfile=err.txt]\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
}
