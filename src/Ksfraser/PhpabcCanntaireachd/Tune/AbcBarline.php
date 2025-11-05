<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;
/**
 * Class AbcBarline
 *
 * Represents a barline in ABC notation.
 * Provides validation and listing of all valid ABC barline strings per spec.
 *
 * @package Ksfraser\PhpabcCanntaireachd\Tune
 * @uml
 * @startuml
 * class AbcBarline {
 *   + type: string
 *   + __construct(type: string)
 *   + getType(): string
 *   + isValidBarline(type: string): bool
 *   + getValidBarlineStrings(): array
 * }
 * @enduml
 */
class AbcBarline {
	// Dummy canntaireachd function for compatibility with AbcNote
	public function renderCanntaireachd(): string {
		return '';
	}
	/**
	 * @var string Barline type (e.g., '|', '||', '[|', etc.)
	 */
	protected $type;

	/**
	 * List of valid ABC barline strings per spec.
	 * @return string[]
	 */
	public static function getValidBarlineStrings() {
		return [
			'|', '||', '|:', ':|', '[:', ':]', '::',
			'|1', '|2', '[1', '[2', '|]', '[|', ':|1', ':|2', ':|]',
		];
	}

	/**
	 * Validate if a string is a valid ABC barline.
	 * @param string $type
	 * @return bool
	 */
	public static function isValidBarline($type) {
		return in_array($type, self::getValidBarlineStrings(), true);
	}

	public function __construct($type = '|') {
		if (!self::isValidBarline($type)) {
			throw new \InvalidArgumentException("Invalid ABC barline: '$type'");
		}
		$this->type = $type;
	}

	public function getType() {
		return $this->type;
	}
}
