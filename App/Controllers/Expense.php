<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\Flash;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Expense extends \Core\Controller
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
}
