<?php
namespace Ksfraser\PhpabcCanntaireachd\InformationField;

/**
 * Information fields contain info about the file, or tune
 *
 * Some can be in the file header (i.e. A,B, C,D F, G, H, I...)
 * Some can be in the Tune header (i.e. A,B, C,D F, G, H, I...)
 * Some can be in the tune body wrapped in [] (i.e. I, K, L, M, ...)
 * Some can be inline wrapped in [] (i.e. I, K, L, M, ...)
 *
 * Some expect a string
 * Others expect a specific "special Instruction" format that is specific to the field
 */
class XInformationField extends AbcInformationField {
	static public  = 'X'
}
