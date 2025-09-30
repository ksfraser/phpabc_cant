<?php
namespace Ksfraser\PhpabcCanntaireachd\Contract;

/**
 * Interface for notes that can be translated by a token translator.
 */
interface TranslatableNoteInterface {
    /**
     * Translate this note using the provided translator.
     * @param object $translator
     * @return mixed
     */
    public function translate($translator);
}