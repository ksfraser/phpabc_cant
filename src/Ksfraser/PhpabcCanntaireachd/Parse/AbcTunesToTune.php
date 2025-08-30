<?php
namespace Ksfraser\PhpabcCanntaireachd\Parse;

class AbcTunesToTune
{
    static public function parse( array $tunes, string $xnum ): ?object
    {
        foreach ($tunes as $t) {
            $headers = $t->getHeaders();
            if (isset($headers['X']) && $headers['X']->get() == $xnum) {
                return $t;
            }
        }
        return null;
    }
}
