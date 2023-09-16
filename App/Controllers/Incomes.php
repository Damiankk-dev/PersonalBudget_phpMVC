<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Income;
use \App\Models\UserSettings;
use \App\Auth;
use \App\Flash;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Incomes extends Authenticated
{
	/**
	* Before filter - called before each action method
	*
	* @return void
	*/
	protected function before()
	{
		parent::before();
		$this->user = Auth::getUser();
		$this->userSettings = new UserSettings($forView=true);
	}

    /**
     * Show the page allowing creation of a new Income
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Income/new.html',[
			'income_categories' => $this->userSettings->userSettingsForView["income"]
		]);
    }

	/**
	 * Add a new income
	 *
	 * @return void
	 */
	public function addAction()
	{
		$income = new Income($_POST);

		if ($income->save()) {
			Flash::addMessage("Przychód zapisany!");
			View::renderTemplate('Income/new.html',[
				'income_categories' => $this->userSettings->userSettingsForView["income"]
			]);
		} else {
			Flash::addMessage("Przychód nie został dodany", Flash::WARNING);
			View::renderTemplate('Income/new.html', [
                'income' => $income,
				'income_categories' => $this->userSettings->userSettingsForView["income"]
            ]);
		}
	}
}
