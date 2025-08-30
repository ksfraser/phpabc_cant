<?php
namespace Ksfraser\PhpabcCanntaireachd;

class HeaderDefaults {
    public static function getDefaults(): array {
        $defaults = [];
        $loadedFromDb = false;
        // First, try to load from database table abc_header_field_defaults if possible
        try {
            if (class_exists('Ksfraser\\Database\\DbManager')) {
                $rows = \Ksfraser\Database\DbManager::fetchAll('SELECT field_name, field_value FROM abc_header_field_defaults');
                if (is_array($rows) && count($rows) > 0) {
                    $loadedFromDb = true;
                    foreach ($rows as $r) {
                        if (isset($r['field_name'])) {
                            $defaults[$r['field_name']] = $r['field_value'] ?? '';
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // DB not available or query failed; fall back to file-based defaults below
        }

        // If no results from DB, attempt to parse the SQL schema file for seeded values
        if (empty($defaults)) {
            $sqlFile = __DIR__ . '/../../../sql/abc_header_field_defaults_schema.sql';
            if (file_exists($sqlFile)) {
                $schema = file_get_contents($sqlFile);
                if (preg_match_all("/'([A-Z])',\s*'([^']*)'/", $schema, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $defaults[$m[1]] = $m[2];
                    }
                }
            }
        }

        // Overlay config file defaults (project config/header_defaults.txt)
        // If values were loaded from the DB, treat DB as authoritative — do not overwrite.
        // If values came only from the SQL seed (or nothing), allow the config file to override the seeded values.
        $configFile = __DIR__ . '/../../../config/header_defaults.txt';
        if (file_exists($configFile)) {
            $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, ';') === 0) continue;
                if (preg_match('/^([A-Z]):\s*(.*)$/', $line, $m)) {
                    $key = $m[1];
                    $val = $m[2];
                    if ($loadedFromDb) {
                        // do not overwrite DB-provided values; only set missing ones
                        if (!isset($defaults[$key]) || $defaults[$key] === '') {
                            $defaults[$key] = $val;
                        }
                    } else {
                        // no DB values present; config should override seed values
                        $defaults[$key] = $val;
                    }
                }
            }
        }
        return $defaults;
    }

    /**
     * Migrate seeded SQL defaults into the database table.
     * Returns array of migrated field => value for reporting.
     */
    public static function migrateFromSqlToDb(): array {
        $migrated = [];
        try {
            if (!class_exists('Ksfraser\\Database\\DbManager')) {
                return $migrated;
            }
            // Read SQL file for INSERT values
            $sqlFile = __DIR__ . '/../../../sql/abc_header_field_defaults_schema.sql';
            if (!file_exists($sqlFile)) return $migrated;
            $schema = file_get_contents($sqlFile);
            if (!preg_match_all("/\('([A-Z])',\s*'([^']*)'\)/", $schema, $matches, PREG_SET_ORDER)) {
                return $migrated;
            }
            // Use a transaction where possible. Do NOT delete existing values — only insert missing ones.
            try {
                \Ksfraser\Database\DbManager::execute('BEGIN');
            } catch (\Throwable $e) {
                // ignore if DB driver doesn't support transactions
            }

            foreach ($matches as $m) {
                $field = $m[1];
                $value = $m[2];
                try {
                    // Check if a value already exists for this field
                    $existing = \Ksfraser\Database\DbManager::fetchOne(
                        'SELECT field_value FROM abc_header_field_defaults WHERE field_name = ?',
                        [$field]
                    );
                    $has = (is_array($existing) && count($existing) > 0);
                    if (!$has) {
                        \Ksfraser\Database\DbManager::execute(
                            'INSERT INTO abc_header_field_defaults (field_name, field_value) VALUES (?, ?)',
                            [$field, $value]
                        );
                        $migrated[$field] = $value;
                    }
                } catch (\Throwable $e) {
                    // Possibly the table doesn't exist. Try to create it and insert.
                    try {
                        \Ksfraser\Database\DbManager::execute('CREATE TEMPORARY TABLE IF NOT EXISTS abc_header_field_defaults (field_name VARCHAR(10), field_value TEXT)');
                        \Ksfraser\Database\DbManager::execute(
                            'INSERT INTO abc_header_field_defaults (field_name, field_value) VALUES (?, ?)',
                            [$field, $value]
                        );
                        $migrated[$field] = $value;
                    } catch (\Throwable $e2) {
                        // ignore individual failures
                    }
                }
            }

            try {
                \Ksfraser\Database\DbManager::execute('COMMIT');
            } catch (\Throwable $e) {
                // ignore
            }
        } catch (\Throwable $e) {
            // if DB not available or queries fail, return whatever migrated
        }
        return $migrated;
    }

    /**
     * Basic validation for a defaults array. Returns list of error messages (empty if ok).
     */
    public static function validateDefaults(array $defaults): array {
        $errors = [];
        // Required header keys
        $required = ['K','Q','L','M','R','B','O','Z'];
        foreach ($required as $k) {
            if (!array_key_exists($k, $defaults)) {
                $errors[] = "Missing header key: $k";
            }
        }
        // Basic sanity checks
        if (isset($defaults['Q']) && !preg_match('/^\d+\/\d+=\d+$/', $defaults['Q'])) {
            $errors[] = "Q header looks invalid: " . ($defaults['Q'] ?? '');
        }
        if (isset($defaults['L']) && !preg_match('/^\d+\/\d+$/', $defaults['L'])) {
            $errors[] = "L header looks invalid: " . ($defaults['L'] ?? '');
        }
        return $errors;
    }
}
