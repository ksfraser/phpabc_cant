<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;

class CliOutputWriterTest extends TestCase {
    public function testWriteOutputFile() {
        $file = __DIR__ . '/test_output.txt';
        $content = "Test output";
        CliOutputWriter::write($content, $file);
        $this->assertFileExists($file);
        $this->assertEquals($content, file_get_contents($file));
        unlink($file);
    }
}
