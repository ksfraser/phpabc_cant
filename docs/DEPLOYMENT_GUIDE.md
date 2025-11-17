# Deployment Guide

**ABC Canntaireachd Converter - Production Deployment**  
**Version**: 2.0  
**Date**: 2025-11-17  
**Status**: Ready for Production

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [System Requirements](#system-requirements)
3. [Deployment Steps](#deployment-steps)
4. [Database Migration](#database-migration)
5. [WordPress Plugin Activation](#wordpress-plugin-activation)
6. [Post-Deployment Verification](#post-deployment-verification)
7. [Rollback Procedures](#rollback-procedures)
8. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Checklist

### Code Quality ✅
- [x] All code reviewed and approved
- [x] 37/37 integration tests passing (100%)
- [x] 28/28 transpose tests passing (100%)
- [x] 6/6 voice ordering tests passing (100%)
- [x] 3/3 pipeline tests passing (100%)
- [x] 5/5 configuration tests passing (100%)
- [x] Security audit complete
- [x] Performance acceptable

### Documentation ✅
- [x] README.md updated with Phase 4 features
- [x] CLI User Guide created (500+ lines)
- [x] Transpose User Guide created (400+ lines)
- [x] WordPress UI test plan (34 test cases)
- [x] Database migration test plan (30 test cases)
- [x] Phase completion certificates

### Testing ✅
- [x] Unit tests passing
- [x] Integration tests passing
- [x] Configuration tests passing
- [x] Regression tests passing
- [x] Real-world test files validated (test-Suo.abc)

### Repository ✅
- [x] Code cleanup complete (55 files removed)
- [x] No debug or temporary files
- [x] Clean git history
- [x] All commits pushed to repository

---

## System Requirements

### Server Requirements
- **PHP**: 7.3 or higher (PHP 8.x recommended)
- **MySQL/MariaDB**: 5.7+ or 10.2+
- **WordPress**: 5.0 or higher (if using WordPress features)
- **Composer**: Latest version for dependency management

### PHP Extensions Required
- `pdo_mysql` - Database connectivity
- `mbstring` - Multi-byte string handling
- `json` - JSON parsing
- `xml` - XML parsing (for some ABC features)

### Server Configuration
- **Memory Limit**: 128M minimum (256M recommended)
- **Execution Time**: 60 seconds minimum (for large files)
- **Upload Size**: 10M minimum (for ABC file uploads)

### File Permissions
- `src/logs/` - Writable (755 or 775)
- `config/` - Readable (644)
- `vendor/` - Readable (644)
- `bin/` - Executable (755)

---

## Deployment Steps

### Step 1: Backup Current System

```bash
# Backup files
tar -czf phpabc_backup_$(date +%Y%m%d).tar.gz \
  --exclude='vendor' \
  --exclude='.git' \
  /path/to/phpabc_cant

# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Step 2: Update Codebase

```bash
# Navigate to project directory
cd /path/to/phpabc_cant

# Pull latest code
git pull origin main

# Or deploy from archive
# tar -xzf phpabc_cant_v2.0.tar.gz

# Install/update dependencies
composer install --no-dev --optimize-autoloader
```

### Step 3: Configuration

```bash
# Copy configuration template (if first install)
cp config/db_config.php.example config/db_config.php

# Edit database configuration
nano config/db_config.php
```

**config/db_config.php**:
```php
<?php
return [
    'host' => 'localhost',
    'database' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
];
```

### Step 4: Set Permissions

```bash
# Set directory permissions
chmod -R 755 bin/
chmod -R 755 src/
chmod -R 775 src/logs/
chmod 644 config/db_config.php

# Set ownership (if needed)
chown -R www-data:www-data /path/to/phpabc_cant
```

---

## Database Migration

### Automatic Migration (Recommended)

```bash
# Check migration status
php bin/run-migrations.php --status

# Run pending migrations
php bin/run-migrations.php

# Verify successful migration
php bin/run-migrations.php --status
```

**Expected Output**:
```
=== Database Migration Runner ===

Connected to database: your_database

Checking pending migrations...
Found 1 pending migration(s)

Applying migration: 001_add_transpose_columns.sql
  - Adding transpose and octave columns...
  - Populating instrument data (29 rows)...
  - Adding index on voice_name...
  ✓ Migration applied successfully

All migrations completed!
Total applied: 1
```

### Manual Migration (If Needed)

```bash
# Load SQL file directly
mysql -u username -p database_name < sql/migrations/001_add_transpose_columns.sql
```

### Verify Migration

```sql
-- Check table structure
DESCRIBE abc_voice_names;

-- Should show:
-- | transpose | int | YES | | NULL | |
-- | octave | int | YES | | NULL | |

-- Check data
SELECT voice_name, transpose, octave 
FROM abc_voice_names 
WHERE transpose IS NOT NULL 
LIMIT 10;

-- Should return 29 rows with transpose values
```

---

## WordPress Plugin Activation

### If Using WordPress Features

#### Step 1: Copy Plugin Files

```bash
# Copy plugin to WordPress plugins directory
cp -r wp-abc-canntaireachd /path/to/wordpress/wp-content/plugins/

# Set permissions
chown -R www-data:www-data /path/to/wordpress/wp-content/plugins/wp-abc-canntaireachd
chmod -R 755 /path/to/wordpress/wp-content/plugins/wp-abc-canntaireachd
```

#### Step 2: Activate Plugin

1. Log in to WordPress admin panel
2. Navigate to **Plugins → Installed Plugins**
3. Find "ABC Canntaireachd Converter"
4. Click **Activate**

#### Step 3: Configure Settings

##### Transpose Settings
1. Navigate to **Settings → Transpose Settings**
2. Select default transpose mode:
   - **MIDI Mode**: All instruments at concert pitch (recommended for MIDI imports)
   - **Bagpipe Mode**: Bagpipes at written pitch, others +2 semitones
   - **Orchestral Mode**: Instrument-specific transposition
3. Configure per-voice overrides if needed
4. Click **Save Changes**

##### Voice Order Settings
1. Navigate to **Settings → Voice Order Settings**
2. Select voice ordering mode:
   - **Source**: Preserve original order from ABC file
   - **Orchestral**: Standard orchestral sections order
   - **Custom**: Define your own ordering
3. If custom, enter comma-separated voice list
4. Click **Save Changes**

#### Step 4: Verify WordPress UI

- [ ] Transpose Settings page loads correctly
- [ ] Voice Order Settings page loads correctly
- [ ] Settings save and persist
- [ ] Database connection working
- [ ] No PHP errors in WordPress debug log

---

## Post-Deployment Verification

### 1. CLI Tools Verification

```bash
# Test main converter
php bin/abc-cannt-cli.php --help
php bin/abc-cannt-cli.php test-simple.abc test-output.abc

# Test transpose modes
php bin/abc-cannt-cli.php --transpose-mode=orchestral test-simple.abc test-output.abc

# Test voice ordering
php bin/abc-voice-order-pass-cli.php --mode=orchestral test-simple.abc test-output.abc

# Test configuration system
php bin/abc-cannt-cli.php --config=config/examples/transpose_test.json test-simple.abc test-output.abc
```

### 2. Integration Tests

```bash
# Run master test suite
php test_transpose_master.php

# Expected: ALL 28 TESTS PASSING ✅

# Run pipeline tests
php test_pipeline_refactor.php

# Expected: ALL 3 TESTS PASSING ✅

# Run voice ordering tests
php test_voice_ordering_integration.php

# Expected: ALL 6 TESTS PASSING ✅

# Run configuration tests
php test_config_system.php

# Expected: ALL 5 TESTS PASSING ✅
```

### 3. Sample File Processing

```bash
# Process real-world file
php bin/abc-cannt-cli.php \
  --transpose-mode=orchestral \
  --voice-order=orchestral \
  test-Suo.abc \
  output-Suo.abc

# Verify output
head -n 50 output-Suo.abc
```

**Check for**:
- ✓ Bagpipes voice created
- ✓ Melody bars copied to Bagpipes
- ✓ Canntaireachd ONLY under Bagpipes voice
- ✓ NO canntaireachd under Melody voice
- ✓ Voices ordered correctly
- ✓ Transpose values applied

### 4. Database Verification

```sql
-- Check migrations applied
SELECT * FROM migration_tracking ORDER BY applied_at DESC;

-- Check transpose data
SELECT voice_name, transpose, octave 
FROM abc_voice_names 
WHERE transpose IS NOT NULL;

-- Should return 29 instruments

-- Check voice order defaults
SELECT * FROM abc_voice_order_defaults 
ORDER BY order_position;
```

### 5. WordPress Verification (If Applicable)

- [ ] Admin pages accessible
- [ ] Settings save correctly
- [ ] Database queries working
- [ ] No JavaScript errors in browser console
- [ ] No PHP errors in error log

### 6. Performance Check

```bash
# Time large file processing
time php bin/abc-cannt-cli.php large-file.abc output.abc

# Should complete in reasonable time (< 30 seconds for typical files)

# Check memory usage
php -d memory_limit=128M bin/abc-cannt-cli.php large-file.abc output.abc

# Should not exceed memory limit
```

---

## Rollback Procedures

### Code Rollback

```bash
# Restore from backup
cd /path/to
tar -xzf phpabc_backup_YYYYMMDD.tar.gz

# Or use git
cd phpabc_cant
git checkout <previous-commit-hash>
composer install
```

### Database Rollback

#### Method 1: Using Migration System

```bash
# Rollback last migration
php bin/run-migrations.php --rollback

# Verify rollback
php bin/run-migrations.php --status
```

#### Method 2: Manual Rollback

```sql
-- Remove transpose/octave columns
ALTER TABLE abc_voice_names 
  DROP COLUMN transpose,
  DROP COLUMN octave,
  DROP INDEX idx_voice_name;

-- Delete migration tracking
DELETE FROM migration_tracking 
WHERE migration_name = '001_add_transpose_columns.sql';
```

#### Method 3: Full Database Restore

```bash
# Restore from backup
mysql -u username -p database_name < backup_YYYYMMDD.sql
```

### WordPress Rollback

1. Deactivate plugin via WordPress admin
2. Delete plugin files
3. Restore previous plugin version if needed

---

## Troubleshooting

### Issue 1: Migration Fails

**Symptoms**: Migration script reports errors

**Solutions**:
```bash
# Check database connection
php bin/run-migrations.php --status

# Check database user permissions
mysql -u username -p
SHOW GRANTS FOR 'username'@'localhost';

# User needs: CREATE, ALTER, INSERT, UPDATE, SELECT, INDEX

# Try dry run
php bin/run-migrations.php --dry-run

# Manually apply migration
mysql -u username -p database_name < sql/migrations/001_add_transpose_columns.sql
```

### Issue 2: WordPress Plugin Not Activating

**Symptoms**: Plugin activation fails or causes errors

**Solutions**:
1. Check PHP version: `php -v` (must be 7.3+)
2. Check file permissions: `ls -la wp-content/plugins/wp-abc-canntaireachd`
3. Enable WordPress debug mode:
   ```php
   // wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
4. Check `wp-content/debug.log` for errors

### Issue 3: Canntaireachd Not Generating

**Symptoms**: Output has no w: lines with canntaireachd

**Solutions**:
1. Check voice name (must be Bagpipes/Pipes/P)
2. Verify token dictionary loaded:
   ```bash
   php bin/abc-load-schemas-cli.php --schema=abc_dict
   ```
3. Check file has music notes (not just headers)
4. Run with verbose mode:
   ```bash
   php bin/abc-cannt-cli.php --verbose input.abc output.abc
   ```

### Issue 4: Transpose Not Working

**Symptoms**: Instruments not transposing correctly

**Solutions**:
1. Verify instrument in database:
   ```sql
   SELECT * FROM abc_voice_names WHERE voice_name = 'YourInstrument';
   ```
2. Check mode setting:
   ```bash
   php bin/abc-cannt-cli.php --transpose-mode=orchestral input.abc output.abc
   ```
3. Use override for unlisted instruments:
   ```bash
   php bin/abc-cannt-cli.php --transpose-override="YourInstrument:2" input.abc output.abc
   ```

### Issue 5: Performance Issues

**Symptoms**: Processing very slow or timeout

**Solutions**:
1. Increase PHP memory limit:
   ```bash
   php -d memory_limit=512M bin/abc-cannt-cli.php input.abc output.abc
   ```
2. Increase execution time:
   ```bash
   php -d max_execution_time=300 bin/abc-cannt-cli.php input.abc output.abc
   ```
3. Process files in batches
4. Check for memory leaks in custom code

### Issue 6: Configuration File Not Loading

**Symptoms**: Config file options not applied

**Solutions**:
1. Verify file exists and readable:
   ```bash
   ls -la myconfig.json
   cat myconfig.json
   ```
2. Validate JSON syntax:
   ```bash
   php -r "json_decode(file_get_contents('myconfig.json')); echo json_last_error_msg();"
   ```
3. Check file path (use absolute path):
   ```bash
   php bin/abc-cannt-cli.php --config=/full/path/to/config.json input.abc output.abc
   ```

---

## Support & Maintenance

### Regular Maintenance Tasks

1. **Weekly**: Check log files for errors (`src/logs/`)
2. **Monthly**: Review database performance, optimize if needed
3. **Quarterly**: Review and update instrument transpose values as needed
4. **Annually**: Review security updates for PHP and dependencies

### Monitoring

- Monitor PHP error logs
- Monitor WordPress error logs (if applicable)
- Monitor database query performance
- Monitor disk space usage
- Monitor processing times for large files

### Updates

```bash
# Check for updates
cd /path/to/phpabc_cant
git fetch origin
git log --oneline main..origin/main

# Apply updates
git pull origin main
composer install
php bin/run-migrations.php
```

---

## Version Information

**Current Version**: 2.0  
**Release Date**: 2025-11-17  
**Compatibility**: PHP 7.3+, WordPress 5.0+, MySQL 5.7+

### What's New in 2.0

- ✅ Voice ordering system (3 modes)
- ✅ Transpose modes (3 modes, 80+ instruments)
- ✅ Configuration file support
- ✅ WordPress admin UI
- ✅ Database-driven settings
- ✅ 37/37 integration tests passing
- ✅ Comprehensive documentation

---

## Contact & Resources

- **Documentation**: `docs/` directory
- **CLI Guide**: `docs/CLI_User_Guide.md`
- **Transpose Guide**: `docs/Transpose_User_Guide.md`
- **Test Plans**: `docs/DB_Migration_Test_Plan.md`, `docs/WordPress_UI_Test_Plan.md`
- **Repository**: https://github.com/ksfraser/phpabc_cant

---

*Deployment Guide - Version 2.0 - November 17, 2025*
