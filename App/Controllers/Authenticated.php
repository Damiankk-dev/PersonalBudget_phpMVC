<?php

namespace App\Controllers;

/**
* Authenticated base controller
*
* PHP version 7.0
*/
abstract class Authenticated extends \Core\Controller
{
	/**
	* Before filter
	*
	* @return void
	*/
	protected function before()
	{
		$this->requireLogin();
	}
}