<?php

namespace As247\CloudStorages\Controllers;

use Exception;
use Google_Client;
use Google_Service_Drive;

class GoogleDriveController
{
	protected $client;
	public function __construct($clientId,$clientSecret)
	{
		$this->client=new Google_Client();
		$this->client->setClientId($clientId);
		$this->client->setClientSecret($clientSecret);
		$this->client->addScope(Google_Service_Drive::DRIVE);
		$this->client->setAccessType('offline');
		$this->client->setApprovalPrompt("force");
		$this->client->setRedirectUri($this->getCurrentUrl());
	}

	public function dispatch(){
		if($code=$this->getCode()){
            try{
				$result=$this->client->fetchAccessTokenWithAuthCode($code);
				$refreshToken=$result['refresh_token'];
			}catch (Exception $e){
				$refreshToken=$e->getMessage();
			}
			$this->showRefreshToken($refreshToken);
		}else{
			$this->redirectTo($this->client->createAuthUrl());
		}
	}
	protected function redirectTo($url){
		$redirect='<html lang="en">
					<head>
						<meta http-equiv="refresh" content="1; url=%1$s">
						<title>Redirecting....</title>
					</head>
					<body>Redirecting to %1$s...</body>
					</html>';
		printf($redirect,$url);
	}
	protected function showRefreshToken($refreshToken){
		echo '<textarea cols="100" rows="20">', htmlspecialchars($refreshToken,ENT_QUOTES) . '</textarea>';
	}
	protected function getCode(){
		return $_REQUEST['code']??null;
	}

	protected function getCurrentUrl()
	{
		return 'http://' . $_SERVER['HTTP_HOST'];
	}
}
