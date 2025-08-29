<?php
namespace Ksfraser\PhpabcCanntaireachd;

use ksfraser\origin\Origin;
use Ksfraser\PhpabcCanntaireachd\HeaderExtractorTrait;

/**
 * Class AbcTuneBase
 *
 * Represents the common structure for ABC tunes, including headers, body, and metadata.
 *
 * @package Ksfraser\PhpabcCanntaireachd
 */
class AbcTuneBase extends Origin
{
	use HeaderExtractorTrait;

	/** @var array<string,string> */
	protected $headers_anglo_arr;
	/** @var int|null */
	protected $index = null;
	/** @var string|null */
	protected $key = null;
	/** @var string|null */
	protected $notelength = null;
	/** @var string|null */
	protected $meter = null;
	/** @var string|null */
	protected $tempo = null;
	/** @var array */
	protected $title_arr = array();
	/** @var string|null */
	protected $composer = null;
	/** @var array */
	protected $history_arr = array();
	/** @var array */
	protected $books_arr = array();
	/** @var array */
	protected $voice_arr = array();
	/** @var string|null */
	protected $discography = null;
	/** @var string|null */
	protected $file_url = null;
	/** @var string|null */
	protected $group = null;
	/** @var array */
	protected $instruction_arr = array();
	/** @var array */
	protected $macro_arr = array();
	/** @var string|null */
	protected $notes = null;
	/** @var string|null */
	protected $origin = null;
	/** @var string|null */
	protected $parts = null;
	/** @var string|null */
	protected $rythm = null;
	/** @var string|null */
	protected $source = null;
	/** @var array */
	protected $userdef_arr = array();
	/** @var string|null */
	protected $transcription = null;
	/** @var array */
	protected $words_arr = array();
	/** @var array */
	protected $body_voice_arr = array();
	/** @var array */
	protected $voice_name_arr = array();
	/** @var int */
	protected $voicecount = 0;
	/** @var array */
	protected $words_bottom_arr = array();
	/** @var string|null */
	protected $complete_tune = null;
	/** @var array */
	protected $body_arr = array();
	/** @var string|null */
	protected $body = null;
	/** @var int */
	protected $line_count = 0;
	/** @var array */
	protected $header_symbols_arr = array();
	/** @var array */
	protected $voices_obj_arr = array();
	/** @var string|null */
	protected $current_voice = null;
	/** @var int */
	protected $current_barnum = 1;
	/** @var int */
	protected $current_linenum = 1;

	public function __construct()
	{
		parent::__construct();
		$this->headers_anglo_arr = [
			'X' => 'index',
			'K' => 'key',
			'L' => 'notelength',
			'M' => 'meter',
			'Q' => 'tempo',
			'T' => 'title_arr',
			'C' => 'composer',
			'H' => 'history_arr',
			'B' => 'books_arr',
			'V' => 'voice_arr',
			'D' => 'discography',
			'F' => 'file_url',
			'G' => 'group',
			'I' => 'instruction_arr',
			'm' => 'macro_arr',
			'N' => 'notes',
			'O' => 'origin',
			'P' => 'parts',
			'R' => 'rythm',
			'S' => 'source',
			'U' => 'userdef_arr',
			'Z' => 'transcription',
			'w' => 'words_arr',
			'W' => 'words_bottom_arr'
		];
	}

	/**
	 * Debug output method
	 * @param mixed $message
	 * @param string|null $level
	 */
	public function var_dump($message, $level = null): void
	{
		if (is_string($message)) {
			error_log($message);
		} else {
			error_log(print_r($message, true));
		}
	}

	/**
	 * Logging method
	 * @param string $message
	 * @param string $level
	 */
	public function Log($message, $level = 'info'): void
	{
		error_log("[$level] $message");
	}

	// Methods will need to be implemented based on the original class
	// For now, adding basic structure
}
