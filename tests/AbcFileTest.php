<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\AbcFile;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\AbcFile
 */
class AbcFileTest extends TestCase {
    public function testAbcTuneBasePhp73Compatible() {
        $obj = new \ksfraser\PhpabcCanntaireachd\AbcTuneBase();
        $this->assertInstanceOf(\ksfraser\PhpabcCanntaireachd\AbcTuneBase::class, $obj);
        $ref = new \ReflectionClass($obj);
        $titleArrProp = $ref->getProperty('title_arr');
        $titleArrProp->setAccessible(true);
        $titleArr = $titleArrProp->getValue($obj);
        $this->assertIsArray($titleArr);
        $composerProp = $ref->getProperty('composer');
        $composerProp->setAccessible(true);
        $composer = $composerProp->getValue($obj);
        $this->assertNull($composer);
    }
    public function testCanInstantiate() {
        $obj = new AbcFile("");
        $this->assertInstanceOf(AbcFile::class, $obj);
    }
}
