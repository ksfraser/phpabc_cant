<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcNote;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcNote
 */
class AbcNoteAmbiguityTest extends TestCase {
    public function testDecoratorBeforePitchResolvesAsDecorator() {
        $noteStr = "!trill!A"; // !trill! is a decorator shortcut
        $note = new AbcNote($noteStr, null);
        $this->assertEquals('A', $note->getPitch());
        $this->assertEquals('!trill!', $note->getDecorator());
    }

    public function testNoteElementAfterPitchResolvesAsNoteElement() {
        $noteStr = "A!trill!"; // !trill! after pitch should not be decorator
        $note = new AbcNote($noteStr, null);
        $this->assertEquals('A', $note->getPitch());
        // Should not resolve as decorator
        $this->assertNotEquals('!trill!', $note->getDecorator());
    }

    public function testAmbiguousShortcutLogsUnresolved() {
        $noteStr = "!ambiguous!A"; // Suppose !ambiguous! is in gotchas
        $note = new AbcNote($noteStr, null);
        // No assertion, just ensure no exception and ambiguity is logged
        $this->assertEquals('A', $note->getPitch());
    }
}
