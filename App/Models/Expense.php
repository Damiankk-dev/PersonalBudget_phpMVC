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
class Expense extends Cashflow
{   
    /**
     * Save the expense model with the current property values
     *
     * @return boolean True if the user was saved, false otherwise
     */
    public function save()
    {
        $this->validate();
        $user = Auth::getUser();
        foreach ($this->errors as $key => $error_value)
        {
            if (empty($error_value)) unset($this->errors[$key]);
        }
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
        //method
        $this->errors[] = $this->validateMethod($this->payment_method);
        //Category
        $this->errors[] = $this->validateCategory($this->expense_category);
        //amount
        if (isset($this->expense_amount)) 
        {
            $this->errors[] = $this->validateAmount($this->expense_amount);
        }
        //date
        if (isset($this->expense_date)) 
        {
            $this->errors[] = $this->validateDate($this->expense_date);
        }
    }   
}