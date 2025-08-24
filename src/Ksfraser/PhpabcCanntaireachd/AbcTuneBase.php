<?php

namespace Ksfraser\PhpabcCanntaireachd;

use ksfraser\origin\Origin;

/**//**************************************************************
 * I have built an array of items from the DOCs from bmw to try
 * and convert into ABC.  This should save a bunch of typesetting tim.
 *
 * * ***************************************************************/

/**//**
 * This class is the components of an ABC tune.  It is like a syntax.
 *
 * This class is meant to be the common parts between an 
 *	INPUT class (i.e. parser)
 * 	OUTPUT class (i.e. aspd_tune)
 * 
 * */
class AbcTuneBase extends Origin
{
	protected $headers_anglo_arr;
	protected $index; //X
	protected $key; //K
	protected $notelength;	//L Default of 1/16 for 2/4 or 6/8.  1/8 for 3/4 4/4 9/8 12/8...
	protected $meter;	//M Default is 4/4 if omitted
	protected $tempo;	//Q default 100bpm   e.g. Q: "Allegro" 1/4=120
	protected $title_arr;	//T
	protected $composer;	//C
	protected $history_arr;	//H
	protected $books_arr;	//B
	protected $voice_arr;	//V
	protected $discography;	//D
	protected $file_url;	//F
	protected $group;	//G  e.g. G:Flute
	protected $instruction_arr;	//I
	protected $macro_arr;	//m
	protected $notes;	//N  i.e. referrences to other similar tunes.
	protected $origin;	//O  e.g. O:Canada; Nova Scotia; Halifax.
	protected $parts;	//P
	protected $rythm;	//R  e.g. hornpipe, double jig, single jig, 48-bar polka
	protected $source;	//S
	protected $userdef_arr;	//U
	protected $transcription;	//Z
	protected $words_arr;	//w	LYRICS
	protected $body_voice_arr;	//The list of voices for the [V:1] line headers
	protected $voice_name_arr;	//The list of voices Names for searching
	protected $voicecount;
	protected $words_bottom_arr;//W
	protected $complete_tune;//What we are going to output.
	protected $body_arr;
	protected $body;
	protected $line_count;	//How many lines did we add?
	protected $header_symbols_arr;
	protected $voices_obj_arr;	//Array of Voice objects
	protected $current_voice;	//For when the source file doesn't use [V: at the start of each line
	protected $current_barnum;
	protected $current_linenum;

	function __construct()
	{
		parent::__construct();
		$this->headers_anglo_arr = array(
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
		);
		
		$this->title_arr = array();
		$this->history_arr = array();
		$this->books_arr = array();
		$this->voice_arr = array();
		$this->instruction_arr = array();
		$this->macro_arr = array();
		$this->userdef_arr = array();
		$this->words_arr = array();
		$this->body_voice_arr = array();
		$this->voice_name_arr = array();
		$this->words_bottom_arr = array();
		$this->body_arr = array();
		$this->header_symbols_arr = array();
		$this->voices_obj_arr = array();
		$this->line_count = 0;
		$this->current_barnum = 1;
		$this->current_linenum = 1;
		$this->voicecount = 0;
	}
	
	/**
	 * Debug output method
	 */
	protected function var_dump($message, $level = null)
	{
		// Simple debug output - can be enhanced later
		if (is_string($message)) {
			error_log($message);
		} else {
			error_log(print_r($message, true));
		}
	}
	
	/**
	 * Logging method
	 */
	protected function Log($message, $level = 'info')
	{
		// Simple logging - can be enhanced with proper logging later
		error_log("[$level] $message");
	}
	
	// Methods will need to be implemented based on the original class
	// For now, adding basic structure
}
