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
class Income extends Cashflow
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
        $this->errors[] = $this->validateCategory($this->income_category);
        //amount
        if (isset($this->income_amount)) 
        {
            $this->errors[] = $this->validateAmount($this->income_amount);
        }
        //date
        if (isset($this->income_date)) 
        {
            $this->errors[] = $this->validateDate($this->income_date);
        }
    }
}