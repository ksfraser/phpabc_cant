<?php
// CLI tool for managing MIDI defaults
$config = require __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/config_db.php';
$pdo = new PDO($config['dsn'], $config['user'], $config['password']);
$options = getopt('', ['list', 'add:', 'edit:', 'delete:', 'midi_channel:', 'midi_program:', 'validate:', 'save:']);
$table = 'abc_midi_defaults';
if (isset($options['list'])) {
    $stmt = $pdo->query("SELECT * FROM $table");
    foreach ($stmt as $row) {
        echo "{$row['voice_name']}: Channel {$row['midi_channel']}, Program {$row['midi_program']}\n";
    }
} elseif (isset($options['add'])) {
    $voice = $options['add'];
    $channel = $options['midi_channel'] ?? 0;
    $program = $options['midi_program'] ?? 0;
    $stmt = $pdo->prepare("INSERT INTO $table (voice_name, midi_channel, midi_program) VALUES (?, ?, ?)");
    $stmt->execute([$voice, $channel, $program]);
    echo "Added $voice.\n";
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
    echo "Edited $voice.\n";
} elseif (isset($options['delete'])) {
    $voice = $options['delete'];
    $stmt = $pdo->prepare("DELETE FROM $table WHERE voice_name=?");
    $stmt->execute([$voice]);
    echo "Deleted $voice.\n";
} elseif (isset($options['validate'])) {
    $abcFile = $options['validate'];
    $abcContent = file_get_contents($abcFile);
    $dict = include __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
    $result = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abcContent, $dict);
    $output = $result['lines'];
    $canntDiff = $result['canntDiff'];
    $newFile = preg_replace('/\.abc$/', '_1.abc', $abcFile);
    file_put_contents($newFile, implode("\n", $output));
    echo "Saved with canntaireachd: $newFile\n";
    if ($canntDiff) {
        file_put_contents('cannt_diff.txt', implode("\n", $canntDiff));
        echo "Canntaireachd diff written to cannt_diff.txt\n";
    }
} elseif (isset($options['save'])) {
    $abcFile = $options['save'];
    $abcContent = file_get_contents($abcFile);
    $validator = new \Ksfraser\PhpabcCanntaireachd\AbcValidator();
    $validator->validate($abcContent);
    // Only add canntaireachd to Bagpipes voice
    $lines = explode("\n", $abcContent);
    $output = [];
    $inBagpipes = false;
    foreach ($lines as $line) {
        if (preg_match('/^V:Bagpipes/', $line)) {
            $inBagpipes = true;
            $output[] = $line;
            $output[] = "%canntaireachd: <add your canntaireachd here>";
        } elseif (preg_match('/^V:/', $line)) {
            $inBagpipes = false;
            $output[] = $line;
        } else {
            $output[] = $line;
        }
    }
    $newFile = preg_replace('/\.abc$/', '_canntaireachd.abc', $abcFile);
    file_put_contents($newFile, implode("\n", $output));
    echo "Saved with canntaireachd: $newFile\n";
} else {
    echo "Usage:\n  --list\n  --add=<voice> --midi_channel=<ch> --midi_program=<prog>\n  --edit=<voice> [--midi_channel=<ch>] [--midi_program=<prog>]\n  --delete=<voice>\n  --validate=<file.abc>\n  --save=<file.abc>\n";
}
