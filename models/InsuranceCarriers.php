<?php
class InsuranceCarrier
{
	private $id;
	private $entityID;
	private $name;
	private $contactName;
	private $contactEmail;
	private $contactPhone;
	private $policyNumber;
	private $policyExpirationDate;
	private $fileupload;
	private $status;
	private $createdAt;
	private $updatedAt;

    public function __construct() {

    }

    public function post($api_host,$id) {

    }

    public function uploadPolicy($http_host,$file_location,$fileupload,$fileName,$entityID) {
		$rename_file = null;
		$filebase = pathinfo($fileupload['name'],PATHINFO_FILENAME);;//md5(time());
		$imageFileType = strtolower(pathinfo($fileupload['name'],PATHINFO_EXTENSION));
		$filename = $filebase . "." .$imageFileType;
		$target_directory = $file_location . "users/".floor($entityID / 65535)."/".$entityID."/";
		$target_file = $target_directory.$filename;
		$uploadOk = 1;
		// Check file size
		if ($fileupload["size"] > 20000000) {
			// File Too Large
			$uploadOk = 0;
			//return "File too big";
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" && $imageFileType != "zip") {
			// Only JPG, JPEG, PNG, GIF, PDF, doc, docx & ZIP files are allowed
			$uploadOk = 0;
			//return "Invalid extention -> " . $imageFileType;
		}
		// Check if file already exists
		if ((file_exists($target_file)) && ($uploadOk == 1)) {
			$i = count(glob($target_directory . $filename)) + 1;
			$rf = $filebase . "_".$i.$imageFileType;
			$rename_file = $target_directory.$rf;
			rename($target_file,$rename_file);
		}
		if ($uploadOk == 1) { //($this->status == 0) && (
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				// file was not uploaded
				return "failed";
			} else {
				// make user file directory
				try { mkdir($file_location . "users/".floor($entityID / 65535)."/".$entityID."/", 0755, true); } catch(Exception $e) {/*echo 'Message: ' .$e->getMessage();*/}
				if ( file_put_contents($target_file, file_get_contents($fileupload["tmp_name"])) ) {
				    return "success";
				} else {
				    return "failed";
				}
			}
		}
		return "failed";
	}

    public function load($api_host,$id) {
      $args = array(
            "transform"=>"1"
      );
      $url = $api_host . "/" . API_ROOT . "/documents/".$id."?".http_build_query($args);
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'GET'
          )
      );
      $context  = stream_context_create($options);
      $result = json_decode(file_get_contents($url,false,$context),true);
      $this->entityID = $result["entityID"];
      $this->name = $result["name"];
      $this->documentID = $result["documentID"];
      $this->documentURL = $result["documentURL"];
      $this->createdAt = $result["createdAt"];
      $this->updatedAt = $result["updatedAt"];
    }

    public function put($locationid,$address1,$address2,$city,$state,$zip) {
        try {

        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 401 Unauthorized');
              header('Content-Type: text/plain; charset=utf8');
              return $e->getMessage();
        }
    }

    public function delete() {

    }

    public function viewpolicy($entityID,$file_location,$filename) {
		$dir = $file_location . "users/".floor($entityID / 65535)."/".$entityID."/";
		$file = $dir . $filename;
		$fileType = pathinfo($filename,PATHINFO_EXTENSION);
		//$mime_type="";
		//if(@is_array(getimagesize($mediapath))){
		//	$mime_type = "image/jpeg, image/png, image/bmp, image/gif";
		//} else {
		//	$mime_type = "application/".$fileType;
		//}
		$mime_type = "application/".$fileType;

		if(file_exists($file)){
			// Try and open the remote stream
			if (!$stream = fopen($file, 'r')) {
				// If opening failed, inform the client we have no content
				header('HTTP/1.1 500 Internal Server Error');
				exit('Unable to open remote stream');
			}
			// It's probably an idea to remove the execution time limit - on Windows hosts
			// this could result in the audio stream cutting off mid-flow
			set_time_limit(0);
			//header("Content-Transfer-Encoding: binary");
			//header('content-type: application/octet-stream');
    		header('Content-type: {$mime_type}');
    		header('Content-length: ' . filesize($file));
    		header('Content-Disposition: filename="' . $filename);
    		header('X-Pad: avoid browser bug');
    		header('Cache-Control: no-cache');
    		readfile($file);
  			// Send the data
  			//fpassthru($stream);
		}else{
			$dir = $file_location . "images/users/default";
			$filename = "default.png";
			$file = $dir . "/" . $filename;
			$mime_type = "image/jpeg, image/png, image/bmp, image/gif";
			if(file_exists($file)){
				// Try and open the remote stream
				if (!$stream = fopen($file, 'r')) {
					// If opening failed, inform the client we have no content
					header('HTTP/1.1 500 Internal Server Error');
					exit('Unable to open remote stream');
				}
				// It's probably an idea to remove the execution time limit - on Windows hosts
				// this could result in the audio stream cutting off mid-flow
				set_time_limit(0);
				//header("Content-Transfer-Encoding: binary");
				//header('content-type: application/octet-stream');
    			header('Content-type: {$mime_type}');
    			header('Content-length: ' . filesize($file));
    			header('Content-Disposition: filename="' . $filename);
    			header('X-Pad: avoid browser bug');
    			header('Cache-Control: no-cache');
    			readfile($file);
			} else {
    			header("HTTP/1.0 404 Not Found");
    		}
		}
    }
}
