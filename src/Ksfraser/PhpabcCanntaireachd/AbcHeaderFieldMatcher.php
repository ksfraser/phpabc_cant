<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * AbcHeaderFieldMatcher: compares tune header fields to stored values, scores matches, and suggests corrections.
 */
class AbcHeaderFieldMatcher
{
    /** @var AbcHeaderFieldTable */
    protected $table;

    public function __construct(AbcHeaderFieldTable $table)
    {
        $this->table = $table;
    }

    /**
     * Compare a tune's header field value to stored values, return best match score and value.
     * @param string $field
     * @param string $value
     * @return array [score, bestMatch]
     */
    public function matchField($field, $value)
    {
        $values = $this->table->getFieldValues($field);
        $bestScore = 0;
        $bestMatch = null;
        foreach ($values as $stored) {
            $score = $this->score($value, $stored);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $stored;
            }
        }
        return [$bestScore, $bestMatch];
    }

    /**
     * Score similarity between two strings (simple normalized Levenshtein).
     * @param string $a
     * @param string $b
     * @return float
     */
    public function score($a, $b)
    {
        if ($a === $b) return 1.0;
        $maxLen = max(strlen($a), strlen($b));
        if ($maxLen === 0) return 0.0;
        $lev = levenshtein($a, $b);
        return 1.0 - ($lev / $maxLen);
    }

    /**
     * Process a tune's header fields, update table and suggest corrections.
     * @param array $tuneFields [field => value]
     * @return array List of suggestions: [field, value, bestMatch, score]
     */
    public function processTuneFields(array $tuneFields)
    {
        $suggestions = [];
        foreach ($tuneFields as $field => $value) {
            list($score, $bestMatch) = $this->matchField($field, $value);
            if ($score < 0.5) {
                // Low match: add new value
                $this->table->addFieldValue($field, $value);
            } elseif ($score < 0.95 && $bestMatch !== null) {
                // High but not exact: suggest correction
                $suggestions[] = [
                    'field' => $field,
                    'value' => $value,
                    'bestMatch' => $bestMatch,
                    'score' => $score
                ];
            }
        }
        return $suggestions;
    }
}
