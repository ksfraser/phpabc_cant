<?php
namespace Ksfraser\PhpabcCanntaireachd;

trait NoteParserTrait {
    /**
     * Parse an ABC note string into components.
     * @param string $noteStr
     * @return array [pitch, octave, sharpflat, length, decorator]
     */
    public static function parseNote($noteStr) {
        if (preg_match("/^([_=^]?)([a-gA-GzZ])([,']*)([0-9]+\/?[0-9]*|\/{1,}|)(.*)$/", $noteStr, $m)) {
            return [
                'pitch' => $m[2],
                'octave' => $m[3],
                'sharpflat' => $m[1],
                'length' => $m[4],
                'decorator' => $m[5]
            ];
        }
        return [
            'pitch' => '',
            'octave' => '',
            'sharpflat' => '',
            'length' => '',
            'decorator' => ''
        ];
    }
}
