<?php
/**
 * Legacy dictionary location — shim only.
 *
 * The canonical ABC -> canntaireachd token dictionary now lives at
 *   src/ksfraser/PhpabcCanntaireachd/abc_dict.php
 * This file remains so the original scripts in this directory keep working.
 *
 * @see \Ksfraser\PhpabcCanntaireachd\TokenDictionary
 */
$abc = require __DIR__ . '/../../src/ksfraser/PhpabcCanntaireachd/abc_dict.php';
