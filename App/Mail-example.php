<?php

namespace App;

use \PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use \App\Flash;
/* use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
 */
/**
 * Mail
 *
 * PHP version 7.0
 */
class Mail
{
    /**
     * Send a message
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     *
     * @return mixed
     */
	public static function send($to, $subject, $text, $html)
	{
		//Create an instance; passing `true` enables exceptions
		$mail = new PHPMailer(true);
		
		try {
			//Server settings
			//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
			$mail->isSMTP();                                            //Send using SMTP
			
			$mail->Host = 'smtp.provider'; # Gmail SMTP host
			$mail->Port = ---; # Gmail SMTP port
			$mail->SMTPAuth = true; # Enable SMTP authentication / Autoryzacja SMTP
			$mail->AuthType = 'XOAUTH2'; # Set AuthType to use XOAUTH2
			$mail->Username = "your.mail@gmail.com"; # Gmail username (e-mail) / Nazwa użytkownika
			$mail->Password = 'hardPass123'; # Gmail password / Hasło użytkownika
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail->CharSet = "UTF-8";

			$email = "your.mail@gmail.com";
			$clientId = "RANDOM NUMS--------.apps.googleusercontent.com";
			$clientSecret = "SECRET";
			$refreshToken = "RANDOM NUMS";

			//Create a new OAuth2 provider instance
			$provider = new Google(
				[
					'clientId' => $clientId,
					'clientSecret' => $clientSecret,
				]
			);

			//Pass the OAuth provider instance to PHPMailer
			$mail->setOAuth(
				new OAuth(
					[
						'provider' => $provider,
						'clientId' => $clientId,
						'clientSecret' => $clientSecret,
						'refreshToken' => $refreshToken,
						'userName' => $email,
					]
				)
			);

			//Recipients
			$mail->setFrom('dkacztest@gmail.com', 'Strona PersonalBudget');
			$mail->addAddress($to, 'Nowy użytkownik');     //Add a recipient     'damian.kaczmarzyk.programista@gmail.com'

			//Content
			$mail->isHTML(true);                                  //Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $html;
			$mail->AltBody = $text;

			$mail->send();
		} catch (Exception $e) {
			Flash::addMessage("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", Flash::WARNING);
		}
	}
}