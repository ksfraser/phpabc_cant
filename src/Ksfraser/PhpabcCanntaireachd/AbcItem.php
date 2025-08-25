<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Base class for ABC items (tune, part, line, bar, note, etc.)
 * Supports subitems, add, and render.
 */
abstract class AbcItem {
    protected $subitems = [];
    public function add(AbcItem $item) {
        $this->subitems[] = $item;
    }
    /**
     * Render this item and all subitems as ABC text.
     * @return string
     */
    public function render(): string {
        $output = $this->renderSelf();
        foreach ($this->subitems as $item) {
            $output .= $item->render();
        }
        return $output;
    }
    /**
     * Render just this item (override in subclasses)
     */
    abstract protected function renderSelf(): string;
}
