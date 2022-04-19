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
            $this->errors[] = 'Proszę podaj swoje imię';
        }
        
        //email
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Nieprawidłowy adres email';
        }
        
        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'Uzyty adres email znajduje się już w naszej bazie';
        }
        
        // Password	
        if (isset($this->password)) {
            if (strlen($this->password) < 6 ) {			
                $this->errors[] = 'Hasło musi zawierać conajmniej 6 znaków';
            }
                
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0 ) {			
                $this->errors[] = 'Hasło musi zawierać przynajmniej jedną literę';
            }
            
            if (preg_match('/.*\d+.*/i', $this->password) == 0 ) {			
                $this->errors[] = 'Hasło musi zawierać przynajmniej jedną cyfrę';
            }
        }
        // Rules
        if ( ! isset($this->rules_accepted)) {
            $this->errors[] = 'Proszę o akceptację regulaminu';            
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
	 * Activate the user account with the specified activation token
	 *
	 * @param string $value Activation token from the url
	 *
	 * @return void
	 */
	public static function activate($value)
	{
		$token = new Token($value);
		$hashed_token = $token->getHash();
			$sql = 'UPDATE users
					SET is_active = 1, 
					activation_hash = null
					WHERE activation_hash = :hashed_token';
				
			$db = static::getDB();
			$stmt = $db->prepare($sql);
			
			$stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
			
			$stmt->execute();
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
     * Authenticate a user by email and password.
     *
     * @param string $email email address
     * @param string $password password
     *
     * @return mixed  The user object or false if authentication fails
     */
	public static function authenticate($email, $password)
	{
		$user = static::findByEmail($email);
		
		if ($user && $user->is_active)
		{
			if (password_verify($password, $user->password_hash)){
				return $user;
			}
		}
		
		return false;
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
