<?php
namespace ksfraser\PhpabcCanntaireachd;
class AbcGuitarChord extends AbcWrapped
{
    function __construct()
    {
        parent::__construct();
        $this->set("startchar", '"');
        $this->set("endchar", '"');
    }
}
