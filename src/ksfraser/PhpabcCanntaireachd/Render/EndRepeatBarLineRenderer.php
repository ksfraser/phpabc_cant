<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders an end repeat bar line ':|'.
 */
class EndRepeatBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct(':|');
    }
}
