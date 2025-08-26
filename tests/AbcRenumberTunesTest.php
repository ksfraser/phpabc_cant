<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

class AbcRenumberTunesTest extends TestCase {
    public function testRenumberWidth() {
        $abc = "X:1\nT:First\nK:C\nabc\n\nX:1\nT:Second\nK:C\ndef\n";
        $parser = new AbcFileParser();
        $tunes = $parser->parse($abc);
        $width = 4;
        $newX = 1;
        $seenX = [];
        $output = '';
        foreach ($tunes as $tune) {
            $headers = $tune->getHeaders();
            $x = isset($headers['X']) ? $headers['X']->get() : null;
            $xStr = null;
            if ($x !== null && isset($seenX[$x])) {
                while (isset($seenX[$newX])) {
                    $newX++;
                }
                $xStr = str_pad($newX, $width, '0', STR_PAD_LEFT);
                $headers['X']->set($xStr);
                $output .= "X:$xStr\n";
                $seenX[$newX] = true;
                $newX++;
            } else if ($x !== null) {
                $seenX[$x] = true;
                $xStr = str_pad($x, $width, '0', STR_PAD_LEFT);
                $headers['X']->set($xStr);
                $output .= "X:$xStr\n";
            }
            foreach ($headers as $key => $headerObj) {
                if ($key !== 'X') {
                    $val = $headerObj->get();
                    if ($val !== '') $output .= "$key:$val\n";
                }
            }
            $output .= "\n";
        }
        $this->assertStringContainsString("X:0001", $output);
        $this->assertStringContainsString("X:0002", $output);
    }
}
