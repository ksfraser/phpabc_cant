<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Exceptions\AbcProcessingException;

/**
 * Class AbcProcessingPipeline
 *
 * Encapsulates the ABC processing pipeline, replacing control blocks with SRP/DI classes and custom exceptions.
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
    public function run(array $lines, array $headerFields, array $suggestions = []) {
        $canntDiff = [];
        $errors = [];
        foreach ($this->passes as $pass) {
            try {
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
                } else {
                    $lines = $pass->process($lines);
                }
            } catch (\Throwable $ex) {
                throw new AbcProcessingException('Error in pipeline: ' . $ex->getMessage(), 0, $ex);
            }
        }
        foreach ($suggestions as $s) {
            $lines[] = "% Suggested: {$s['field']} '{$s['value']}' ~ '{$s['bestMatch']}' (score: {$s['score']})";
        }
        return [
            'lines' => $lines,
            'canntDiff' => $canntDiff,
            'errors' => $errors
        ];
    }
}
