<?php
namespace ksfraser\PhpabcCanntaireachd\Header;

use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderField;

/**
 * W: — words (lyrics) header field.
 *
 * Previously only declared inside Header/AbcHeaderAll.php, which duplicated the
 * classes that also exist in their own PSR-4 files. Declaring the same FQCN in
 * two files is a fatal waiting to happen ("Cannot declare class ..."), so the
 * W field now has a home of its own.
 */
class AbcHeaderW extends AbcHeaderField {
    public static $label = 'W';
}
