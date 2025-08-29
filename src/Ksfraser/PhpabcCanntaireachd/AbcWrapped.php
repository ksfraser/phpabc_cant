<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcWrapped extends AbcToken
{
    protected $startchar;
    protected $endchar;
    function set($field, $value = null, $enforce = true)
    {
        switch ($field) {
            case "token":
                return $this->set_token($value, $enforce);
            default:
                return parent::set($field, $value, $enforce);
        }
    }
    protected function set_token($value, $enforce)
    {
        if (is_array($value)) {
            $len = count($value);
            if (strcmp($value[0], $this->startchar) !== 0) {
                throw new \Exception("We are in the wrong class.  Start char doesn't match!");
            }
            if (strcmp($value[$len - 1], $this->endchar) !== 0) {
                throw new \Exception("Last char of passed in value doesn't match expected! ");
            }
            return parent::set("token", implode($value), $enforce);
        } else {
            $len = strlen($value);
            if (strncmp($value[0], $this->startchar, 1) !== 0) {
                throw new \Exception("We are in the wrong class.  Start char doesn't match!");
            }
            if (strncmp($value[$len], $this->endchar, 1) !== 0) {
                throw new \Exception("Last char of passed in value doesn't match expected! ");
            }
            return parent::set("token", $value, $enforce);
        }
    }
}
