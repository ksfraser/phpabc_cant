<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\ContextManager;

class ContextManagerTest extends TestCase {
    public function testSetAndGet() {
        $ctx = new ContextManager();
        $ctx->set('key', 'D');
        $this->assertEquals('D', $ctx->get('key'));
    }
    public function testGetAllReturnsContext() {
        $ctx = new ContextManager(['key' => 'C', 'meter' => '4/4']);
        $all = $ctx->getAll();
        $this->assertEquals('C', $all['key']);
        $this->assertEquals('4/4', $all['meter']);
    }
    public function testApplyTokenKey() {
        $ctx = new ContextManager();
        $this->assertTrue($ctx->applyToken('K:G')); 
        $this->assertEquals('G', $ctx->get('key'));
    }
    public function testApplyTokenMeter() {
        $ctx = new ContextManager();
        $this->assertTrue($ctx->applyToken('M:6/8'));
        $this->assertEquals('6/8', $ctx->get('meter'));
    }
    public function testApplyTokenLength() {
        $ctx = new ContextManager();
        $this->assertTrue($ctx->applyToken('L:1/8'));
        $this->assertEquals('1/8', $ctx->get('length'));
    }
    public function testApplyTokenVoice() {
        $ctx = new ContextManager();
        $this->assertTrue($ctx->applyToken('V:1'));
        $this->assertEquals('1', $ctx->get('voice'));
    }
    public function testApplyTokenReturnsFalseForUnknown() {
        $ctx = new ContextManager();
        $this->assertFalse($ctx->applyToken('Z:foo'));
    }
}
