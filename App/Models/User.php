<?php

namespace App\Models;

use \Core\View;
use PDO;
use App\Token;
use App\Mail;

/**
 * Example user model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
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
     * Save the user model with the current property values
     *
     * @return boolean True if the user was saved, false otherwise
     */
    public function save()
    {
        $this->validate();
        
        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
            
            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();
            
            $sql = 'INSERT INTO users (username, email, password_hash, activation_hash)
                    VALUES (:username, :email, :password_hash, :activation_hash)';
                    
            $db = static::getDB();
            if ($db !== null ) {
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
                $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
                $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);
                
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
        //Name
        if ($this->username =='') {
            $this->errors[] = 'Name is required';
        }
        
        //email
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }
        
        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'email already taken';
        }
        
        // Password	
        if (isset($this->password)) {
            if (strlen($this->password) < 6 ) {			
                $this->errors[] = 'Password must contain at least 6 characters';
            }
                
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0 ) {			
                $this->errors[] = 'Password needs at least one letter';
            }
            
            if (preg_match('/.*\d+.*/i', $this->password) == 0 ) {			
                $this->errors[] = 'Password needs at least one number';
            }
        }
    }

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
	public static function emailExists($email, $ignore_id = null)
	{
		$user = static::findByEmail($email);
		if ($user) {
			if ($user->id != $ignore_id) {
				return true;
			}
		}

		return false;
	}

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     *
     * @return mixed User object if found, flase otherwise
     */
	public static function findByEmail($email)
	{
		$sql = 'SELECT * FROM users WHERE email = :email';
		
		
		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':email', $email, PDO::PARAM_STR);
		
		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
		
		$stmt->execute();
		
		return $stmt->fetch();
	}
	
	/**
	 * Send password reset instructions in an email to the user
	 *
	 * @return void
	 */
	public function sendActivationEmail()
	{
		$url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;
		
		$text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
		$html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);
		
		Mail::send($this->email, 'Account activation', $text, $html);		
	}

    /**
     * Find a user model by ID
     *
     * @param string $id The user ID
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get all the users as an associative array
     *
     * @return array
     */
    public static function getAll()
    {
        $db = static::getDB();
        $stmt = $db->query('SELECT id, username FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
