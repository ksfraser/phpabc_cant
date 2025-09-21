<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Simple CLI options parser used by th                if (preg_match('/^--errorfile=(.+)$/', $arg, $m)) {
                    $this->errorFile = $m[1];
                } elseif (preg_match('/^--canntdiff=(.+)$/', $arg, $m)) {
                    $this->canntDiffFile = $m[1];
                } elseif (preg_match('/^--exclude=(.+)$/', $arg, $m)) {in/*.php scripts.
 *
 * Recognizes both short/long getopt-style options and simple --key=value argv styles.
 */
class CLIOptions {
    public  $file = null;         // input abc file path -f or --file
    public  $convert = false;            // --convert or -c
    public  $outputFile = null;   // --output or -o
    public  $errorFile = null;    // --errorfile or -e
    public  $canntDiffFile = null; // --canntdiff or -d
    public  $xnum = null;         // tune number (positional) -x or --xnum
    public $validate = false;  // --validate or -v
    public $save = false;      // --save or -s
    public $interleaveBars = false; // --interleave_bars or -i
    public $barsPerLine = null; // --bars_per_line or -b
    public $joinBarsWithBackslash = false; // --join_bars_with_backslash or -j
    public $voiceOutputStyle = null; // --voice_output_style or -V
    public $width = null; // --width or -w  for renumbering X
    public $updateVoiceNamesFromMidi = false; // --update-voice-names-from-midi or -u

    // Additional hooks seen in some scripts
    public  $voiceOrder = [];
    public  $exclude = [];

    // Raw parsed arrays
    public  $opts = [];
    public  $argv = [];

    public function __construct( $argv = null)
    {
        $this->argv = $argv ?? ($_SERVER['argv'] ?? []);
        $this->parse();
    }

    protected function parse(): void
    {
        // Attempt to use getopt first (handles short and long forms)
        $short = 'f:c:o:e:d:x:v:s:i:b:j:V:w:u:h';
        $long = ['file:', 'convert', 'output:', 'errorfile:', 'canntdiff:', 'xnum:', 'validate:', 'save:', 'interleave_bars:', 'bars_per_line:', 'join_bars_with_backslash:', 'voice_ouptut_style:', 'width:', 'update_voice_names_from_midi', 'help'];
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
        if (isset($this->opts['d'])) $this->canntDiffFile = $this->opts['d'];
        if (isset($this->opts['canntdiff'])) $this->canntDiffFile = $this->opts['canntdiff'];
        if (isset($this->opts['x'])) $this->xnum = $this->opts['x'];
        if (isset($this->opts['xnum'])) $this->xnum = $this->opts['xnum'];
        if (isset($this->opts['v'])) $this->validate = $this->opts['v'];
        if (isset($this->opts['validate'])) $this->validate = $this->opts['validate'];
        if (isset($this->opts['s'])) $this->save = $this->opts['s'];
        if (isset($this->opts['save'])) $this->save = $this->opts['save'];
        if (isset($this->opts['i'])) $this->interleaveBars = $this->opts['i'];
        if (isset($this->opts['interleave_bars'])) $this->interleaveBars = $this->opts['interleave_bars'];
        if (isset($this->opts['b'])) $this->barsPerLine = $this->opts['b'];
        if (isset($this->opts['bars_per_line'])) $this->barsPerLine = $this->opts['bars_per_line'];
        if (isset($this->opts['j'])) $this->joinBarsWithBackslash = $this->opts['j'];
        if (isset($this->opts['join_bars_with_backslash'])) $this->joinBarsWithBackslash = $this->opts['join_bars_with_backslash'];
        if (isset($this->opts['V'])) $this->joinBarsWithBackslash = $this->opts['V'];
        if (isset($this->opts['voice_output_style'])) $this->joinBarsWithBackslash = $this->opts['voice_output_style'];
        if (isset($this->opts['u']) || isset($this->opts['update_voice_names_from_midi'])) $this->updateVoiceNamesFromMidi = true;


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
                } elseif (preg_match('/^--update[_-]voice[_-]names[_-]from[_-]midi(?:=(.+))?$/', $arg, $m)) {
                    $this->updateVoiceNamesFromMidi = true;
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
    public static function fromArgv( $argv = null): self
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
            'canntDiffFile' => $this->canntDiffFile,
            'voiceOrder' => $this->voiceOrder,
            'exclude' => $this->exclude,
            'updateVoiceNamesFromMidi' => $this->updateVoiceNamesFromMidi,
        ];
    }
}
