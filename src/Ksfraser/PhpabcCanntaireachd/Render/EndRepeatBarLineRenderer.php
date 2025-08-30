<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders an end repeat bar line ':|'.
 */
class EndRepeatBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct(':|');
    }
}
