<?php

namespace App;

use \PHPMailer\PHPMailer\PHPMailer;
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
			$mail->Username = "dkacztest@gmail.com"; # Gmail username (e-mail) / Nazwa użytkownika
			$mail->Password = 'Mwzmj$un2102'; # Gmail password / Hasło użytkownika
			$mail->SMTPSecure = 'ssl';
			$mail->CharSet = "UTF-8";

			//Recipients
			$mail->setFrom('dkacztest@gmail.com', 'Strona PersonalBudget');
			$mail->addAddress($to, 'Nowy użytkownik');     //Add a recipient     'damian.kaczmarzyk.programista@gmail.com'

			//Content
			$mail->isHTML(true);                                  //Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $html;
			$mail->AltBody = $text;

			$mail->send();
			echo 'Message has been sent';
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}
}