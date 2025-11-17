# Database Migration Test Plan

## Overview
Tests for database schema updates adding transpose and octave columns to support Phase 4B transpose modes.

## Test Environment
- PHP 7.3+
- MySQL/MariaDB database
- PDO extension enabled

---

## Test 1: Migration Script Execution

### Prerequisites
- Database connection configured in `config/db_config.php`
- `sql/migrations/` directory exists
- Migration file `001_add_transpose_columns.sql` present

### Steps
1. Run migration script:
   ```bash
   php bin/run-migrations.php
   ```

### Expected Results
- ✅ Connects to database successfully
- ✅ Creates `migrations` tracking table
- ✅ Applies migration 001_add_transpose_columns.sql
- ✅ Records migration in tracking table
- ✅ Exits with status 0

### Validation
```sql
-- Check migrations table
SELECT * FROM migrations;

-- Check new columns exist
DESCRIBE abc_voice_names;

-- Should show:
-- - transpose INT DEFAULT 0
-- - octave INT DEFAULT 0
-- - idx_voice_name INDEX
```

---

## Test 2: Schema Column Types

### Steps
```sql
DESCRIBE abc_voice_names;
```

### Expected Results
```
Field         Type         Null    Key     Default
id            INT          NO      PRI     NULL
voice_name    VARCHAR(64)  NO      MUL     NULL
name          VARCHAR(64)  NO              NULL
sname         VARCHAR(64)  NO              NULL
transpose     INT          YES             0
octave        INT          YES             0
```

---

## Test 3: Default Transpose Values

### Steps
```sql
SELECT voice_name, name, transpose, octave 
FROM abc_voice_names 
ORDER BY voice_name;
```

### Expected Results

| voice_name    | name          | transpose | octave |
|---------------|---------------|-----------|--------|
| AltoSax       | Alto Sax      | 9         | 0      |
| Bagpipes      | Bagpipes      | 0         | 0      |
| BaritoneSax   | Baritone Sax  | 9         | 0      |
| Clarinet      | Clarinet      | 2         | 0      |
| FrenchHorn    | French Horn   | 7         | 0      |
| Piano         | Piano         | 0         | 0      |
| Trumpet       | Trumpet       | 2         | 0      |
| Violin        | Violin        | 0         | 0      |

Verify:
- ✅ Bb instruments (Trumpet, Clarinet, TenorSax) = 2
- ✅ Eb instruments (AltoSax, BaritoneSax) = 9
- ✅ F instruments (FrenchHorn, EnglishHorn) = 7
- ✅ Concert pitch instruments = 0

---

## Test 4: Insert New Voice with Transpose

### Steps
```sql
INSERT INTO abc_voice_names 
(voice_name, name, sname, transpose, octave) 
VALUES ('Cornet', 'Cornet', 'Cnt', 2, 0);

SELECT * FROM abc_voice_names WHERE voice_name = 'Cornet';
```

### Expected Results
- ✅ Insert succeeds
- ✅ Transpose defaults to 2 (Bb instrument)
- ✅ Octave defaults to 0

---

## Test 5: Update Transpose Value

### Steps
```sql
UPDATE abc_voice_names 
SET transpose = 5 
WHERE voice_name = 'Piano';

SELECT voice_name, transpose FROM abc_voice_names 
WHERE voice_name = 'Piano';
```

### Expected Results
- ✅ Update succeeds
- ✅ Piano transpose now equals 5

---

## Test 6: Index Performance

### Steps
```sql
EXPLAIN SELECT * FROM abc_voice_names 
WHERE voice_name = 'Trumpet';
```

### Expected Results
- ✅ Uses `idx_voice_name` index
- ✅ `type` shows "ref" or "const"
- ✅ `key` shows "idx_voice_name"

---

## Test 7: Rollback Migration (if needed)

### Steps
```sql
-- Remove columns
ALTER TABLE abc_voice_names DROP COLUMN transpose;
ALTER TABLE abc_voice_names DROP COLUMN octave;
ALTER TABLE abc_voice_names DROP INDEX idx_voice_name;

-- Remove migration record
DELETE FROM migrations WHERE migration_name = '001_add_transpose_columns.sql';
```

### Expected Results
- ✅ Columns removed
- ✅ Index removed
- ✅ Migration record deleted
- ✅ Can re-run migration successfully

