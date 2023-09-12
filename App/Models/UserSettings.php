<?php

namespace App\Models;

use \Core\View;
use PDO;
use App\Token;
use App\Mail;

/**
 * User settings model
 *
 * PHP version 7.0
 */
class UserSettings extends \Core\Model
{
	/**
	 * User settings displayed on a view
	 *
	 * @var array
	 */
	public $userSettingsForView = [];

	/**
	 * User id
	 *
	 * @var int
	 */
	private $userId = null;

    /**
     * Class constructor
     *
     * 
     * @param int $userId User id
     * @param array $data  Initial property values
     *
     * @return void
     */
    public function __construct()
    {
        $this->userId = $_SESSION['user_id'];
    }

    /**
     * Fill user setting object
     *
     * @return void
     */
    public function getUserSettingsForView(){
        $this->userSettingsForView = $this->getUserSettingsByUserId();
    }

    /**
     * Get user settings of logged in user by user id from a constructor
     *
     *  @return mixed array of settings arrays if succesfully fetched from db false otherwise
     */

     private function getUserSettingsByUserId(){
        $db = static::getDB();
        if ($db !== null )
        {
            $settings = ["expenses", "incomes", "payments"];
            $userSettings = array();
            foreach($settings as $setting){
                switch($setting):
                    case "expenses":
                        $sql = 'SELECT expense_category_id, name FROM expenses_category_assigned_to_users WHERE user_id = :user_id';
                        break;
                    case "incomes":
                        $sql = 'SELECT income_category_id, name FROM incomes_category_assigned_to_users WHERE user_id = :user_id';
                        break;
                    case "payments":
                        $sql = 'SELECT payment_method_id, name FROM payment_methods_assigned_to_users WHERE user_id = :user_id';
                        break;
                endswitch;
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
                $stmt->execute();
                $userSettings[$setting] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $userSettings;
        }

        return false;
    }
}
