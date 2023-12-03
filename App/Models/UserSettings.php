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
    public function __construct($forView = false)
    {
        $this->userId = $_SESSION['user_id'];
        if ($forView){
            $this->userSettingsForView = $this->getUserSettingsByUserId();
        }
    }

    /**
     * Fill user setting object
     *
     * @return void
     */
    public function getUserSettingsForView(){
        $this->userSettingsForView = $this->getUserSettingsByUserId();
    }

    public function updateSetting($setting){
        if ($setting->settingType == "expense"){
            $sql = 'UPDATE expenses_category_assigned_to_users SET name = ? WHERE user_id = ? AND id = ? ';
        } else if ($setting->settingType == "income"){
            $sql = 'UPDATE incomes_category_assigned_to_users SET name = ? WHERE user_id = ? AND id = ? ';
        } else if ($setting->settingType == "payment"){
            $sql = 'UPDATE payment_methods_assigned_to_users SET name = ? WHERE user_id = ? AND id = ? ';
        }

        $insertData = array();
        if (!count($setting->errors) > 0 ){
            $insertData[] = $setting->name;
            $insertData[] = $this->userId;
            $insertData[] = $setting->id;
        }

        $db = $this->getDB();
        if (!empty($insertData)) {
            $stmt = $db->prepare($sql);
            $stmt->execute($insertData);
        }

    }

    /**
     * Check whether setting name is not empty, if not check whether given name do not exist in database
     *
     * @param UserSetting $userSetting
     *
     * @return bool false if name is not correct, true otherwise
     */
    public function isNameExists($userSetting){
        $wantedSetting = $this->findByName($userSetting->name, $userSetting->settingType);
        if ($wantedSetting) {
                return true;
        }

        return false;
    }

    /**
     * Check whether setting is not used in the database
     *
     * @param UserSetting $userSetting
     *
     * @return bool true if setting is in use, false otherwise
     */
    public function isSettingInUse($userSetting){
		$setting = $this->findById($userSetting->id, $userSetting->settingType);
		if ($setting) {
            return true;
		}

		return false;
    }

    /**
     * Returns setting by type and id
     * @param int $settingId
     * @param string $type
     *
     * @return mixed UserSetting $userSetting when id exists, false otherwise
     */
    public function getSettingByIdAndType($settingId, $type){
		$setting = $this->getByIdAndType($settingId, $type);
		if ($setting) {
            return $setting;
		}

		return false;
    }

    /**
     * Find a setting record model by setting id
     *
     * @param string $settingId category id to search for
     * @param string $settingType type of setting the category name is from
     *
     * @return mixed Setting record if found, flase otherwise
     */
	private function findById($settingId, $settingType){
        switch($settingType):
            case "expense":
                $sql = 'SELECT c.* FROM expenses_category_assigned_to_users c
                        INNER JOIN expenses e
                            on c.id = e.expense_category_id
                        WHERE c.id = :settingId
                        limit 1';
                break;
            case "income":
                $sql = 'SELECT c.* FROM incomes_category_assigned_to_users c
                        INNER JOIN incomes e
                            on c.id = e.income_category_id
                        WHERE c.id = :settingId
                        limit 1';
                break;
            case "payment":
                $sql = 'SELECT c.* FROM payment_methods_assigned_to_users c
                        INNER JOIN expenses e
                            on c.id = e.payment_method_id
                        WHERE c.id = :settingId
                        limit 1';
                break;
        endswitch;

		$db = $this->getDB();
		$stmt = $db->prepare($sql);
        $stmt->bindParam(':settingId', $settingId, PDO::PARAM_STR);

		//$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();

		return $stmt->fetch();
	}

    /**
     * Gets a setting record model by setting id and setting type
     *
     * @param string $settingId category id to search for
     * @param string $settingType type of setting the category name is from
     *
     * @return mixed Setting record if found, flase otherwise
     */
	private function getByIdAndType($settingId, $settingType){
        switch($settingType):
            case "expense":
                $sql = 'SELECT c.* FROM expenses_category_assigned_to_users c
                        WHERE c.id = :settingId';
                break;
            case "income":
                $sql = 'SELECT c.* FROM incomes_category_assigned_to_users c
                        WHERE c.id = :settingId';
                break;
            case "payment":
                $sql = 'SELECT c.* FROM payment_methods_assigned_to_users c
                        WHERE c.id = :settingId';
                break;
        endswitch;

		$db = $this->getDB();
		$stmt = $db->prepare($sql);
        $stmt->bindParam(':settingId', $settingId, PDO::PARAM_STR);

		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();

		return $stmt->fetch();
	}

    /**
     * Get user settings of logged in user by user id from a constructor
     *
     *  @return mixed array of settings arrays if succesfully fetched from db false otherwise
     */

     private function getUserSettingsByUserId(){
        $db = $this->getDB();
        if ($db !== null )
        {
            $settings = ["expense", "income", "payment"];
            $userSettings = array();
            foreach($settings as $setting){
                switch($setting):
                    case "expense":
                        $sql = 'SELECT id, name FROM expenses_category_assigned_to_users WHERE user_id = :user_id';
                        break;
                    case "income":
                        $sql = 'SELECT id, name FROM incomes_category_assigned_to_users WHERE user_id = :user_id';
                        break;
                    case "payment":
                        $sql = 'SELECT id, name FROM payment_methods_assigned_to_users WHERE user_id = :user_id';
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

    /**
     * Find a setting record model by setting name
     *
     * @param string $settingName setting name to search for
     * @param string $settingType type of the setting is
     *
     * @return mixed Setting record if found, flase otherwise
     */
	public function findByName($settingName, $settingType){
        switch($settingType):
            case "expense":
                $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE user_id = :user_id AND name = :settingName';
                break;
            case "income":
                $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id = :user_id AND name = :settingName';
                break;
            case "payment":
                $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE user_id = :user_id AND name = :settingName';
                break;
        endswitch;

		$db = $this->getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindParam(':settingName', $settingName, PDO::PARAM_STR);

		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();

		return $stmt->fetch();
	}

    /**
     * Get limit value by id
     * @param int $categoryId expense category id
     * @return mixed category limit (nullable) or false
     */
    public function getLimitValueById($categoryId){
        $sql = 'SELECT category_limit FROM expenses_category_assigned_to_users WHERE id = :id';

        $db = $this->getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam('id', $categoryId, PDO::PARAM_INT);

        $stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetch();

    }

    /**
     * Updates limit value by id
     * @param int $categoryId expense category id
     * @param int $limitValue value of chosen limit
     * @return boolean true if no error else otherwise
     */
    public function updateLimitById($categoryId, $limitValue){
        if ($limitValue == 'null'){
            $limitValue = NULL;
        }

        $sql = 'UPDATE expenses_category_assigned_to_users
            SET category_limit = :categoryLimit
            WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':categoryLimit', $limitValue, PDO::PARAM_STR);
        $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Prepare and execute query for adding a setting
     * @param UserSetting $setting setting with defined name and type
     * @param $limit optional, when type is expense, limit can be set
     *
     * @return void
     */
    public function addSetting($setting, $limit = null){
        $insertQuery = array();
        $insertData = array();
        if ($setting->settingType == "expense"){
            $sql = 'INSERT INTO expenses_category_assigned_to_users (category_limit, user_id, name) VALUES';
            $insertQuery[] = '(?, ?, ?)';
            $insertData[] = $limit;
        } else if ($setting->settingType == "income"){
            $sql = 'INSERT INTO incomes_category_assigned_to_users (user_id, name) VALUES';
            $insertQuery[] = '(?, ?)';
        } else if ($setting->settingType == "payment"){
            $sql = 'INSERT INTO payment_methods_assigned_to_users (user_id, name) VALUES';
            $insertQuery[] = '(?, ?)';
        }

        if (!count($setting->errors) > 0 ){
            $insertData[] = $this->userId;
            $insertData[] = $setting->name;
        }

        $db = $this->getDB();
        if (!empty($insertQuery)) {
            $sql .= implode(', ', $insertQuery);
            $stmt = $db->prepare($sql);
            $stmt->execute($insertData);
        }
    }

    /**
     * Prepare and execute query for removing a setting
     * @param UserSetting $setting setting with defined id and type
     *
     * @return void
     */
    public function removeSetting($setting){
        if ($setting->settingType == "expense"){
            $sql = 'DELETE FROM expenses_category_assigned_to_users WHERE user_id = ? AND name = ?';
        } else if ($setting->settingType == "income"){
            $sql = 'DELETE FROM incomes_category_assigned_to_users WHERE user_id = ? AND name = ?';
        } else if ($setting->settingType == "payment"){
            $sql = 'DELETE FROM payment_methods_assigned_to_users WHERE user_id = ? AND name = ?';
        }
        $insertData = array();
        $insertData[] = $this->userId;
        $insertData[] = $setting->name;
        $db = $this->getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($insertData);
    }
}

