<?php
/** Read-only checks for an explicitly provided isolated WordPress installation. */
if (empty($argv[1]) || !is_file($argv[1])) {
    fwrite(STDERR, "Usage: php tests/wordpress-check.php /absolute/path/to/isolated/wp-load.php\n");
    exit(1);
}
require $argv[1];
$expectations = array(
    '#join' => home_url('/#join'),
    '/be-a-volunteer' => '/be-a-volunteer',
    'https://example.test/join/' => 'https://example.test/join/',
    '' => '',
);
foreach ($expectations as $input => $expected) {
    if (yea_resolve_header_url($input) !== $expected) {
        throw new RuntimeException('Header URL resolution failed.');
    }
}
if (false === strpos(yea_get_header_ctas_markup(), '/be-a-volunteer')) {
    throw new RuntimeException('Configured Join destination was not preserved in this test installation.');
}
echo "WordPress header URL checks passed.\n";
