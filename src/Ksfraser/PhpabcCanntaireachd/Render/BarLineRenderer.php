<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

/**
 * Base class for rendering bar lines in ABC notation.
 */
class BarLineRenderer {
    /**
     * @var string Bar line type (e.g. '|', '||', '|:', ':|', '[:', ':]')
     */
    protected string $barLineType;

    /**
     * @param string $barLineType
     */
    public function __construct(string $barLineType = '|') {
        $this->barLineType = $barLineType;
    }

    /**
     * Render the bar line as a string.
     * @return string
     */
    public function render(): string {
        return $this->barLineType;
    }
}

/**
 * Renders a simple bar line '|'.
 */
class SimpleBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('|');
    }
}

/**
 * Renders a double bar line '||'.
 */
class DoubleBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('||');
    }
}

/**
 * Renders a start repeat bar line '|:'.
 */
class StartRepeatBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('|:');
    }
}

/**
 * Renders an end repeat bar line ':|'.
 */
class EndRepeatBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct(':|');
    }
}

/**
 * Renders a start bar line '[:'.
 */
class StartBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct('[:');
    }
}

/**
 * Renders an end bar line ':]'.
 */
class EndBarLineRenderer extends BarLineRenderer {
    public function __construct() {
        parent::__construct(':]');
    }
}
