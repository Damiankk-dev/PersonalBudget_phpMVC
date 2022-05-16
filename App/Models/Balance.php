<?php

namespace App\Models;

use \App\Auth;
use \App\Models\Cashflow;
use \Core\View;
use PDO;


/**
 * An expense model
 *
 * PHP version 7.0
 */
class Balance extends \Core\Model
{
    /**
     * Actual month balance type
     *
     * @var string 
     */
	const ACTUAL = 'actual';

    /**
     * Previous month balance type
     *
     * @var string 
     */
	const PREVIOUS = 'previous';
    
    /**
     * A year form actual day balance type
     *
     * @var string 
     */
	const LAST_YEAR = 'last-year';
    
    /**
     * Any period balance type
     *
     * @var string 
     */
	const ANY = 'any';

	/**
	 * Error messages
	 * 
	 * @var array
	 */
	public $errors = [];

	/**
	 * Incomes
	 * 
	 * @var array
	 */
	public $incomes = [];

	/**
	 * Expenses
	 * 
	 * @var array
	 */
	public $expenses = [];

	/**
	 * Chosen period
	 * 
	 * @var string
	 */
	public $type = 'any';

    /**
     * Show balance 
     * 
     * @param string $type  A type of chosen balance
     *
     * @return void 
     */
   
    public function show($type)
    {
        $this->type = $type;
        $user = Auth::getUser();
        $this->incomes = Cashflow::getByIdCategory($user->id, 'income', $this->getPeriod());
        $this->expenses = Cashflow::getByIdCategory($user->id, 'expense', $this->getPeriod());
        $this->incomes_sum = Cashflow::getSumById($user->id, 'income', $this->getPeriod());
        $this->expenses_sum = Cashflow::getSumById($user->id, 'expense', $this->getPeriod());
        $this->balance_value = $this->incomes_sum - $this->expenses_sum;
        if ($this->balance_value > 0){
            $this->status = "Gratulacje! W tym okresie udało Ci się zaoszczędzić pieniądze!";
        } else {
            $this->status = "Uwaga! W tym okresie Twoje wydatki przerosły przychody!";
        }
    }

    /**
     * Gets balance's period based on a chosen type
     *
     * @param string $type Type of chosen balance 
     * 
     * @return datetime[] array of two dates, beginning and end of the balance period
     */
    public function getPeriod()
    {
        switch($this->type){
            case $this::ACTUAL:
                $end_date = date('Y-m-d');
                $start_date = date('Y-m-01');
                break;
                
            case $this::PREVIOUS:
                $end_date = date('Y-m-d', strtotime('last day of previous month'));
                $start_date = date('Y-m-d', strtotime('first day of previous month'));
                break;
            
            case $this::LAST_YEAR:
                $end_date = date('Y-m-d');
                $start_date = date('Y-m-d', mktime(0, 0, 0, 1, 1, date('Y')));
                break;
            
            case $this::ANY:
            default:
                return false;
        }        
        $chosen_period = [$end_date, $start_date];
        return $chosen_period;
    }    
}