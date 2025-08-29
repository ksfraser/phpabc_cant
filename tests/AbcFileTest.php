<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcFile;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcFile
 */
class AbcFileTest extends TestCase {
    public function testAbcTuneBasePhp73Compatible() {
        $obj = new \Ksfraser\PhpabcCanntaireachd\AbcTuneBase();
        $this->assertInstanceOf(\Ksfraser\PhpabcCanntaireachd\AbcTuneBase::class, $obj);
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
        $obj = new AbcFile();
        $this->assertInstanceOf(AbcFile::class, $obj);
    }
}
