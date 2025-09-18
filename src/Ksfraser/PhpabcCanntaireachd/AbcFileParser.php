<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Parses ABC files into AbcTune objects, handling multiple tunes per file.
 */
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderAll;

class AbcFileParser {
    /**
     * Config: 'first' or 'last' for single-value header fields
     */
    protected $singleHeaderPolicy = 'last';
    
    /**
     * Config: whether to update voice names from MIDI program information
     */
    protected $updateVoiceNamesFromMidi = false;

    public function __construct($config = []) {
        if (isset($config['singleHeaderPolicy'])) {
            switch ($config['singleHeaderPolicy']) {
                case 'first':
                case 'last':
            		$this->singleHeaderPolicy = $config['singleHeaderPolicy'];
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid singleHeaderPolicy: " . $config['singleHeaderPolicy']);
            }
        }
        
        if (isset($config['updateVoiceNamesFromMidi'])) {
            $this->updateVoiceNamesFromMidi = (bool)$config['updateVoiceNamesFromMidi'];
        }
    }

    /**
     * Parse ABC file content into an array of AbcTune objects.
     * @param string $abcContent
     * @return AbcTune[]
     */
    public function parse($abcContent): array {
        $lines = preg_split('/\r?\n/', $abcContent);
        $tunes = [];
        $currentTune = null;
        
        // Initialize parsers in order of specificity
        $parsers = [
            new HeaderParser($this->singleHeaderPolicy),
            new FormattingParser(),
            new MidiParser(),
            new CommentParser(),
            new BodyParser()
        ];
        
        foreach ($lines as $idx => $line) {
            // Special handling for X: lines that start new tunes
            if (preg_match('/^X:/', $line)) {
                // Ensure blank line before X: header
                if ($idx > 0 && trim($lines[$idx-1]) !== '') {
                    if ($currentTune) $tunes[] = $currentTune;
                    $currentTune = null;
                }
                if ($currentTune) $tunes[] = $currentTune;
                $currentTune = new AbcTune();
                $currentTune->addHeader('X', substr($line, 2));
                continue; // Skip further processing of this line
            }
            
            if (!$currentTune) {
                continue;
            }
            
            // Skip blank lines
            if (trim($line) === '') {
                continue;
            }
            
            // Special handling for V: lines (preserve original behavior)
            if ($currentTune && preg_match('/^V:/', $line)) {
                // Always preserve V: header line
                $abcLine = new AbcLine();
                $abcLine->setHeaderLine($line);
                $currentTune->add($abcLine);
                continue; // Skip further parser processing
            }
            
            // Try each parser in order
            $parsed = false;
            $valid = true;
            foreach ($parsers as $parser) {
                if ($parser->canParse($line)) {
                    $parsed = $parser->parse($line, $currentTune);
                    $valid = $parser->validate($line);
                    if ($parsed) {
                        break;
                    }
                }
            }
            
            // If parsing failed or line is invalid, add as body line (fallback)
            if (!$parsed) {
                $abcLine = new AbcLine();
                foreach (preg_split('/\|/', $line) as $barText) {
                    $barText = trim($barText);
                    if ($barText !== '') {
                        $abcLine->add(new AbcBar($barText));
                    }
                }
                $currentTune->add($abcLine);
                // For fallback body lines, validate using BodyParser
                $bodyParser = new BodyParser();
                $valid = $bodyParser->validate($line);
            }
            
            // Store validation result for later use
            if (!$valid) {
                // Could store validation errors here for reporting
            }
        }
        
        if ($currentTune) $tunes[] = $currentTune;
        // Centralized header defaults loader
        $headerDefaults = \Ksfraser\PhpabcCanntaireachd\HeaderDefaults::getDefaults();
        // Fill missing header fields with defaults
        foreach ($tunes as $tune) {
            $headers = $tune->getHeaders();
            foreach ($headerDefaults as $key => $value) {
                // Apply default when header missing or present but empty
                if (!isset($headers[$key]) || (method_exists($headers[$key], 'get') && $headers[$key]->get() === '')) {
                    $tune->replaceHeader($key, $value);
                }
            }
        }
        // Remove nulls
        return array_filter($tunes);
    }
}
