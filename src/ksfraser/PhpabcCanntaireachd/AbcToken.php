<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;
use ksfraser\origin\Origin;
class AbcToken extends Origin
{
    protected $token;
    function set($field, $value = null, $enforce_only_native_vars = true)
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
