/**
 * WordPress admin screen for ABC/Canntaireachd/BMW token conversions
 *
 * Provides CRUD UI for managing the abc_dict_tokens table, which maps ABC tokens
 * to canntaireachd and BMW tokens. If a BMW token is added and the ABC token exists,
 * the BMW token is updated in that row. If a new ABC token is added, all values are inserted.
 *
 * @file admin-abc-dict.php
 * @package Ksfraser\PhpabcCanntaireachd
 */
// Ensure WordPress helper functions are available in admin context
if (!function_exists('esc_html') || !function_exists('esc_attr') || !function_exists('sanitize_text_field') || !function_exists('sanitize_textarea_field')) {
    require_once(ABSPATH . 'wp-includes/formatting.php');
}
<?php
/**
 * Registers the ABC Dict Tokens admin menu page.
 * @return void
 */
add_action('admin_menu', function() {
    add_menu_page('ABC Dict Tokens', 'ABC Dict Tokens', 'manage_options', 'abc-dict-tokens', 'abc_dict_tokens_admin');
});

/**
 * Renders the ABC/Canntaireachd/BMW Token Conversions admin screen and handles CRUD actions.
 *
 * - Add: If ABC token exists, updates BMW token and description. Otherwise, inserts new row.
 * - Edit: Updates all fields for the selected token.
 * - Delete: Removes the selected token.
 * - List: Displays all tokens in a table with edit/delete forms.
 *
 * @global wpdb $wpdb WordPress database access object
 * @return void
 */
function abc_dict_tokens_admin() {
    global $wpdb;
    $table = $wpdb->prefix . 'abc_dict_tokens';
    echo '<h1>ABC/Canntaireachd/BMW Token Conversions</h1>';
    // Handle add
    if (isset($_POST['add_dict_token'])) {
        $abc = sanitize_text_field($_POST['abc_token']);
        $cannt = sanitize_text_field($_POST['cannt_token']);
        $bmw = sanitize_text_field($_POST['bmw_token']);
        $desc = sanitize_textarea_field($_POST['description']);
        // Check for existing ABC token
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE abc_token = %s", $abc));
        if ($existing) {
            // Update BMW token and description only
            $wpdb->update($table, [
                'bmw_token' => $bmw,
                'description' => $desc
            ], ['id' => $existing->id]);
            echo '<div class="updated"><p>Updated BMW token for existing ABC token.</p></div>';
        } else {
            $wpdb->insert($table, [
                'abc_token' => $abc,
                'cannt_token' => $cannt,
                'bmw_token' => $bmw,
                'description' => $desc
            ]);
            echo '<div class="updated"><p>Added token.</p></div>';
        }
    }
    // Handle delete
    if (isset($_POST['delete_dict_token'])) {
        $id = intval($_POST['delete_id']);
        $wpdb->delete($table, ['id' => $id]);
        echo '<div class="updated"><p>Deleted token.</p></div>';
    }
    // Handle edit
    if (isset($_POST['edit_dict_token'])) {
        $id = intval($_POST['edit_id']);
        $abc = sanitize_text_field($_POST['edit_abc_token']);
        $cannt = sanitize_text_field($_POST['edit_cannt_token']);
        $bmw = sanitize_text_field($_POST['edit_bmw_token']);
        $desc = sanitize_textarea_field($_POST['edit_description']);
        $wpdb->update($table, [
            'abc_token' => $abc,
            'cannt_token' => $cannt,
            'bmw_token' => $bmw,
            'description' => $desc
        ], ['id' => $id]);
        echo '<div class="updated"><p>Updated token.</p></div>';
    }
    // List table contents
    $results = $wpdb->get_results("SELECT * FROM $table");
    echo '<table><tr><th>ABC Token</th><th>Canntaireachd</th><th>BMW</th><th>Description</th><th>Actions</th></tr>';
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>'.esc_html($row->abc_token).'</td>';
        echo '<td>'.esc_html($row->cannt_token).'</td>';
        echo '<td>'.esc_html($row->bmw_token).'</td>';
        echo '<td>'.esc_html($row->description).'</td>';
        // Edit form
        echo '<td>';
        echo '<form method="post" style="display:inline-block; margin-right:5px;">';
        echo '<input type="hidden" name="edit_id" value="'.intval($row->id).'">';
        echo '<input type="text" name="edit_abc_token" value="'.esc_attr($row->abc_token).'" size="10">';
        echo '<input type="text" name="edit_cannt_token" value="'.esc_attr($row->cannt_token).'" size="10">';
        echo '<input type="text" name="edit_bmw_token" value="'.esc_attr($row->bmw_token).'" size="10">';
        echo '<input type="text" name="edit_description" value="'.esc_attr($row->description).'" size="20">';
        echo '<input type="submit" name="edit_dict_token" value="Update">';
        echo '</form>';
        // Delete form
        echo '<form method="post" style="display:inline-block;">';
        echo '<input type="hidden" name="delete_id" value="'.intval($row->id).'">';
        echo '<input type="submit" name="delete_dict_token" value="Delete" onclick="return confirm(\'Delete this token?\');">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    // Add form
    echo '<h2>Add Token</h2>';
    echo '<form method="post">';
    echo '<label>ABC Token: <input type="text" name="abc_token" required></label> ';
    echo '<label>Canntaireachd: <input type="text" name="cannt_token"></label> ';
    echo '<label>BMW: <input type="text" name="bmw_token"></label> ';
    echo '<label>Description: <input type="text" name="description"></label> ';
    echo '<input type="submit" name="add_dict_token" value="Add">';
    echo '</form>';
}
