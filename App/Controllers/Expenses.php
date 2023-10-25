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

    /**
     * Show the page allowing to edit an existing Expense
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
		$expenseId = $this->getQueryStringParams()['id'];
		//pobranie wpisu
		$expense = Expense::getById($expenseId, 'expense');

		View::renderTemplate('Expense/new.html', [
			'expense' => $expense,
			'expense_categories' => $this->userSettings->userSettingsForView["expense"],
			'payment_methods' =>  $this->userSettings->userSettingsForView["payment"]
		]);
    }

	/**
	 * Add a new expense
	 *
	 * @return void
	 */
	public function updateAction()
	{
		$expense = new Expense($_POST);

		if ($expense->update()) {
			Flash::addMessage("Zmiany zapisane!");
			$redirect = $_SESSION['redirect_url'];
			unset($_SESSION['redirect_url']);
			$this->redirect('/'.$redirect);
		} else {
			Flash::addMessage("Przychód nie został zapisany", Flash::WARNING);
			View::renderTemplate('Expense/new.html', [
                'expense' => $expense,
				'expense_categories' => $this->userSettings->userSettingsForView["expense"],
				'payment_methods' =>  $this->userSettings->userSettingsForView["payment"]
            ]);
		}
	}

	/**
	 * Add a new expense
	 *
	 * @return void
	 */
	public function removeAction()
	{
		//query string: 'remove&id=1', pobranie ID
		$expenseId = $this->getQueryStringParams()['id'];
		if (Expense::deleteById($expenseId, 'expense')) {
			Flash::addMessage("Wpis został usunięty!");
			$this->returnToPrevious();
		} else {
			Flash::addMessage("Nie udało się połączyć z bazą danych", Flash::ERROR);
			View::renderTemplate();
		}
	}

	/**
	 * Gets limit value for given month and category
	 *
	 * @param int $categoryId
	 * @param string $dateOfExpense
	 *
	 * @return void?
	 */
	public function monthlyExpensesAction(){
		$expense = new Expense();
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$queryParams = explode('&', $parts[2]);
		$categoryId = $queryParams[0];
		$expenseDate = $queryParams[1];
		$data = $expense->getMonthlyExpensesForCategory($categoryId, $expenseDate);
		$dataJSON = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		echo $dataJSON;
	}
}
