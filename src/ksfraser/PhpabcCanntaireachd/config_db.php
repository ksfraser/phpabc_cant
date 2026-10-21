<?php
// Database configuration for MIDI defaults and fallback
//
// To encrypt credentials, use Symfony's secrets vault:
//   1. Install symfony/console and symfony/secrets via Composer.
//   2. Run: php bin/console secrets:generate-keys
//   3. Run: php bin/console secrets:set MYSQL_USER --env=prod
//   4. Run: php bin/console secrets:set MYSQL_PASS --env=prod
//   5. Run: php bin/console secrets:set MYSQL_DB --env=prod
//   6. Run: php bin/console secrets:set MYSQL_HOST --env=prod
//   7. Run: php bin/console secrets:set MYSQL_PORT --env=prod
//
// To use secrets in PHP:
//   $user = $_ENV['MYSQL_USER'] ?? Symfony\Component\Runtime\Secrets\getSecret('MYSQL_USER');
//   $pass = $_ENV['MYSQL_PASS'] ?? Symfony\Component\Runtime\Secrets\getSecret('MYSQL_PASS');
//   ...
//
// If not using secrets, fallback to these values:
return [
    'mysql_user' => 'abcmidi',
    'mysql_pass' => 'abcmidi',
    'mysql_db'   => 'abcmidi',
    'mysql_host' => 'mysql.ksfraser.com',
    'mysql_port' => 3306,
    'dsn'        => 'mysql:host=mysql.ksfraser.com;port=3306;dbname=abcmidi;charset=utf8mb4'
];
