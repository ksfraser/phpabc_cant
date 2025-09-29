<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcNote;
use Ksfraser\PhpabcCanntaireachd\Exceptions\AbcNoteLengthException;

class AbcNoteTest extends TestCase {
    public function testDecoratorParsingWithDI() {
        $shortcutMap = [
            '.' => \Ksfraser\PhpabcCanntaireachd\Decorator\StaccatoDecorator::class,
            '!staccato!' => \Ksfraser\PhpabcCanntaireachd\Decorator\StaccatoDecorator::class
        ];
        $note = new AbcNote('A.', null, $shortcutMap);
        $decorators = (new \ReflectionClass($note))->getProperty('decorators');
        $decorators->setAccessible(true);
        $decoratorObjs = $decorators->getValue($note);
        $this->assertNotEmpty($decoratorObjs);
        $this->assertInstanceOf(\Ksfraser\PhpabcCanntaireachd\Decorator\StaccatoDecorator::class, $decoratorObjs[0]);
        $this->assertEquals('!staccato!', $decoratorObjs[0]->render());
    }
    public function testValidNoteLength() {
        $note = new AbcNote('A/');
        $this->assertEquals('A/', $note->get_body_out());
    }
    public function testInvalidNoteLengthThrows() {
        $this->expectException(AbcNoteLengthException::class);
        new AbcNote('A///');
    }
    public function testSetAndGetLyrics() {
        $note = new AbcNote('B');
        $note->setLyrics('lyric');
        $this->assertEquals('lyric', $note->getLyrics());
    }
    public function testSetAndGetCanntaireachd() {
        $note = new AbcNote('C');
        $note->setCanntaireachd('cannt');
        $this->assertEquals('cannt', $note->getCanntaireachd());
    }
    public function testSetAndGetSolfege() {
        $note = new AbcNote('D');
        $note->setSolfege('do');
        $this->assertEquals('do', $note->getSolfege());
    }
    public function testSetAndGetBmwToken() {
        $note = new AbcNote('E');
        $note->setBmwToken('bmw');
        $this->assertEquals('bmw', $note->getBmwToken());
    }
}
