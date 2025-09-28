<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;

use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderMultiField;

/**
 * Class to handle macros
 *
 * The spec allows macros to be a short hand to substitue in the tune body
 * 	m: ~g2 = {a}g{f}g
 *	m: ~D2 = {E}D{C}D
 *
 * From our perspective the problem is we need to first substitute the replacement values into the boduy
 *   before we can do any other processing.
 * 
 * From a header perspective it is just another line.
 */
class AbcHeaderMacro extends AbcHeaderMultiField {
    public static $label = 'm';
}
