<?php
use PHPUnit\Framework\TestCase;

class BagpipeCanntaireachdTest extends TestCase
{
    public function testBagpipeCanntaireachdGeneration()
    {
        $abcFile = __DIR__ . '/test-bagpipe-cannt.abc';
        $lines = file($abcFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $expectedCannt = [];
        $abcSource = [];
        $inExpected = false;
        foreach ($lines as $line) {
            if (strpos($line, '%expected cannt:') === 0) {
                $inExpected = true;
                continue;
            }
            if ($inExpected && strpos($line, '%') === 0) {
                $expectedCannt[] = trim(substr($line, 1));
            }
            if (!$inExpected) {
                $abcSource[] = $line;
            }
        }
        // Write ABC source to a temp file for parsing
        $tmpAbc = tempnam(sys_get_temp_dir(), 'abc');
        file_put_contents($tmpAbc, implode("\n", $abcSource));

        // Run the actual parser/conversion process
        // Use the same process as your CLI or main code
        // For this test, let's assume you have a CLI script: test_cannt.php
        $cmd = "php " . escapeshellarg(__DIR__ . '/../test_cannt.php') . " " . escapeshellarg($tmpAbc);
        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);
        unlink($tmpAbc);
        $rendered = implode("\n", $output);

        // Extract canntaireachd lines from rendered output (e.g., w: lines)
        $renderedCannt = [];
        foreach (explode("\n", $rendered) as $line) {
            if (preg_match('/^w:\s*(.+)$/', $line, $m)) {
                $renderedCannt[] = trim($m[1]);
            }
        }

        // Compare rendered canntaireachd to expected
        foreach ($expectedCannt as $i => $expected) {
            $this->assertEquals(
                preg_replace('/\s+/', ' ', trim($expected)),
                isset($renderedCannt[$i]) ? preg_replace('/\s+/', ' ', trim($renderedCannt[$i])) : '',
                "Mismatch in canntaireachd for line $i"
            );
        }
    }
}
