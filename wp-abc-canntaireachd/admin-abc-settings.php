<?php
// WordPress admin: ABC Canntaireachd settings page
// To use encrypted DB credentials, set Symfony secrets (see config/db_config.php for instructions)
function abc_canntaireachd_get_db_config() {
    if (class_exists('Symfony\Component\Runtime\Secrets\getSecret')) {
        $getSecret = \Closure::fromCallable(['Symfony\Component\Runtime\Secrets', 'getSecret']);
        $env = getenv();
        $config = [
            'mysql_user' => $env['MYSQL_USER'] ?? $getSecret('MYSQL_USER') ?? null,
            'mysql_pass' => $env['MYSQL_PASS'] ?? $getSecret('MYSQL_PASS') ?? null,
            'mysql_db'   => $env['MYSQL_DB']   ?? $getSecret('MYSQL_DB')   ?? null,
            'mysql_host' => $env['MYSQL_HOST'] ?? $getSecret('MYSQL_HOST') ?? 'localhost',
            'mysql_port' => $env['MYSQL_PORT'] ?? $getSecret('MYSQL_PORT') ?? 3306,
        ];
        $fallback = require __DIR__ . '/../config/db_config.php';
        foreach ($fallback as $k => $v) {
            if (!isset($config[$k]) || $config[$k] === null) $config[$k] = $v;
        }
    } else {
        $config = require __DIR__ . '/../config/db_config.php';
    }
    // Option overrides
    $config['mysql_user'] = get_option('abc_mysql_user', $config['mysql_user']);
    $config['mysql_pass'] = get_option('abc_mysql_pass', $config['mysql_pass']);
    $config['mysql_db']   = get_option('abc_mysql_db', $config['mysql_db']);
    $config['mysql_host'] = get_option('abc_mysql_host', $config['mysql_host']);
    $config['mysql_port'] = get_option('abc_mysql_port', $config['mysql_port']);
    $config['dsn'] = "mysql:host={$config['mysql_host']};port={$config['mysql_port']};dbname={$config['mysql_db']};charset=utf8mb4";
    return $config;
}

add_action('admin_menu', function() {
    add_options_page('ABC Canntaireachd Settings', 'ABC Canntaireachd', 'manage_options', 'abc-canntaireachd-settings', 'abc_canntaireachd_settings_page');
});
function abc_canntaireachd_settings_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        update_option('abc_voice_output_style', $_POST['voice_output_style']);
        update_option('abc_interleave_bars', (int)$_POST['interleave_bars']);
        update_option('abc_bars_per_line', (int)$_POST['bars_per_line']);
        update_option('abc_join_bars_with_backslash', !empty($_POST['join_bars_with_backslash']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    $voice_output_style = get_option('abc_voice_output_style', 'grouped');
    $interleave_bars = get_option('abc_interleave_bars', 1);
    $bars_per_line = get_option('abc_bars_per_line', 4);
    $join_bars_with_backslash = get_option('abc_join_bars_with_backslash', false);
    echo '<h2>ABC Canntaireachd Settings</h2>';
    echo '<form method="post">';
    echo '<label>Voice Output Style: <select name="voice_output_style"><option value="grouped"' . ($voice_output_style=='grouped'?' selected':'') . '>Grouped</option><option value="interleaved"' . ($voice_output_style=='interleaved'?' selected':'') . '>Interleaved</option></select></label><br>';
    echo '<label>Interleave Bars: <input type="number" name="interleave_bars" value="' . esc_attr($interleave_bars) . '"></label><br>';
    echo '<label>Bars Per Line: <input type="number" name="bars_per_line" value="' . esc_attr($bars_per_line) . '"></label><br>';
    echo '<label>Join Bars With Backslash: <input type="checkbox" name="join_bars_with_backslash"' . ($join_bars_with_backslash?' checked':'') . '></label><br>';
    echo '<input type="submit" value="Save Settings">';
    echo '</form>';
}
