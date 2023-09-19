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

    /**
     * Show the page allowing to edit an existing Income
     *
     * @return void
     */
    public function editAction()
    {
		//query string: 'edit&id=1', pobranie ID
		$referer = $_SERVER['HTTP_REFERER'];
		$referer = explode('//',$_SERVER['HTTP_REFERER']);
		$referer = explode('/',$referer[1]);
		unset($referer[0]);
		$referer = implode('/', $referer);
		$_SESSION['redirect_url'] = $referer;
		$incomeId = $this->getQueryStringParams()['id'];
		//pobranie wpisu
		$income = Income::getById($incomeId, 'income');

		View::renderTemplate('Income/new.html', [
			'income' => $income,
			'income_categories' => $this->userSettings->userSettingsForView["income"]
		]);
    }

	/**
	 * Add a new income
	 *
	 * @return void
	 */
	public function updateAction()
	{
		$income = new Income($_POST);

		if ($income->update()) {
			Flash::addMessage("Zmiany zapisane!");
			$redirect = $_SESSION['redirect_url'];
			unset($_SESSION['redirect_url']);
			$this->redirect('/'.$redirect);
		} else {
			Flash::addMessage("Przychód nie został zapisany", Flash::WARNING);
			View::renderTemplate('Income/new.html', [
                'income' => $income,
				'income_categories' => $this->userSettings->userSettingsForView["income"]
            ]);
		}
	}

	/**
	 * Add a new income
	 *
	 * @return void
	 */
	public function removeAction()
	{
		//query string: 'remove&id=1', pobranie ID
		$incomeId = $this->getQueryStringParams()['id'];
		if (Income::deleteById($incomeId, 'income')) {
			Flash::addMessage("Wpis został usunięty!");
			$this->returnToPrevious();
		} else {
			Flash::addMessage("Nie udało się połączyć z bazą danych", Flash::ERROR);
			View::renderTemplate();
		}
	}
}
