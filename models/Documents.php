<?php


class Documents
{
	private $name;
	private $documentID;
	private $documentURL;
	private $createdAt;
	private $updatedAt;
	private $entityID;
    public function __construct() {

    }
    public function post() {

    }
    public function createFromExisting($api_host,$id) {
		//error_log("uploadPic", 0);
		$rename_file = null;
		$filebase = md5(time());
		$imageFileType = pathinfo($_FILES['fileToUpload']['name'],PATHINFO_EXTENSION);
		$filename = $filebase . $imageFileType; //".jpg"; //"profile.jpg";//$_GET['file'];
		$target_directory = "/var/www/files/" . "users/".floor($uid / 65535)."/".$uid."/";
		//error_log("Image Directory:".$target_directory, 0);
		//$imageFileType = pathinfo($_FILES['fileToUpload']['name'],PATHINFO_EXTENSION);
		$target_file = $target_directory.$filename; //basename($_FILES["fileToUpload"]["name"]);
		$uploadOk = 1;
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 20000000) {
			/* File Too Large */
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "zip") {
			/* Only JPG, JPEG, PNG, GIF, PDF & ZIP files are allowed */
			$uploadOk = 0;
		}
		// Check if file already exists
		if ((file_exists($target_file)) && ($uploadOk == 1)) {
			$i = count(glob($target_directory . $filename)) + 1;
			$rf = $filebase . "_".$i.$imageFileType;
			$rename_file = $target_directory.$rf;
			rename($target_file,$rename_file);
		}
		if (($this->status == 0) && ($uploadOk == 1)) {
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				// file was not uploaded
			} else {
				mkdir("/var/www/files/" . "users/".floor($uid / 65535)."/".$uid."/", 0755, true);
				file_put_contents($target_file, file_get_contents($_REQUEST['file']));
				// Load the documents data to send notification
				$this->load($api_host,$id);
				$data = array(
					"name"=>$name,
					"documentID"=>$filename,
					"documentURL"=>$documentURL,
					"entityID"=>$this->entityID,
					"createdAt" => date('Y-m-d H:i:s'),
					"updatedAt" => date('Y-m-d H:i:s')
				);
				$url = $api_host."/api/documents/";
				$options = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($data)
					)
				);
				$context  = stream_context_create($options);
				try {
					$result = json_decode(file_get_contents($url,false,$context),true);
					if ($result > 0) {
						return "success";
					} else {
						return "failed";
					}
				} catch (Exception $e) {
					return $e;
				}
			}
		}
		return "failed";
	}
    public function load($api_host,$id) {
      $args = array(
            "transform"=>"1"
      );
      $url = $api_host."/api/documents/".$id."?".http_build_query($args);
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
}