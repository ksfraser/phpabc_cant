<?php
/**
 * ABC Token Dictionary
 * Maps ABC notation tokens to canntaireachd syllables and BMW notation
 */

$abc = [
    // Basic notes
    'A' => [
        'cannt_token' => 'dar',
        'bmw_token' => 'A',
        'description' => 'Low A'
    ],
    'B' => [
        'cannt_token' => 'dod',
        'bmw_token' => 'B',
        'description' => 'B'
    ],
    'C' => [
        'cannt_token' => 'hid',
        'bmw_token' => 'C',
        'description' => 'C'
    ],
    'D' => [
        'cannt_token' => 'dar',
        'bmw_token' => 'D',
        'description' => 'D'
    ],
    'E' => [
        'cannt_token' => 'dod',
        'bmw_token' => 'E',
        'description' => 'E'
    ],
    'F' => [
        'cannt_token' => 'hid',
        'bmw_token' => 'F',
        'description' => 'F'
    ],
    'G' => [
        'cannt_token' => 'dar',
        'bmw_token' => 'G',
        'description' => 'G'
    ],
    'a' => [
        'cannt_token' => 'dar',
        'bmw_token' => 'a',
        'description' => 'High A'
    ],
    'b' => [
        'cannt_token' => 'dod',
        'bmw_token' => 'b',
        'description' => 'High B'
    ],
    'c' => [
        'cannt_token' => 'hid',
        'bmw_token' => 'c',
        'description' => 'High C'
    ],
    'd' => [
        'cannt_token' => 'dar',
        'bmw_token' => 'd',
        'description' => 'High D'
    ],
    'e' => [
        'cannt_token' => 'dod',
        'bmw_token' => 'e',
        'description' => 'High E'
    ],
    'f' => [
        'cannt_token' => 'hid',
        'bmw_token' => 'f',
        'description' => 'High F'
    ],
    'g' => [
        'cannt_token' => 'dar',
        'bmw_token' => 'g',
        'description' => 'High G'
    ],

    // Bar lines
    '|' => [
        'cannt_token' => '|',
        'bmw_token' => '|',
        'description' => 'Bar line'
    ],
    '||' => [
        'cannt_token' => '||',
        'bmw_token' => '||',
        'description' => 'Double bar line'
    ],
    '|]' => [
        'cannt_token' => '|]',
        'bmw_token' => '|]',
        'description' => 'End bar line'
    ],

    // Rests
    'z' => [
        'cannt_token' => 'z',
        'bmw_token' => 'z',
        'description' => 'Rest'
    ],
    'Z' => [
        'cannt_token' => 'Z',
        'bmw_token' => 'Z',
        'description' => 'Multi-measure rest'
    ],

    // Grace notes
    '{g}' => [
        'cannt_token' => '{g}',
        'bmw_token' => '{g}',
        'description' => 'Grace note g'
    ],
    '{e}' => [
        'cannt_token' => '{e}',
        'bmw_token' => '{e}',
        'description' => 'Grace note e'
    ]
];

return $abc;
