<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders a start repeat bar line '|:'.
 */
class StartRepeatBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('|:');
    }
}
