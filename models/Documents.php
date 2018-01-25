<?php

class Documents
{

    /**
     * The table name
     *
     * @var string
     */
    public $table = "documents";

    public function post($api_host,$id) {

    }

    public function viewdocument($entityID,$file_location,$filename) {
		$dir = $file_location . "/users/".floor($entityID / 65535)."/".$entityID;
		$file = $dir . "/" . $filename;
		$fileType = pathinfo($filename,PATHINFO_EXTENSION);
		$mime_type="";
		if(@is_array(getimagesize($mediapath))){
			$mime_type = "image/jpeg, image/png, image/bmp, image/gif";
		} else {
			$mime_type = "application/".strtolower($fileType);
		}
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

    public function createFromExisting($api_host,$file_location,$fileupload,$name,$documentID,$documentURL,$updatedAt,$entityID) {
		$rename_file = null;
		$filebase = pathinfo($fileupload['name'],PATHINFO_FILENAME);;//md5(time());
		$imageFileType = strtolower(pathinfo($fileupload['name'],PATHINFO_EXTENSION));
		$filename = $filebase . "." .$imageFileType;
		$target_directory = $file_location . "/users/".floor($entityID / 65535)."/".$entityID."/";
		$target_file = $target_directory.$filename;
		$uploadOk = 1;

		// Check file size
		if ($fileupload["size"] > 20000000) {
			// File Too Large
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" && $imageFileType != "zip") {
			// Only JPG, JPEG, PNG, GIF, PDF, doc, docx & ZIP files are allowed
			$uploadOk = 0;
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
				try { mkdir($file_location . "/users/".floor($entityID / 65535)."/".$entityID."/", 0755, true); } catch(Exception $e) {/*echo 'Message: ' .$e->getMessage();*/}
				file_put_contents($target_file, file_get_contents($fileupload["tmp_name"]));
				// Load the documents data to send notification
				//$this->load($api_host,$id);
				$data = array(
					"name"=>$name,
					"documentID"=>$filename,
					"documentURL"=>$documentURL,
				    //"content"=> Flight::db()->($fileupload["tmp_name"]);
					"entityID"=>$entityID,
					"createdAt" => date('Y-m-d H:i:s'), //$updatedAt
					"updatedAt" => date('Y-m-d H:i:s')
				);
				$url = $api_host . "/" . API_ROOT. "/documents/";
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
						return $result;
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

    public function bulkUploadORIG($api_host,$http_host,$file_location,$fileupload,$name,$documentID,$documentURL,$updatedAt,$entityID) {
		$rename_file = null;
		$filebase = pathinfo($fileupload['name'],PATHINFO_FILENAME);
		$imageFileType = strtolower(pathinfo($fileupload['name'],PATHINFO_EXTENSION));
		$filename = $filebase . "." .$imageFileType;
		$target_directory = $file_location . "/users/".floor($entityID / 65535)."/".$entityID."/";
		$target_file = $target_directory.$filename;
		$uploadOk = 1;

		// Check file size
		if ($fileupload["size"] > 20000000) {
			// File Too Large
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "csv") {
			// Only CSV files are allowed
			$uploadOk = 0;
		}
		// Check if file already exists
		if ((file_exists($target_file)) && ($uploadOk == 1)) {
			$i = count(glob($target_directory . $filename)) + 1;
			$rf = $filebase . "_".$i.".".$imageFileType;
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
				try { mkdir($file_location . "/users/".floor($entityID / 65535)."/".$entityID."/", 0755, true); } catch(Exception $e) {/*echo 'Message: ' .$e->getMessage();*/}
				file_put_contents($target_file, file_get_contents($fileupload["tmp_name"]));
				// Load the documents data to send notification
				//$this->load($api_host,$id);

				/*
				try {
					$result = json_decode(file_get_contents($url,false,$context),true);
					if ($result > 0) {
				*/
						$file = fopen($target_file, 'r');
						$counter=0;
						$goodCounter=0;
						$badCounter=0;
						$failureReason=array();
						$contacts_iterator=array();
						$needs_iterator=array();
						while (($line = fgetcsv($file)) !== FALSE) {
							//$line is an array of the csv elements
							if ($counter==0) {
								foreach ($line as $key => $value) {
									$pieces = explode(" ", $value);
									if ($pieces[0]=="Contact") {
										$contacts_iterator[$key]=$value;
									} else {
										if ($pieces[0]=="Trailer") {
											$needs_iterator[$key]=strtolower($value);
										}
									}
								}
							} else {
								$data = array(
									"address1"=>$line[4],
									"city"=>$line[3],
									"state"=>$line[5],
									"zip"=>$line[6],
									"entityID" => $entityID,
									"locationType" => "Origination"
								);
								$url = $http_host."/getlocationbycitystatezip";
								$options = array(
									'http' => array(
										'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
										'method'  => 'POST',
										'content' => http_build_query($data)
									)
								);
								$context  = stream_context_create($options);
								try {
									$result = file_get_contents($url,false,$context);
									//return $url.":".file_get_contents($url,false,$context);
									if ($result == "success") {
										$data = array(
											"address1"=>$line[8],
											"city"=>$line[7],
											"state"=>$line[9],
											"zip"=>$line[10],
											"entityID" => $entityID,
											"locationType" => "Destination"
										);
										$url = $http_host."/getlocationbycitystatezip";
										$options = array(
											'http' => array(
												'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
												'method'  => 'POST',
												'content' => http_build_query($data)
											)
										);
										$context  = stream_context_create($options);
										try {
											$result = file_get_contents($url,false,$context);
											if ($result == "success") {
											    echo $line[5];
											    echo $line[6];
											    die();
												$originationaddress = $line[4] . ' ' + $line[3] + ' ' + $line[5] + ' ' + $line[6];
												$destinationaddress = $line[8] . ' ' + $line[7] + ' ' + $line[9] + ' ' + $line[10];
												$originationlatitude="";
												$originationlongitude="";
												$originationformatted_address="";
												$destinationlatitude="";
												$destinationlongitude="";
												$destinationformatted_address="";
												// get latitude, longitude and formatted address
												$data_arr = $this->geocode($originationaddress);
												echo $data_arr;
												die();
												if ($data_arr) {
													$originationlatitude = $data_arr[0];
													$originationlongitude = $data_arr[1];
													$originationformatted_address = $data_arr[2];
												} else {
													$badCounter++;
													$failureReason[$counter]=$counter.":Origination address not found.";
												}
												// get latitude, longitude and formatted address
												$data_arr = $this->geocode($destinationaddress);
												if ($data_arr) {
													$destinationlatitude = $data_arr[0];
													$destinationlongitude = $data_arr[1];
													$destinationformatted_address = $data_arr[2];
												} else {
													$badCounter++;
													$failureReason[$counter]=$counter.":Destination address not found.";
												}
												if (!isset($failureReason[$counter])) {
													/*
													if ($("#id").val() > '') {
														var url = '<?php echo API_HOST_URL . "/carrier_needs" ?>/' + $("#id").val();
														type = "PUT";
													} else {
														var url = '<?php echo API_HOST_URL . "/carrier_needs" ?>';
														type = "POST";
													}
													*/
													if ((isset($line[0])) && ($line[0] != '')) {
													    $url = $api_host  . "/" . API_ROOT. "/carrier_needs/" . $line[0];
														$type = "PUT";
													} else {
													    $url = $api_host . "/" . API_ROOT. "/carrier_needs";
														$type = "POST";
													}
													/*
													 // Build the contacts
													var contactsarray = [];
													var obj = $("#check-list-box li.active");
													for (var i = 0; i < obj.length; i++) {
														item = {};
														item[obj[i].id] = obj[i].innerText;
														contactsarray.push(item);
													}
													var $contacts = contactsarray;
													*/
													$contacts= array();
													foreach ($contacts_iterator as $key => $value) {
														$pieces = explode(" ", $value);
														$contactIndex=substr($pieces[1], 1,-1);
														unset($pieces[0]);
														unset($pieces[1]);
														$contactName = implode(" ", $pieces);
														if (strtolower($line[$key])=="yes") {
															$contacts[$contactIndex]=$contactName;
														}
													}
													if (count($contacts)==0) {
														$badCounter++;
														$failureReason[$counter]=$counter.":No contact selected.";
													}
													/*
													// Build the needsDataPoints
													var needsarray = [];
													var obj = $("#dp-check-list-box li select");
													for (var i = 0; i < obj.length; i++) {
														item = {};
														item[obj[i].id] = obj[i].value;
														needsarray.push(item);
													}
													var needsdatapoints = needsarray;
													*/
													$needsDataPoints= array();
													foreach ($needs_iterator as $key => $value) {
														$dataName = explode(" ", $value)[1];
														$pieces = explode("(", $value);
														$dataValues=substr($pieces[1], 0,-1);
														$pieces = explode("/", $dataValues);
														if (in_array(strtolower($line[$key]), $pieces)) {
															$needsDataPoints[$dataName]=$line[$key];
														} else {
															$badCounter++;
															$failureReason[$counter]=$counter.":Invalid need option for option ".$key."-".$value.": ".$line[$key].": possible values are ".implode(",",$pieces);
														}
													}

													if (!isset($failureReason[$counter])) {
														$dttime=new DateTime();
														$dttime->format('Y-m-d H:i:s');
														if ($type == "PUT") {
															$data = array(
																"entityID"=>$entityID,
																"qty" => $line[0],
																"transportationMode" => $line[11],
																"originationAddress1" => $line[4],
																"originationCity" => $line[3],
																"originationState" => $line[5],
																"originationZip" => $line[6],
																"destinationAddress1"=>$line[8],
																"destinationCity"=>$line[7],
																"destinationState"=>$line[9],
																"destinationZip"=>$line[10],
																"originationLat"=>$originationlatitude,
																"originationLng"=>$originationlongitude,
																"destinationLat"=>$destinationlatitude,
																"destinationLng"=>$destinationlongitude,
																"needsDataPoints"=>$needsDataPoints,
																"status"=>'Available',
																"contactEmails"=>$contacts,
																"availableDate"=>$line[1],
																"expirationDate"=>$line[2],
																"createdAt"=>$dttime,
																"updatedAt"=>$dttime
															);
														} else {
															$data = array(
																"entityID"=>$entityID,
																"qty" => $line[0],
																"transportationMode" => $line[11],
																"originationAddress1" => $line[4],
																"originationCity" => $line[3],
																"originationState" => $line[5],
																"originationZip" => $line[6],
																"destinationAddress1"=>$line[8],
																"destinationCity"=>$line[7],
																"destinationState"=>$line[9],
																"destinationZip"=>$line[10],
																"originationLat"=>$originationlatitude,
																"originationLng"=>$originationlongitude,
																"destinationLat"=>$destinationlatitude,
																"destinationLng"=>$destinationlongitude,
																"needsDataPoints"=>$needsDataPoints,
																"status"=>'Available',
																"contactEmails"=>$contacts,
																"availableDate"=>$line[1],
																"expirationDate"=>$line[2],
																"createdAt"=>$dttime,
																"updatedAt"=>$dttime
															);
														}
														/*
														$.ajax({
														 url: url,
														 type: type,
														 data: JSON.stringify(data),
														 contentType: "application/json",
														 async: false,
														*/
														$options = array(
															'http' => array(
																'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
																'method'  => 'POST',
																'content' => http_build_query($data)
															)
														);
														$context = stream_context_create($options);
														try {
															//$result = json_decode(file_get_contents($url,false,$context));
															$result = file_get_contents($url,false,$context);
															/*
															 success: function(data){
																if (data > 0) {
															*/
															//return $result;
															//return $url;
															//return http_build_query($data);

															if ($result > 0) {
															//if ($result == "success") {
																/*
															  		if (type == 'POST') {
																	var params = {id: data};
																*/
																if ($type == 'POST') {
																	$data = array(
																		"id" => $result
																	);
																	$url = $http_host."/api/carrierneedsnotification";
																	/*
																			$.ajax({
																			   url: '<?php echo HTTP_HOST."/carrierneedsnotification" ?>',
																			   type: 'POST',
																			   data: JSON.stringify(params),
																	 },
																	*/
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
																		/*
																		 success: function(notification){
																			  alert(notification);
																		   },
																		*/
																		if ($result > 0) {
																			//success
																		} else {
																			/*
																				   error: function() {
																					  alert('Failed Sending Notifications! - Notify NEC of this failure.');
																				   }
																				});
																			  }
																			} else {
																			  alert("Adding Need Failed! Invalid Data...");
																			}
																			*/
																			//failed sending notification
																		}
																		$goodCounter++;
																	} catch (Exception $e) {
																		$badCounter++;
																		$failureReason[$counter]=$counter.":There Was An Error Adding Location!";
																		//return $e;
																	}
																	//return $result;
																}
															} else {
																/*
																 error: function() {
																	alert("There Was An Error Adding Location!");
																 }
																});
																*/
																$badCounter++;
																$failureReason[$counter]=$counter.":Adding Need Failed! Invalid Data...";
																//return "failed";
															}
														} catch (Exception $e) {
															$badCounter++;
															$failureReason[$counter]=$counter.":There Was An Error Adding Location! ".$e->getMessage();
															//return $e;
														}
													}
												}
											}
										} catch (Exception $e) {
											$badCounter++;
											$failureReason[$counter]=$counter.":There Was An Error Adding Location!";
											//return $e;
										}
									} else {
										$badCounter++;
										$failureReason[$counter]=$counter.":".$result;
										//return "failed";
									}
								} catch (Exception $e) {
									$badCounter++;
									$failureReason[$counter]=$counter.":No results returned!".$e->getMessage();
									//return $e;
								}
							}
							$counter++;
						}
						fclose($file);
						for ($i = 1; $i < $counter-1; $i++) {
							if (!isset($failureReason[$counter])) {
								$failureReason[$counter]="Row #".$i.": Imported successfully.";
							} else {
								$failureReason[$counter]="Row #".$i.": ".$failureReason[$counter];
							}
						}
						$result = $failureReason;
						return json_encode($result, 128);
				/*
					} else {
						return "failed";
					}
				} catch (Exception $e) {
					return $e;
				}
				*/
			}
		}
		return "failed";
	}

	public function carrierBulkUpload($api_host,$http_host,$file_location,$fileupload,$name,$documentID,$documentURL,$updatedAt,$entityID) {
		$rename_file = null;
		$filebase = pathinfo($fileupload['name'],PATHINFO_FILENAME);
		$imageFileType = strtolower(pathinfo($fileupload['name'],PATHINFO_EXTENSION));
		$filename = $filebase . "." .$imageFileType;
		$target_directory = $file_location . "/users/".floor($entityID / 65535)."/".$entityID."/";
		$target_file = $target_directory.$filename;
		$uploadOk = 1;

		//Get entity configuration_settings
		$entity = '';
        $entity = json_decode(file_get_contents(API_HOST_URL . '/entities?filter[]=id,eq,' . $entityID . '&transform=1'));
        $configuration_settings = $entity->entities[0]->configuration_settings;

        $need_expire_days = 30; // This is the default for how many days until expired if entity has not set one
        for ($cs=0;$cs<count($configuration_settings);$cs++) {
            while (list($key, $val) = each($configuration_settings[$cs])) {
                //echo "$key => $val\n";
                if ($key == "need_expire_days") {
                    $need_expire_days = $val;
                }
            }
        }

		// Check file size
		if ($fileupload["size"] > 20000000) {
			// File Too Large
			$uploadOk = 0;
		}

		// Allow certain file formats
		if($imageFileType != "csv") {
			// Only CSV files are allowed
			$uploadOk = 0;
		}

		// Check if file already exists
		if ((file_exists($target_file)) && ($uploadOk == 1)) {
			$i = count(glob($target_directory . $filename)) + 1;
			$rf = $filebase . "_".$i.".".$imageFileType;
			$rename_file = $target_directory.$rf;
			rename($target_file,$rename_file);
		}

		if ($uploadOk == 1) {

				// make user file directory
				try { mkdir($file_location . "/users/".floor($entityID / 65535)."/".$entityID."/", 0755, true); } catch(Exception $e) {/*echo 'Message: ' .$e->getMessage();*/}
				file_put_contents($target_file, file_get_contents($fileupload["tmp_name"]));

                $file = fopen($target_file, 'r');
                $counter=0;
                $goodCounter=0;
                $badCounter=0;
                $failureReason=array();
                $contacts_iterator=array();
                $needs_iterator=array();

                while ( ($line = fgetcsv($file) ) !== false) {
                    //$line is an array of the csv elements
                    if ($counter == 0) {
                        /*
                        foreach ($line as $key => $value) {
                            $pieces = explode(" ", $value);
                            if ($pieces[0]=="Contact") {
                                $contacts_iterator[$key]=$value;
                            } else if ($pieces[0]=="Trailer") {
                                $needs_iterator[$key]=strtolower($value);
                            }
                        }
                        */

                        $counter++; // Increment after we have processed the column header row

                    } else {

                        $data = array(
                            "address1"=>$line[4],
                            "city"=>$line[3],
                            "state"=>$line[5],
                            "zip"=>$line[6],
                            "entityID" => $entityID,
                            "locationType" => "Origination"
                        );

                        try {
                            // Create the address in the locations table
                            // url encode the address
                            $address = urlencode($line[4].", ".$line[3].", ".$line[5].", ".$line[6]);

                            // google map geocode api url
                            $url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key=".GOOGLE_MAPS_API;

                            // get the json response
                            $resp_json = file_get_contents($url);

                            // decode the json
                            $resp = json_decode($resp_json, true);

                            // response status will be 'OK', if able to geocode given address
                            if($resp['status']=='OK') {

                                // get the important data
                                $olati = $resp['results'][0]['geometry']['location']['lat'];
                                $olongi = $resp['results'][0]['geometry']['location']['lng'];
                                $oformatted_address = $resp['results'][0]['formatted_address'];

                                $result = "success";

                            } else {

                                $result = "Origination Geocode Failed";
                                echo $result;

                            }

                            if ($result == "success") {
                                $data = array(
                                    "address1"=>$line[8],
                                    "city"=>$line[7],
                                    "state"=>$line[9],
                                    "zip"=>$line[10],
                                    "entityID" => $entityID,
                                    "locationType" => "Destination"
                                );
                                try {
                                    // Create the address in the locations table
                                    // url encode the address
                                    $address = urlencode($line[8].", ".$line[7].", ".$line[9].", ".$line[10]);

                                    // google map geocode api url
                                    $url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key=".GOOGLE_MAPS_API;

                                    // get the json response
                                    $resp_json = file_get_contents($url);

                                    // decode the json
                                    $resp = json_decode($resp_json, true);

                                    // response status will be 'OK', if able to geocode given address
                                    if($resp['status']=='OK') {

                                        // get the important data
                                        $dlati = $resp['results'][0]['geometry']['location']['lat'];
                                        $dlongi = $resp['results'][0]['geometry']['location']['lng'];
                                        $dformatted_address = $resp['results'][0]['formatted_address'];

                                        $result = "success";

                                    } else {

                                        $result = "Destination Geocode Failed";
                                        echo $result;

                                    }


                                    if ($result == "success") {

                                        $originationaddress = $line[4] . ' ' . $line[3] . ' ' . $line[5] . ' ' . $line[6];
                                        $destinationaddress = $line[8] . ' ' . $line[7] . ' ' . $line[9] . ' ' . $line[10];
                                        $originationlatitude = $olati;
                                        $originationlongitude = $olongi;
                                        $originationformatted_address = $oformatted_address;
                                        $destinationlatitude = $olati;
                                        $destinationlongitude = $olongi;
                                        $destinationformatted_address = $oformatted_address;

                                        $url = $api_host . "/" . API_ROOT. "/carrier_needs";
                                        $type = "POST";
/*
                                        if ((isset($line[0])) && ($line[0] != '')) {
                                            $url = $api_host . "/" . API_ROOT. "/carrier_needs/" . $line[0];
                                            $type = "PUT";
                                        } else {
                                            $url = $api_host . "/" . API_ROOT. "/carrier_needs";
                                            $type = "POST";
                                        }

*/

                                        $needsDataPoints[] = array("length"=>"$line[12]");
                                        $needsDataPoints[] = array("width"=>"$line[13]");
                                        $needsDataPoints[] = array("height"=>"$line[14]");
                                        $needsDataPoints[] = array("carb"=>"$line[15]");
                                        $needsDataPoints[] = array("decals"=>"$line[16]");
                                        $needsDataPoints[] = array("door"=>"$line[17]");
                                        $needsDataPoints[] = array("floor"=>"$line[18]");
                                        $needsDataPoints[] = array("king_pin"=>"$line[19]");
                                        $needsDataPoints[] = array("lift_pads"=>"$line[20]");
                                        $needsDataPoints[] = array("num_axles"=>"$line[21]");
                                        $needsDataPoints[] = array("railable"=>"$line[22]");
                                        $needsDataPoints[] = array("side_skirts"=>"$line[23]");
                                        $needsDataPoints[] = array("suspension"=>"$line[24]");
                                        $needsDataPoints[] = array("type"=>"$line[25]");

                                        $contacts = json_decode($line[26]);

                                        if ($line[2] == "") {
                                            $expirationDate = new DateTime($line[1]);
                                            $expirationDate->add(new DateInterval('P'.$need_expire_days.'D'));
                                            $expirationDate = $expirationDate->format('Y-m-d');
                                        } else {
                                            $expirationDate = $line[2]; // Use the expiration date from the uploaded file
                                        }

                                        $dttime = date('Y-m-d H:i:s');
                                        if ($type == "PUT") {
                                            $data = array(
                                                "entityID"=>$entityID,
                                                "qty" => $line[0],
                                                "transportationMode" => $line[11],
                                                "originationAddress1" => $line[4],
                                                "originationCity" => $line[3],
                                                "originationState" => $line[5],
                                                "originationZip" => $line[6],
                                                "destinationAddress1"=>$line[8],
                                                "destinationCity"=>$line[7],
                                                "destinationState"=>$line[9],
                                                "destinationZip"=>$line[10],
                                                "originationLat"=>$originationlatitude,
                                                "originationLng"=>$originationlongitude,
                                                "destinationLat"=>$destinationlatitude,
                                                "destinationLng"=>$destinationlongitude,
                                                "needsDataPoints"=>$needsDataPoints,
                                                "status"=>'Available',
                                                "contactEmails"=>$contacts,
                                                "availableDate"=>$line[1],
                                                "expirationDate"=>$expirationDate,
                                                "createdAt"=>$dttime,
                                                "updatedAt"=>$dttime
                                            );
                                        } else {
                                            $data = array(
                                                "entityID"=>$entityID,
                                                "qty" => $line[0],
                                                "transportationMode" => $line[11],
                                                "originationAddress1" => $line[4],
                                                "originationCity" => $line[3],
                                                "originationState" => $line[5],
                                                "originationZip" => $line[6],
                                                "destinationAddress1"=>$line[8],
                                                "destinationCity"=>$line[7],
                                                "destinationState"=>$line[9],
                                                "destinationZip"=>$line[10],
                                                "originationLat"=>$originationlatitude,
                                                "originationLng"=>$originationlongitude,
                                                "destinationLat"=>$destinationlatitude,
                                                "destinationLng"=>$destinationlongitude,
                                                "needsDataPoints"=>$needsDataPoints,
                                                "status"=>'Available',
                                                "contactEmails"=>[$contacts],
                                                "availableDate"=>$line[1],
                                                "expirationDate"=>$expirationDate,
                                                "createdAt"=>$dttime,
                                                "updatedAt"=>$dttime
                                            );
                                        }

                                        $options = array(
                                            'http' => array(
                                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                                'method'  => $type,
                                                'content' => http_build_query($data)
                                            )
                                        );

                                        $context = stream_context_create($options);
                                        try {
                                            //$result = json_decode(file_get_contents($url,false,$context));
                                            $result = file_get_contents($url,false,$context);
                                        } catch (Exception $e) {
                                            return $e;
                                        } // Try

                                    } // If

                                } catch (Exception $e) {
                                    return $e;
                                } // Try

                            } // If

                        } catch (Exception $e) {
                            return $e;
                        } // Try

                    } // If
                    $counter++;

                } // While

                fclose($file);

		} else {
		    return "uploadOk was 0 - File failed uploading.";
        } // If
		return "success";
	}

	public function customerBulkUpload($api_host,$http_host,$file_location,$fileupload,$name,$documentID,$documentURL,$updatedAt,$entityID) {
		$rename_file = null;
		$filebase = pathinfo($fileupload['name'],PATHINFO_FILENAME);
		$imageFileType = strtolower(pathinfo($fileupload['name'],PATHINFO_EXTENSION));
		$filename = $filebase . "." .$imageFileType;
		$target_directory = $file_location . "/users/".floor($entityID / 65535)."/".$entityID."/";
		$target_file = $target_directory.$filename;
		$uploadOk = 1;


		//Get entity configuration_settings
		$entity = '';
        $entity = json_decode(file_get_contents(API_HOST_URL . '/entities?filter[]=id,eq,' . $entityID . '&transform=1'));
        $configuration_settings = $entity->entities[0]->configuration_settings;

        $availability_expire_days = 30; // This is the default for how many days until expired if entity has not set one
        for ($cs=0;$cs<count($configuration_settings);$cs++) {
            while (list($key, $val) = each($configuration_settings[$cs])) {
                //echo "$key => $val\n";
                if ($key == "availability_expire_days") {
                    $availability_expire_days = $val;
                }
            }
        }


		// Check file size
		if ($fileupload["size"] > 20000000) {
			// File Too Large
			$uploadOk = 0;
		}

		// Allow certain file formats
		if($imageFileType != "csv") {
			// Only CSV files are allowed
			$uploadOk = 0;
		}

		// Check if file already exists
		if ((file_exists($target_file)) && ($uploadOk == 1)) {
			$i = count(glob($target_directory . $filename)) + 1;
			$rf = $filebase . "_".$i.".".$imageFileType;
			$rename_file = $target_directory.$rf;
			rename($target_file,$rename_file);
		}

		if ($uploadOk == 1) {

				// make user file directory
				try { mkdir($file_location . "/users/".floor($entityID / 65535)."/".$entityID."/", 0755, true); } catch(Exception $e) {/*echo 'Message: ' .$e->getMessage();*/}
				file_put_contents($target_file, file_get_contents($fileupload["tmp_name"]));

                $file = fopen($target_file, 'r');
                $counter=0;
                $goodCounter=0;
                $badCounter=0;
                $failureReason=array();
                $contacts_iterator=array();
                $needs_iterator=array();

                while ( ($line = fgetcsv($file) ) !== false) {
                    //$line is an array of the csv elements
                    if ($counter == 0) {
                        /*
                        foreach ($line as $key => $value) {
                            $pieces = explode(" ", $value);
                            if ($pieces[0]=="Contact") {
                                $contacts_iterator[$key]=$value;
                            } else if ($pieces[0]=="Trailer") {
                                $needs_iterator[$key]=strtolower($value);
                            }
                        }
                        */

                        $counter++; // Increment after we have processed the column header row

                    } else {

                        $data = array(
                            "address1"=>$line[4],
                            "city"=>$line[3],
                            "state"=>$line[5],
                            "zip"=>$line[6],
                            "entityID" => $entityID,
                            "locationType" => "Origination"
                        );

                        try {
                            // Create the address in the locations table
                            // url encode the address
                            $address = urlencode($line[4].", ".$line[3].", ".$line[5].", ".$line[6]);

                            // google map geocode api url
                            $url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key=".GOOGLE_MAPS_API;

                            // get the json response
                            $resp_json = file_get_contents($url);

                            // decode the json
                            $resp = json_decode($resp_json, true);

                            // response status will be 'OK', if able to geocode given address
                            if($resp['status']=='OK') {

                                // get the important data
                                $olati = $resp['results'][0]['geometry']['location']['lat'];
                                $olongi = $resp['results'][0]['geometry']['location']['lng'];
                                $oformatted_address = $resp['results'][0]['formatted_address'];

                                $result = "success";

                            } else {

                                $result = "Origination Geocode Failed";
                                echo $result;

                            }

                            if ($result == "success") {
                                $data = array(
                                    "address1"=>$line[8],
                                    "city"=>$line[7],
                                    "state"=>$line[9],
                                    "zip"=>$line[10],
                                    "entityID" => $entityID,
                                    "locationType" => "Destination"
                                );
                                try {
                                    // Create the address in the locations table
                                    // url encode the address
                                    $address = urlencode($line[8].", ".$line[7].", ".$line[9].", ".$line[10]);

                                    // google map geocode api url
                                    $url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key=".GOOGLE_MAPS_API;

                                    // get the json response
                                    $resp_json = file_get_contents($url);

                                    // decode the json
                                    $resp = json_decode($resp_json, true);

                                    // response status will be 'OK', if able to geocode given address
                                    if($resp['status']=='OK') {

                                        // get the important data
                                        $dlati = $resp['results'][0]['geometry']['location']['lat'];
                                        $dlongi = $resp['results'][0]['geometry']['location']['lng'];
                                        $dformatted_address = $resp['results'][0]['formatted_address'];

                                        $result = "success";

                                    } else {

                                        $result = "Destination Geocode Failed";
                                        echo $result;

                                    }


                                    if ($result == "success") {

                                        $originationaddress = $line[4] . ' ' . $line[3] . ' ' . $line[5] . ' ' . $line[6];
                                        $destinationaddress = $line[8] . ' ' . $line[7] . ' ' . $line[9] . ' ' . $line[10];
                                        $originationlatitude = $olati;
                                        $originationlongitude = $olongi;
                                        $originationformatted_address = $oformatted_address;
                                        $destinationlatitude = $olati;
                                        $destinationlongitude = $olongi;
                                        $destinationformatted_address = $oformatted_address;

                                        $url = $api_host  . "/" . API_ROOT. "/customer_needs";
                                        $type = "POST";
/*
                                        if ((isset($line[0])) && ($line[0] != '')) {
                                            $url = $api_host  . "/" . API_ROOT. "/api/carrier_needs/" . $line[0];
                                            $type = "PUT";
                                        } else {
                                            $url = $api_host  . "/" . API_ROOT. "/api/carrier_needs";
                                            $type = "POST";
                                        }

*/

                                        $needsDataPoints[] = array("type"=>"$line[14]");
                                        $needsDataPoints[] = array("length"=>"$line[15]");
                                        $needsDataPoints[] = array("width"=>"$line[16]");
                                        $needsDataPoints[] = array("height"=>"$line[17]");
                                        $needsDataPoints[] = array("carb"=>"$line[18]");
                                        $needsDataPoints[] = array("decals"=>"$line[19]");
                                        $needsDataPoints[] = array("door"=>"$line[20]");
                                        $needsDataPoints[] = array("floor"=>"$line[21]");
                                        $needsDataPoints[] = array("king_pin"=>"$line[22]");
                                        $needsDataPoints[] = array("lift_pads"=>"$line[23]");
                                        $needsDataPoints[] = array("num_axles"=>"$line[24]");
                                        $needsDataPoints[] = array("railable"=>"$line[25]");
                                        $needsDataPoints[] = array("side_skirts"=>"$line[26]");
                                        $needsDataPoints[] = array("suspension"=>"$line[27]");

                                        $contacts = json_decode($line[28]);

                                        if ($line[2] == "") {
                                            $expirationDate = new DateTime($line[1]);
                                            $expirationDate->add(new DateInterval('P'.$availability_expire_days.'D'));
                                            $expirationDate = $expirationDate->format('Y-m-d');
                                        } else {
                                            $expirationDate = $line[2]; // Use the expiration date from the uploaded file
                                        }

                                        $dttime = date('Y-m-d H:i:s');
                                        if ($type == "PUT") {
                                            $data = array(
                                                "entityID"=>$entityID,
                                                "qty" => $line[0],
                                                "rate" => $line[11],
                                                "rateType" => $line[12],
                                                "transportationMode" => $line[13],
                                                "originationAddress1" => $line[4],
                                                "originationCity" => $line[3],
                                                "originationState" => $line[5],
                                                "originationZip" => $line[6],
                                                "destinationAddress1"=>$line[8],
                                                "destinationCity"=>$line[7],
                                                "destinationState"=>$line[9],
                                                "destinationZip"=>$line[10],
                                                "originationLat"=>$originationlatitude,
                                                "originationLng"=>$originationlongitude,
                                                "destinationLat"=>$destinationlatitude,
                                                "destinationLng"=>$destinationlongitude,
                                                "needsDataPoints"=>$needsDataPoints,
                                                "status"=>'Available',
                                                "contactEmails"=>[$contacts],
                                                "availableDate"=>$line[1],
                                                "expirationDate"=>$expirationDate,
                                                "createdAt"=>$dttime,
                                                "updatedAt"=>$dttime
                                            );
                                        } else {
                                            $data = array(
                                                "entityID"=>$entityID,
                                                "qty" => $line[0],
                                                "rate" => $line[11],
                                                "rateType" => $line[12],
                                                "transportationMode" => $line[13],
                                                "originationAddress1" => $line[4],
                                                "originationCity" => $line[3],
                                                "originationState" => $line[5],
                                                "originationZip" => $line[6],
                                                "destinationAddress1"=>$line[8],
                                                "destinationCity"=>$line[7],
                                                "destinationState"=>$line[9],
                                                "destinationZip"=>$line[10],
                                                "originationLat"=>$originationlatitude,
                                                "originationLng"=>$originationlongitude,
                                                "destinationLat"=>$destinationlatitude,
                                                "destinationLng"=>$destinationlongitude,
                                                "needsDataPoints"=>$needsDataPoints,
                                                "status"=>'Available',
                                                "contactEmails"=>[$contacts],
                                                "availableDate"=>$line[1],
                                                "expirationDate"=>$expirationDate,
                                                "createdAt"=>$dttime,
                                                "updatedAt"=>$dttime
                                            );
                                        }

                                        $options = array(
                                            'http' => array(
                                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                                'method'  => $type,
                                                'content' => http_build_query($data)
                                            )
                                        );

                                        $context = stream_context_create($options);
                                        try {
                                            //$result = json_decode(file_get_contents($url,false,$context));
                                            $result = file_get_contents($url,false,$context);
                                        } catch (Exception $e) {
                                            return $e;
                                        } // Try

                                    } // If

                                } catch (Exception $e) {
                                    return $e;
                                } // Try

                            } // If

                        } catch (Exception $e) {
                            return $e;
                        } // Try

                    } // If
                    $counter++;

                } // While

                fclose($file);

		} else {
		    return "uploadOk was 0 - File failed uploading.";
        } // If
		return "success";
	}

	// function to geocode address, it will return false if unable to geocode address
	public function geocode($address){

		// url encode the address
		$address = urlencode($address);
		echo $address;
		die();

		// google map geocode api url
		$url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key=".GOOGLE_MAPS_API;

		// get the json response
		$resp_json = file_get_contents($url);

		// decode the json
		$resp = json_decode($resp_json, true);

		// response status will be 'OK', if able to geocode given address
		if($resp['status']=='OK'){

			// get the important data
			$lati = $resp['results'][0]['geometry']['location']['lat'];
			$longi = $resp['results'][0]['geometry']['location']['lng'];
			$formatted_address = $resp['results'][0]['formatted_address'];

			// verify if data is complete
			if($lati && $longi && $formatted_address){

				// put the data in the array
				$data_arr = array();

				array_push(
					$data_arr,
						$lati,
						$longi,
						$formatted_address
					);

				return $data_arr;

			}else{
				return false;
			}

		}else{
			return false;
		}
	}

    public function load($api_host,$id) {
      $args = array(
            "transform"=>"1"
      );
      $url = $api_host . "/" . API_ROOT. "/documents/".$id."?".http_build_query($args);
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

}
