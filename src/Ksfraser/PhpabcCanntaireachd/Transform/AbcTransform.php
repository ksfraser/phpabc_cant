<?php

declare(strict_types=1);

namespace Ksfraser\PhpabcCanntaireachd\Transform;

use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

/**
 * Interface for ABC Tune transformations
 * 
 * Defines the contract for all transformation passes in the processing pipeline.
 * Transforms receive an AbcTune object, modify it, and return the modified tune.
 * 
 * Design Principles:
 * - Single Responsibility: Each transform does ONE thing
 * - Open/Closed: Pipeline is open for extension (add new transforms) but closed for modification
 * - Liskov Substitution: All transforms are interchangeable
 * - Dependency Inversion: Pipeline depends on this interface, not concrete implementations
 * 
 * @package Ksfraser\PhpabcCanntaireachd\Transform
 * 
 * @uml
 * @startuml
 * interface AbcTransform {
 *   + transform(tune: AbcTune): AbcTune
 * }
 * 
 * class VoiceCopyTransform implements AbcTransform
 * class CanntaireachdTransform implements AbcTransform
 * class AbcProcessingPipeline {
 *   - transforms: AbcTransform[]
 *   + process(text: string): string
 * }
 * 
 * AbcProcessingPipeline o-- AbcTransform
 * @enduml
 */
interface AbcTransform
{
    /**
     * Transform an AbcTune object
     * 
     * Receives a tune, applies the transformation, and returns the modified tune.
     * Implementations should be idempotent when possible (running twice produces same result).
     * 
     * @param AbcTune $tune The tune to transform
     * @return AbcTune The transformed tune (may be the same object or a new one)
     * 
     * @throws \InvalidArgumentException if the tune cannot be transformed
     * @throws \RuntimeException if an error occurs during transformation
     */
    public function transform(AbcTune $tune): AbcTune;
}
