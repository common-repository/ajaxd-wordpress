<?php
	if(extension_loaded('zlib')){
		$z = strtolower(ini_get('zlib.output_compression'));
		if ($z == false || $z == 'off')
			ob_start('ob_gzhandler');
	}
	header ("content-type: text/javascript; charset: UTF-8");
	$offset = 60 * 60 * 24;
	$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
	header ($expire);
	include('core.cache.js');
?>
