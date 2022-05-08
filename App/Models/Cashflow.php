<?php

namespace App\Models;

use \App\Auth;
use \Core\View;
use PDO;

/**
 * A cashflow model
 * 
 * PHP version 7.0
 */
class Cashflow extends \Core\Model
{
	/**
	 * Error messages
	 * 
	 * @var array
	 */
	public $errors = [];
    
    /**
     * Class constructor
     *
     * @param array $data  Initial property values
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value){
            $this->$key = $value;
        };
    }
    
    protected function validateAmount($amount)
    {
        if (preg_match('/(?!^0*$)(?!^0*\.0*$)^\d*((\.\d{1,2})|(,\d{1,2}))?$/', $amount) == 0 ) {			
            return 'Proszę podać kwotę w odpowiednim formacie z dokładnością do 2 miejsc po przecinku';
        }
    }
    protected function validateDate($date)
    {
        if (preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $date) == 0 ) {			
            return 'Data powinna być w formacie YYYY-mm-dd';
        }
    }
    protected function validateCategory($category)
    {
        if ($category == 0)
        {
            return 'Proszę wybrać kategorię';
        }
    }
    protected function validateMethod($method)
    {
        if ($method == 0)
        {
            return 'Proszę wybrać metodę płatności';
        }
    }
    

    /**
     * Gets expenses based on user ID and demended of a chosen period
     *
     * @param int $user_id Authenticated user id
     * 
     * @return mixed Expenses array if no error false otherwise
     */
    static function getById($user_id, $cashflow_type, $chosen_period)
    {
        $sql = 'SELECT * FROM '. $cashflow_type .'s WHERE
            user_id = :user_id
            AND
            date_of_'. $cashflow_type .' BETWEEN :start_date AND :end_date';
            
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

    /**
     * Calculates sum of incomes assigned to user's id
     * 
     * @param int $user_id CHosen user's id
     * 
     * @return mixed double sum of chosen's user incomes if no error false otherwise
     */
    static function getSumById($user_id, $cashflow_type, $chosen_period){     
        $sql = 'SELECT SUM(amount) AS sum FROM '. $cashflow_type .'s 
                    WHERE 
                    user_id = :user_id
                    AND 
                    date_of_'. $cashflow_type .' BETWEEN :start_date AND :end_date';
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
            return current($stmt->fetch());
        }
    }

}