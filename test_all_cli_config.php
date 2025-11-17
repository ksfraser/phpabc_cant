<?php
/**
 * Test Configuration Support Across All CLI Scripts
 * 
 * Verifies that all CLI scripts properly support:
 * - --config option
 * - --show-config option
 * - --save-config option
 */

require_once __DIR__ . '/vendor/autoload.php';

$testResults = [];
$totalTests = 0;
$passedTests = 0;

// List of CLI scripts that should support configuration
$cliScripts = [
    'bin/abc-cannt-cli.php' => ['requires_file' => true, 'help_flags' => ['--help']],
    'bin/abc-voice-pass-cli.php' => ['requires_file' => false, 'help_flags' => ['--help']],
    'bin/abc-timing-validator-pass-cli.php' => ['requires_file' => false, 'help_flags' => ['--help']],
    'bin/abc-renumber-tunes-cli.php' => ['requires_file' => false, 'help_flags' => ['--help']],
    'bin/abc-midi-defaults-cli.php' => ['requires_file' => false, 'help_flags' => ['--help']],
    'bin/abc-lyrics-pass-cli.php' => ['requires_file' => false, 'help_flags' => ['--help']],
];

echo "=== Testing Configuration Support in CLI Scripts ===\n\n";

foreach ($cliScripts as $script => $options) {
    $scriptName = basename($script);
    echo "Testing: $scriptName\n";
    echo str_repeat('-', 60) . "\n";
    
    // Test 1: Check if --help mentions configuration options
    $totalTests++;
    $helpCmd = "C:\\php\\php.exe $script " . implode(' ', $options['help_flags']) . " 2>&1";
    $helpOutput = shell_exec($helpCmd);
    
    $hasConfigHelp = (
        stripos($helpOutput, '--config') !== false &&
        stripos($helpOutput, '--show-config') !== false &&
        stripos($helpOutput, '--save-config') !== false
    );
    
    if ($hasConfigHelp) {
        echo "  ✅ Help text includes configuration options\n";
        $passedTests++;
        $testResults[$scriptName]['help'] = 'PASS';
    } else {
        echo "  ❌ Help text missing configuration options\n";
        $testResults[$scriptName]['help'] = 'FAIL';
    }
    
    // Test 2: Check if --show-config works
    $totalTests++;
    $showConfigCmd = "C:\\php\\php.exe $script --show-config 2>&1";
    $showConfigOutput = shell_exec($showConfigCmd);
    
    $hasValidConfig = (
        stripos($showConfigOutput, 'processing') !== false &&
        stripos($showConfigOutput, 'output') !== false &&
        (stripos($showConfigOutput, '{') !== false || stripos($showConfigOutput, 'Configuration') !== false)
    );
    
    if ($hasValidConfig) {
        echo "  ✅ --show-config displays configuration\n";
        $passedTests++;
        $testResults[$scriptName]['show-config'] = 'PASS';
    } else {
        echo "  ❌ --show-config not working properly\n";
        $testResults[$scriptName]['show-config'] = 'FAIL';
    }
    
    // Test 3: Check if --config=file.yml loads configuration
    $totalTests++;
    $configFileCmd = "C:\\php\\php.exe $script --config=config/examples/bagpipe_ensemble.yml --show-config 2>&1";
    $configFileOutput = shell_exec($configFileCmd);
    
    // Check for bagpipe-specific configuration
    $hasCustomConfig = (
        stripos($configFileOutput, 'bagpipe') !== false ||
        stripos($configFileOutput, 'bagpipe_errors.log') !== false
    );
    
    if ($hasCustomConfig) {
        echo "  ✅ --config loads custom configuration file\n";
        $passedTests++;
        $testResults[$scriptName]['load-config'] = 'PASS';
    } else {
        echo "  ❌ --config not loading custom configuration\n";
        $testResults[$scriptName]['load-config'] = 'FAIL';
    }
    
    echo "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Test Summary\n";
echo str_repeat('=', 60) . "\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";

// Detailed results table
echo "Detailed Results:\n";
echo str_repeat('-', 60) . "\n";
printf("%-35s %-8s %-12s %-12s\n", "Script", "Help", "Show Config", "Load Config");
echo str_repeat('-', 60) . "\n";
foreach ($testResults as $script => $results) {
    printf(
        "%-35s %-8s %-12s %-12s\n",
        $script,
        $results['help'] ?? 'N/A',
        $results['show-config'] ?? 'N/A',
        $results['load-config'] ?? 'N/A'
    );
}
echo str_repeat('-', 60) . "\n";

// Exit with appropriate code
if ($passedTests === $totalTests) {
    echo "\n✅ ALL TESTS PASSED\n";
    exit(0);
} else {
    echo "\n❌ SOME TESTS FAILED\n";
    exit(1);
}
