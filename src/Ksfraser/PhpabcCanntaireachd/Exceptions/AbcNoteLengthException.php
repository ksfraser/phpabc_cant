<?php
namespace Ksfraser\PhpabcCanntaireachd\Exceptions;

class AbcNoteLengthException extends \Exception {
    public function __construct($length, $noteStr = '') {
        parent::__construct("Invalid ABC note length: '$length' in note '$noteStr'. Three or more slashes are not ABC spec compliant.");
    }
}
