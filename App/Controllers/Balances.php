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
class Balances extends \Core\Controller
{

    /**
     * Show balance based on chosen period
     *
     * @return void
     */
    public function showAction()
    {
		$balance = new Balance();
        $debugMsg = $balance->getPeriod($this->route_params['type']);
		if ($debugMsg)
		{
			Flash::addMessage($debugMsg);
			View::renderTemplate('Balance/new.html');
		} else {
			Flash::addMessage("Coś poszło nie tak", Flash::WARNING);
			View::renderTemplate('Balance/new.html');
		}
    }
}
