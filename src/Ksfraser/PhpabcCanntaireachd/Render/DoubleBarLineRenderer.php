<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders a double bar line '||'.
 */
class DoubleBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('||');
    }
}
