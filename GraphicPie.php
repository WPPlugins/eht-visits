<?php

define ("DARK_FACTOR", 0.65);

$width = $_REQUEST["width"];
$height = $_REQUEST["height"];
$border = $_REQUEST["border"];
$from = $_REQUEST["from"];

$image = imagecreatetruecolor ($width, $height + $border);

$color = hexdec ($_REQUEST["transparent"]);
$red = (($color & 0x00FF0000) >> 16);
$green = (($color & 0x0000FF00) >> 8);
$blue = ($color & 0x000000FF);
$transparent = imagecolorallocate ($image, $red, $green, $blue);

$count = $_REQUEST["count"];
$realCount = 0;
$colors = array ();
for ($i = 0; $i < $count; $i++)
{
	$value = $_REQUEST["value$i"];
	if ($value > 0)
	{
		$colors[$i] = array ();
		$color = hexdec ($_REQUEST["color$i"]);
		$red = (($color & 0x00FF0000) >> 16);
		$green = (($color & 0x0000FF00) >> 8);
		$blue = ($color & 0x000000FF);
		$colors[$i]["color"] = imagecolorallocate ($image, $red, $green, $blue);
		$colors[$i]["darkColor"] = imagecolorallocate ($image, $red * DARK_FACTOR, $green * DARK_FACTOR, $blue * DARK_FACTOR);
		$colors[$i]["value"] = $value;
		$realCount++;
	}
}

imagefilledrectangle ($image, 0, 0, $width, $height + $border, $transparent);
imagecolortransparent ($image, $transparent);

$halfWidth = $width / 2;
$end = $height / 2;
$start = $end + $border; 
for ($i = $start; $i >= $end; $i--)
{
	$angle = $from;
	for ($j = 0; $j < $realCount; $j++, $angle += $increment)
	{
		$increment = $colors[$j]["value"] * 3.6;
		imagefilledarc ($image, 
						$halfWidth, $i, $width - 1, $height - 1,
						$angle, $angle + $increment, 
						$colors[$j][($i == $end) ? "color" : "darkColor"], 
						IMG_ARC_PIE);	
	}
}

header ("Content-type: image/png");
imagepng ($image);
imagedestroy ($image);

?>