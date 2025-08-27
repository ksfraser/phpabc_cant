<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Common output writer for CLI tools
 */
class CliOutputWriter {
    /**
     * Write content to output file (overwrites if exists)
     * @param string $content
     * @param string $outputFile
     * @return void
     */
    public static function write($content, $outputFile) {
        if (!$outputFile) throw new \InvalidArgumentException('No output file specified');
        file_put_contents($outputFile, $content);
    }
}
