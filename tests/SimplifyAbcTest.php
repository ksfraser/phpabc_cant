<?php
/**
 * Unit tests for SimplifyAbc class
 *
 * @package ksfraser\PhpabcCanntaireachd
 */
namespace ksfraser\PhpabcCanntaireachd\Tests;

use ksfraser\PhpabcCanntaireachd\SimplifyAbc;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ksfraser\PhpabcCanntaireachd\SimplifyAbc
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
