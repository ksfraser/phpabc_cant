<?php

namespace Ksfraser\PhpabcCanntaireachd;

use ksfraser\origin\Origin;

// Include constants
require_once __DIR__ . '/Defines.php';

/**//***********************
* https://github.com/jwdj/EasyABC/blob/master/tune_elements.py has items we may want eventually (but in python)
***************************/

/***********************************
*	ABC File Structure
*		Tune
*		blankline
*		Tune(s)(optional)
*
*	Tune Structure
*		X: index
*		... header lines
*		K:
*		...body lines
*
*	Body Lines
*		[Instruction]
*		Bar indicators [|:
*		Notes and Embellishments, decorators, etc
*		Bar indicator | [1 |1 ...
*		EOL indicator
*
*
*************************************/

class AbcToken extends Origin
{
	protected $token;	//string
	
	function set( $field, $value, $enforce = true )
	{
		switch( $field )
		{
			case "token":
				$this->token = $value;
				return true;
			default:
				// Let parent handle other fields if needed
				return false;
		}
	}
}

class AbcComment extends AbcToken
{
	//Line starts with % but not %%
}

class AbcWrapped extends AbcToken
{
	//string wrapped in a matching pair e.g. { }
	protected $startchar;
	protected $endchar;
	
	function set( $field, $value = null, $enforce = true )
	{
		switch( $field )
		{
			case "token":
				return $this->set_token( $value, $enforce );
			default:
				return parent::set( $field, $value, $enforce );
		}
	}
	
	protected function set_token( $value, $enforce )
	{
		if( is_array( $value ) )
		{
			$len = count( $value );
			if( strcmp( $value[0], $this->startchar ) !== 0 )
			{
				throw new \Exception( "We are in the wrong class.  Start char doesn't match!" );
			}
			if( strcmp( $value[$len - 1], $this->endchar ) !== 0 )
			{
				throw new \Exception( "Last char of passed in value doesn't match expected! " );
			}
			return parent::set( "token", implode( $value ), $enforce );
		}
		else
		{
			$len = strlen( $value );
			if( strncmp( $value[0], $this->startchar, 1 ) !== 0 )
			{
				throw new \Exception( "We are in the wrong class.  Start char doesn't match!" );
			}
			if( strncmp( $value[$len], $this->endchar, 1 ) !== 0 )
			{
				throw new \Exception( "Last char of passed in value doesn't match expected! " );
			}
			return parent::set( "token", $value, $enforce );
		}
	}
}

class AbcChord extends AbcWrapped
{
	//string wrapped in " "
	function __construct()
	{
		parent::__construct();
		$this->set( "startchar", '"' );
		$this->set( "endchar", '"' );
	}
}

class AbcDecorator extends AbcWrapped
{
	//string wrapped in ! !
	function __construct()
	{
		parent::__construct();
		$this->set( "startchar", '!' );
		$this->set( "endchar", '!' );
	}
}

/**//**********************************************************************************************
*Take apart an ABC file and stick the components into variables within the class for re-assembly later.
*
*Tunesetting is designed (goal) to generate a compliant abc file that has components being used in
*the typesetting of the band settings of Bagpipe (i.e. Pipe Band) music.  This is also including the
*textual representation of the ABC code without embellishments, as well as the Canntaireachd equivalent
*so that those that are challenged reading the notes on the staff might be able to learn the tune in
*other ways.
*
*ABCparser sets out to take an existing tune apart, and eventully do the "translation" steps to
*put that textual representation as well as the canntaireachd into the setting without the typesettor
*having to manually type all of those too.
*
**************************************************************************************************/
class AbcParser extends AbcTuneBase
{
	protected $tune;
	protected $lines;
	protected $headers;
	protected $body_start;	//!<int which line is the start of the body
	protected $tokens;
	protected $tokens_arr;	//array of classes for the tokens
	
	function __construct()
	{
		parent::__construct();
		$this->tokens = array();
	}
	
	/**//**************************************
	* Find the line where the body starts
	*
	* The body starts after the first K: in the tune
	*
	* @param none uses internal
	* @return bool did we find a body start
	*******************************************/
	protected function find_body_start()
	{
		$this->var_dump( get_class() . "::" . __METHOD__ );
		if( ! isset( $this->lines ) )
		{
			throw new \Exception( "Variable LINES not set", KSF_FIELD_NOT_SET );
		}
		$count = 0;
		foreach ($this->lines as $line) 
		{
			$count++;
			$l = trim($line);
			if( strncmp( "K:", $l, 2 ) == 0 )
			{
				//This is the last line of the HEADER.
				$this->body_start = $count;
				$this->Log( "Found Body at line:  $this->body_start", 'PEAR_LOG_DEBUG' );
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Process ABC content and return as string
	 * @param string|null $abcContent
	 * @return string
	 */
	public function process($abcContent = null) {
		$parser = new \Ksfraser\PhpabcCanntaireachd\AbcFileParser();
		$tunes = $parser->parse($abcContent);
		$output = '';
		foreach ($tunes as $tuneIdx => $tune) {
			$headers = $tune->getHeaders();
			// Check/fix missing K header
			if (isset($headers['K']) && ($headers['K']->get() === '' || $headers['K']->get() === null)) {
				$tune->replaceHeader('K', 'HP');
			}
			// Fix voice headers (do not log)
			if (method_exists($tune, 'fixVoiceHeaders')) {
				$tune->fixVoiceHeaders();
			}
			$output .= $tune->render();
			$output .= "\n";
		}
		// Only return processed ABC, not logs
		return $output;
	}
}
