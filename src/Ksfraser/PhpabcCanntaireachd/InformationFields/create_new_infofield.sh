#!/bin/sh


  cat <<EOF > AbcInformationField.php
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
class AbcInformationField {
	static public \$label = 'W'
	protected \$value;

	public function __construct(\$value = '') 
	{ 
		\$this->set( \$value ); 
	}
        //For headers that allow multiple lines, we will need to override the set fcn
    	public function set(\$value)
    	{
        	// Only set if not already set or empty
        	if( ! isset( \$this->value ) || \$this->value === ''  )
        	{
                	\$this->value = \$value;
        	}
    	}
    	public function get() 
	{ 
		return \$this->value; 
	}
    	public function render()
    	{
        	return static::\$label . ':' . \$this->value ;
    	}
    	public function renderTuneBody()
    	{
        	return '[' . \$this->render() . "]";
    	}
	public function renderEOL()
	{
		return "\n";
	}
	/**
	 * Render Header format - File OR Tune
	 */
	public function renderHeader()
	{
		return \$this->render() . \$this->renderEOL();
	}
}
EOF


for x in A B C d F G H I K L M m N O P Q R r S s T U  W w X Z
do 
  cat <<EOF > ${x}InformationField.php
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
class ${x}InformationField extends AbcInformationField {
	static public $label = '${x}'
}
EOF

done


##Field V has extra attributes so not generated above.
##	There are a few more I haven't pulled out such as I or %%
