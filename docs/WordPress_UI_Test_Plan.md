# WordPress UI Test Plan - Phase 4 Features

## Overview
Tests for WordPress admin interfaces for transpose modes and voice ordering (Phase 4A & 4B).

---

## Test Environment Setup

### Prerequisites
- WordPress 5.0+ installed
- ABC Canntaireachd plugin activated
- Database schema loaded with transpose/octave columns
- User with 'manage_options' capability

### Plugin Location
`wp-content/plugins/abc-canntaireachd/`

### Admin Pages
- Main: `wp-admin/admin.php?page=abc-canntaireachd`
- Transpose: `wp-admin/admin.php?page=abc-transpose-settings`
- Voice Order: `wp-admin/admin.php?page=abc-voice-order-settings`

---

## Transpose Settings UI Tests

### Test 1: Page Load
**Steps:**
1. Log in to WordPress admin
2. Navigate to: ABC Canntaireachd → Transpose Settings

**Expected Results:**
- ✅ Page loads without PHP errors
- ✅ Title shows "Transpose Settings"
- ✅ Three radio buttons visible (MIDI, Bagpipe, Orchestral)
- ✅ Voice override table displays
- ✅ All database voices listed
- ✅ Current transpose values shown

### Test 2: MIDI Mode Selection
**Steps:**
1. Select "MIDI Mode" radio button
2. Click "Save Transpose Settings"

**Expected Results:**
- ✅ Success message displays
- ✅ Option saved in database (`abc_transpose_mode` = 'midi')
- ✅ Page reloads with MIDI mode selected

**Verification:**
```php
get_option('abc_transpose_mode'); // Should return 'midi'
```

### Test 3: Bagpipe Mode Selection
**Steps:**
1. Select "Bagpipe Mode" radio button
2. Click "Save Transpose Settings"

**Expected Results:**
- ✅ Success message displays
- ✅ Option saved (`abc_transpose_mode` = 'bagpipe')
- ✅ Selection persists on reload

### Test 4: Orchestral Mode Selection
**Steps:**
1. Select "Orchestral Mode" radio button
2. Click "Save Transpose Settings"

**Expected Results:**
- ✅ Success message displays
- ✅ Option saved (`abc_transpose_mode` = 'orchestral')
- ✅ Selection persists on reload

### Test 5: Per-Voice Override - Single Voice
**Steps:**
1. Find "Piano" in the voice table
2. Enter "5" in the Override Transpose field
3. Click "Save Transpose Settings"

**Expected Results:**
- ✅ Success message displays
- ✅ Override saved in database
- ✅ Piano override shows "5" on reload

**Verification:**
```php
$overrides = get_option('abc_transpose_overrides');
// $overrides['Piano'] should equal 5
```

### Test 6: Per-Voice Override - Multiple Voices
**Steps:**
1. Set Piano override = 5
2. Set Trumpet override = 0
3. Set Violin override = -2
4. Click "Save Transpose Settings"

**Expected Results:**
- ✅ All three overrides saved
- ✅ Values persist on reload
- ✅ Other voices remain empty

### Test 7: Clear Override
**Steps:**
1. Set Piano override = 5
2. Save settings
3. Clear Piano override (delete value)
4. Save again

**Expected Results:**
- ✅ Piano override removed from saved options
- ✅ Field shows placeholder value on reload
- ✅ Default transpose value used

### Test 8: Update Database Defaults
**Steps:**
1. Set Trumpet override = 5
2. Check "Update database defaults"
3. Click "Save Transpose Settings"

**Expected Results:**
- ✅ Option saved
- ✅ Database updated
- ✅ Query shows new value:
```sql
SELECT transpose FROM abc_voice_names WHERE voice_name = 'Trumpet';
-- Should return 5
```

### Test 9: Input Validation - Valid Range
**Steps:**
1. Try entering transpose values: -12, -1, 0, 1, 12
2. Save settings

**Expected Results:**
- ✅ All values accepted (within -12 to +12 range)
- ✅ Values saved correctly

### Test 10: Input Validation - Invalid Values
**Steps:**
1. Try entering: 20, -20, "abc", empty
2. Check browser validation

**Expected Results:**
- ✅ Browser validates min/max constraints
- ✅ Non-numeric input rejected
- ✅ Empty input treated as "no override"

### Test 11: Reference Table Display
**Steps:**
1. Scroll to "Transpose Reference" section

**Expected Results:**
- ✅ Table shows four instrument types
- ✅ Concert Pitch = 0
- ✅ Bb = +2
- ✅ Eb = +9
- ✅ F = +7
- ✅ Example instruments listed for each type

