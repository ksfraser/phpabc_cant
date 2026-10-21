<?php
namespace ksfraser\PhpabcCanntaireachd;
class AbcDecorator extends AbcWrapped
{
    function __construct()
    {
        parent::__construct();
        $this->set("startchar", '!');
        $this->set("endchar", '!');
    }
}
