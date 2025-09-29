<?php
/**
 * Class AbcFile
 *
 * Represents an ABC file.
 *
 * @package Ksfraser\PhpabcCanntaireachd
 */
namespace Ksfraser\PhpabcCanntaireachd;

use ksfraser\origin\Origin;

/**
 * https://abcnotation.com/wiki/abc:standard:v2.1#abc_file_identification
 * File description
 *
 * Every file is supposed to start with %abc-VERSOIN
 * Developers should ignore a leading BOM
 * 
 * Contains
 *	File Header
 *		Can contain Information Fields
 *	ABC Tune
 *	Free text and typeset text
 *	Empty Lines
 *	%
 *
 * Settings in a tune override the file settings, but only within that tune scope.
 *
 * It is not recommended to use file settings for tunebooks that will be distributed
 *  since users can extract tunes without that header and therefore the settings could
 *  be forgotten/missed/ignored.
 *
 * Empty lines separate tunes, freetext and fileheaders.
 * 
 * % indicates start of comments.  Everything after % is to be ignored.
 * [r: ...] indicates a remark in the middle of a tune body
 */
class AbcFile extends Origin
{
	protected $raw;
	protected $FileHeader;
	protected $Body;
	public function __construct( $contents )
	{
		$this->raw = $contents;
	}
}
