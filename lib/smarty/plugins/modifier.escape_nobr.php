<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */

function smarty_modifier_escape_nobr($string) {
    
    $string = preg_replace("/\<br\>/ims", "{{br}}", $string);
    $string = preg_replace("/\< br\>/ims", "{{br}}", $string);
    $string = preg_replace("/\<br \/\>/ims", "{{br}}", $string);
    $string = preg_replace("/\<br\/\>/ims", "{{br}}", $string);
    $string = preg_replace("/\<\/br\>/ims", "{{br}}", $string);
    $string = htmlspecialchars($string);
    $string = preg_replace("/{{br}}/ims", "<br>", $string);
    
    return $string;
}


/* vim: set expandtab: */

?>
