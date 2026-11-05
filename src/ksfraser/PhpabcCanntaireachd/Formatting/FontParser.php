<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Formatting;

use ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for font directives (%%titlefont, %%composerfont, %%vocalfont, etc.)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class FontParser extends FormattingDirectiveParser implements AbcLineParser
{
    private const VALID_FONT_TYPES = [
        'titlefont', 'subtitlefont', 'composerfont', 'footerfont',
        'vocalfont', 'gchordfont', 'textfont', 'wordsfont',
        'tempofont', 'partsfont', 'voicefont', 'annotationfont'
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, self::VALID_FONT_TYPES) &&
               preg_match('/\S/', trim(preg_replace('/^%%\s*\w+\s*/i', '', $line)));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune = null) {
        if (!preg_match('/^%%\s*(' . implode('|', self::VALID_FONT_TYPES) . ')\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $fontType = strtolower($matches[1]);
        $fontSpec = trim($matches[2]);

        // Basic validation - font spec should not be empty
        if (empty($fontSpec)) {
            if ($tune !== null) {
                $tune->add(new AbcFormattingLine("%% Invalid $fontType specification: empty value"));
            }
            return false;
        }

        // Extract font name and size (size is last token if numeric)
        $fontParts = preg_split('/\s+/', $fontSpec);
        $fontSize = '';
        $fontName = $fontSpec;
        if (count($fontParts) > 1 && is_numeric(end($fontParts))) {
            $fontSize = array_pop($fontParts);
            $fontName = implode(' ', $fontParts);
        }

        // Create standardized formatting line
        $correctedLine = "%% $fontType $fontSpec";
        if ($tune !== null) {
            $tune->add(new AbcFormattingLine($correctedLine));
        }

        return [
            'directive' => $fontType,
            'value' => $fontSpec,
            'font_name' => $fontName,
            'font_size' => $fontSize,
        ];
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(' . implode('|', self::VALID_FONT_TYPES) . ')\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $fontSpec = trim($matches[2]);
        if (empty($fontSpec)) {
            return false;
        }

        // Must have font size (numeric last token)
        $fontParts = preg_split('/\s+/', $fontSpec);
        if (count($fontParts) < 2 || !is_numeric(end($fontParts))) {
            return false;
        }

        return true;
    }
}