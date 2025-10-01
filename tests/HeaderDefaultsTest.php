<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\HeaderDefaults;

class HeaderDefaultsTest extends TestCase {
    public function testGetDefaultsReturnsArray() {
        $defaults = HeaderDefaults::getDefaults();
        $this->assertIsArray($defaults);
    }
    public function testValidateDefaultsDetectsMissingKeys() {
        $defaults = ['K' => 'D', 'Q' => '1/4=120'];
        $errors = HeaderDefaults::validateDefaults($defaults);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Missing header key', $errors[0]);
    }
    public function testValidateDefaultsDetectsInvalidQ() {
        $defaults = ['K' => 'D', 'Q' => 'bad', 'L' => '1/8', 'M' => '4/4', 'R' => '', 'B' => '', 'O' => '', 'Z' => ''];
        $errors = HeaderDefaults::validateDefaults($defaults);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Q header looks invalid', $errors[0]);
    }
    public function testValidateDefaultsDetectsInvalidL() {
        $defaults = ['K' => 'D', 'Q' => '1/4=120', 'L' => 'bad', 'M' => '4/4', 'R' => '', 'B' => '', 'O' => '', 'Z' => ''];
        $errors = HeaderDefaults::validateDefaults($defaults);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('L header looks invalid', $errors[0]);
    }
    public function testValidateDefaultsPassesValid() {
        $defaults = ['K' => 'D', 'Q' => '1/4=120', 'L' => '1/8', 'M' => '4/4', 'R' => '', 'B' => '', 'O' => '', 'Z' => ''];
        $errors = HeaderDefaults::validateDefaults($defaults);
        $this->assertEmpty($errors);
    }
}
