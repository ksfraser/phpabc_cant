<?php

declare(strict_types=1);

/**
 * MuseScore Drumline -> Canntaireachd token dictionary.
 *
 * Data file, not a class: populates $abc in the including scope and returns it.
 *
 * Entry shape: $abc[<abc token>]['cannt'] = <canntaireachd syllable>;
 * $abc[<abc token>]['bmw']   = <BMW token>   (optional);
 * $abc[<abc token>]['desc']  = <description> (optional);
 *
 * Source: MuseScore Drumline keyboard mappings (C3 = Left hand, D3 = Right hand).
 */

$abc = [];

// --- Left / Right foundation strokes ---
$abc['C3']['cannt'] = 'la';
$abc['C3']['desc'] = 'Left hand stroke (L)';

$abc['D3']['cannt'] = 'ra';
$abc['D3']['desc'] = 'Right hand stroke (R)';

// --- Hit / snare family ---
$abc['D3']['cannt'] = 'ra';
$abc['D3']['desc'] = 'Hit (snare) — right hand';

// --- Rim family ---
$abc['F3']['cannt'] = 'rim';
$abc['F3']['desc'] = 'Rim shot';

$abc['D#3']['cannt'] = 'rims';
$abc['D#3']['desc'] = 'Rim shot (short)';

// --- Stick / click family ---
$abc['G3']['cannt'] = 'click';
$abc['G3']['desc'] = 'Stick click';

$abc['C4']['cannt'] = 'bstk';
$abc['C4']['desc'] = 'Back stick';

$abc['G#3']['cannt'] = 'xstk';
$abc['G#3']['desc'] = 'Cross stick';

$abc['A3']['cannt'] = 'stick';
$abc['A3']['desc'] = 'Stick shot';

// --- Cymbal family ---
$abc['E5']['cannt'] = 'hh';
$abc['E5']['desc'] = 'Cymbal hi-hat';

$abc['C2']['cannt'] = 'crash';
$abc['C2']['desc'] = 'Cymbal crash';

$abc['E2']['cannt'] = 'ride';
$abc['E2']['desc'] = 'Cymbal ride';

$abc['D2']['cannt'] = 'bell';
$abc['D2']['desc'] = 'Cymbal bell';

// --- Specialty / auxiliary ---
$abc['B3']['cannt'] = 'shell';
$abc['B3']['desc'] = 'Shell';

$abc['A#3']['cannt'] = 'cow';
$abc['A#3']['desc'] = 'Cowbell';

$abc['B4']['cannt'] = 'rib';
$abc['B4']['desc'] = 'Ribbon crasher';

$abc['F1']['cannt'] = 'clack';
$abc['F1']['desc'] = 'Harness hit (clack)';

$abc['C1']['cannt'] = 'stck';
$abc['C1']['desc'] = 'Sticks in (click)';

$abc['C#1']['cannt'] = 'dut';
$abc['C#1']['desc'] = 'Dut (vocal)';

$abc['F#4']['cannt'] = 'brush';
$abc['F#4']['desc'] = 'Brushes';

$abc['F4']['cannt'] = 'rods';
$abc['F4']['desc'] = 'Rods';

$abc['G4']['cannt'] = 'dread';
$abc['G4']['desc'] = 'Dreads';

// --- Shot / ping family ---
$abc['C#3']['cannt'] = 'ping';
$abc['C#3']['desc'] = 'Ping';

$abc['E3']['cannt'] = 'gok';
$abc['E3']['desc'] = 'Gok shot';

// Return the dictionary for both include and require consumers.
return $abc;
