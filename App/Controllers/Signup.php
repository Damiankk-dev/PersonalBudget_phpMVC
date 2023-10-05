<?php

namespace App\Controllers;

use \Core\View;
use \App\Flash;
use \App\Models\User;
use Exception;

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
		try{
			$user = new User($_POST);
			
			if ($user->save()) {
				try{
					$user->sendActivationEmail();
					//static::redirect('/Signup/success');
				} catch (\Exception $e){
					Flash::addMessage("Problem with sending activation email {$e->getMessage()}", Flash::WARNING);
					View::renderTemplate('500.html');
				}

			} else {
				View::renderTemplate('Signup/index.html', [
					'user' => $user
					]);
			}
		} catch (\Exception $e){
			Flash::addMessage("User cannot be created: {$e->getMessage()}", Flash::WARNING);
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
	
	/**
	 * Activate a new account
	 *
	 * @return void
	 */
	public function activateAction()
	{
		User::activate($this->route_params['token']);	

		$this->redirect('/signup/activated');
	}
	
	/**
	 * Show the activation success page
	 *
	 * @return void
	 */
	public function activatedAction()
	{
		View::renderTemplate('Signup/activated.html');
	}
}
