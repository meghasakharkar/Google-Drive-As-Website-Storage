<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class DriveHandler{

	/**
	 * Stores authorization code
	 * @return redirect to OAuth2URI URL
	 */
	function generate_authorization_token() {
		define('SCOPES', implode(' ', array('https://www.googleapis.com/auth/drive')));
		$client = new Google_Client();
		$client->setApplicationName(APPLICATION_NAME);
		$client->setScopes(SCOPES);
		$client->setAuthConfig(CLIENT_SECRET_PATH);
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
		$redirect_uri = REDIRECT_URI;
		$client->setRedirectUri($redirect_uri);
		$authUrl = $client->createAuthUrl();
		redirect($authUrl);
	}

	/**
	 * Stores authentication code
	 * @return Google_Client the authorized client object
	 */
	function generate_access_token() {
		$obj = new Drive_config_model();
		$obj = $obj->get_drive_config_object();
		$authCode = $obj->authorization_code;
		$client = new Google_Client();
		// Exchange authorization code for an access token.
    	//$accessToken = $client->fetchAccessTokenWithAuthCode();
    	$client->setAuthConfig(CLIENT_SECRET_PATH);
    	$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
    	$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
    	$client->setAccessToken($accessToken);
    	$obj = new Drive_config_model();
    	$obj->access_token = json_encode($accessToken);
    	$obj->refresh_token = $client->getRefreshToken();
    	$obj->store_authentication_code();
	}

	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	function refresh_access_token() {
		$config_obj = new Drive_config_model();
		$obj = $config_obj->get_drive_config_object();
		$refreshToken = $obj->refresh_token;
		// create new google client
		$client = new Google_Client();
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		$client->setAuthConfig(CLIENT_SECRET_PATH);
    	$new_access_token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
    	$client->setAccessToken($new_access_token);
    	return $client;
	}

	/**
	 * Returns service
	 * @return Google_Client the authorized client object
	 */
	function get_drive_service() {
		$client = DriveHandler::refresh_access_token();
		$service = new Google_Service_Drive($client);
		return $service;
	}

	function upload_file($title,$description,$ext,$filepath){
		$service = DriveHandler::get_drive_service();
		$file = new Google_Service_Drive_DriveFile();
		$name = $title.'.'.$ext;
		$fileMetadata = new Google_Service_Drive_DriveFile(array(
			'name' => $name,
			'mimeType' => MIME_TYPE,
			'parents' => array(PUBLIC_ID))
		);
		$content = file_get_contents($filepath);
		$file = $service->files->create($fileMetadata, array(
		'data' => $content,
		'mimeType' => MIME_TYPE,
		'uploadType' => 'multipart',
		'fields' => 'id'));
		$obj = new Image_gallery_model();
		$obj->title = $title;
		$obj->description = $description;
		$obj->file_id = $file->getId();
		$link = "https://drive.google.com/uc?export=download&id=".$obj->file_id;
		$obj->link = $link;
		$obj->save();
	}

	function list_files(){
		$service = DriveHandler::get_drive_service();
		$optParams = array(
		  'pageSize' => 10,
		  'fields' => 'nextPageToken, files(id, name)'
		);
		$results = $service->files->listFiles($optParams);
		if (count($results->getFiles()) == 0) {
		  print "No files found.\n";
		} else {
		  print "Files:\n";
		  foreach ($results->getFiles() as $file) {
		    printf("%s (%s)\n", $file->getName(), $file->getId());
		
		  }
		}
	}

}