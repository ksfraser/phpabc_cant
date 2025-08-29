<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcChord extends AbcWrapped
{
    function __construct()
    {
        parent::__construct();
        $this->set("startchar", '[');
        $this->set("endchar", ']');
    }
}
