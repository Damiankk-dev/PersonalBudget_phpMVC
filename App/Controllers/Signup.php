<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Signup extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Signup/index.html');
    }
	
	/**
	 * Sign up a new user
	 *
	 * @return void
	 */
	public function createAction()
	{
		$user = new User($_POST);
		
		if ($user->save()) {
			$user->sendActivationEmail();
			
			static::redirect('/Signup/success');
		} else {
			View::renderTemplate('Signup/index.html', [
				'user' => $user
				]);
		}
	}
	
	/**
	 * Show the signup success page
	 *
	 * @return void
	 */
	public function successAction()
	{
		View::renderTemplate('Signup/success.html');
	}
}
