<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\Flash;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Login extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Login/index.html');
    }
	
	/**
	 * Log in a new user
	 *
	 * @return void
	 */
	public function createAction()
	{
		$user = User::authenticate($_POST['email'], $_POST['password']);
		$remember_me = isset($_POST['remember_me']);
		
		
		if ($user)
		{
			Auth::login($user, $remember_me);
			
			Flash::addMessage('Logowanie powiodło się!');
			
			$this->redirect(Auth::getReturnToPage());
		}
		else 
		{
			Flash::addMessage('Logowanie nieudane, spróbuj jeszcze raz!', Flash::WARNING);
			
			View::renderTemplate('Login/index.html', [
				'email' => $_POST['email'],
				'remember_me' => $remember_me
				]);
		}
	}
	
	/**
	 * Log out a user
	 *
	 * @return void
	 */
	public function destroyAction()
	{
		Auth::logout();		
		
		$this->redirect('/login/show-logout-message');		
	}
	
	/**
	 * Show a logged out flash message an dredirect to the homepage. Necessary to use the flash messages
	 * as they use the session an dat the end of the logout method (destroyAction) the session i destroyed
	 * so a new action needs to be called in order to use the session.
	 *
	 * @return void
	 */
	public function showLogoutMessage()
	{	
		Flash::addMessage('Logout successful');
		
		static::redirect('/');
	}
}
