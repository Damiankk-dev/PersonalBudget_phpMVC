<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;

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
		View::renderTemplate('Settings/index.html');
    }
}