<?php

require_once('../v3-php-sdk-2.4.1/config.php');

require_once(PATH_SDK_ROOT . 'Core/ServiceContext.php');
require_once(PATH_SDK_ROOT . 'DataService/DataService.php');
require_once(PATH_SDK_ROOT . 'PlatformService/PlatformService.php');
require_once(PATH_SDK_ROOT . 'Utility/Configuration/ConfigurationManager.php');
require_once('helper/AttachableHelper.php'); 

//Specify QBO or QBD
$serviceType = IntuitServicesType::QBO;

// Get App Config
$realmId = ConfigurationManager::AppSettings('RealmID');
if (!$realmId)
	exit("Please add realm to App.Config before running this sample.\n");

// Prep Service Context
$requestValidator = new OAuthRequestValidator(ConfigurationManager::AppSettings('AccessToken'),
                                              ConfigurationManager::AppSettings('AccessTokenSecret'),
                                              ConfigurationManager::AppSettings('ConsumerKey'),
                                              ConfigurationManager::AppSettings('ConsumerSecret'));
$serviceContext = new ServiceContext($realmId, $serviceType, $requestValidator);
if (!$serviceContext)
	exit("Problem while initializing ServiceContext.\n");

// Prep Data Services
$dataService = new DataService($serviceContext);
if (!$dataService)
	exit("Problem while initializing DataService.\n");

// Add a attachable
$addAttachable = $dataService->Add(AttachableHelper::getAttachableFields($dataService));
echo "Attachable created :::  Note ::: {$addAttachable->Note} \n";

//sparse update attachable
date_default_timezone_set('UTC');
$addAttachable->Note = "Attachable note " . rand();
$addAttachable->sparse = 'true';
$savedAttachable = $dataService->Update($addAttachable);
echo "Attachable sparse updated :::  Note ::: {$savedAttachable->Note} \n";


// update attachable with all fields
$updatedAttachable = AttachableHelper::getAttachableFields($dataService);
$updatedAttachable->Id = $savedAttachable->Id;
$updatedAttachable->SyncToken = $savedAttachable->SyncToken;
$savedAttachable = $dataService->Update($updatedAttachable);
echo "Attachable updated with all fields :::  Note ::: {$savedAttachable->Note} \n";

?>
