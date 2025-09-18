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
        foreach ($lines as $idx => $line) {
            if (preg_match('/^X:/', $line)) {
                // Ensure blank line before X: header
                if ($idx > 0 && trim($lines[$idx-1]) !== '') {
                    $tunes[] = $currentTune;
                    $currentTune = null;
                }
                if ($currentTune) $tunes[] = $currentTune;
                $currentTune = new AbcTune();
                $currentTune->addHeader('X', substr($line, 2));
            } elseif ($currentTune && preg_match('/^V:/', $line)) {
                // Always preserve V: header line
                $abcLine = new AbcLine();
                $abcLine->setHeaderLine($line);
                $currentTune->add($abcLine);
            } elseif ($currentTune && preg_match('/^([A-Z]):(.*)/', $line, $m)) {
                $key = $m[1];
                $value = trim($m[2]);
                // Use header class if available
                $headerClass = 'Ksfraser\\PhpabcCanntaireachd\\Header\\AbcHeader' . $key;
                if (class_exists($headerClass)) {
                    // Multi-value fields
                    if (in_array($key, ['C', 'B'])) {
                        $currentTune->addHeader($key, $value);
                    } else {
                        // Single-value: first/last policy
                        $existing = $currentTune->getHeaders();
                        if ($this->singleHeaderPolicy === 'first' && isset($existing[$key]) && $existing[$key]->get() !== '') {
                            // Ignore subsequent
                        } else {
                            $currentTune->replaceHeader($key, $value);
                        }
                    }
                } else {
                    // Fallback: treat as string
                    $currentTune->addHeader($key, $value);
                }
            } elseif ($currentTune && trim($line) === '') {
                // Skip blank lines inside tune to avoid extra blank lines in output
                continue;
            } elseif ($currentTune && preg_match('/^%%(landscape|portrait|continueall|breakall|newpage|leftmargin|rightmargin|topmargin|bottommargin|pagewidth|pageheight|scale|staffwidth)/i', trim($line))) {
                // %% formatting directive
                $currentTune->add(new AbcFormattingLine($line));
            } elseif ($currentTune && preg_match('/^%%/', trim($line))) {
                // %% instruction line (MIDI, etc.)
                $currentTune->add(new AbcMidiLine($line));
            } elseif ($currentTune && preg_match('/^%/', trim($line))) {
                // % comment line
                $currentTune->add(new AbcCommentLine($line));
            } elseif ($currentTune) {
                // Parse bars for each line
                $abcLine = new AbcLine();
                foreach (preg_split('/\|/', $line) as $barText) {
                    $barText = trim($barText);
                    if ($barText !== '') {
                        $abcLine->add(new AbcBar($barText));
                    }
                }
                $currentTune->add($abcLine);
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
