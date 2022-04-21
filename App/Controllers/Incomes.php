<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Income;
use \App\Auth;
use \App\Flash;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Incomes extends \Core\Controller
{

    /**
     * Show the page allowing creation of a new Income
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Income/new.html');
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
			Flash::addMessage("Income added");			
			View::renderTemplate('Income/new.html');
		} else {
			Flash::addMessage("Error", Flash::WARNING);		
			View::renderTemplate('Income/new.html');
		}
	}
}
