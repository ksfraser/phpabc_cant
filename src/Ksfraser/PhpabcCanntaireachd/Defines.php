<?php

namespace Ksfraser\PhpabcCanntaireachd;

// Constants for the phpabc_canntaireachd package
define('KSF_FIELD_NOT_SET', 1001);
define('KSF_INVALID_VALUE', 1002);
define('KSF_FILE_NOT_FOUND', 1003);

// PEAR Log constants (if not already defined)
if (!defined('PEAR_LOG_DEBUG')) {
    define('PEAR_LOG_DEBUG', 7);
}
if (!defined('PEAR_LOG_INFO')) {
    define('PEAR_LOG_INFO', 6);
}
if (!defined('PEAR_LOG_ERR')) {
    define('PEAR_LOG_ERR', 3);
}
