<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Exceptions\AbcProcessingException;
use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;
use Ksfraser\PhpabcCanntaireachd\Transform\AbcTransform;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

/**
 * Class AbcProcessingPipeline
 *
 * Encapsulates the ABC processing pipeline with support for both legacy text-based passes
 * and modern object-based transforms.
 * 
 * Legacy mode (run): Text-based passes operating on line arrays
 * Modern mode (processWithTransforms): Parse → Transform* → Render pattern
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @uml
 * @startuml
 * start
 * :explode abcContent into lines;
 * :initialize passes array;
 * :extract header fields;
 * :match/update header fields;
 * :foreach pass in passes;
 *   if pass is validator/processor then
 *     :run pass;
 *     if errors then
 *       :throw AbcProcessingException;
 *     endif
 *   endif
 * endfor
 * :add suggestions as comments;
 * :return result array;
 * stop
 * @enduml
 */
class AbcProcessingPipeline {
    protected $passes;
    public function __construct(array $passes) {
        $this->passes = $passes;
    }
    /**
     * Run the ABC processing pipeline.
     * @param array $lines
     * @param array $headerFields
     * @param array $suggestions
     * @return array
     * @throws AbcProcessingException
     */
    public function run(array $lines, array $headerFields, array $suggestions = [], $logFlow = false) {
        if ($logFlow) FlowLog::log('AbcProcessingPipeline::run ENTRY', true);
        $canntDiff = [];
        $errors = [];
        foreach ($this->passes as $pass) {
            try {
                if ($logFlow) FlowLog::log('Pipeline pass: '.get_class($pass).' ENTRY', true);
                if ($pass instanceof AbcTuneNumberValidatorPass) {
                    $result = $pass->validate($lines);
                    $lines = $result['lines'];
                    if (!empty($result['errors'])) {
                        foreach ($result['errors'] as $err) {
                            $errors[] = 'TUNE NUMBER: ' . $err;
                        }
                    }
                } elseif ($pass instanceof AbcLyricsPass) {
                    $result = $pass->process($lines);
                    $lines = $result['lines'];
                    if (!empty($result['lyricsWords'])) {
                        $lines[] = 'W: ' . implode(' ', $result['lyricsWords']);
                    }
                } elseif ($pass instanceof AbcCanntaireachdPass) {
                    $result = $pass->process($lines);
                    $lines = $result['lines'];
                    $canntDiff = $result['canntDiff'];
                } elseif ($pass instanceof AbcTimingValidator) {
                    $result = $pass->validate($lines);
                    $lines = $result['lines'];
                    if (!empty($result['errors'])) {
                        $errors = array_map(function($e){return 'TIMING: '.$e;}, $result['errors']);
                    }
                } elseif ($pass instanceof AbcFormattingPass) {
                    $result = $pass->process($lines);
                    $lines = $result['lines'];
                } else {
                    $lines = $pass->process($lines);
                }
                if ($logFlow) FlowLog::log('Pipeline pass: '.get_class($pass).' EXIT', true);
            } catch (\Throwable $ex) {
                $file = $ex->getFile();
                $line = $ex->getLine();
                $trace = $ex->getTrace();
                $function = '';
                $class = '';
                if (isset($trace[0])) {
                    if (isset($trace[0]['function'])) {
                        $function = $trace[0]['function'];
                    }
                    if (isset($trace[0]['class'])) {
                        $class = $trace[0]['class'];
                    }
                }
                $where = ($class ? $class.'::':'').$function.'()';
                $msg = 'Pipeline pass: '.get_class($pass).' EXCEPTION: '.$ex->getMessage()." at $file:$line in $where";
                if ($logFlow) FlowLog::log($msg, true);
                throw new AbcProcessingException('Error in pipeline: ' . $ex->getMessage()." at $file:$line in $where", 0, $ex);
            }
        }
        if ($logFlow) FlowLog::log('AbcProcessingPipeline::run EXIT', true);
        foreach ($suggestions as $s) {
            $lines[] = "% Suggested: {$s['field']} '{$s['value']}' ~ '{$s['bestMatch']}' (score: {$s['score']})";
        }
        return [
            'lines' => $lines,
            'canntDiff' => $canntDiff,
            'errors' => $errors
        ];
    }

    /**
     * Process ABC text using modern object-based transforms.
     * 
     * Implements the Parse → Transform* → Render pattern:
     * 1. Parse ABC text into AbcTune object
     * 2. Apply each transform sequentially to the tune
     * 3. Render the transformed tune back to ABC text
     * 
     * @param string $abcText The ABC notation text to process
     * @param AbcTransform[] $transforms Array of transform objects to apply
     * @param bool $logFlow Enable flow logging (default: false)
     * @return array Result with keys: 'text' (string), 'errors' (array)
     * 
     * @throws \Exception If parsing or transformation fails
     */
    public function processWithTransforms(string $abcText, array $transforms, bool $logFlow = false): array
    {
        $errors = [];

        try {
            if ($logFlow) {
                FlowLog::log('AbcProcessingPipeline::processWithTransforms ENTRY', true);
                FlowLog::log('Input text length: ' . strlen($abcText), true);
                FlowLog::log('Transforms count: ' . count($transforms), true);
            }

            // Step 1: Parse ABC text into object model
            $tune = AbcTune::parse($abcText);
            
            if ($logFlow) {
                FlowLog::log('Parsed tune with ' . count($tune->getVoices()) . ' voices', true);
            }

            // Step 2: Apply each transform sequentially
            foreach ($transforms as $index => $transform) {
                if (!($transform instanceof AbcTransform)) {
                    $error = 'Transform at index ' . $index . ' does not implement AbcTransform interface';
                    $errors[] = $error;
                    if ($logFlow) {
                        FlowLog::log('ERROR: ' . $error, true);
                    }
                    continue;
                }

                if ($logFlow) {
                    FlowLog::log('Applying transform: ' . get_class($transform), true);
                }

                $tune = $transform->transform($tune);
                
                if ($logFlow) {
                    FlowLog::log('Transform complete, voices: ' . count($tune->getVoices()), true);
                }
            }

            // Step 3: Render back to ABC text
            $resultText = $tune->renderSelf();
            
            if ($logFlow) {
                FlowLog::log('Rendered text length: ' . strlen($resultText), true);
                FlowLog::log('AbcProcessingPipeline::processWithTransforms EXIT', true);
            }

            return [
                'text' => $resultText,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $error = 'Pipeline processing failed: ' . $e->getMessage();
            $errors[] = $error;
            
            if ($logFlow) {
                FlowLog::log('EXCEPTION: ' . $error, true);
                FlowLog::log('Stack trace: ' . $e->getTraceAsString(), true);
            }

            return [
                'text' => $abcText, // Return original text on error
                'errors' => $errors
            ];
        }
    }
}
