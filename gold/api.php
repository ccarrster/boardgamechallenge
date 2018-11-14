<?php
require_once('PHPMailer.php');
require_once('SMTP.php');
require_once('Exception.php');
$accountFile = 'accounts.json';
$salt = 'sse5e#$#Sdfasdf35XSADF';
$emailSalt = 'nasfl345n25k3%aSDFASDF';
$tempPasswordSalt = 'Jln35i3nLkldln35l34';

$response = [];
if(isset($_GET['action'])){
	$action = $_GET['action'];
	if($action == 'createaccount'){
		if(isset($_POST['data'])){
			$data = $_POST['data'];
			$requiredFields = ['username', 'email', 'password'];
			if(validateRequiredFields($requiredFields, $data, $response)){
				$accounts = [];
				if(file_exists($accountFile)){
					$accounts = json_decode(file_get_contents($accountFile));
					foreach($accounts as $account){
						if($account->username === $data['username']){
							$response['type'] = 'error';
							$error = [];
							$error['type'] = 'username already taken.';
							$error['message'] = 'The username '.$data['username'].' is already taken.';
							$response['errors'][] = $error;
						}
						if($account->email === $data['email']){
							$response['type'] = 'error';
							$error = [];
							$error['type'] = 'email already taken.';
							$error['message'] = 'A user with the email address '.$data['email'].' already exists.';
							$response['errors'][] = $error;
						}
					}
				}
				//No errors
				if(count($response) == 0){
					$account = [];
					$account['username'] = $data['username'];
					$account['email'] = $data['email'];
					$account['passwordhash'] = crypt($data['password'], $salt);
					$account['validation'] = false;
					$accounts[] = $account;
					$result = file_put_contents($accountFile, json_encode($accounts));
					if($result === false){
						$response['type'] = 'error';
						$error = [];
						$error['type'] = 'server error saving account.';
						$error['message'] = 'Some issue on the server saving the sccount.';
						$response['errors'][] = $error;
					} else {
						$response['type'] = 'success';
						sendEmail($data['email'], 'Welcome to Dwarf Gold', '<a href="http://localhost/boardgamechallenge/gold/api.php?action=validate&key='.crypt($data["email"], $emailSalt).'">Verify your email address</a>');
					}
				}
			}
		} else {
			requiredDataError();
		}
	} elseif($action == 'signin'){
		if(isset($_POST['data'])){
			$data = $_POST['data'];
			$requiredFields = ['username', 'password'];
			if(validateRequiredFields($requiredFields, $data, $response)){
				$match = false;
				if(file_exists($accountFile)){
					$accounts = json_decode(file_get_contents($accountFile));
					foreach($accounts as $account){
						if($account->username === $data['username'] && $account->passwordhash === crypt($data['password'], $salt)){
							if($account->validation === true){
								$response['type'] = 'success';
							} else {
								$response['type'] = 'error';
								$error = [];
								$error['type'] = 'Email not validated.';
								$error['message'] = 'Check your email and click validation link to activate your account.';
								$response['errors'][] = $error;
							}
							$match = true;
						}
					}
				}
				if($match === false){
					$response['type'] = 'error';
					$error = [];
					$error['type'] = 'username and password combo not found.';
					$error['message'] = 'username and password combo not found, try again or reset password.';
					$response['errors'][] = $error;
				}
			}
		} else {
			requiredDataError();
		}
	} elseif($action == 'validate'){
		if(isset($_GET['key'])){
			$match = false;
			$key = $_GET['key'];
			if(file_exists($accountFile)){
				$accounts = json_decode(file_get_contents($accountFile));
				foreach($accounts as &$account){
					if(crypt($account->email, $emailSalt) === $key){
						$response['type'] = 'success';
						$account->validation = true;
						$match = true;
					}
				}
			}
			if($match === false){
				$response['type'] = 'error';
				$error = [];
				$error['type'] = 'account not found to validate.';
				$error['message'] = 'Account not found to validate.';
				$response['errors'][] = $error;
			} else {
				file_put_contents($accountFile, json_encode($accounts));
			}
		} else {
			$response['type'] = 'error';
			$error = [];
			$error['type'] = 'required field key missing.';
			$error['message'] = 'Required field key missing.';
			$response['errors'][] = $error;
		}
	if($action == 'resetpassword'){
		if(isset($_POST['data'])){
			$data = $_POST['data'];
			$requiredFields = ['email'];
			if(validateRequiredFields($requiredFields, $data, $response)){
				$match = false;
				if(file_exists($accountFile)){
					$accounts = json_decode(file_get_contents($accountFile));
					foreach($accounts as &$account){
						if($account->email === $data['email']){
							$response['type'] = 'success';
							//TODO generate and store temporary password
							//TODO make new password frontend
							sendEmail($data['email'], 'Password reset for Dwarf Gold', '<a href="http://localhost/boardgamechallenge/gold/index.html?action=newPassword>Change your password</a> Temporary Password: ');
							$match = true;
						}
					}
				}
				
				//xxx
			}
		}
	} else {
		$response['type'] = 'error';
		$error = [];
		$error['type'] = 'action not supported.';
		$error['message'] = $_GET['action'].' is not supported';
		$response['errors'][] = $error;
	}
} else {
	$response['type'] = 'error';
	$error = [];
	$error['type'] = 'no action set.';
	$error['message'] = 'api call requires a get parameter of action';
	$response['errors'][] = $error;
}

function requiredDataError(){
	$response['type'] = 'error';
	$error = [];
	$error['type'] = 'no data posted.';
	$error['message'] = $_GET['action'].' required data to be posted.';
	$response['errors'][] = $error;
}

function validateRequiredFields($requiredFields, $data, &$response){
	foreach($requiredFields as $requiredField){
		if(!isset($data[$requiredField])){
			$response['type'] = 'error';
			$error = [];
			$error['type'] = 'missing required field.';
			$error['message'] = $requiredField.' is required.';
			$response['errors'][] = $error;
		}
	}
	foreach($requiredFields as $requiredField){
		if(isset($data[$requiredField]) && $data[$requiredField] == ''){
			$response['type'] = 'error';
			$error = [];
			$error['type'] = 'blank required field.';
			$error['message'] = $requiredField.' is requires a value.';
			$response['errors'][] = $error;
		}
	}
	if(count($response) == 0){
		return true;
	} else {
		return false;
	}
}

function sendEmail($to, $subject, $body){
	$mail = new PHPMailer\PHPMailer\PHPMailer;

	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'smtp.gmail.com';                       // Specify main and backup server
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'forwhatidonotwantthat@gmail.com';                   // SMTP username
	$mail->Password = 'ben54321';               // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
	$mail->Port = 587;                                    //Set the SMTP port number - 587 for authenticated TLS
	$mail->setFrom('forwhatidonotwantthat@gmail.com');     //Set who the message is to be sent from
	$mail->addAddress($to);  // Add a recipient
	$mail->isHTML(true);                                  // Set email format to HTML

	$mail->Subject = $subject;
	$mail->Body    = $body;

	if(!$mail->send()) {
	   echo 'Message could not be sent.';
	   echo 'Mailer Error: ' . $mail->ErrorInfo;
	   exit;
	}

	echo 'Message has been sent';
}


echo(json_encode($response));