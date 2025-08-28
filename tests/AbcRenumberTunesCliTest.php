<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

class AbcRenumberTunesCliTest extends TestCase {
    private $testFile;
    private $errorFile;

    protected function setUp(): void {
        $this->testFile = __DIR__ . '/test.abc';
        $this->errorFile = __DIR__ . '/err.txt';
        file_put_contents($this->testFile, "X:1\nT:Test1\nK:C\nCDEF\n\nX:1\nT:Test2\nK:C\nGABc\n\nX:2\nT:Test3\nK:C\nEFGA\n");
        if (file_exists($this->errorFile)) unlink($this->errorFile);
    }

    protected function tearDown(): void {
        @unlink($this->testFile);
        @unlink($this->testFile . '.renumbered');
        @unlink($this->errorFile);
    }

    public function testRenumberDuplicates() {
        $cmd = sprintf('php %s/../bin/abc-renumber-tunes-cli.php %s --width=4 --errorfile=%s', __DIR__, $this->testFile, $this->errorFile);
        exec($cmd, $output, $code);
        $renum = file_get_contents($this->testFile . '.renumbered');
        $this->assertStringContainsString('X:0001', $renum);
        $this->assertStringContainsString('X:0002', $renum);
        $this->assertStringContainsString('X:0003', $renum);
        $this->assertFileExists($this->errorFile);
        $err = file_get_contents($this->errorFile);
        $this->assertStringContainsString('Renumbered file written', $err);
        $this->assertEquals(0, $code);
    }

    public function testMissingFile() {
        $cmd = sprintf('php %s/../bin/abc-renumber-tunes-cli.php missing.abc --errorfile=%s', __DIR__, $this->errorFile);
        exec($cmd, $output, $code);
        $this->assertFileExists($this->errorFile);
        $err = file_get_contents($this->errorFile);
        $this->assertStringContainsString('File not found', $err);
        $this->assertEquals(1, $code);
    }

    public function testNoInputFile() {
        $cmd = sprintf('php %s/../bin/abc-renumber-tunes-cli.php --errorfile=%s', __DIR__, $this->errorFile);
        exec($cmd, $output, $code);
        $this->assertFileExists($this->errorFile);
        $err = file_get_contents($this->errorFile);
        $this->assertStringContainsString('Usage:', $err);
        $this->assertEquals(1, $code);
    }
}
