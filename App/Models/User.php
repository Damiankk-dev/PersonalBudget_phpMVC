<?php

namespace App\Models;

use \Core\View;
use PDO;
use App\Token;
use App\Mail;
use Exception;

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
		try {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

            $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
            $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

            Mail::send($this->email, 'Aktywacja konta na stronie PersonalBudget', $text, $html);
        } catch (Exception $e) {
            $this->errors[] = "Problem with sending activation email {$e->getMessage()}";
        }
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
                    is_first_login = 1,
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
	public function authenticate($email, $password)
	{
		$user = static::findByEmail($email);

		if ($user && $user->is_active)
		{
			if (password_verify($password, $user->password_hash)){
				return $user;
			}
            $user->errors[] = "Błędne hasło!";

		} else {
            $user = new User();
            $user->errors[] = "Użytkownik o podanym adrese email nie istnieje lub jest nie aktywny";
        }

		return $user;
	}

    /**
     * Remember the login by inserting a new unique token into the remembered_logins table
     * for this user record
     *
     * @return boolean  True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin()
    {
		$SECONDS_TO_DAYS = 60 * 60 * 24;
		$DAYS_UNTIL_EXPIRY = 30;

		$token = new Token();
		$hashed_token = $token->getHash();
		$this->remember_token = $token->getValue();

		$this->expiry_timestamp = time() + $SECONDS_TO_DAYS * $DAYS_UNTIL_EXPIRY;

		$sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
			   VALUES (:token_hash, :user_id, :expires_at)';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
		$stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
		$stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

		return $stmt->execute();
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

    /**
     * Update user password
     *
     * @return void
     */
    public static function updatePassword($id, $password){
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

		$db = static::getDB();

        if ($db !== null ) {
            $sql = 'UPDATE users
                    SET password_hash = :password_hash
                    WHERE id = :user_id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        return false;
    }

    /**
     * Inserts default users settings into individual params table
     *
     * @return boolean True if all settings iserted correctly flase otherwise
     */
    private function saveDefaultSettings()
    {
        $sql = 'INSERT INTO payment_methods_assigned_to_users (user_id, name) VALUES ';
        $sql = $this->concatenateParamsIntoSeparateValues($sql, 'payment_methods_default');

        $sql .= 'INSERT INTO expenses_category_assigned_to_users (user_id, name) VALUES ';
        $sql = $this->concatenateParamsIntoSeparateValues($sql, 'expenses_category_default');

        $sql .= 'INSERT INTO incomes_category_assigned_to_users (user_id, name) VALUES ';
        $sql = $this->concatenateParamsIntoSeparateValues($sql, 'incomes_category_default');

		$db = static::getDB();
        if ($db !== null ) {
            $stmt = $db->prepare($sql);
            return $stmt->execute();
        }
        $this->errors[] = 'Null database!';

        return false;
    }

    /**
     * Get default params assigned to user
     *
     * @param string $table_name parameter's table name
     *
     * @return mixed Array with default params if no db errors false otherwise
     */
    private function getDefaultParams($table_name)
    {
        $db = static::getDB();
        $stmt = $db->query('SELECT * FROM '. $table_name);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Replace first from the left occurrence of searched string
     *
     * @param string $search searched string
     * @param string $replace string to replace
     * @param string $subject string with removed piece
     *
     * @return string input value with changed last occurrence of search part or not changed input value otherwise
     */
    private function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    /**
     * Creates arrays of input values in order corresponding to schema (id, user_id, param_name)
     *
     * @param string $query 'INSERT' part of query
     * @param string $params_table Name of table which includes default values of parameters
     *
     * @return string Complete INSERT query with VALUES (rows to insert)
     */
    private function concatenateParamsIntoSeparateValues($query, $params_table)
    {
        $params = $this->getDefaultParams($params_table);

        foreach ($params as $row)
        {
            $query .= '('. $this->id . ', \'' .  $row['name'] . '\'),';
        }
        $query = $this->str_lreplace(',', ';', $query);

        return $query;
    }

    /**
     * Removes is_first_login flag from users table
     *
     * @return void
     */
    private function removeFirstLoginFlag()
    {
        $sql = 'UPDATE users
                SET is_first_login = 0
                WHERE id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * Initialize default settings of cashflow parameters ( payment methods, categories etc. )
     *
     * @return boolean True if database updated correctly, false otherwise
     */
    public function prepareUserAtFirstLogin()
    {
        if ($this->is_first_login) {
            $this->saveDefaultSettings();
            if (empty($this->errors)) {
                $this->removeFirstLoginFlag();
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Removes user record when sending email failed
     */
    public function removeUserByEmailOnFailure(){
        $sql = 'DELETE FROM users WHERE
            email = :email';

        $db = static::getDB();
        if ($db !== null )
        {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);

            return $stmt->execute();
        }

        $this->errors[] = 'Null database!';
        return false;

    }
}
