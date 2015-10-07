<?php
/**
 * INLINE CSS COMPRESSOR AND/OR FILTER
 * Can also be used as a filter in .htaccess as:
 *   RewriteRule ^(.*)\.(css)$ css.php?f=$1.$2 [NC,L]
 * 
 * Copyleft (c) 2013 Vino Rodrigues
 * 
 * This work is Public Domain.
 *
 * **********************************************************************
 *   This code is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * **********************************************************************
 */

if ('cli' === PHP_SAPI) {
	// called from the command line
	if (is_array($argv) && (count($argv) == 2))
		$_REQUEST['f'] = $argv[1];
}

function css_compress($buffer)
{
	/* Derived from http://davidwalsh.name/css-compression-php */
	return str_replace(': ',':',
		str_replace(';}','}',
		str_replace('; ',';',
		str_replace(' }','}',
		str_replace('{ ','{',
		str_replace(' {','{',
		str_replace(array("\r\n","\r","\n","\t",'  ','   ','    '),"",
		preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*'.'/!','',$buffer))))))));
}

header('Content-Type: text/css');

$filename = isset($_REQUEST['f']) ? $_REQUEST['f'] : '';
if ($filename != '') {
	if (file_exists($filename)) {
		// ... so compress the existing .css
		header('Content-Disposition: inline; filename="' . basename($filename) . '"');
		echo css_compress(file_get_contents($filename));  // inefficient!
	} else {
		// ... show not found blank css
		echo '/* File ' . $filename . ' not found */';
	}
} else {
	echo '/* File query missing. Use ?f=filename */';
}

/* eof */
