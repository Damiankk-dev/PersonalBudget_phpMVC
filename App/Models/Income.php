<?php

namespace App\Models;

use \App\Auth;
use \Core\View;
use PDO;


/**
 * An income model
 *
 * PHP version 7.0
 */
class Income extends \Core\Model
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
   
    /**
     * Save the expense model with the current property values
     *
     * @return boolean True if the user was saved, false otherwise
     */
    public function save()
    {
        $this->validate();
        $user = Auth::getUser();
        if (empty($this->errors)) 
        {
            $sql = 'INSERT INTO incomes (
                user_id, 
                income_category_assigned_to_user, 
                amount, 
                date_of_income, 
                income_comment)
            VALUES(
                :user_id, 
                :income_category, 
                :income_amount, 
                :income_date, 
                :income_comment)';
                
            $db = static::getDB();
            if ($db !== null ) 
            {
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
                $stmt->bindValue(':income_category', 
                                    $this->income_category, PDO::PARAM_STR);
                $stmt->bindValue(':income_amount', $this->income_amount, PDO::PARAM_STR);
                $stmt->bindValue(':income_date', $this->income_date, PDO::PARAM_STR);
                $stmt->bindValue(':income_comment', $this->income_comment, PDO::PARAM_STR);
                
		        return $stmt->execute();
            }
            $this->errors[] = 'Null database!';
            return false;
        }
        
        return false;
    }
   
    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate()
    {
        //Category
        if ($this->income_category == 0)
        {
            $this->errors[] = 'Proszę wybrać kategorię';
        }
        //amount
        if (isset($this->income_amount)) 
        {
            if (preg_match('/(?!^0*$)(?!^0*\.0*$)^\d*((\.\d{1,2})|(,\d{1,2}))?$/', $this->income_amount) == 0 ) {			
                $this->errors[] = 'Proszę podać kwotę w odpowiednim formacie z dokładnością do 2 miejsc po przecinku';
            }
        }
        //date
        if (isset($this->income_date)) 
        {
            if (preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $this->income_date) == 0 ) {			
                $this->errors[] = 'Data powinna być w formacie YYYY-mm-dd';
            }
        }
    }
}