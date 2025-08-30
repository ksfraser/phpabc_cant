<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Tokenizes ABC note strings using dictionary-based longest-match-first logic.
 * Handles embellishments, grace notes, and complex groupings.
 */
class AbcNoteTokenizer
{
    /**
     * @var array The ABC dictionary for embellishments and note parsing.
     */
    protected $abcDict;

    /**
     * @param array $abcDict The ABC dictionary (from abc_dict.php)
     */
    public function __construct(array $abcDict)
    {
        $this->abcDict = $abcDict;
    }

    /**
     * Tokenize an ABC string into AbcNote objects using longest-match-first.
     * @param string $abcStr
     * @return AbcNote[]
     */
    public function tokenize($abcStr)
    {
        $tokens = [];
        $input = $abcStr;
        $dictKeys = array_keys($this->abcDict);
        // Sort keys by length descending for longest-match-first
        usort($dictKeys, function($a, $b) { return strlen($b) - strlen($a); });
        while (strlen($input) > 0) {
            $matched = false;
            foreach ($dictKeys as $key) {
                if ($key === '') continue;
                if (strpos($input, $key) === 0) {
                    $tokens[] = new AbcNote($key);
                    $input = substr($input, strlen($key));
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                // Fallback: try to parse a single note using regex
                if (preg_match("/^([_=^]?)([a-gA-GzZ])([,']*)([0-9]+\/?[0-9]*|\/{1,}|)([^\s]*)/", $input, $m)) {
                    $noteStr = $m[0];
                    $tokens[] = new AbcNote($noteStr);
                    $input = substr($input, strlen($noteStr));
                } else {
                    // Skip unknown character
                    $input = substr($input, 1);
                }
            }
            // Trim leading whitespace
            $input = ltrim($input);
        }
        return $tokens;
    }
}
