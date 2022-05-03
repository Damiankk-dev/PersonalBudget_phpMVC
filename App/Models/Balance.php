<?php

namespace App\Models;

use \App\Auth;
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
     * Show balance 
     * 
     * @param string $type  A type of chosen balance
     *
     * @return void 
     */
   
    public function show($type)
    {
        $user = Auth::getUser();
    }

    /**
     * Gets balance based on user ID and demended of a chosen period
     *
     * @param int $user_id Authenticated user id
     * 
     * @return mixed Balance object if no error false otherwise
     */
    public function getById($user_id)
    {
        //SELECT * FROM expenses WHERE user_id=4 AND date_of_expense BETWEEN start_date AND end_date
    }


    /**
     * Gets balance's period based on a chosen type
     *
     * @param string $type Type of chosen balance 
     * 
     * @return datetime[] array of two dates, beginning and end of the balance period
     */
    public function getPeriod($type)
    {
        switch($type){
            case $this::ACTUAL:
                    return "Actual month";
                break;
                
            case $this::PREVIOUS:
                return "Last month";
            break;
            
            case $this::LAST_YEAR:
                return "Last year";
            break;
            
            case $this::ANY:
                return "Any period";
            break;
            default:
                return $type;
        }
    }
}