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
	protected array $headers_anglo_arr;
	/** @var int|null */
	protected ?int $index = null;
	/** @var string|null */
	protected ?string $key = null;
	/** @var string|null */
	protected ?string $notelength = null;
	/** @var string|null */
	protected ?string $meter = null;
	/** @var string|null */
	protected ?string $tempo = null;
	/** @var array */
	protected array $title_arr = [];
	/** @var string|null */
	protected ?string $composer = null;
	/** @var array */
	protected array $history_arr = [];
	/** @var array */
	protected array $books_arr = [];
	/** @var array */
	protected array $voice_arr = [];
	/** @var string|null */
	protected ?string $discography = null;
	/** @var string|null */
	protected ?string $file_url = null;
	/** @var string|null */
	protected ?string $group = null;
	/** @var array */
	protected array $instruction_arr = [];
	/** @var array */
	protected array $macro_arr = [];
	/** @var string|null */
	protected ?string $notes = null;
	/** @var string|null */
	protected ?string $origin = null;
	/** @var string|null */
	protected ?string $parts = null;
	/** @var string|null */
	protected ?string $rythm = null;
	/** @var string|null */
	protected ?string $source = null;
	/** @var array */
	protected array $userdef_arr = [];
	/** @var string|null */
	protected ?string $transcription = null;
	/** @var array */
	protected array $words_arr = [];
	/** @var array */
	protected array $body_voice_arr = [];
	/** @var array */
	protected array $voice_name_arr = [];
	/** @var int */
	protected int $voicecount = 0;
	/** @var array */
	protected array $words_bottom_arr = [];
	/** @var string|null */
	protected ?string $complete_tune = null;
	/** @var array */
	protected array $body_arr = [];
	/** @var string|null */
	protected ?string $body = null;
	/** @var int */
	protected int $line_count = 0;
	/** @var array */
	protected array $header_symbols_arr = [];
	/** @var array */
	protected array $voices_obj_arr = [];
	/** @var string|null */
	protected ?string $current_voice = null;
	/** @var int */
	protected int $current_barnum = 1;
	/** @var int */
	protected int $current_linenum = 1;

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
