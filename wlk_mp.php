<?php

$plugin['version'] = '0.5.3';
$plugin['author'] = 'Walker Hamilton';
$plugin['author_uri'] = 'http://www.walkerhamilton.com';
$plugin['description'] = 'A magazine image manipulation tool.';

$plugin['type'] = 0; 


@include_once('zem_tpl.php');

if (0) {

?>
# --- BEGIN PLUGIN HELP ---
h1. wlk_mp
	
* v 0.5 - This fixes a bug in specifying the image category via the tag rather than the custom_field (both work now). Also did general housekeeping and cleanup. You can now specify the number of images to appear (when using category) by creating a custom field call "magnum" or using the attribute "number" within the tag.
* v 0.4 - This adds category selection to the plugin.
* v 0.3 - This version is a name-change version only.
	
This plugin is an integration of the Magazine Formatted Images as seen on ALA.

You're going to need to download "this zip":http://dev.signalfade.com/txp/wlk_mp_imagedotphp.zip and then upload it to your "files" directory to use this plugin.

h2. Installation

Since you can read this help, you have installed the plugin to txp.

Did you activate it?

h2. Usage

Place the @<txp:wlk_mp />@ tag in an article, form or page.

h2. Attributes

* *width*: specify entire width of magazine box.
* *padding*: specify padding between images	
* *alt*: specify yer alt tag (I'm just putting this in there)
* *number*: use only if specifying category, is the number of images you want returned.<br />(Defaults to 3)
* *sort*: use only if specifying category, can be 'ASC', 'DESC', or 'RAND()'. (Defaults to 'RAND()' - Sort is by date col [date uplaoded])

*images*: specify your images by id from the images list. should be a list of numbers, pipe-delimited (Ex. images = "1|5|8|12|16" )

or.....

*category* - specify a category from which to draw images (draws randomly is sort is not specified).

The selection tiers itself like so:

# Checks to see if a category is specified in the tag itself, if not goes on to...
# Checks to see if a category was specified in a custom_field called 'magcategory', if not goes on to....
# Checks to see if tag had an attribute called images and had image ids or uris specified, if not goes on to....
# Checks to see if the custom_field, magimages had content and if those were image uris or numbers.
# Then, if the category was set checks to see if a number of images is set in a custom_field called 'magnum'...if that's not found....
# Then, checks to see if a number of images is set in a property called 'number' in the tag itself.....if that's not found....
# defaults to 3 images from that category.

Complete Example: @<txp:wlk_mp width="600" padding="3" alt="magazine images" images="1|5|6|7" />@

h2. Putting the tag in a form means that every article tries to load this up. How do you specify images, then?

Easy enough. Go to "Admin" -> "Advanced Preferences" and create a custom field called "magimages". Then whenever you want to specify images for your article, click the "Advanced Options" on the left hand side of your article creation page and enter the image numbers with the pipe character between them. (Ex. images = 1|5|8|12|16 )

And don't worry about using up too many resources, if no images are specified, this plugin doesn't try and output anything! So you can have some articles with images and some without.

# --- END PLUGIN HELP ---
<?php

}

# --- BEGIN PLUGIN CODE ---
class magazinelayout {
	var $images = array();
	var $_numimages = 0;
	var $_fullwidth;
	var $_imagetemplate = "<img src=\"image.php?size=[size]&amp;file=[image]\" alt=\"\" />";
	var $_padding = 3;

	function magazinelayout($maxwidth=600,$padding=3,$imagetemplate='')
	{
		$this->_fullwidth = $maxwidth;
		$this->_padding = $padding;
		if ($imagetemplate != '') $this->_imagetemplate = $imagetemplate;
	}

	function _getFileExt($file)
	{
		$ext = explode(".", $file);
		if (count($ext) == 0) return '';
		return $ext[count($ext)-1];
	}

	function _transpose($arr)
	{
		$newarr = array();
		foreach($arr as $keyx => $valx) {
			foreach($valx as $keyy => $valy) {
				$newarr[$keyy][$keyx] = $valy;
			}
		}
		return $newarr;
	}

	function addImage($filename,$url='')
	{
		if ($url == '') $url = $filename;
		if (
			(strtolower($this->_getFileExt($filename)) != "jpg") &&
			(strtolower($this->_getFileExt($filename)) != "jpeg") &&
			(strtolower($this->_getFileExt($filename)) != "gif") &&
			(strtolower($this->_getFileExt($filename)) != "png")
			 ) {
			return false;
		}

		$imagesize = getimagesize($url);
		$w = $imagesize[0];
		$h = $imagesize[1];

		if (($h == 0) || ($w == 0)) return false;

		$ratio = $w / $h;

		$format = ($w > $h) ? 'landscape' : 'portrait';

		$this->_numimages++;

		$i = $this->_numimages - 1;
		$this->images[$i] = array();
		$this->images[$i]['filename'] = $filename;
		$this->images[$i]['url'] = $url;
		$this->images[$i]['format'] = $format;
		$this->images[$i]['ratio'] = $ratio;
		$this->images[$i]['w'] = $w; //Not currently used
		$this->images[$i]['h'] = $h; //Not currently used

		return true;
	}

	function insertImage($size,$name)
	{
		return str_replace('[image]',$name,str_replace('[size]',$size,$this->_imagetemplate));
	}

	function get1a($i1) {
		$s = floor($this->_fullwidth - ($this->_padding * 2));
		$html = '';
		$html .= "<div style=\"float: left; clear: both;\">".$this->insertImage(''.$s,$this->images[$i1]['filename'])."</div>\n";
		return $html;
	}

	function get2a($i1,$i2) {
		$a = $this->images[$i1]['ratio'];
		$b = $this->images[$i2]['ratio'];
		$t = $this->_fullwidth;
		$p = $this->_padding;

		$h1 = floor( (4*$p - $t) / (-$a - $b) );

		$html = '';
		$html .= "<div style=\"float: left; clear: both;\">".$this->insertImage('h'.$h1,$this->images[$i1]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('h'.$h1,$this->images[$i2]['filename'])."</div>\n";
		return $html;
	}

	function get3a($i1,$i2,$i3) {
		$a = $this->images[$i3]['ratio'];
		$b = $this->images[$i1]['ratio'];
		$c = $this->images[$i2]['ratio'];
		$t = $this->_fullwidth;
		$p = $this->_padding;

		$h1 = floor(
		(6 * $p - $t)
		/
		(-$a -$b -$c)
		);

		$html = '';
		$html .= "<div style=\"float: left; clear: both;\">".$this->insertImage('h'.$h1,$this->images[$i1]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('h'.$h1,$this->images[$i3]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('h'.$h1,$this->images[$i2]['filename'])."</div>\n";
		return $html;
	}


	function get3b($i1,$i2,$i3) {
		$a = $this->images[$i3]['ratio'];
		$b = $this->images[$i1]['ratio'];
		$c = $this->images[$i2]['ratio'];
		$t = $this->_fullwidth;
		$p = $this->_padding;

		$w1 = floor(
		-(
		(2 * $a * $b * $c * $p + 4 * $b * $c * $p - $b * $c * $t)
		/
		($a * $b + $c * $b + $a * $c)
		)
		);

		$w2 = floor(
		($a * (-4 * $b * $p + 2 * $b * $c * $p - 4 * $c * $p + $b * $t + $c * $t))
		/
		($a * $b + $c * $b + $a * $c)
		);

		$html = '';
		$html .= "<div style=\"float: right; clear: both;\">".$this->insertImage('w'.$w2,$this->images[$i3]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('w'.$w1,$this->images[$i1]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('w'.$w1,$this->images[$i2]['filename'])."</div>\n";

		return $html;
	}

	function get4a($i1,$i2,$i3,$i4) {
		$a = $this->images[$i1]['ratio'];
		$b = $this->images[$i2]['ratio'];
		$c = $this->images[$i3]['ratio'];
		$d = $this->images[$i4]['ratio'];
		$t = $this->_fullwidth;
		$p = $this->_padding;

		$h1 = floor(
		(8 * $p - $t)
		/
		(-$a -$b -$c -$d)
		);

		//$h1 = floor($this->_fullwidth / ($this->images[$p1]['ratio'] + $this->images[$p2]['ratio'] + $this->images[$p3]['ratio'] + $this->images[$p4]['ratio']));
		$html = '';
		$html .= "<div style=\"float: left; clear: both;\">".$this->insertImage('h'.$h1,$this->images[$i1]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('h'.$h1,$this->images[$i2]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('h'.$h1,$this->images[$i3]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('h'.$h1,$this->images[$i4]['filename'])."</div>\n";

		return $html;
	}



	function get4b($i1,$i2,$i3,$i4) {
		$a = $this->images[$i4]['ratio'];
		$b = $this->images[$i1]['ratio'];
		$c = $this->images[$i2]['ratio'];
		$d = $this->images[$i3]['ratio'];
		$t = $this->_fullwidth;
		$p = $this->_padding;

		$w1 = floor(
		-(
		(4 * $a * $b * $c * $d * $p + 4 * $b * $c * $d * $p - $b * $c * $d * $t)
		/
		($a * $b * $c + $a * $d * $c + $b * $d * $c + $a * $b * $d)
		)
		);

		$w2 = floor(
		-(
		(-4 * $p - (-(1/$c) -(1/$d) -(1/$b)) * (4 * $p - $t) )
		/
		( (1/$b) + (1/$c) + (1/$d) + (1/$a) )
		)
		);

		$html = '';
		$html .= "<div style=\"float: right; clear: both;\">".$this->insertImage('w'.$w2,$this->images[$i4]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('w'.$w1,$this->images[$i1]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('w'.$w1,$this->images[$i2]['filename'])."</div>\n";
		$html .= "<div style=\"float: left;\">".$this->insertImage('w'.$w1,$this->images[$i3]['filename'])."</div>\n";

		return $html;
	}


	function getHtml() {
		$this->images = $this->_transpose($this->images);
		array_multisort($this->images['format'], SORT_STRING, SORT_ASC, $this->images['url'], $this->images['ratio']);
		$this->images = $this->_transpose($this->images);

		$profile = '';
		foreach ($this->images as $i) {
			$profile .= $i['format'] == 'landscape' ? 'L' : 'P';
		}

		$html = '';
		$html .= "<div class=\"magazine-image\" style=\"width: ".$this->_fullwidth."px;\">\n";

		if ($this->_numimages == 1) {
			$html .= $this->get1a(0);
		}

		if ($this->_numimages == 2) {
			$html .= $this->get2a(0,1);
		}

		if ($this->_numimages == 3) {
			if ($profile == 'LLL') {
			  $html .= $this->get3b(0,1,2);
				//$html .= $this->get2a(1,2);
				//$html .= $this->get1a(0);
			} else {
				$html .= $this->get3b(0,1,2);
			}
		}

		if ($this->_numimages == 4) {

			if ($profile == 'LLLP') {
				$html .= $this->get4b(0,1,2,3);
			} elseif ($profile == 'LPPP') {
				$html .= $this->get3a(1,2,3);
				$html .= $this->get1a(0);
			} else {
				$html .= $this->get2a(2,0);
				$html .= $this->get2a(1,3);
			}
		}

		if ($this->_numimages == 5) {
			if ($profile == 'LLLLL') {
				$html .= $this->get3a(0,1,2);
				$html .= $this->get2a(3,4);
			} elseif ($profile == 'LLLLP') {
				$html .= $this->get3b(0,1,4);
				$html .= $this->get2a(2,3);
			} elseif ($profile == 'LLLPP') {
				$html .= $this->get3b(0,1,4);
				$html .= $this->get2a(2,3);
			} elseif ($profile == 'LLPPP') {
				$html .= $this->get3b(2,3,4);
				$html .= $this->get2a(0,1);
			} elseif ($profile == 'LPPPP') {
				$html .= $this->get3b(2,3,4);
				$html .= $this->get2a(0,1);
			} elseif ($profile == 'PPPPP') {
				$html .= $this->get2a(4,0);
				$html .= $this->get3a(1,2,3);
			}
		}

		if ($this->_numimages == 6) {
			if ($profile == 'LLLLLL') {
				$html .= $this->get2a(0,1);
				$html .= $this->get2a(2,3);
				$html .= $this->get2a(4,5);
			} elseif ($profile == 'LLLLLP') {
				$html .= $this->get4b(0,1,2,5);
				$html .= $this->get2a(3,4);
			} elseif ($profile == 'LLLLPP') {
				$html .= $this->get3b(0,1,4);
				$html .= $this->get3b(2,3,5);
			} elseif ($profile == 'LLLPPP') {
				$html .= $this->get3b(0,1,5);
				$html .= $this->get3b(2,3,4);
			} elseif ($profile == 'LLPPPP') {
				$html .= $this->get3b(0,2,4);
				$html .= $this->get3b(1,3,5);
			} elseif ($profile == 'LPPPPP') {
				$html .= $this->get3b(0,1,5);
				$html .= $this->get3a(2,3,4);
			} elseif ($profile == 'PPPPPP') {
				$html .= $this->get3a(3,4,5);
				$html .= $this->get3a(0,1,2);
			}
		}

		if ($this->_numimages == 7) {
			if ($profile == 'LLLLLLL') {
				$html .= $this->get3a(0,1,2);
				$html .= $this->get2a(3,4);
				$html .= $this->get2a(5,6);
			} elseif ($profile == 'LLLLLLP') {
				$html .= $this->get4b(0,1,2,6);
				$html .= $this->get3a(3,4,5);
			} elseif ($profile == 'LLLLLPP') {
				$html .= $this->get4b(0,1,2,5);
				$html .= $this->get3b(3,4,6);
			} elseif ($profile == 'LLLLPPP') {
				$html .= $this->get3b(0,1,5);
				$html .= $this->get4b(2,3,4,6);
			} elseif ($profile == 'LLLPPPP') {
				$html .= $this->get3b(0,1,5);
				$html .= $this->get4b(2,3,4,6);
			} elseif ($profile == 'LLPPPPP') {
				$html .= $this->get3a(4,5,6);
				$html .= $this->get2a(0,1);
				$html .= $this->get2a(2,3);
			} elseif ($profile == 'LPPPPPP') {
				$html .= $this->get3a(0,1,2);
				$html .= $this->get4b(3,4,5,6);
			} elseif ($profile == 'PPPPPPP') {
				$html .= $this->get4a(0,1,2,3);
				$html .= $this->get3b(4,5,6);
			}
		}

		if ($this->_numimages >= 8) {
			if ($profile == 'LLLLLLLL') {
				$html .= $this->get3a(0,1,2);
				$html .= $this->get2a(3,4);
				$html .= $this->get3a(5,6,7);
			} elseif ($profile == 'LLLLLLLP') {
				$html .= $this->get4b(0,1,2,7);
				$html .= $this->get2a(3,4);
				$html .= $this->get2a(5,6);
			} elseif ($profile == 'LLLLLLPP') {
				$html .= $this->get4b(0,1,2,6);
				$html .= $this->get4b(3,4,5,7);
			} elseif ($profile == 'LLLLLPPP') {
				$html .= $this->get4b(0,1,2,6);
				$html .= $this->get4b(3,4,5,7);
			} elseif ($profile == 'LLLLPPPP') {
				$html .= $this->get4b(0,1,2,6);
				$html .= $this->get4b(3,4,5,7);
			} elseif ($profile == 'LLLPPPPP') {
				$html .= $this->get3a(4,5,6);
				$html .= $this->get2a(0,1);
				$html .= $this->get3a(2,3,7);
			} elseif ($profile == 'LLPPPPPP') {
				$html .= $this->get3b(5,6,7);
				$html .= $this->get2a(0,1);
				$html .= $this->get3b(2,3,4);
			} elseif ($profile == 'LPPPPPPP') {
				$html .= $this->get3b(5,6,7);
				$html .= $this->get2a(0,1);
				$html .= $this->get3b(2,3,4);
			} elseif ($profile == 'PPPPPPP') {
				$html .= $this->get4a(0,1,2,3);
				$html .= $this->get4a(4,5,6,7);
			} else {
				$html .= $this->get3b(5,4,7);
				$html .= $this->get2a(1,0);
				$html .= $this->get3b(2,3,6);
			}
		}

		$html .= "<div style=\"clear: both;\"></div>\n</div>\n";

		return $html;
	}
}

function wlk_mp($atts) {
	global $prefs;
	global $permlink_mode;
	global $thisarticle;
	global $img_dir;

	extract(lAtts(array(
		'width'=> (!empty($prefs['wlk_mp_width']))?$prefs['wlk_mp_width']:'400',
		'padding'=> (!empty($prefs['wlk_mp_padding']))?$prefs['wlk_mp_padding']:'0',
		'alt'=> "Article Image",
		'images'=> (!empty($prefs['wlk_mp_images']))?$prefs['wlk_mp_images']:'',
		'category'=> (!empty($prefs['wlk_mp_category']))?$prefs['wlk_mp_images']:'',
		'number'=> (!empty($prefs['wlk_mp_number']))?$prefs['wlk_mp_number']:'3',
		'sort'=> (!empty($prefs['wlk_mp_sort']))?$prefs['wlk_mp_sort']:'RAND()',
		'debug'=> 'false'
	),$atts));

	if(!empty($thisarticle['magnum']))
	{
		$thenumber = $thisarticle['magnum'];
	} else {
		$thenumber = $number;
	}

	$template = "<img src=\"http://".$prefs['siteurl']."/files/image.php?size=[size]&amp;file=[image]\" alt=\"".$alt."\" />";
	$mag = new magazinelayout($width,$padding,$template);
	$imgcount=0;
	
	if($category!='')
	{
		$imagesarr = wlk_mp_get_images_from_category($category, $thenumber, $sort);
		if(count($imagesarr)>0)
		{
			foreach($imagesarr AS $img)
			{
				$walkimgfn = $img['id'].$img['ext'];
				$walkimgpath = hu.$prefs['img_dir'].'/'.$img['id'].$img['ext'];
				$mag->addImage($walkimgfn, $walkimgpath);
				$imgcount++;
			}
		}
	}
	else if(!empty($thisarticle['magcategory']))
	{
		$imagesarr = wlk_mp_get_images_from_category($thisarticle['magcategory'], $thenumber, $sort);
		if(count($imagesarr)>0)
		{
			foreach($imagesarr AS $img)
			{
				$walkimgfn = $img['id'].$img['ext'];
				$walkimgpath = hu.$prefs['img_dir'].'/'.$img['id'].$img['ext'];
				$mag->addImage($walkimgfn, $walkimgpath);
				$imgcount++;
			}
		}
	}
	else if(!empty($images))
	{
		$imagesarr = explode("|", $images);
		if(count($imagesarr)>0)
		{
			foreach($imagesarr AS $img)
			{
				$walkimgfn = wlk_mp_get_image($img);
				if(count($walkimgfn)>0)
				{
					$walkimgpath = hu.$prefs['img_dir'].'/'.$walkimgfn;
					$mag->addImage($walkimgfn, $walkimgpath);
					$imgcount++;
				}
			}
		}
	}
	else
	{
		if(!empty($thisarticle['magimages']))
		{
			$imagesarr = explode("|", $thisarticle['magimages']);
			if(count($imagesarr)>0)
			{
				foreach($imagesarr AS $img)
				{
					$walkimgfn = wlk_mp_get_image($img);
					if(count($walkimgfn)>0)
					{
						$walkimgpath = hu.$prefs['img_dir'].'/'.$walkimgfn;
						$mag->addImage($walkimgfn, $walkimgpath);
						$imgcount++;
					}
				}
			} else {
				$msg[] = 'txp:wlk_mp Error: No images defined. Use the attribut images or a custom-field named magimages to define image ids';
			}
		}
		else
		{
			$msg[] = 'txp:wlk_mp Error: No images defined. Use the attribut images or a custom-field named magimages to define image ids';
		}
	}

	if($imgcount!=0)
	{
		$completed = $mag->getHtml();
		return $completed;
	} else {
		return 'No images returned.';
	}
}

//-----------------------------------
//				Get Image
//------------------------------------

    function wlk_mp_get_image($id)
    {
    	global $img_dir;
    	$rs = safe_row("*", "txp_image", "id='$id' limit 1");
    	if($rs)
    	{
    		extract($rs);
    		$out = $id.$ext;
    		return $out;
    	}
    }

    function wlk_mp_get_images_from_category($catname, $thenum, $assort)
    {
    	global $img_dir;
    	$q = "category='$catname' ORDER BY `date` $assort limit $thenum";
    	$rs = safe_rows("id, ext", "txp_image", $q);
    	if($rs)
    	{
    		return $rs;
    	}
    }

	
# --- END PLUGIN CODE ---

?>