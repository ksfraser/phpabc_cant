<?php
namespace ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders a start bar line '[:'.
 */
class StartBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('[:');
    }
}
