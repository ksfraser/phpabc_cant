<?php
namespace Ksfraser\PhpabcCanntaireachd\Parse;

use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

class AbcFileToTunes
{
    static public function parse( $filename ): array
    {

        $abcContent = file_get_contents($filename);
        $parser = new AbcFileParser();
        $tunes = $parser->parse($abcContent);
        return $tunes;
    }
}
