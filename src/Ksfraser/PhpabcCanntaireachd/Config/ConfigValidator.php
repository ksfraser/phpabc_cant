<?php
namespace Ksfraser\PhpabcCanntaireachd\Config;

/**
 * Configuration validator
 * Validates configuration structure and values
 */
class ConfigValidator
{
    /**
     * Validate complete configuration
     * 
     * @param array $config Configuration to validate
     * @return array Array of validation errors (empty if valid)
     */
    public static function validate(array $config): array
    {
        $errors = [];

        // Validate processing section
        if (isset($config['processing'])) {
            $errors = array_merge($errors, self::validateProcessing($config['processing']));
        }

        // Validate transpose section
        if (isset($config['transpose'])) {
            $errors = array_merge($errors, self::validateTranspose($config['transpose']));
        }

        // Validate voice_ordering section
        if (isset($config['voice_ordering'])) {
            $errors = array_merge($errors, self::validateVoiceOrdering($config['voice_ordering']));
        }

        // Validate canntaireachd section
        if (isset($config['canntaireachd'])) {
            $errors = array_merge($errors, self::validateCanntaireachd($config['canntaireachd']));
        }

        // Validate output section
        if (isset($config['output'])) {
            $errors = array_merge($errors, self::validateOutput($config['output']));
        }

        // Validate database section
        if (isset($config['database'])) {
            $errors = array_merge($errors, self::validateDatabase($config['database']));
        }

        // Validate validation section
        if (isset($config['validation'])) {
            $errors = array_merge($errors, self::validateValidation($config['validation']));
        }

        return $errors;
    }

    /**
     * Validate processing configuration
     */
    private static function validateProcessing(array $processing): array
    {
        $errors = [];

        if (isset($processing['voice_output_style'])) {
            if (!in_array($processing['voice_output_style'], ['grouped', 'interleaved'])) {
                $errors[] = "processing.voice_output_style must be 'grouped' or 'interleaved'";
            }
        }

        if (isset($processing['interleave_bars'])) {
            if (!is_int($processing['interleave_bars']) || $processing['interleave_bars'] < 1) {
                $errors[] = "processing.interleave_bars must be a positive integer";
            }
        }

        if (isset($processing['bars_per_line'])) {
            if (!is_int($processing['bars_per_line']) || $processing['bars_per_line'] < 1 || $processing['bars_per_line'] > 20) {
                $errors[] = "processing.bars_per_line must be between 1 and 20";
            }
        }

        if (isset($processing['join_bars_with_backslash'])) {
            if (!is_bool($processing['join_bars_with_backslash'])) {
                $errors[] = "processing.join_bars_with_backslash must be a boolean";
            }
        }

        if (isset($processing['tune_number_width'])) {
            if (!is_int($processing['tune_number_width']) || $processing['tune_number_width'] < 1 || $processing['tune_number_width'] > 10) {
                $errors[] = "processing.tune_number_width must be between 1 and 10";
            }
        }

        return $errors;
    }

    /**
     * Validate transpose configuration
     */
    private static function validateTranspose(array $transpose): array
    {
        $errors = [];

        if (isset($transpose['mode'])) {
            if (!in_array($transpose['mode'], ['midi', 'bagpipe', 'orchestral'])) {
                $errors[] = "transpose.mode must be 'midi', 'bagpipe', or 'orchestral'";
            }
        }

        if (isset($transpose['overrides'])) {
            if (!is_array($transpose['overrides'])) {
                $errors[] = "transpose.overrides must be an array";
            } else {
                foreach ($transpose['overrides'] as $voice => $value) {
                    if (!is_int($value) || $value < -12 || $value > 12) {
                        $errors[] = "transpose.overrides.$voice must be an integer between -12 and 12";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate voice_ordering configuration
     */
    private static function validateVoiceOrdering(array $voiceOrdering): array
    {
        $errors = [];

        if (isset($voiceOrdering['mode'])) {
            if (!in_array($voiceOrdering['mode'], ['source', 'orchestral', 'custom'])) {
                $errors[] = "voice_ordering.mode must be 'source', 'orchestral', or 'custom'";
            }
        }

        if (isset($voiceOrdering['custom_order'])) {
            if (!is_array($voiceOrdering['custom_order'])) {
                $errors[] = "voice_ordering.custom_order must be an array";
            }
        }

        return $errors;
    }

    /**
     * Validate canntaireachd configuration
     */
    private static function validateCanntaireachd(array $canntaireachd): array
    {
        $errors = [];

        if (isset($canntaireachd['convert'])) {
            if (!is_bool($canntaireachd['convert'])) {
                $errors[] = "canntaireachd.convert must be a boolean";
            }
        }

        if (isset($canntaireachd['generate_diff'])) {
            if (!is_bool($canntaireachd['generate_diff'])) {
                $errors[] = "canntaireachd.generate_diff must be a boolean";
            }
        }

        return $errors;
    }

    /**
     * Validate output configuration
     */
    private static function validateOutput(array $output): array
    {
        $errors = [];

        // File paths can be null or strings
        foreach (['output_file', 'error_file', 'cannt_diff_file'] as $key) {
            if (isset($output[$key]) && $output[$key] !== null && !is_string($output[$key])) {
                $errors[] = "output.$key must be a string or null";
            }
        }

        return $errors;
    }

    /**
     * Validate database configuration
     */
    private static function validateDatabase(array $database): array
    {
        $errors = [];

        if (isset($database['use_midi_defaults'])) {
            if (!is_bool($database['use_midi_defaults'])) {
                $errors[] = "database.use_midi_defaults must be a boolean";
            }
        }

        if (isset($database['use_voice_order_defaults'])) {
            if (!is_bool($database['use_voice_order_defaults'])) {
                $errors[] = "database.use_voice_order_defaults must be a boolean";
            }
        }

        return $errors;
    }

    /**
     * Validate validation configuration
     */
    private static function validateValidation(array $validation): array
    {
        $errors = [];

        if (isset($validation['timing_validation'])) {
            if (!is_bool($validation['timing_validation'])) {
                $errors[] = "validation.timing_validation must be a boolean";
            }
        }

        if (isset($validation['strict_mode'])) {
            if (!is_bool($validation['strict_mode'])) {
                $errors[] = "validation.strict_mode must be a boolean";
            }
        }

        return $errors;
    }
}
