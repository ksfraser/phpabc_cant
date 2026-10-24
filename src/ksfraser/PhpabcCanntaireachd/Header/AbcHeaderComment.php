<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Header;

use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderMultiField;

/**
 * Comment header field (%).
 *
 * Previously declared as class AbcHeaderZ, which collided with
 * Header/AbcHeaderZ.php (the Z field) — two files, one FQCN.
 */
class AbcHeaderComment extends AbcHeaderMultiField {
    public static $label = '%';
}
