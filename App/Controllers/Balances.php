<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Balance;
use \App\Auth;
use \App\Flash;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Balances extends Authenticated
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
	}

    /**
     * Show balance based on chosen period
     *
     * @return void
     */
    public function showAction()
    {
		$balance = new Balance($_POST);
        $balance->show($this->route_params['type']);
		if ($balance)
		{
			View::renderTemplate('Balance/new.html', [
				'balance' => $balance
			]);
		} else {
			Flash::addMessage("Coś poszło nie tak", Flash::WARNING);
			View::renderTemplate('Balance/new.html');
		}
    }
}
