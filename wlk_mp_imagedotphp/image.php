<?php

//for wlk_mp v0.5
if (!isset($_GET['file']) || !isset($_GET['size'])) {
	echo "Image variables not specified correctly";
	exit();
}
$file = '../images/'.$_GET['file'];
$size = $_GET['size'];
list($width, $height) = getimagesize($file);
$ratio = $width / $height;
if (substr($size, 0, 1) == 'h') {
	$type = 'fixedheight';
} elseif (substr($size, 0, 1) == 'w') {
	$type = 'fixedwidth';
} elseif ($height > $width) {
	$type = 'fixedheight';
} else {
	$type = 'fixedwidth';
}
if ($type == 'fixedheight') {
	$new_width = floor(str_replace('h','',$size) * $ratio);
	$new_height = str_replace('h','',$size);
} else {
	$new_width = str_replace('w','',$size);
	$new_height = floor(str_replace('w','',$size) / $ratio);
}
$new_image = imagecreatetruecolor($new_width, $new_height);
$old_image = imagecreatefromjpeg($file);
imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
header('Content-type: image/jpeg');
imagejpeg($new_image, null, 100);
exit();
?>