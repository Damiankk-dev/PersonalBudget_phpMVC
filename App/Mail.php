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
			
			$mail->Host = 'smtp.gmail.com'; # Gmail SMTP host
			$mail->Port = 465; # Gmail SMTP port
			$mail->SMTPAuth = true; # Enable SMTP authentication / Autoryzacja SMTP
			$mail->AuthType = 'XOAUTH2'; # Set AuthType to use XOAUTH2
			$mail->Username = "dkacztest@gmail.com"; # Gmail username (e-mail) / Nazwa użytkownika
			$mail->Password = 'Mwzmj$un2102'; # Gmail password / Hasło użytkownika
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail->CharSet = "UTF-8";

			$email = "dkacztest@gmail.com";
			$clientId = "777147694551-kqs25e1mrlgsgtp7outubips2sq4u62b.apps.googleusercontent.com";
			$clientSecret = "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";
			$refreshToken = "1//0cxFaftYsJhC2CgYIARAAGAwSNwF-L9IrTT9fU7220lD18wYEvu_Ie0L33lM6e0Z7xMIqhS87z0HIx0Rdq-IOPRDt7d4C9kQHw7M";

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