<?php
class FlickrHelper extends AppHelper {
	function buildPhotoUrl($photo, $size = 'medium') {
		//receives an array (can use the individual photo data returned
		//from an API call) and returns a URL (doesn't mean that the
		//file size exists)
		$sizes = array(
			"square" => "_s",
			"thumbnail" => "_t",
			"small" => "_m",
			"medium" => "",
			"large" => "_b",
			"original" => "_o"
		);
		
		$size = strtolower($size);
		if (!array_key_exists($size, $sizes)) {
			$size = "medium";
		}
		
		if ($size == "original") {
			$url = "http://farm" . $photo['farm'] . ".static.flickr.com/" . $photo['server'] . "/" . $photo['id'] . "_" . $photo['originalsecret'] . "_o" . "." . $photo['originalformat'];
		} else {
			$url = "http://farm" . $photo['farm'] . ".static.flickr.com/" . $photo['server'] . "/" . $photo['id'] . "_" . $photo['secret'] . $sizes[$size] . ".jpg";
		}
		return $this->output($url);
	}
}
?>