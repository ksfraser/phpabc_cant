<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders a simple bar line '|'.
 */
class SimpleBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('|');
    }
}
