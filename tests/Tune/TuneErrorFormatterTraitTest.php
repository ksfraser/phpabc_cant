<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\TuneErrorFormatterTrait;

class TuneErrorFormatterTraitTest extends TestCase
{
    use TuneErrorFormatterTrait;

    public function testFormatErrorReturnsString()
    {
    $msg = self::formatError(0, '42', 7, 'Test error');
    $this->assertIsString($msg);
    $this->assertStringContainsString('Test error', $msg);
    $this->assertStringContainsString('42', $msg);
    $this->assertStringContainsString('7', $msg);
    }
}
