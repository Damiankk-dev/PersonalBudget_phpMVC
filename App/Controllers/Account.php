<?php

namespace App\Controllers;

use \App\Models\User;

/**
* Post contrller
*
* PHP version 7.0
*/
class Account extends \Core\Controller
{
	
	/**
	 * Validate if email is avaliable (AJAX) for a new signup.
	 *
	 * @return void
	 */
	public function validateEmailAction()
	{
		$is_valid = ! User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null );
		
		header('Content-Type: application/json');
		echo json_encode($is_valid);
	}
}