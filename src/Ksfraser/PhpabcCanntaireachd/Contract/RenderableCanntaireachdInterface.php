<?php
namespace Ksfraser\PhpabcCanntaireachd\Contract;

/**
 * Interface for objects that can render canntaireachd output.
 */
interface RenderableCanntaireachdInterface {
    /**
     * Render canntaireachd for this object.
     * @return string
     */
    public function renderCanntaireachd(): string;
}