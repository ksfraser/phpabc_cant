<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders a simple bar line '|'.
 */
class SimpleBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('|');
    }
}
