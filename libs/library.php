<?php 
// $document should contain an HTML document. 
// This will remove HTML tags, javascript sections 
// and white space. It will also convert some 
// common HTML entities to their text equivalent. 

function html2text($document){
  $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript 
                 "'<[/!]*?[^<>]*?>'si",          // Strip out HTML tags 
                 "'([rn])[s]+'",                // Strip out white space 
                 "'&(quot|#34);'i",                // Replace HTML entities 
                 "'&(amp|#38);'i", 
                 "'&(lt|#60);'i", 
                 "'&(gt|#62);'i", 
                 "'&(nbsp|#160);'i", 
                 "'&(iexcl|#161);'i", 
                 "'&(cent|#162);'i", 
                 "'&(pound|#163);'i", 
                 "'&(copy|#169);'i", 
                 "'&#(d+);'e");                    // evaluate as php 

  $replace = array ("", 
                 "", 
                 "\1", 
                 "\"", 
                 "&", 
                 "<", 
                 ">", 
                 " ", 
                 chr(161), 
                 chr(162), 
                 chr(163), 
                 chr(169), 
                 "chr(\1)"); 

$text = preg_replace($search, $replace, $document);
}
?>