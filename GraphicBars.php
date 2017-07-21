<?php

define ("DARK_FACTOR", 0.65);

$height = $_REQUEST["height"];
$barWidth = $_REQUEST["bar"];
$border = $_REQUEST["border"];
$font = $_REQUEST["font"];
$x = $_REQUEST["x"];
$y = $_REQUEST["y"];

$space = (($y + 1) * $border);
$width = ((($barWidth + $space) * $x) + $border);

$image = imagecreatetruecolor ($width, $height);

$color = hexdec ($_REQUEST["transparent"]);
$red = (($color & 0x00FF0000) >> 16);
$green = (($color & 0x0000FF00) >> 8);
$blue = ($color & 0x000000FF);
$transparent = imagecolorallocate ($image, $red, $green, $blue);

$color = hexdec ($_REQUEST["text"]);
$red = (($color & 0x00FF0000) >> 16);
$green = (($color & 0x0000FF00) >> 8);
$blue = ($color & 0x000000FF);
$textColor = imagecolorallocate ($image, $red, $green, $blue);

$colors = array ();
for ($i = 0; $i < $y; $i++)
{
	$colors[$i] = array ();
	$color = hexdec ($_REQUEST["color$i"]);
	$red = (($color & 0x00FF0000) >> 16);
	$green = (($color & 0x0000FF00) >> 8);
	$blue = ($color & 0x000000FF);
	$colors[$i]["color"] = imagecolorallocate ($image, $red, $green, $blue);
	$colors[$i]["darkColor"] = imagecolorallocate ($image, $red * DARK_FACTOR, $green * DARK_FACTOR, $blue * DARK_FACTOR);
	$colors[$i]["values"] = array ();
	for ($j = 0; $j < $x; $j++)
	{
		$value = $_REQUEST["value$i-$j"];
		$colors[$i]["values"][$j] = $value;
		if ((($i == 0) && ($j == 0)) || ($max < $value))
		{
			$max = $value;
		}
		if ((($i == 0) && ($j == 0)) || ($min > $value))
		{
			$min = $value;
		}
	}
}

$texts = array ();
for ($i = 0; $i < $x; $i++)
{
	$texts[$i] = $_REQUEST["text$i"];
}

imagefilledrectangle ($image, 0, 0, $width, $height, $transparent);
imagecolortransparent ($image, $transparent);

$spaceLeft = $border;
$spaceBottom = 10;
$fontSize = 6;
for ($i = $y - 1; $i >= 0; $i--)
{
	$offsetX = $spaceLeft + ($i * $border);
	$offsetY = $height - $spaceBottom - ($i * $border);
	$barColor = $colors[$i]["color"];
	$barDarkColor = $colors[$i]["darkColor"];
	for ($j = 0; $j < $x; $j++)
	{
		$value = $colors[$i]["values"][$j];
		$barHeight = floor ((($height - $space - $spaceBottom) * $value) / $max);
		for ($k = 1; $k <= $border; $k++)
		{
			imagefilledrectangle ($image,
								  $offsetX + $k, $offsetY - $barHeight - $k,
								  $offsetX + $barWidth + $k, $offsetY - $k,
								  $barDarkColor);
		}
		imagefilledrectangle ($image,
							  $offsetX, $offsetY - $barHeight,
							  $offsetX + $barWidth, $offsetY,
							  $barColor);

		if ($barHeight > $fontSize)
		{
			$size = imagettfbbox ($fontSize, 0, $font, $value);
			imagettftext ($image, $fontSize, 0,
						  $offsetX + floor (($barWidth - ($size[2] - $size[0])) / 2), $offsetY - $barHeight + $fontSize + 1,
						  $textColor, $font,
						  $value);
		}
		
		$offsetX += $barWidth + $space;
	}
}

$offsetX = $spaceLeft;
$offsetY = $height;
for ($j = 0; $j < $x; $j++)
{
	$size = imagettfbbox ($fontSize, 0, $font, $texts[$j]);
	imagettftext ($image, $fontSize, 0,
				  $offsetX + floor (($barWidth + $space - ($size[2] - $size[0])) / 2), 
				  $offsetY,
				  $textColor, $font,
				  $texts[$j]);
	$offsetX += $barWidth + $space;
}

header ("Content-type: image/png");
imagepng ($image);
imagedestroy ($image);

?>