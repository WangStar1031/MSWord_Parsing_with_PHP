<?php
class DocxConversion{
    private $filename;

    public function __construct($filePath) {
        $this->filename = $filePath;
    }

    private function read_doc() {
        $fileHandle = fopen($this->filename, "r");
        $line = @fread($fileHandle, filesize($this->filename));   
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach($lines as $thisline)
          {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
              {
              } else {
                $outtext .= $thisline." ";
              }
          }
         $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        return $outtext;
    }

    private function read_docx(){

        $striped_content = '';
        $DocContent = '';
        $FooterContent = '';

        $zip = zip_open($this->filename);

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (!(zip_entry_name($zip_entry) == "word/document.xml" || zip_entry_name($zip_entry) == "word/footnotes.xml")) continue;
            if( zip_entry_name($zip_entry) == "word/document.xml" )
                $DocContent .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            if( zip_entry_name($zip_entry) == "word/footnotes.xml" )
                $FooterContent .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            zip_entry_close($zip_entry);
        }// end while

        zip_close($zip);
        file_put_contents("33.txt", $DocContent);
        $DocContent = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $DocContent);
        $DocContent = str_replace('</w:r></w:p>', "\r\n", $DocContent);
        $DocContent = str_replace('<w:rStyle w:val="FootnoteReference"/>', '<w:rStyle w:val="FootnoteReference"/> ', $DocContent);
        $DocContent = str_replace('–', " ", $DocContent);
        $DocContent = str_replace(" ", " ", $DocContent);
        $DocContent = str_replace('<w:proofErr w:type="spellEnd"/>', "\r\n", $DocContent);
        
        // $DocContent = str_replace('<w:pStyle w:val="Heading3"/><w:numPr><w:ilvl w:val="0"/><w:numId w:val="0"/>', "", $DocContent);
        $startPos = 0;
        while( ($pos = strpos( $DocContent, '<w:pStyle w:val="Heading', $startPos)) !== false){
            $numPos = $pos + strlen('<w:pStyle w:val="Heading');
            $afterLen = strlen('"/><w:numPr><w:ilvl w:val="0"/><w:numId w:val="0"/>');
            if( substr($DocContent, $numPos + 1, $afterLen) == '"/><w:numPr><w:ilvl w:val="0"/><w:numId w:val="0"/>'){
                $nHeadingNum = substr($DocContent, $numPos, 1);
                $DocContent = str_replace('<w:pStyle w:val="Heading'.$nHeadingNum.'"/><w:numPr><w:ilvl w:val="0"/><w:numId w:val="0"/>', '', $DocContent);
            }
            $startPos = $numPos;
        }
        $DocContent = str_replace('<w:pStyle w:val="ListParagraph"/><w:spacing w:line="360" w:lineRule="auto"/>', "", $DocContent);
        $DocContent = str_replace('<w:t xml:space="preserve">: </w:t>', "", $DocContent);

        $DocContent = str_replace("\r", " ", $DocContent);
        $startPos = 0;
        $pos = 0;
        while( ($pos = strpos($DocContent, '<w:pStyle w:val="', $startPos)) != false){
            $endPos = strpos($DocContent, "/>", $pos);
            $beforeContent = substr($DocContent, 0, $pos);
            $afterContent = substr($DocContent, $endPos + strlen("/>"));
            $DocContent = $beforeContent . " vuletine " . $afterContent;
            $startPos = $pos;
        }
        $startPos = 0;
        $pos = 0;
        while( ($pos = strpos($DocContent, "<w:instrText", $startPos)) != false){
            $endPos = strpos($DocContent, "</w:instrText>", $pos);
            $beforeContent = substr($DocContent, 0, $pos);
            $afterContent = substr($DocContent, $endPos + strlen("</w:instrText>"));
            $DocContent = $beforeContent . $afterContent;
            $startPos = $pos;
        }
        $striped_Doc_content = strip_tags($DocContent);
        file_put_contents("44.txt", $striped_Doc_content);

        // exit();
        $FooterContent = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $FooterContent);
        $FooterContent = str_replace('</w:r></w:p>', "\r\n", $FooterContent);
        $FooterContent = str_replace('–', " ", $FooterContent);
        $FooterContent = str_replace(" ", " ", $FooterContent);
        $FooterContent = str_replace('<w:proofErr w:type="spellEnd"/>', "\r\n", $FooterContent);
        // $FooterContent = str_replace('<w:pStyle w:val="Heading3"/><w:numPr><w:ilvl w:val="0"/><w:numId w:val="0"/>', "", $FooterContent);
        // $FooterContent = str_replace('<w:pStyle w:val="ListParagraph"/><w:spacing w:line="360" w:lineRule="auto"/>', "", $FooterContent);
        $FooterContent = str_replace('<w:t xml:space="preserve">: </w:t>', "", $FooterContent);
        // $startPos = 0;
        // $pos = 0;
        // while( ($pos = strpos($FooterContent, '<w:pStyle w:val="', $startPos)) != false){
        //     $endPos = strpos($FooterContent, "/>", $pos);
        //     $beforeContent = substr($FooterContent, 0, $pos);
        //     $afterContent = substr($FooterContent, $endPos + strlen("</w:instrText>"));
        //     $FooterContent = $beforeContent . " vuletine " . $afterContent;
        //     $startPos = $pos;
        // }
        $startPos = 0;
        $pos = 0;
        while( ($pos = strpos($FooterContent, "<w:instrText", $startPos)) != false){
            $endPos = strpos($FooterContent, "</w:instrText>", $pos);
            $beforeContent = substr($FooterContent, 0, $pos);
            $afterContent = substr($FooterContent, $endPos + strlen("</w:instrText>"));
            $FooterContent = $beforeContent . $afterContent;
            $startPos = $pos;
        }
        $striped_Footer_content = strip_tags($FooterContent);

        $striped_content = $striped_Doc_content . $striped_Footer_content;
        return $striped_content;
    }

 /************************excel sheet************************************/

    function xlsx_to_text($input_file){
        $xml_filename = "xl/sharedStrings.xml"; //content file name
        $zip_handle = new ZipArchive;
        $output_text = "";
        if(true === $zip_handle->open($input_file)){
            if(($xml_index = $zip_handle->locateName($xml_filename)) !== false){
                $xml_datas = $zip_handle->getFromIndex($xml_index);
                $xml_handle = DOMDocument::loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $output_text = strip_tags($xml_handle->saveXML());
            }else{
                $output_text .="";
            }
            $zip_handle->close();
        }else{
        $output_text .="";
        }
        return $output_text;
    }

    /*************************power point files*****************************/
    function pptx_to_text($input_file){
        $zip_handle = new ZipArchive;
        $output_text = "";
        if(true === $zip_handle->open($input_file)){
            $slide_number = 1; //loop through slide files
            while(($xml_index = $zip_handle->locateName("ppt/slides/slide".$slide_number.".xml")) !== false){
                $xml_datas = $zip_handle->getFromIndex($xml_index);
                $xml_handle = DOMDocument::loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $output_text .= strip_tags($xml_handle->saveXML());
                $slide_number++;
            }
            if($slide_number == 1){
                $output_text .="";
            }
            $zip_handle->close();
        }else{
        $output_text .="";
        }
        return $output_text;
    }


    public function convertToText() {

        if(isset($this->filename) && !file_exists($this->filename)) {
            return "File Not exists";
        }

        $fileArray = pathinfo($this->filename);
        $file_ext  = $fileArray['extension'];
        if($file_ext == "doc" || $file_ext == "docx" || $file_ext == "xlsx" || $file_ext == "pptx")
        {
            if($file_ext == "doc") {
                return $this->read_doc();
            } elseif($file_ext == "docx") {
                return $this->read_docx();
            } elseif($file_ext == "xlsx") {
                return $this->xlsx_to_text();
            }elseif($file_ext == "pptx") {
                return $this->pptx_to_text();
            }
        } else {
            return "Invalid File Type";
        }
    }

}
?>