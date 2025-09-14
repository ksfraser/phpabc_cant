<?php
namespace Ksfraser\PhpabcCanntaireachd;
use ksfraser\origin\Origin;
class AbcToken extends Origin
{
    protected $token;
    function set($field, $value, $enforce = true)
    {
        switch ($field) {
            case "token":
                $this->token = $value;
                return true;
            default:
                return false;
        }
    }
}