---

## Test 8: Re-run Migration (Idempotent)

### Steps
1. Run migration again:
   ```bash
   php bin/run-migrations.php
   ```

### Expected Results
- ✅ Script detects migration already applied
- ✅ Skips migration
- ✅ No errors
- ✅ Message: "No migrations to apply"

---

## Test 9: Multiple Migrations

### Steps
1. Create second migration file:
   ```bash
   echo "-- Test migration" > sql/migrations/002_test.sql
   ```

2. Run migrations:
   ```bash
   php bin/run-migrations.php
   ```

### Expected Results
- ✅ Only applies 002_test.sql (001 already applied)
- ✅ Both migrations recorded in table
- ✅ Order preserved (001, then 002)

---

## Test 10: Integration with WordPress

### Steps
1. Access WordPress admin
2. Navigate to: ABC Canntaireachd → Transpose Settings

### Expected Results
- ✅ Page loads without errors
- ✅ Shows all voices from database
- ✅ Displays current transpose values
- ✅ Transpose mode selector visible
- ✅ Per-voice override inputs visible

---

## Automation Script

Create `test_db_migration.php`:

```php
<?php
require_once __DIR__ . '/config/db_config.php';

$tests_passed = 0;
$tests_failed = 0;

// Test 1: Check columns exist
$pdo = new PDO($dsn, $db_user, $db_pass);
$result = $pdo->query("DESCRIBE abc_voice_names");
$columns = $result->fetchAll(PDO::FETCH_COLUMN);

if (in_array('transpose', $columns) && in_array('octave', $columns)) {
    echo "✓ Test 1: Columns exist\n";
    $tests_passed++;
} else {
    echo "✗ Test 1: Columns missing\n";
    $tests_failed++;
}

// Test 2: Check transpose values
$stmt = $pdo->query("SELECT COUNT(*) FROM abc_voice_names WHERE transpose = 2");
$bb_count = $stmt->fetchColumn();

if ($bb_count >= 3) {  // At least Trumpet, Clarinet, TenorSax
    echo "✓ Test 2: Transpose values set\n";
    $tests_passed++;
} else {
    echo "✗ Test 2: Transpose values incorrect\n";
    $tests_failed++;
}

echo "\nResults: $tests_passed passed, $tests_failed failed\n";
exit($tests_failed > 0 ? 1 : 0);
```

---

## Success Criteria

All tests must pass:
- ✅ Migration script runs without errors
- ✅ Columns added with correct types
- ✅ Default values set correctly
- ✅ Index created for performance
- ✅ Transpose values match instrument types
- ✅ Can insert/update transpose values
- ✅ WordPress UI displays data correctly
- ✅ Migration is idempotent (can run multiple times safely)

---

## Troubleshooting

### Error: "Table 'abc_voice_names' doesn't exist"
**Solution**: Run base schema first:
```bash
mysql -u user -p database < sql/abc_voice_names_schema.sql
```

### Error: "Column 'transpose' already exists"
**Solution**: Migration was already applied. Check migrations table:
```sql
SELECT * FROM migrations;
```

### Error: "Access denied for user"
**Solution**: Check database credentials in `config/db_config.php`

---

## Rollback Plan

If migration causes issues:

1. **Quick rollback**:
   ```sql
   ALTER TABLE abc_voice_names DROP COLUMN transpose;
   ALTER TABLE abc_voice_names DROP COLUMN octave;
   DELETE FROM migrations WHERE migration_name = '001_add_transpose_columns.sql';
   ```

2. **Full schema restore**:
   ```bash
   mysql -u user -p database < sql/abc_voice_names_schema.sql.backup
   ```

3. **Verify rollback**:
   ```sql
   DESCRIBE abc_voice_names;
   ```

---

## Post-Migration Verification

Run this query to verify all expected voices have correct transpose values:

```sql
SELECT 
    voice_name,
    name,
    transpose,
    CASE 
        WHEN transpose = 0 THEN 'Concert Pitch'
        WHEN transpose = 2 THEN 'Bb Instrument'
        WHEN transpose = 7 THEN 'F Instrument'
        WHEN transpose = 9 THEN 'Eb Instrument'
        ELSE 'Custom'
    END as instrument_type
FROM abc_voice_names
ORDER BY transpose, name;
```

Expected output shows instruments grouped by transpose value.
