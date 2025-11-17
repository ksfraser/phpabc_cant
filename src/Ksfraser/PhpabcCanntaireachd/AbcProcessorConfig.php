<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Config\ConfigLoader;
use Ksfraser\PhpabcCanntaireachd\Config\ConfigMerger;
use Ksfraser\PhpabcCanntaireachd\Config\ConfigValidator;

/**
 * ABC Processor Configuration
 * 
 * Configuration for ABC processing options including voice ordering,
 * transpose modes, canntaireachd generation, and output formatting.
 */
class AbcProcessorConfig {
    // === Existing Properties ===
    public $voiceOutputStyle = 'grouped'; // 'grouped' or 'interleaved'
    public $interleaveBars = 1; // X bars per voice before switching (if interleaved)
    public $barsPerLine = 4; // How many bars per ABC line
    public $joinBarsWithBackslash = false; // true: use \ to join bars, false: one line per typeset line
    public $tuneNumberWidth = 5; // Number of digits for X: tune numbers, left-filled with 0s
    
    // === NEW: Voice Ordering ===
    public $voiceOrderingMode = 'source'; // 'source', 'orchestral', or 'custom'
    public $customVoiceOrder = []; // Array of voice names/patterns for custom ordering
    
    // === NEW: Transpose Settings ===
    public $transposeMode = 'midi'; // 'midi', 'bagpipe', or 'orchestral'
    public $transposeOverrides = []; // ['VoiceName' => transposeValue]
    
    // === NEW: Canntaireachd Processing ===
    public $convertCanntaireachd = false;
    public $generateCanntDiff = false;
    
    // === NEW: Output File Paths ===
    public $outputFile = null;
    public $errorFile = null;
    public $canntDiffFile = null;
    
    // === NEW: Database Usage ===
    public $useMidiDefaults = true;
    public $useVoiceOrderDefaults = true;
    
    // === NEW: Validation Settings ===
    public $timingValidation = true;
    public $strictMode = false;
    
    /**
     * Load configuration from file
     * 
     * @param string $path Path to configuration file (.json, .yml, .ini, .php)
     * @return self Configuration instance
     * @throws \InvalidArgumentException If file not found or invalid
     */
    public static function loadFromFile(string $path): self
    {
        $config = ConfigLoader::loadFromFile($path);
        
        $instance = new self();
        $instance->mergeFromArray($config);
        
        return $instance;
    }
    
