<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Simple CLI options parser used by the bin/*.php scripts.
 *
 * Recognizes both short/long getopt-style options and simple --key=value argv styles.
 */
class CLIOptions {
    public string|null $file = null;         // input abc file path
    public bool $convert = false;            // --convert or -c
    public string|null $outputFile = null;   // --output or -o
    public string|null $errorFile = null;    // --errorfile or -e
    public string|null $xnum = null;         // tune number (positional)

    // Additional hooks seen in some scripts
    public array $voiceOrder = [];
    public array $exclude = [];

    // Raw parsed arrays
    public array $opts = [];
    public array $argv = [];

    public function __construct(array $argv = null)
    {
        $this->argv = $argv ?? ($_SERVER['argv'] ?? []);
        $this->parse();
    }

    protected function parse(): void
    {
        // Attempt to use getopt first (handles short and long forms)
        $short = 'f:c:o:e:';
        $long = ['file:', 'convert', 'output:', 'errorfile:'];
        $parsed = @getopt($short, $long);
        $this->opts = is_array($parsed) ? $parsed : [];

        // Map getopt results to properties
        if (isset($this->opts['f'])) $this->file = $this->opts['f'];
        if (isset($this->opts['file'])) $this->file = $this->opts['file'];
        if (isset($this->opts['c']) || isset($this->opts['convert'])) $this->convert = true;
        if (isset($this->opts['o'])) $this->outputFile = $this->opts['o'];
        if (isset($this->opts['output'])) $this->outputFile = $this->opts['output'];
        if (isset($this->opts['e'])) $this->errorFile = $this->opts['e'];
        if (isset($this->opts['errorfile'])) $this->errorFile = $this->opts['errorfile'];

        // If getopt returned values, determine leftover positional args using argv
        $positional = [];
        if (!empty($this->argv)) {
            // remove script name
            $args = $this->argv;
            array_shift($args);
            // Filter out any args consumed by getopt (e.g., --key or --key=val or -k val)
            foreach ($args as $arg) {
                // skip known long/short opts occurrences
                if (preg_match('/^-/',$arg)) continue;
                $positional[] = $arg;
            }
        }

        // If no getopt options were used (some scripts parse argv manually), parse --key=value style
        if (empty($this->opts) && !empty($this->argv)) {
            $args = $this->argv;
            array_shift($args);
            foreach ($args as $i => $arg) {
                if (preg_match('/^--output=(.+)$/', $arg, $m)) {
                    $this->outputFile = $m[1];
                } elseif (preg_match('/^--errorfile=(.+)$/', $arg, $m)) {
                    $this->errorFile = $m[1];
                } elseif (preg_match('/^--exclude=(.+)$/', $arg, $m)) {
                    $this->exclude = array_map('trim', explode(',', $m[1]));
                } elseif (preg_match('/^--voiceOrder=(.+)$/', $arg, $m)) {
                    // voiceOrder may be JSON or comma-separated
                    $raw = $m[1];
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) $this->voiceOrder = $decoded;
                    else $this->voiceOrder = array_map('trim', explode(',', $raw));
                } elseif (!isset($this->file)) {
                    $this->file = $arg;
                } elseif (!isset($this->xnum)) {
                    $this->xnum = $arg;
                }
            }
        } else {
            // Use positional elements collected above
            if (isset($positional[0]) && $this->file === null) $this->file = $positional[0];
            if (isset($positional[1]) && $this->xnum === null) $this->xnum = $positional[1];
        }

        // Normalise empty strings to null
        if ($this->file === '') $this->file = null;
        if ($this->outputFile === '') $this->outputFile = null;
        if ($this->errorFile === '') $this->errorFile = null;
    }

    // Convenience factory
    public static function fromArgv(array $argv = null): self
    {
        return new self($argv);
    }

    // Export for debug/logging
    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'xnum' => $this->xnum,
            'convert' => $this->convert,
            'outputFile' => $this->outputFile,
            'errorFile' => $this->errorFile,
            'voiceOrder' => $this->voiceOrder,
            'exclude' => $this->exclude,
        ];
    }
}
