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
class Expense extends \Core\Model
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
            $sql = 'INSERT INTO expenses (
                user_id, 
                expense_category_assigned_to_user, 
                payment_method_assigned_to_user, 
                amount, 
                date_of_expense, 
                expense_comment)
            VALUES(
                :user_id, 
                :expense_category, 
                :payment_method, 
                :expense_amount, 
                :expense_date, 
                :expense_comment)';
                
            $db = static::getDB();
            if ($db !== null ) 
            {
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
                $stmt->bindValue(':expense_category', 
                                    $this->expense_category, PDO::PARAM_STR);
                $stmt->bindValue(':payment_method', 
                                    $this->payment_method, PDO::PARAM_STR);
                $stmt->bindValue(':expense_amount', $this->expense_amount, PDO::PARAM_STR);
                $stmt->bindValue(':expense_date', $this->expense_date, PDO::PARAM_STR);
                $stmt->bindValue(':expense_comment', $this->expense_comment, PDO::PARAM_STR);
                
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
        if ($this->expense_category == 0)
        {
            $this->errors[] = 'Proszę wybrać kategorię';
        }
        //method
        if ($this->payment_method == 0)
        {
            $this->errors[] = 'Proszę wybrać metodę płatności';
        }
        //amount
        if (isset($this->expense_amount)) 
        {
            if (preg_match('/(?!^0*$)(?!^0*\.0*$)^\d*((\.\d{1,2})|(,\d{1,2}))?$/', $this->expense_amount) == 0 ) {			
                $this->errors[] = 'Proszę podać kwotę w odpowiednim formacie z dokładnością do 2 miejsc po przecinku';
            }
        }
        //date
        if (isset($this->expense_date)) 
        {
            if (preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $this->expense_date) == 0 ) {			
                $this->errors[] = 'Data powinna być w formacie YYYY-mm-dd';
            }
        }
    }
}