    /**
     * Load configuration with precedence from multiple locations
     * Checks: global config → user config → project config
     * 
     * @param array|null $customPaths Additional paths to check (lowest precedence)
     * @return self Configuration instance
     */
    public static function loadWithPrecedence(?array $customPaths = null): self
    {
        $paths = $customPaths ?? [];
        
        // Add standard paths (lowest to highest precedence)
        $paths[] = __DIR__ . '/../../config/abc_processor_config.json';
        $paths[] = __DIR__ . '/../../config/abc_processor_config.yml';
        $paths[] = __DIR__ . '/../../config/abc_processor_config.ini';
        
        // User config
        if (isset($_SERVER['HOME'])) {
            $paths[] = $_SERVER['HOME'] . '/.abc_processor_config.json';
            $paths[] = $_SERVER['HOME'] . '/.abc_processor_config.yml';
            $paths[] = $_SERVER['HOME'] . '/.abc_processor_config.ini';
        } elseif (isset($_SERVER['USERPROFILE'])) {
            // Windows
            $paths[] = $_SERVER['USERPROFILE'] . '/.abc_processor_config.json';
            $paths[] = $_SERVER['USERPROFILE'] . '/.abc_processor_config.yml';
            $paths[] = $_SERVER['USERPROFILE'] . '/.abc_processor_config.ini';
        }
        
        // Project config
        $paths[] = getcwd() . '/abc_config.json';
        $paths[] = getcwd() . '/abc_config.yml';
        $paths[] = getcwd() . '/abc_config.ini';
        
        // Load all available configs and merge
        $configs = [];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                try {
                    $configs[] = ConfigLoader::loadFromFile($path);
                } catch (\Exception $e) {
                    // Log but continue
                    error_log("Failed to load config from $path: " . $e->getMessage());
                }
            }
        }
        
        // Merge all configurations
        $mergedConfig = empty($configs) ? [] : ConfigMerger::merge(...$configs);
        
        $instance = new self();
        if (!empty($mergedConfig)) {
            $instance->mergeFromArray($mergedConfig);
        }
        
        return $instance;
    }
    
    /**
     * Merge configuration from array
     * 
     * @param array $config Configuration array
     * @return void
     */
    public function mergeFromArray(array $config): void
    {
        // Validate configuration
        $errors = ConfigValidator::validate($config);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                "Configuration validation failed:\n  - " . implode("\n  - ", $errors)
            );
        }
        
        // Merge processing section
        if (isset($config['processing'])) {
            if (isset($config['processing']['voice_output_style'])) {
                $this->voiceOutputStyle = $config['processing']['voice_output_style'];
            }
            if (isset($config['processing']['interleave_bars'])) {
                $this->interleaveBars = $config['processing']['interleave_bars'];
            }
            if (isset($config['processing']['bars_per_line'])) {
                $this->barsPerLine = $config['processing']['bars_per_line'];
            }
            if (isset($config['processing']['join_bars_with_backslash'])) {
                $this->joinBarsWithBackslash = $config['processing']['join_bars_with_backslash'];
            }
            if (isset($config['processing']['tune_number_width'])) {
                $this->tuneNumberWidth = $config['processing']['tune_number_width'];
            }
        }
        
        // Merge transpose section
        if (isset($config['transpose'])) {
            if (isset($config['transpose']['mode'])) {
                $this->transposeMode = $config['transpose']['mode'];
            }
            if (isset($config['transpose']['overrides'])) {
                $this->transposeOverrides = array_merge(
                    $this->transposeOverrides,
                    $config['transpose']['overrides']
                );
            }
        }
        
        // Merge voice_ordering section
        if (isset($config['voice_ordering'])) {
            if (isset($config['voice_ordering']['mode'])) {
                $this->voiceOrderingMode = $config['voice_ordering']['mode'];
            }
            if (isset($config['voice_ordering']['custom_order'])) {
                $this->customVoiceOrder = $config['voice_ordering']['custom_order'];
            }
        }
        
        // Merge canntaireachd section
        if (isset($config['canntaireachd'])) {
            if (isset($config['canntaireachd']['convert'])) {
                $this->convertCanntaireachd = $config['canntaireachd']['convert'];
            }
            if (isset($config['canntaireachd']['generate_diff'])) {
                $this->generateCanntDiff = $config['canntaireachd']['generate_diff'];
            }
        }
        
        // Merge output section
        if (isset($config['output'])) {
            if (isset($config['output']['output_file'])) {
                $this->outputFile = $config['output']['output_file'];
            }
            if (isset($config['output']['error_file'])) {
                $this->errorFile = $config['output']['error_file'];
            }
            if (isset($config['output']['cannt_diff_file'])) {
                $this->canntDiffFile = $config['output']['cannt_diff_file'];
            }
        }
        
        // Merge database section
        if (isset($config['database'])) {
            if (isset($config['database']['use_midi_defaults'])) {
                $this->useMidiDefaults = $config['database']['use_midi_defaults'];
            }
            if (isset($config['database']['use_voice_order_defaults'])) {
                $this->useVoiceOrderDefaults = $config['database']['use_voice_order_defaults'];
            }
        }
        
        // Merge validation section
        if (isset($config['validation'])) {
            if (isset($config['validation']['timing_validation'])) {
                $this->timingValidation = $config['validation']['timing_validation'];
            }
            if (isset($config['validation']['strict_mode'])) {
                $this->strictMode = $config['validation']['strict_mode'];
            }
        }
    }
    
    /**
     * Save configuration to file
     * 
     * @param string $path Path to save configuration (.json, .yml, .ini)
     * @return bool True on success
     * @throws \InvalidArgumentException If format not supported for saving
     */
    public function saveToFile(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $config = $this->toArray();
        
        switch ($extension) {
            case 'json':
                $content = $this->toJSON();
                break;
            
            case 'yml':
            case 'yaml':
                throw new \InvalidArgumentException(
                    "YAML saving not yet implemented. Use JSON format instead."
                );
            
            case 'ini':
                throw new \InvalidArgumentException(
                    "INI saving not yet implemented. Use JSON format instead."
                );
            
            default:
                throw new \InvalidArgumentException(
                    "Unsupported configuration file format for saving: .$extension (use .json)"
                );
        }
        
        return file_put_contents($path, $content) !== false;
    }
    
    /**
     * Convert configuration to array
     * 
     * @return array Configuration as associative array
     */
    public function toArray(): array
    {
        return [
            'processing' => [
                'voice_output_style' => $this->voiceOutputStyle,
                'interleave_bars' => $this->interleaveBars,
                'bars_per_line' => $this->barsPerLine,
                'join_bars_with_backslash' => $this->joinBarsWithBackslash,
                'tune_number_width' => $this->tuneNumberWidth,
            ],
            'transpose' => [
                'mode' => $this->transposeMode,
                'overrides' => $this->transposeOverrides,
            ],
            'voice_ordering' => [
                'mode' => $this->voiceOrderingMode,
                'custom_order' => $this->customVoiceOrder,
            ],
            'canntaireachd' => [
                'convert' => $this->convertCanntaireachd,
                'generate_diff' => $this->generateCanntDiff,
            ],
            'output' => [
                'output_file' => $this->outputFile,
                'error_file' => $this->errorFile,
                'cannt_diff_file' => $this->canntDiffFile,
            ],
            'database' => [
                'use_midi_defaults' => $this->useMidiDefaults,
                'use_voice_order_defaults' => $this->useVoiceOrderDefaults,
            ],
            'validation' => [
                'timing_validation' => $this->timingValidation,
                'strict_mode' => $this->strictMode,
            ],
        ];
    }
    
    /**
     * Convert configuration to JSON string
     * 
     * @param bool $pretty Pretty-print JSON
     * @return string JSON string
     */
    public function toJSON(bool $pretty = true): string
    {
        $flags = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;
        return json_encode($this->toArray(), $flags);
    }
}
