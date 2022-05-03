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
        $this->expenses = $this->getExpensesById($user->id);
        $this->incomes = $this->getIncomesById($user->id);
    }

    /**
     * Gets expenses based on user ID and demended of a chosen period
     *
     * @param int $user_id Authenticated user id
     * 
     * @return mixed Expenses array if no error false otherwise
     */
    public function getExpensesById($user_id)
    {
        if($chosen_period = $this->getPeriod())
        {
            $sql = 'SELECT * FROM expenses WHERE
                user_id = :user_id
                AND
                date_of_expense BETWEEN :start_date AND :end_date';
                
            $db = static::getDB();
            if ($db !== null ) 
            {
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':start_date', 
                                    $chosen_period[1], PDO::PARAM_STR);
                $stmt->bindParam(':end_date', 
                                    $chosen_period[0], PDO::PARAM_STR);                
                
		        $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);                
            }

            $this->errors[] = 'Null database!';
            return false;            
        }
    }

    /**
     * Gets incomes based on user ID and demended of a chosen period
     *
     * @param int $user_id Authenticated user id
     * 
     * @return mixed Incomes array if no error false otherwise
     */
    public function getIncomesById($user_id)
    {
        if($chosen_period = $this->getPeriod())
        {
            $sql = 'SELECT * FROM incomes WHERE
                user_id = :user_id
                AND
                date_of_income BETWEEN :start_date AND :end_date';
                
            $db = static::getDB();
            if ($db !== null ) 
            {
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':start_date', 
                                    $chosen_period[1], PDO::PARAM_STR);
                $stmt->bindParam(':end_date', 
                                    $chosen_period[0], PDO::PARAM_STR);                
                
		        $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);                
            }

            $this->errors[] = 'Null database!';
            return false;            
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
    /*
    
					case "Actual_month":
						$end_date = date('Y-m-d');
						$start_date = date('Y-m-01');						
						break;				
					case "Previous_month":
						$end_date = date('Y-m-d', strtotime('last day of previous month'));
						$start_date = date('Y-m-d', strtotime('first day of previous month'));
						break;
					case "Actual_year":
						$end_date = date('Y-m-d');
						$start_date = date('Y-m-d', mktime(0, 0, 0, 1, 1, date('Y')));
						break;
					case "Custom":
						if (isset($_POST['endBalancePeriod']) && 
							isset($_POST['startBalancePeriod'])){
							$end_date = $_POST['endBalancePeriod'];
							$start_date = $_POST['startBalancePeriod'];
							if ($start_date > $end_date){
								$start_date = $date_zero;
								$end_date = $date_zero;
								echo "</br>Invalid date!";
							}	
						}						
						break;	
					default:
						echo "Invalid query";
    */
}