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
		if (isset($_SESSION['user_id'])) {
			View::renderTemplate('Home/index.html');
		} else {
			View::renderTemplate('Login/index.html');
		}
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

			if ( !($user->prepareUserAtFirstLogin()) )
			{
				Flash::addMessage('Inicjalizacja użytkownika nieudana, niektóre funkcje mogą nie działać poprawnie, skontaktuj się z administratorem!', Flash::WARNING);
			}

			Flash::addMessage('Logowanie powiodło się!');

			$this->redirect(Auth::getReturnToPage());
		}
		else
		{
			Flash::addMessage('Logowanie nieudane, spróbuj jeszcze raz!', Flash::WARNING);

			$error = 'Użytkownik przypisany do adresu: '. $_POST['email'] .' nie istnieje lub jest jeszcze nie aktywny';

			View::renderTemplate('Login/index.html', [
				'error' => $error,
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
		Flash::addMessage('Wylogowano z powodzeniem, zapraszam ponownie');

		static::redirect('/');
	}
}
