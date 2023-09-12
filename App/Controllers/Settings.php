<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\UserSettings;

/**
* Post contrller
*
* PHP version 7.0
*/
class Settings extends Authenticated
{
	/**
	* Before filter - called before each action method
	*
	* @return void
	*/
	protected function before()
	{
		parent::before();
	}

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
		$userSettings = new UserSettings();
		$userSettings->getUserSettingsForView();
		if ($userSettings->userSettingsForView) {
			View::renderTemplate('Settings/index.html', [
				'expensesCategories' => $userSettings->userSettingsForView["expenses"],
				'incomesCategories' => $userSettings->userSettingsForView["incomes"],
				'paymentMethods' => $userSettings->userSettingsForView["payments"]
			]);
		} else {
			Flash::addMessage('Nie udało się pobrać ustawień użytkownika, spróbuj ponownie później', Flash::WARNING);
			View::renderTemplate('Settings/index.html');
		}
    }
}