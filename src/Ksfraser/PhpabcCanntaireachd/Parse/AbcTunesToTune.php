<?php
namespace Ksfraser\PhpabcCanntaireachd\Parse;

class AbcTunesToTune
{
    /**
     * Return a specific Tune Numbered by X:
     * @param array|null$tunes
     * @param string $xnum
     */
    static public function locate( array $tunes, string $xnum ): ?object
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
