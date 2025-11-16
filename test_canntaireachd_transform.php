<?php
// Test runner for CanntaireachdTransform

require_once __DIR__ . '/vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\Transform\CanntaireachdTransform;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

echo "Testing CanntaireachdTransform...\n\n";

// Initialize
$dict = new TokenDictionary();
$transform = new CanntaireachdTransform($dict);

// Test 1: Bagpipes voice should get canntaireachd
echo "Test 1: Bagpipes voice gets canntaireachd\n";
$abc1 = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:Bagpipes
A B c d |
ABC;

$tune1 = AbcTune::parse($abc1);
if (!$tune1) {
    echo "  ❌ FAIL: Could not parse ABC\n\n";
} else {
    $result1 = $transform->transform($tune1);
    $bars1 = $result1->getBarsForVoice('Bagpipes');
    
    $hasCanntaireachd = false;
    if ($bars1) {
        foreach ($bars1 as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd')) {
                        $cannt = $note->getCanntaireachd();
                        if (!empty($cannt)) {
                            echo "  Note has canntaireachd: '$cannt'\n";
                            $hasCanntaireachd = true;
                        }
                    }
                }
            }
        }
    }
    
    if ($hasCanntaireachd) {
        echo "  ✅ PASS: Bagpipes voice has canntaireachd\n\n";
    } else {
        echo "  ❌ FAIL: Bagpipes voice has NO canntaireachd\n\n";
    }
}

// Test 2: Melody voice should NOT get canntaireachd
echo "Test 2: Melody voice does NOT get canntaireachd\n";
$abc2 = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
A B c d |
ABC;

$tune2 = AbcTune::parse($abc2);
if (!$tune2) {
    echo "  ❌ FAIL: Could not parse ABC\n\n";
} else {
    $result2 = $transform->transform($tune2);
    $bars2 = $result2->getBarsForVoice('M');
    
    $hasCanntaireachd = false;
    if ($bars2) {
        foreach ($bars2 as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd')) {
                        $cannt = $note->getCanntaireachd();
                        if (!empty($cannt)) {
                            echo "  Found canntaireachd: '$cannt'\n";
                            $hasCanntaireachd = true;
                        }
                    }
                }
            }
        }
    }
    
    if (!$hasCanntaireachd) {
        echo "  ✅ PASS: Melody voice has NO canntaireachd\n\n";
    } else {
        echo "  ❌ FAIL: Melody voice has canntaireachd (should not!)\n\n";
    }
}

// Test 3: Multi-voice with both M and Bagpipes
echo "Test 3: Multi-voice - Melody NO cannt, Bagpipes HAS cannt\n";
$abc3 = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
A B c d |
V:Bagpipes
A B c d |
ABC;

$tune3 = AbcTune::parse($abc3);
if (!$tune3) {
    echo "  ❌ FAIL: Could not parse ABC\n\n";
} else {
    $result3 = $transform->transform($tune3);
    
    // Check Melody
    $melodyBars = $result3->getBarsForVoice('M');
    $melodyHasCannt = false;
    if ($melodyBars) {
        foreach ($melodyBars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd')) {
                        $cannt = $note->getCanntaireachd();
                        if (!empty($cannt)) {
                            $melodyHasCannt = true;
                        }
                    }
                }
            }
        }
    }
    
    // Check Bagpipes
    $bagpipesBars = $result3->getBarsForVoice('Bagpipes');
    $bagpipesHasCannt = false;
    if ($bagpipesBars) {
        foreach ($bagpipesBars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd')) {
                        $cannt = $note->getCanntaireachd();
                        if (!empty($cannt)) {
                            echo "  Bagpipes note has: '$cannt'\n";
                            $bagpipesHasCannt = true;
                        }
                    }
                }
            }
        }
    }
    
    if (!$melodyHasCannt && $bagpipesHasCannt) {
        echo "  ✅ PASS: Melody has NO cannt, Bagpipes HAS cannt\n\n";
    } else {
        echo "  ❌ FAIL: Melody has cannt=" . ($melodyHasCannt ? 'YES' : 'NO') . 
             ", Bagpipes has cannt=" . ($bagpipesHasCannt ? 'YES' : 'NO') . "\n\n";
    }
}

echo "Testing complete!\n";
