<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Tests;

use ksfraser\PhpabcCanntaireachd\Bmw\BmwDictionary;
use ksfraser\PhpabcCanntaireachd\Bmw\BmwToAbcConverter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ksfraser\PhpabcCanntaireachd\Bmw\BmwDictionary
 * @covers \ksfraser\PhpabcCanntaireachd\Bmw\BmwToAbcConverter
 */
class BmwToAbcConverterTest extends TestCase
{
    private BmwToAbcConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new BmwToAbcConverter(new BmwDictionary());
    }

    public function testTheDictionaryIsRead(): void
    {
        $dictionary = new BmwDictionary();

        $this->assertNotSame([], $dictionary->getTokens());
        $this->assertNotSame([], $dictionary->getHeaders());
    }

    public function testTheTokensAreMatchedLongestFirst(): void
    {
        $keys = (new BmwDictionary())->getTokenKeys();

        $lengths = array_map('strlen', $keys);
        $sorted = $lengths;
        rsort($sorted);
        $this->assertSame($sorted, $lengths);
    }

    public function testANoteIsConverted(): void
    {
        // In BMW a note carries the hand it is played with: "LA_8" is the low
        // A, "Fr_8" the F of the right hand.
        $this->assertSame('A2 ', $this->converter->convertTokens('LA_8 '));
        $this->assertSame('f2 ', $this->converter->convertTokens('Fr_8 '));
    }

    public function testAGraceNoteKeepsItsNote(): void
    {
        // The original converter replaced over the whole line, so a doubling
        // became "{gde}" and then the "gd" was replaced again.
        $converted = $this->converter->convertTokens('e2 {g} e2');

        $this->assertStringNotContainsString('{{', $converted);
        $this->assertSame('e2 {g} e2', $converted);
    }

    public function testTheTitleBecomesATitleHeader(): void
    {
        $converted = $this->converter->convertLine('"9 O\'Clock Walk",(T,L,0,0,arial,12,700,0,0,18,0,0,0)');

        $this->assertSame(["T:9 O'Clock Walk"], $converted);
    }

    public function testTheTypeBecomesTheRhythmHeader(): void
    {
        $converted = $this->converter->convertLine('"Jig",(Y,C,0,0,arial,12,400,0,0,18,0,0,0)');

        $this->assertSame(['R:Jig'], $converted);
    }

    public function testTheComposerAndTheFoot(): void
    {
        $this->assertSame(
            ['C:P. Warren'],
            $this->converter->convertLine('"P. Warren",(M,R,0,0,arial,12,400,0,0,18,0,0,0)')
        );
        $this->assertSame(
            ['O:Celtic Fringe, March 2003'],
            $this->converter->convertLine('"Celtic Fringe, March 2003",(F,R,0,0,arial,12,400,0,0,18,0,0,0)')
        );
    }

    public function testTheTuneHeaderCarriesTheKeyAndTheMeter(): void
    {
        $converted = $this->converter->convertLine('& sharpf sharpc 6_8');

        $this->assertStringContainsString('[K:Amix]', $converted[0]);
        $this->assertStringContainsString('[M:6/8]', $converted[0]);
    }

    public function testTheTempoBecomesAQHeader(): void
    {
        $converted = $this->converter->convertLine('TuneTempo,110');

        $this->assertStringContainsString('Q:', $converted[0]);
        $this->assertStringContainsString('110', $converted[0]);
    }

    public function testABarLineIsConverted(): void
    {
        $this->assertStringContainsString('[|:', $this->converter->convertTokens("I!'' "));
    }

    public function testATokenThatIsNotInTheDictionaryIsKept(): void
    {
        $this->assertSame('zzunknown ', $this->converter->convertTokens('zzunknown '));
    }

    public function testAWholeFileIsConverted(): void
    {
        $bmw = "Bagpipe Reader:1.0\n\nTuneTempo,110\n\n"
            . "\"A Tune\",(T,L,0,0,arial,12,700,0,0,18,0,0,0)\n\n"
            . "& sharpf sharpc 4_4\n"
            . "LA_8 B_8 | C_8 D_8 |\n";

        $abc = $this->converter->convert($bmw);

        $this->assertStringContainsString('T:A Tune', $abc);
        $this->assertStringContainsString('Q:110', $abc);
        $this->assertStringContainsString('c2 d2', $abc);
        $this->assertStringContainsString('A2 B2', $abc);
        $this->assertStringContainsString('[K:Amix]', $abc);
        $this->assertStringContainsString('[M:4/4]', $abc);
        $this->assertStringContainsString('A2 B2', $abc);
    }
}
