<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Expense;
use \App\Models\UserSettings;
use \App\Auth;
use \App\Flash;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Expenses extends Authenticated
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
     * Show the page allowing creation of a new expense
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Expense/new.html', [
			'expense_categories' =>  $this->userSettings->userSettingsForView["expense"],
			'payment_methods' =>  $this->userSettings->userSettingsForView["payment"]
		]);
    }

	/**
	 * Add a new expense
	 *
	 * @return void
	 */
	public function addAction()
	{
		$expense = new Expense($_POST);

		if ($expense->save()) {
			Flash::addMessage("Wydatek zapisany!");
			View::renderTemplate('Expense/new.html', [
				'expense_categories' =>  $this->userSettings->userSettingsForView["expense"],
				'payment_methods' =>  $this->userSettings->userSettingsForView["payment"]
			]);
		} else {
			Flash::addMessage("Wydatek nie został dodany", Flash::WARNING);
			View::renderTemplate('Expense/new.html', [
				'expense' => $expense,
				'expense_categories' =>  $this->userSettings->userSettingsForView["expense"],
				'payment_methods' =>  $this->userSettings->userSettingsForView["payment"]
			]);
		}

	}
}
