<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Class AbcNote
 *
 * Represents a single note in ABC notation, including pitch, octave, accidental, length, decorator, lyrics, canntaireachd, and solfege.
 * Provides parsing, validation, and rendering logic for ABC notes.
 *
 * SOLID: Single Responsibility (note model), DRY (trait for parsing), SRP (validation methods).
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @property string $pitch Note pitch (a-gA-G)
 * @property string $octave Octave modifier (', ,,)
 * @property string $sharpflat Accidental (=, ^, _)
 * @property string $length Note length (1, /, //, etc.)
 * @property string $decorator Note decorator (.MHTR!trill! etc.)
 * @property string $lyrics Lyrics for this note
 * @property string $canntaireachd Canntaireachd for this note
 * @property string $solfege Solfege for this note
 * @property string $bmwToken BMW token for this note
 * @property callable $callback Optional callback for processing
 *
 * @method __construct(string $noteStr, callable|null $callback)
 * @method set(string $field, mixed $value, bool $enforce_only_native_vars)
 * @method setLyrics(string $lyrics)
 * @method getLyrics(): string
 * @method setCanntaireachd(string $cannt)
 * @method getCanntaireachd(): string
 * @method setSolfege(string $solfege)
 * @method getSolfege(): string
 * @method setBmwToken(string $bmw)
 * @method getBmwToken(): string
 * @method renderLyrics(): string
 * @method renderCanntaireachd(): string
 * @method renderSolfege(): string
 * @method get_body_out(): string
 *
 * @uml
 * @startuml
 * class AbcNote {
 *   - pitch: string
 *   - octave: string
 *   - sharpflat: string
 *   - length: string
 *   - decorator: string
 *   - lyrics: string
 *   - canntaireachd: string
 *   - solfege: string
 *   - bmwToken: string
 *   - callback: callable
 *   + __construct(noteStr: string, callback: callable)
 *   + set(field: string, value: mixed, enforce_only_native_vars: bool)
 *   + setLyrics(lyrics: string)
 *   + getLyrics(): string
 *   + setCanntaireachd(cannt: string)
 *   + getCanntaireachd(): string
 *   + setSolfege(solfege: string)
 *   + getSolfege(): string
 *   + setBmwToken(bmw: string)
 *   + getBmwToken(): string
 *   + renderLyrics(): string
 *   + renderCanntaireachd(): string
 *   + renderSolfege(): string
 *   + get_body_out(): string
 * }
 * AbcNote --|> Origin
 * AbcNote <|-- NoteParserTrait
 * @enduml
 */

use Ksfraser\PhpabcCanntaireachd\Exceptions\AbcNoteLengthException;
use Ksfraser\PhpabcCanntaireachd\NoteParserTrait;
use Ksfraser\origin\Origin;

class AbcNote extends Origin
{
	use NoteParserTrait;
	// Properties
	protected $decorator = '';
	public static $shortcutLookup = null;
	use NoteParserTrait;
	// Properties
	public static $shortcutLookup = null;
	protected $shortcutLookup = [];
	protected $pitch;    // a-gA-G
	protected $octave;   // , or '
	protected $sharpflat; // =^_ null/natural/sharp/flat
	protected $length;   // (int)(/)(int)
	/**
	 * @var BaseDecorator[] Decorator objects for this note
	 */
	/**
	 * @var \Ksfraser\PhpabcCanntaireachd\Decorator\BaseDecorator[] Decorator objects for this note
	 */
	protected $decorators = [];
	protected $name;
	protected $lyrics;
	protected $canntaireachd;
	protected $solfege;
	protected $bmwToken;
	protected $callback;
	// ABC spec fields
	protected $graceNotes = [];
	protected $chordSymbol = null;
	protected $annotations = [];
	protected $accidentals = [];

	// Methods
	/**
	 * Exception for invalid ABC note length.
	 */
	private function throwLengthException($length, $noteStr = '') {
		throw new \Exception("Invalid ABC note length: '$length' in note '$noteStr'. Three or more slashes are not ABC spec compliant.");
	}

	/**
	 * Parse an ABC note string into components.
	 * @param string $noteStr
	 * @return array [pitch, octave, sharpflat, length, decorator]
	 */
	private function parseNote($noteStr) {
		if (preg_match("/^([_=^]?)([a-gA-GzZ])([,']*)([0-9]+\/?[0-9]*|\/{1,}|)(.*)$/", $noteStr, $m)) {
			return [
				'pitch' => $m[2],
				'octave' => $m[3],
				'sharpflat' => $m[1],
				'length' => $m[4],
				'decorator' => $m[5]
			];
		}
		return [
			'pitch' => '',
			'octave' => '',
			'sharpflat' => '',
			'length' => '',
			'decorator' => ''
		];
	}

	/**
	 * @param string $noteStr
	 * @param callable|null $callback
	 * @param array|null $shortcutLookup Dependency-injected decorator shortcut map
	 */
	public function __construct($noteStr, $callback = null, $shortcutLookup = null)
	{
		parent::__construct();
		$this->callback = $callback;
		if ($shortcutLookup !== null) {
			$this->shortcutLookup = $shortcutLookup;
		}
		$this->parseAbcNote($noteStr);
	}

	/**
	 * Parse an ABC note string into all ABC spec components (full ABC v2.1 compliance).
	 * @param string $noteStr
	 */
	protected function parseAbcNote($noteStr)
	{
		// Extract chord symbol: "[chord]"
		if (preg_match('/"([^"]+)"/', $noteStr, $m)) {
			$this->chordSymbol = $m[1];
			$noteStr = str_replace($m[0], '', $noteStr);
		}
		// Extract grace notes: {grace notes}
		if (preg_match('/\{([^}]*)\}/', $noteStr, $m)) {
			$this->graceNotes = preg_split('/\s+/', trim($m[1]));
			$noteStr = str_replace($m[0], '', $noteStr);
		}

		// Use injected shortcutLookup if available, else build static
		if (!empty($this->shortcutLookup)) {
			$shortcutLookup = $this->shortcutLookup;
		} else {
			if (self::$shortcutLookup === null) {
				$decoratorMap = \Ksfraser\PhpabcCanntaireachd\Decorator\DecoratorLoader::getDecoratorMap();
				self::$shortcutLookup = [];
				foreach ($decoratorMap as $shortcut => $class) {
					self::$shortcutLookup[strtolower($shortcut)] = $class;
				}
			}
			$shortcutLookup = self::$shortcutLookup;
		}

		// Find all !wrapped! decorators
		preg_match_all('/!(.*?)!/', $noteStr, $bangMatches);
		$this->annotations = $bangMatches[0];
		$this->decorators = [];
		foreach ($bangMatches[1] as $rawShortcut) {
			$shortcut = strtolower('!' . $rawShortcut . '!');
			if (isset($shortcutLookup[$shortcut])) {
				$class = $shortcutLookup[$shortcut];
				$this->decorators[] = new $class();
			}
			$noteStr = str_replace('!' . $rawShortcut . '!', '', $noteStr);
		}

		// Find all raw shortcut decorators (e.g., '.', 'tr', etc.)
		$rawShortcuts = array_filter(array_keys($shortcutLookup), function($s) {
			return strpos($s, '!') !== 0 && $s !== '';
		});
		if (!empty($rawShortcuts)) {
			$shortcutRegex = '/(' . implode('|', array_map('preg_quote', $rawShortcuts)) . ')/i';
			if (count($rawShortcuts) > 0 && preg_match_all($shortcutRegex, $noteStr, $rawMatches) && isset($rawMatches[1])) {
				foreach ($rawMatches[1] as $rawShortcut) {
					$shortcut = strtolower($rawShortcut);
					if (isset($shortcutLookup[$shortcut])) {
						$class = $shortcutLookup[$shortcut];
						$this->decorators[] = new $class();
					}
					$noteStr = str_replace($rawShortcut, '', $noteStr);
				}
			}
		}

		// Extract accidentals: = ^ _
		preg_match_all('/[=_^]/', $noteStr, $accs);
		$this->accidentals = isset($accs[0]) ? $accs[0] : [];
		foreach ($this->accidentals as $a) {
			$noteStr = str_replace($a, '', $noteStr);
		}
		// Parse remaining note string
	$parsed = $this->parseNote($noteStr);
		$this->set("pitch", $parsed['pitch']);
		$this->set("octave", $parsed['octave']);
		$this->set("sharpflat", $parsed['sharpflat']);
		$this->set("length", $parsed['length']);
		// Legacy decorator string (for compatibility)
		$this->set("decorator", $parsed['decorator']);
		// If parsed decorator matches a known shortcut, instantiate its class
		$legacyShortcut = strtolower($parsed['decorator']);
		if ($legacyShortcut && isset($shortcutLookup[$legacyShortcut])) {
			$class = $shortcutLookup[$legacyShortcut];
			$this->decorators[] = new $class();
		}
	}

	public function setBmwToken($bmw) {
		$this->bmwToken = $bmw;
	}
	public function getBmwToken() {
		return $this->bmwToken;
	}
	public function setLyrics($lyrics) {
		$this->lyrics = $lyrics;
	}
	public function getLyrics() {
		return $this->lyrics;
	}
	public function setCanntaireachd($cannt) {
		$this->canntaireachd = $cannt;
	}
	public function getCanntaireachd() {
		return $this->canntaireachd;
	}
	public function setSolfege($solfege) {
		$this->solfege = $solfege;
	}
	public function getSolfege() {
		return $this->solfege;
	}

	public function renderLyrics() {
		return $this->lyrics ?? '';
	}
	public function renderCanntaireachd() {
		return $this->canntaireachd ?? '';
	}
	public function renderSolfege() {
		return $this->solfege ?? '';
	}

	public function set($field, $value = null, $enforce_only_native_vars = true)
	{
		switch ($field) {
			case "pitch":
				$ok = $this->validate_pitch($value);
				if ($ok) $this->pitch = $value;
				break;
			case "octave":
				$ok = $this->validate_octave($value);
				if ($ok) $this->octave = $value;
				break;
			case "sharpflat":
				$ok = $this->validate_sharpflat($value);
				if ($ok) $this->sharpflat = $value;
				break;
			case "length":
				$ok = $this->validate_length($value);
				if ($ok) $this->length = $value;
				break;
			case "decorator":
				$ok = $this->validate_decorator($value);
				if ($ok) $this->decorator = $value;
				break;
			default:
				return parent::set($field, $value, $enforce_only_native_vars);
		}
		return true;
	}

	/**
	 * Validate that the pitch is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_pitch($value)
	{
		switch ($value) {
			case "A": case "B": case "C": case "D": case "E": case "F": case "G":
			case "a": case "b": case "c": case "d": case "e": case "f": case "g":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the octave is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_octave($value)
	{
		switch ($value) {
			case ",": case "'": case ",,": case "''": case "":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the sharpflat is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_sharpflat($value)
	{
		switch ($value) {
			case "=": case "^": case "_": case "":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the length is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_length($value)
	{
		// Check for three or more slashes
		if (preg_match('/^\/{3,}$/', $value)) {
			throw new \Ksfraser\PhpabcCanntaireachd\Exceptions\AbcNoteLengthException($value);
		}
		switch ($value) {
			case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8":
			case "9": case "10": case "11": case "12": case "13": case "14": case "15": case "16":
			case "/": case ">": case "<": case "//":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Validate that the decorator is valid
	 * @param string $value
	 * @return bool
	 */
	function validate_decorator($value)
	{
		switch ($value) {
			case ".": case "<": case "H": case "T": case "R": case "!trill!": case "!fermata!": case "":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Format the class variables into the Voice line in the header
	 * @return string
	 */
	function get_header_out()
	{
		throw new \Exception("Notes can't be in the header!");
	}

	/**
	 * Render the note as ABC, including all spec fields in correct order, using Decorator classes.
	 * @return string
	 */
	public function get_body_out()
	{
		$out = "";
		// Grace notes first
		if (!empty($this->graceNotes)) {
			$out .= "{" . implode(' ', $this->graceNotes) . "}";
		}
		// Chord symbol next
		if ($this->chordSymbol) {
			$out .= '"' . $this->chordSymbol . '"';
		}
		// Accidentals before note
		foreach ($this->accidentals as $a) {
			$out .= $a;
		}
		// Decorators/annotations (ABC spec: decorations before note)
		foreach ($this->decorators as $decoratorObj) {
			$out .= $decoratorObj->render();
		}
		// Decorator field (legacy, for compatibility)
		if (isset($this->decorator) && $this->decorator !== "") {
			$out .= $this->decorator;
		}
		// Pitch
		$out .= $this->pitch;
		// Octave
		if (isset($this->octave) && $this->octave !== "") {
			$out .= $this->octave;
		}
		// Note length
		if (isset($this->length) && $this->length !== "") {
			$out .= $this->length;
		}
		return $out;
	}

}
