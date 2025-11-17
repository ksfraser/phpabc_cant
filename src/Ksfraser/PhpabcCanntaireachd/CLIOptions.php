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

    // NEW: Configuration file options
    public $configFile = null;        // --config=path/to/config.json
    public $saveConfigFile = null;    // --save-config=path/to/config.json
    public $showConfig = false;       // --show-config
    
    // NEW: Voice ordering options
    public $voiceOrderMode = null;    // --voice-order=source|orchestral|custom
    public $voiceOrderConfig = null;  // --voice-order-config=file.json
    
    // NEW: Transpose options
    public $transposeMode = null;     // --transpose-mode=midi|bagpipe|orchestral
    public $transposeOverride = [];   // --transpose-override=Voice:N (can be multiple)
    
    // NEW: Database options
    public $noMidiDefaults = false;   // --no-midi-defaults
    public $strictMode = false;       // --strict

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
        $long = [
            'file:', 'convert', 'output:', 'errorfile:', 'canntdiff:', 'xnum:', 'validate:', 'save:', 
            'interleave_bars:', 'bars_per_line:', 'join_bars_with_backslash:', 'voice_ouptut_style:', 
            'width:', 'update_voice_names_from_midi', 'help',
            // NEW configuration options
            'config:', 'save-config:', 'show-config',
            'voice-order:', 'voice-order-config:',
            'transpose-mode:', 'transpose-override:',
            'no-midi-defaults', 'strict'
        ];
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

        // NEW: Parse configuration options
        if (isset($this->opts['config'])) $this->configFile = $this->opts['config'];
        if (isset($this->opts['save-config'])) $this->saveConfigFile = $this->opts['save-config'];
        if (isset($this->opts['show-config'])) $this->showConfig = true;
        
        if (isset($this->opts['voice-order'])) $this->voiceOrderMode = $this->opts['voice-order'];
        if (isset($this->opts['voice-order-config'])) $this->voiceOrderConfig = $this->opts['voice-order-config'];
        
        if (isset($this->opts['transpose-mode'])) $this->transposeMode = $this->opts['transpose-mode'];
        if (isset($this->opts['transpose-override'])) {
            // Can be single or array
            $overrides = is_array($this->opts['transpose-override']) 
                ? $this->opts['transpose-override'] 
                : [$this->opts['transpose-override']];
            foreach ($overrides as $override) {
                if (preg_match('/^([^:]+):(-?\d+)$/', $override, $m)) {
                    $this->transposeOverride[$m[1]] = (int)$m[2];
                }
            }
        }
        
        if (isset($this->opts['no-midi-defaults'])) $this->noMidiDefaults = true;
        if (isset($this->opts['strict'])) $this->strictMode = true;

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
                } elseif (preg_match('/^--config=(.+)$/', $arg, $m)) {
                    $this->configFile = $m[1];
                } elseif (preg_match('/^--save-config=(.+)$/', $arg, $m)) {
                    $this->saveConfigFile = $m[1];
                } elseif ($arg === '--show-config') {
                    $this->showConfig = true;
                } elseif (preg_match('/^--voice-order=(.+)$/', $arg, $m)) {
                    $this->voiceOrderMode = $m[1];
                } elseif (preg_match('/^--voice-order-config=(.+)$/', $arg, $m)) {
                    $this->voiceOrderConfig = $m[1];
                } elseif (preg_match('/^--transpose-mode=(.+)$/', $arg, $m)) {
                    $this->transposeMode = $m[1];
                } elseif (preg_match('/^--transpose-override=([^:]+):(-?\d+)$/', $arg, $m)) {
                    $this->transposeOverride[$m[1]] = (int)$m[2];
                } elseif ($arg === '--no-midi-defaults') {
                    $this->noMidiDefaults = true;
                } elseif ($arg === '--strict') {
                    $this->strictMode = true;
                } elseif (preg_match('/^--bars[_-]per[_-]line=(\d+)$/', $arg, $m)) {
                    $this->barsPerLine = (int)$m[1];
                } elseif (preg_match('/^--interleave[_-]bars=(\d+)$/', $arg, $m)) {
                    $this->interleaveBars = (int)$m[1];
                } elseif (preg_match('/^--voice[_-]output[_-]style=(.+)$/', $arg, $m)) {
                    $this->voiceOutputStyle = $m[1];
                } elseif ($arg === '--join-bars-with-backslash' || $arg === '--join_bars_with_backslash') {
                    $this->joinBarsWithBackslash = true;
                } elseif (preg_match('/^--width=(\d+)$/', $arg, $m)) {
                    $this->width = (int)$m[1];
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

    /**
     * Apply CLI options to AbcProcessorConfig
     * CLI options override config file settings
     * 
     * @param AbcProcessorConfig $config Configuration to update
     * @return void
     */
    public function applyToConfig(AbcProcessorConfig $config): void
    {
        // Apply processing options
        if ($this->voiceOutputStyle !== null) {
            $config->voiceOutputStyle = $this->voiceOutputStyle;
        }
        if ($this->interleaveBars !== null && $this->interleaveBars !== false) {
            $config->interleaveBars = (int)$this->interleaveBars;
        }
        if ($this->barsPerLine !== null) {
            $config->barsPerLine = (int)$this->barsPerLine;
        }
        if ($this->joinBarsWithBackslash !== null) {
            $config->joinBarsWithBackslash = (bool)$this->joinBarsWithBackslash;
        }
        if ($this->width !== null) {
            $config->tuneNumberWidth = (int)$this->width;
        }
        
        // Apply voice ordering options
        if ($this->voiceOrderMode !== null) {
            $config->voiceOrderingMode = $this->voiceOrderMode;
        }
        if ($this->voiceOrderConfig !== null && file_exists($this->voiceOrderConfig)) {
            // Load custom voice order from file
            $orderConfig = \Ksfraser\PhpabcCanntaireachd\Config\ConfigLoader::loadFromFile($this->voiceOrderConfig);
            if (isset($orderConfig['voice_ordering']['custom_order'])) {
                $config->customVoiceOrder = $orderConfig['voice_ordering']['custom_order'];
            }
        }
        
        // Apply transpose options
        if ($this->transposeMode !== null) {
            $config->transposeMode = $this->transposeMode;
        }
        if (!empty($this->transposeOverride)) {
            $config->transposeOverrides = array_merge(
                $config->transposeOverrides,
                $this->transposeOverride
            );
        }
        
        // Apply canntaireachd options
        if ($this->convert) {
            $config->convertCanntaireachd = true;
        }
        if ($this->canntDiffFile !== null) {
            $config->generateCanntDiff = true;
            $config->canntDiffFile = $this->canntDiffFile;
        }
        
        // Apply output options
        if ($this->outputFile !== null) {
            $config->outputFile = $this->outputFile;
        }
        if ($this->errorFile !== null) {
            $config->errorFile = $this->errorFile;
        }
        
        // Apply database options
        if ($this->noMidiDefaults) {
            $config->useMidiDefaults = false;
        }
        
        // Apply validation options
        if ($this->strictMode) {
            $config->strictMode = true;
        }
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
            // NEW options
            'configFile' => $this->configFile,
            'saveConfigFile' => $this->saveConfigFile,
            'showConfig' => $this->showConfig,
            'voiceOrderMode' => $this->voiceOrderMode,
            'voiceOrderConfig' => $this->voiceOrderConfig,
            'transposeMode' => $this->transposeMode,
            'transposeOverride' => $this->transposeOverride,
            'noMidiDefaults' => $this->noMidiDefaults,
            'strictMode' => $this->strictMode,
        ];
    }
}
