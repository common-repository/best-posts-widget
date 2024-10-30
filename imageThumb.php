<?php


$picname = resizepics($_REQUEST['i'], $_REQUEST['w'], $_REQUEST['h']);
//Error
die( "<font color=\"#FF0066\"><center><b>File not exists :(<b></center></FONT>");
//Funcion resizepics
function resizepics($pics, $newwidth, $newheight){

	if ( $pics{0} == '/' ) {
	 $pics =substr ( $pics, 1 );
	}
	

     if(preg_match("/.jpg/i", "$pics")){
           header('Content-type: image/jpeg');
     }
     if (preg_match("/.gif/i", "$pics")){
           header('Content-type: image/gif');
     }
     list($width, $height) = getimagesize($pics);
     
     
     //if($width > $height && $newheight < $height){
       $newheight = $height / ($width / $newwidth);
     //} else if ($width < $height && $newwidth < $width) {
     //  $newwidth = $width / ($height / $newheight);   
    // } else {
     //  $newwidth = $width;
     //  $newheight = $height;
   //}
   if(preg_match("/.jpg/i", "$pics")){
   $source = imagecreatefromjpeg($pics);
   }
   if(preg_match("/.jpeg/i", "$pics")){
   $source = imagecreatefromjpeg($pics);
   }
   if(preg_match("/.jpeg/i", "$pics")){
   $source = Imagecreatefromjpeg($pics);
   }
   if(preg_match("/.png/i", "$pics")){
   $source = imagecreatefrompng($pics);
   }
   if(preg_match("/.gif/i", "$pics")){
   $source = imagecreatefromgif($pics);
   }
   $thumb = imagecreatetruecolor($newwidth, $newheight);
   //imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
   imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
   
   //return imagejpeg($thumb);
   
   if(preg_match("/.jpg/i", "$pics")){
   return imagejpeg($thumb,null,100);
   }
   if(preg_match("/.jpeg/i", "$pics")){
   return imagejpeg($thumb,null,100);
   }
   if(preg_match("/.jpeg/i", "$pics")){
   return imagejpeg($thumb,null,100);
   }
   if(preg_match("/.png/i", "$pics")){
   return imagepng($thumb,null,100);
   }
   if(preg_match("/.gif/i", "$pics")){
   return imagegif($thumb,null,100);
   }
 }
?>
