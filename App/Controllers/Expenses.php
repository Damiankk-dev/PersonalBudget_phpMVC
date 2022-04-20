<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Expense;
use \App\Auth;
use \App\Flash;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Expenses extends \Core\Controller
{

    /**
     * Show the page allowing creation of a new expense
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Expense/new.html');
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
			Flash::addMessage("Expense added");			
			View::renderTemplate('Expense/new.html');
		} else {
			Flash::addMessage("Error", Flash::WARNING);		
			View::renderTemplate('Expense/new.html');
		}

	}
}