### Test 12: Voice Table Formatting
**Steps:**
1. Review the per-voice override table

**Expected Results:**
- ✅ Columns: Voice Name, Current Transpose, Override, Description
- ✅ Voice names displayed clearly
- ✅ Current transpose values shown
- ✅ Input placeholders show current values
- ✅ Description shows instrument type (Bb, Eb, etc.)

---

## Voice Order Settings UI Tests

### Test 13: Voice Order Page Load
**Steps:**
1. Navigate to: ABC Canntaireachd → Voice Order

**Expected Results:**
- ✅ Page loads without errors
- ✅ Title shows "Voice Ordering Settings"
- ✅ Three radio buttons (Source, Orchestral, Custom)
- ✅ Custom order textarea visible
- ✅ Standard orchestral order displayed
- ✅ Available voices list shown

### Test 14: Source Order Mode
**Steps:**
1. Select "Source Order" radio button
2. Click "Save Voice Order Settings"

**Expected Results:**
- ✅ Success message displays
- ✅ Option saved (`abc_voice_order_mode` = 'source')
- ✅ Selection persists on reload

### Test 15: Orchestral Order Mode
**Steps:**
1. Select "Orchestral Order" radio button
2. Click "Save Voice Order Settings"

**Expected Results:**
- ✅ Success message displays
- ✅ Option saved (`abc_voice_order_mode` = 'orchestral')
- ✅ Selection persists on reload

### Test 16: Custom Order Mode
**Steps:**
1. Select "Custom Order" radio button
2. Click "Save Voice Order Settings"

**Expected Results:**
- ✅ Success message displays
- ✅ Option saved (`abc_voice_order_mode` = 'custom')
- ✅ Selection persists on reload

### Test 17: Custom Order Entry - Simple
**Steps:**
1. Select "Custom Order" mode
2. Enter in textarea:
   ```
   Piano
   Violin
   Trumpet
   ```
3. Click "Save Voice Order Settings"

**Expected Results:**
- ✅ Order saved as array
- ✅ Values persist on reload
- ✅ Each voice on separate line

**Verification:**
```php
$custom = get_option('abc_custom_voice_order');
// Should equal ['Piano', 'Violin', 'Trumpet']
```

### Test 18: Custom Order Entry - Full Orchestra
**Steps:**
1. Enter 20+ voice names in desired order
2. Save settings

**Expected Results:**
- ✅ All voices saved
- ✅ Order preserved
- ✅ No duplicates
- ✅ Whitespace trimmed

### Test 19: Custom Order - Empty Lines
**Steps:**
1. Enter:
   ```
   Piano
   
   Violin
   
   
   Trumpet
   ```
2. Save settings

**Expected Results:**
- ✅ Empty lines ignored
- ✅ Only three voices saved
- ✅ Order preserved

### Test 20: Standard Orchestral Order Display
**Steps:**
1. Scroll to "Standard Orchestral Order" section

**Expected Results:**
- ✅ Shows ordered list from database (if available)
- ✅ Or shows default hardcoded order
- ✅ Multi-column layout for readability
- ✅ Woodwinds first, then brass, percussion, etc.

### Test 21: Available Voices Display
**Steps:**
1. Scroll to "Available Voices" section

**Expected Results:**
- ✅ Shows all voices from database
- ✅ Displays full name and voice_name code
- ✅ Multi-column layout
- ✅ Helpful for reference when creating custom order

---

## Integration Tests

### Test 22: Settings Persistence
**Steps:**
1. Set transpose mode = orchestral
2. Add Piano override = 5
3. Set voice order = custom
4. Add custom order: Piano, Violin, Trumpet
5. Log out and log back in
6. Navigate to both settings pages

**Expected Results:**
- ✅ Transpose mode still orchestral
- ✅ Piano override still 5
- ✅ Voice order still custom
- ✅ Custom order preserved

### Test 23: Security - Nonce Verification
**Steps:**
1. Try to submit form without nonce
2. Or with invalid nonce

**Expected Results:**
- ✅ Request rejected
- ✅ "Security check failed" message
- ✅ No database changes

### Test 24: Security - Capability Check
**Steps:**
1. Log in as subscriber/contributor (not admin)
2. Try to access settings pages

**Expected Results:**
- ✅ Access denied
- ✅ WordPress permission error
- ✅ Cannot view or modify settings

### Test 25: XSS Protection
**Steps:**
1. Try entering: `<script>alert('xss')</script>` in custom order
2. Save and reload

**Expected Results:**
- ✅ Script tags escaped
- ✅ No JavaScript execution
- ✅ Text displayed safely

### Test 26: SQL Injection Protection
**Steps:**
1. Try entering: `Piano'; DROP TABLE abc_voice_names; --`
2. Save settings

