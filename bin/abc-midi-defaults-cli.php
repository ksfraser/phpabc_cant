<?php
// CLI tool for managing MIDI defaults
$config = require __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/config_db.php';
$pdo = new PDO($config['dsn'], $config['user'], $config['password']);
$options = getopt('', ['list', 'add:', 'edit:', 'delete:', 'midi_channel:', 'midi_program:']);
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
} else {
    echo "Usage:\n  --list\n  --add=<voice> --midi_channel=<ch> --midi_program=<prog>\n  --edit=<voice> [--midi_channel=<ch>] [--midi_program=<prog>]\n  --delete=<voice>\n";
}
