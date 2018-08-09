<?php
	$target_dir = "uploads/";
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

	if ($_FILES["fileToUpload"]["size"] > 500000) {
	    echo "Sorry, your file is too large.";
	    $uploadOk = 0;
	}
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";

		include 'doc2txt.php';
		$object = new DocxConversion($target_file);
		$txt = $object->convertToText();

		$specificChr = " ";
		$txt = str_replace( $specificChr, "", $txt);
		$specificChr = " ";
		$txt = str_replace( $specificChr, " ", $txt);
		// print_r($txt);
		file_put_contents("11.txt", $txt);
		echo "<br/>";
		$arrSentences = explode("\n", $txt);
		$txt = implode(" ", $arrSentences);
		$arrWords = explode(" ", $txt);
		file_put_contents("22.txt", json_encode($arrWords));
		$count = 0;
		for( $i = 0; $i < count($arrWords); $i++){
			$word = $arrWords[$i];
			if( $word == "" || $word == " " || $word == "\r")
				continue;
			echo $word . "<br/>";
			$count ++;;
		}
		echo $count;
    } else {
        echo "Sorry, there was an error uploading your file.";
    }


// $str = file_get_contents($target_file);

// function count_words($string)
// {
// $string = htmlspecialchars_decode(strip_tags($string));
// if (strlen($string)==0)
// return 0;
// $t = array(' '=>1, '_'=>1, "\x20"=>1, "\xA0"=>1, "\x0A"=>1, "\x0D"=>1, "\x09"=>1, "\x0B"=>1, "\x2E"=>1, "\t"=>1, '='=>1, '+'=>1, '-'=>1, '*'=>1, '/'=>1, '\\'=>1, ','=>1, '.'=>1, ';'=>1, ':'=>1, '"'=>1, '\''=>1, '['=>1, ']'=>1, '{'=>1, '}'=>1, '('=>1, ')'=>1, '<'=>1, '>'=>1, '&'=>1, '%'=>1, '$'=>1, '@'=>1, '#'=>1, '^'=>1, '!'=>1, '?'=>1); // separators
// $count= isset($t[$string[0]])? 0:1;
// if (strlen($string)==1)
// return $count;
// for ($i=1;$i<strlen($string);$i++)
// if (isset($t[$string[$i-1]]) && !isset($t[$string[$i]])) // if new word starts
// $count++;
// return $count;
// }
// echo count_words($str);
?>