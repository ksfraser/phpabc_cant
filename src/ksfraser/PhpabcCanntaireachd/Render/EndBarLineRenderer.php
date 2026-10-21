<?php
namespace ksfraser\PhpabcCanntaireachd\Render;

/**
 * Renders an end bar line ':]'.
 */
class EndBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct(':]');
    }
}
