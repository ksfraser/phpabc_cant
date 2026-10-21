<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Log\CanntLog;

/**
 * Class CanntGenerator
 *
 * Generates canntaireachd lyrics for ABC note bodies using a token dictionary.
 * Supports legacy dictionary loading and robust token matching for bagpipe voice conversion.
 * Follows SRP by focusing solely on canntaireachd generation.
 * Uses DI for TokenDictionary.
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @requirement FR2, FR8
 * @uml
 * @startuml
 * class CanntGenerator {
 *   - dict: TokenDictionary
 *   + __construct(dict: TokenDictionary)
 *   + generateForNotes(noteBody: string): string
 * }
 * CanntGenerator --> TokenDictionary : uses
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant CanntGenerator
 * participant TokenDictionary
 * User -> CanntGenerator: generateForNotes(noteBody)
 * CanntGenerator -> TokenDictionary: convertAbcToCannt(token)
 * TokenDictionary --> CanntGenerator: canntToken
 * CanntGenerator --> User: canntText
 * @enduml
 */
class CanntGenerator
{
    /** @var TokenDictionary */
    protected TokenDictionary $dict;

    /**
     * Constructor for CanntGenerator.
     * Loads dictionary from file if not provided.
     * Uses DI for TokenDictionary.
     *
     * @param TokenDictionary|null $dict The token dictionary.
     * @requirement FR7
     */
    public function __construct(?TokenDictionary $dict = null)
    {
        // Use TokenDictionary constructor which automatically loads abc_dict.php
        $this->dict = $dict ?? new TokenDictionary();
    }

    /**
     * Generates canntaireachd lyrics for the given ABC note body.
     * Uses proper ABC tokenization with longest-match-first logic.
     *
     * @param string $noteBody The ABC music line.
     * @return string The canntaireachd text.
     * @requirement FR2, FR8
     */
    public function generateForNotes(string $noteBody): string
    {
        $noteBody = trim($noteBody);
        CanntLog::log("generateForNotes input: $noteBody", true);
        if ($noteBody === '') {
            return '[?]';
        }

        // Strip voice prefixes like [V:Bagpipes] from the beginning of the line
        $noteBody = preg_replace('/^\[V:[^\]]*\]/', '', $noteBody) ?? $noteBody;
        $noteBody = trim($noteBody);

        $result = $this->tokenizeAndConvert($noteBody);
        CanntLog::log("generateForNotes output: $result", true);
        return $result !== '' ? $result : '[?]';
    }

    /**
     * Tokenize ABC input and convert to canntaireachd.
     *
     * A grace-note group is kept attached to its principal note, so "{g}A" is
     * looked up as one token — that is how the dictionary stores it.
     *
     * @param string $input The ABC music line.
     * @return string The canntaireachd text.
     */
    private function tokenizeAndConvert(string $input): string
    {
        // grace note | accidental | note | octave marks | length | bar | space
        $pattern = '/(\{[^{}]+\})|([_=^])|([a-gA-GzZ])|([,\']*)|(\d+\/?\d*)|(\|)|(\s+)/';

        if (preg_match_all($pattern, $input, $matches, PREG_SET_ORDER) === 0) {
            return '';
        }

        $result = '';
        $currentToken = '';
        $count = count($matches);

        for ($i = 0; $i < $count; $i++) {
            $token = $matches[$i][0];

            // Grace notes and accidentals belong to the note that follows.
            if (preg_match('/^\{/', $token) === 1 || in_array($token, ['_', '=', '^'], true)) {
                $currentToken .= $token;
                continue;
            }

            if (preg_match('/^[a-gA-GzZ]$/', $token) === 1) {
                $currentToken .= $token;
                // Octave marks are part of the token.
                while ($i + 1 < $count && preg_match('/^[,\']+$/', $matches[$i + 1][0]) === 1) {
                    $currentToken .= $matches[++$i][0];
                }
                // The length is not part of the dictionary key.
                if ($i + 1 < $count && preg_match('/^\d+\/?\d*$/', $matches[$i + 1][0]) === 1) {
                    $i++;
                }
                $cannt = $this->dict->convertAbcToCannt($currentToken);
                $result .= $cannt !== null ? $cannt . ' ' : '[' . $currentToken . '] ';
                $currentToken = '';
                continue;
            }

            // Bar lines become bar lines in the canntaireachd.
            if ($token === '|') {
                $result .= '| ';
                continue;
            }

            // Lengths, spaces and anything else are dropped.
        }

        return rtrim($result, ' ');
    }
}
