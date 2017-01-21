<?php
/** @prefix : <http://purl.org/net/ns/doas#> . <> a :PHPScript;
 :title "An IIIF Manifest generator";
 :created "2017-01-21";
 :creator <http://purl.org/net/who/kanzaki#masahide> ;
 :release [:revision "0.40"; :created "2017-01-21"];
 :description """ generates an IIIF manifest for acompanying iiif-simple.php.
- looks up IMG_ROOT defined in iiif-simple.php, this scipt generates image resource objects for all JPEG images in IMG_ROOT, with servuce@id set to appropriate iiif-simple.php service URIs.
- If change the image dir path with a form, IMG_ROOT should be changed accordingly.
"""
*/

define("SERVICE_SCRIPT", "iiif-simple.php");
if(($imgpath = filter_input(INPUT_GET, "imgpath"))){
	$svuri = filter_input(INPUT_GET, "svuri");
	gen_manifest($imgpath, $svuri);
}elseif($argv[1] and $argv[2]){
	gen_manifest($argv[1], $argv[2]);
}else{
	$svscript = SERVICE_SCRIPT.(preg_match("/\.php/", SERVICE_SCRIPT) ? "" : ".php");
	if(file_exists($svscript)){
		include_once($svscript);
		$imgpath = IMG_ROOT;
	}
	gen_form($imgpath);
}

//generates an IIIF manifest JSON
function gen_manifest($imgpath, $svuri){
	$imgfiles = get_file_list($imgpath);
	$baseuri = get_base_uri($svuri);
	$canvas = array();
	$page = 1;
	foreach($imgfiles as $imgf){
		gen_canvas($canvas, $imgpath, $imgf, $page, $svuri, $baseuri);
	}
	print json_encode(array(
		"@context"=>"http://iiif.io/api/presentation/2/context.json",
		"@type" => "sc:Manifest",
		"label"=>"give me a name",
		"sequences" => array(array("@type" => "sc:Sequence", "canvases" => $canvas))
	),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
	);
}

//generates one canvas for the given image
function gen_canvas(&$canvas, $imgpath, $imgf, &$page, $svuri, $baseuri){
	$imgwh = getimagesize($imgpath . $imgf);
	$canvas[] = array(
		"@id" => $baseuri."canvas/p".$page,
		"@type" => "sc:Canvas",
		"label" => "page $page",
		"width" => $imgwh[0],
		"height" => $imgwh[1],
		"images" => array(gen_imgrsource($imgf, $page, $svuri, $baseuri))
	);
	$page++;
}

//generates an image resource with IIIF service for the given image
function gen_imgrsource($imgf, $page, $svuri, $baseuri){
	return array(
		"@type" => "oa:Annotation",
		"motivation" => "sc:painting",
		"resource" => array(
			"@id" => $baseuri.SERVICE_SCRIPT."/$imgf",
			//content resources MAY have width/height, but not required
			"service" => array(
				"@context" => "http://iiif.io/api/image/2/context.json",
				"@id" => $svuri."/".$imgf,
				"profile" => "http://iiif.io/api/image/2/level1.json"
			)
		),
		"on" => $baseuri."canvas/p".$page
	);
}

// get a base URI
function get_base_uri($uri){
	return join("/", array_slice(explode("/", $uri), 0, -1))."/";
}

//get a list of JPEG files in the given directory
function get_file_list($imgpath){
	$files = array();
	$dir = opendir($imgpath);
	while(($f = readdir($dir))) if(preg_match("/\.jpg$/", $f)) $files[] = $f;
	closedir($dir);
	return $files;
}

//generates an input form page
function gen_form($imgpath){
	$svuri = preg_replace("![^/]+$!", SERVICE_SCRIPT, $_SERVER["SCRIPT_URI"]);
	print <<<EOF
<title>IIIF manifest generator</title>
<h1>IIIF manifest generator</h1>
<form action="" method="get">
<table>
<tr><td>Path to JPEG image dir</td><td><input type="text" size="60" name="imgpath" value="$imgpath"/></td></tr>
<tr><td>Base URI of IIIF service</td><td><input type="text" size="60" name="svuri" value="$svuri"/></td></tr>
<tr><td>Generate a manifest</td><td><input type="submit"/></td></tr>
</table>
</form>

EOF;
}