**Expected Results:**
- ✅ Input sanitized
- ✅ No SQL injection
- ✅ Database intact

---

## Responsive Design Tests

### Test 27: Mobile View - Transpose Settings
**Steps:**
1. Open transpose settings on mobile device
2. Or resize browser to 375px width

**Expected Results:**
- ✅ Page readable on mobile
- ✅ Table adapts to screen
- ✅ No horizontal scrolling required
- ✅ Inputs remain functional

### Test 28: Tablet View - Voice Order
**Steps:**
1. Open voice order settings on tablet
2. Or resize to 768px width

**Expected Results:**
- ✅ Layout adapts appropriately
- ✅ Multi-column lists adjust
- ✅ Textarea remains usable

---

## Error Handling Tests

### Test 29: Database Connection Error
**Steps:**
1. Temporarily break database connection
2. Load transpose settings page

**Expected Results:**
- ✅ Graceful error handling
- ✅ User-friendly error message
- ✅ No PHP fatal errors
- ✅ "No voices found" message if query fails

### Test 30: Missing Database Table
**Steps:**
1. Drop `abc_voice_names` table temporarily
2. Load settings pages

**Expected Results:**
- ✅ Page loads without fatal error
- ✅ "No voices found" message displays
- ✅ Prompt to load schema
- ✅ Settings still saveable

---

## Performance Tests

### Test 31: Large Voice List
**Steps:**
1. Add 100 voices to database
2. Load transpose settings page

**Expected Results:**
- ✅ Page loads in < 2 seconds
- ✅ Table renders correctly
- ✅ No browser lag
- ✅ Scrolling smooth

### Test 32: Multiple Concurrent Users
**Steps:**
1. Have 3 admins open settings simultaneously
2. Each makes different changes
3. All save

**Expected Results:**
- ✅ Last save wins (expected behavior)
- ✅ No database locks
- ✅ No data corruption

---

## Accessibility Tests

### Test 33: Keyboard Navigation
**Steps:**
1. Navigate settings using only keyboard (Tab, Enter, Space)

**Expected Results:**
- ✅ All form elements reachable
- ✅ Clear focus indicators
- ✅ Can select radio buttons with keyboard
- ✅ Can submit form with Enter key

### Test 34: Screen Reader Compatibility
**Steps:**
1. Use screen reader (NVDA, JAWS, VoiceOver)
2. Navigate settings pages

**Expected Results:**
- ✅ Form labels read correctly
- ✅ Radio button states announced
- ✅ Table headers read
- ✅ Helpful descriptions read

---

## Success Criteria

UI is considered production-ready when:
- ✅ All 34 tests pass
- ✅ No PHP errors or warnings
- ✅ Security checks pass (nonce, capabilities, sanitization)
- ✅ Settings persist correctly across sessions
- ✅ Responsive on mobile/tablet
- ✅ Accessible to keyboard and screen reader users
- ✅ Performance acceptable with large datasets

---

## Automated Testing Script

Create `test_wp_ui.php`:

```php
<?php
// Run from WordPress root or with WP-CLI

// Test 1: Check options exist
$transpose_mode = get_option('abc_transpose_mode');
$voice_order = get_option('abc_voice_order_mode');

echo $transpose_mode ? "✓ Transpose mode set\n" : "✗ No transpose mode\n";
echo $voice_order ? "✓ Voice order mode set\n" : "✗ No voice order mode\n";

// Test 2: Check pages exist
$pages = [
    'abc-transpose-settings',
    'abc-voice-order-settings'
];

foreach ($pages as $page) {
    $url = admin_url("admin.php?page=$page");
    echo file_exists(WP_PLUGIN_DIR . "/abc-canntaireachd/admin-$page.php") 
        ? "✓ Page file exists: $page\n" 
        : "✗ Missing page: $page\n";
}

// Test 3: Database tables
global $wpdb;
$table = $wpdb->prefix . 'abc_voice_names';
$exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
echo $exists ? "✓ Database table exists\n" : "✗ Database table missing\n";
```

Run with WP-CLI:
```bash
wp eval-file test_wp_ui.php
```

---

## Troubleshooting

### Issue: "Call to undefined function add_action"
**Solution**: File loaded outside WordPress context. Ensure proper WordPress initialization.

### Issue: Settings not saving
**Solution**: Check write permissions on WordPress options table. Verify nonce is being sent.

### Issue: Voices not displaying
**Solution**: Run database migration first. Check table exists and has data.

### Issue: Permission denied
**Solution**: Ensure current user has 'manage_options' capability. Check with:
```php
current_user_can('manage_options');
```
