<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders a start repeat bar line '|:'.
 */
class StartRepeatBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('|:');
    }
}
