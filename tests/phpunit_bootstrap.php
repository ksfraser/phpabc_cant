<?php
// Enable verbose debug output for ABC parser if --verbose is passed
if (in_array('--verbose', $_SERVER['argv'])) {
    define('PHPABC_VERBOSE', true);
} else {
    define('PHPABC_VERBOSE', false);
}
