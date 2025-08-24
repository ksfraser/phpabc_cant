<?php
/**
 * Unit tests for SimplifyAbc class
 *
 * @package Ksfraser\PhpabcCanntaireachd
 */
namespace Ksfraser\PhpabcCanntaireachd\Tests;

use Ksfraser\PhpabcCanntaireachd\SimplifyAbc;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ksfraser\PhpabcCanntaireachd\SimplifyAbc
 */
class SimplifyAbcTest extends TestCase
{
    /**
     * Test that SimplifyAbc can be instantiated
     */
    public function testCanInstantiate()
    {
        $obj = new SimplifyAbc();
        $this->assertInstanceOf(SimplifyAbc::class, $obj);
    }
}
