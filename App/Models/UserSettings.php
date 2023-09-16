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

    public function updateSettings($settings){

		$settings = $this->sortSettingsForUpdate($settings);
        $this->executeInsertQueriesForAllSettings($settings["new"]);
        $this->executeDeleteQueriesForAllSettings($settings["del"]);
        $this->executeUpdateQueriesForAllSettings($settings["mod"]);
    }
    /**
     * Fills user settings with empty types arrays
     *
     * @return void
     */
    public function initializeSettingArray(){
        $emptySettings = array();
        $settings = ["expenses", "incomes", "payments"];
        foreach ($settings as $type){
            $emptySettings[$type] = array();
        }

        $this->userSettingsForView = $emptySettings;
    }
    /**
     * Check whether setting name is not empty, if not check whether given name do not exist in database
     *
     * @param UserSetting $userSetting
     *
     * @return bool false if name is not correct, true otherwise
     */
    public function isNameExists($userSetting){
        if ($this->findByName($userSetting->name, $userSetting->settingType)) {
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
     * Prepare and execute query for all newly added settings
     *
     * @return void
     */
    private function executeInsertQueriesForAllSettings($newSettings){
        foreach ($newSettings as $settingType => $settings){
            if (count($settings) > 0){
                if ($settingType == "expense"){
                    $sql = 'INSERT INTO expenses_category_assigned_to_users (user_id, name) VALUES';
                } else if ($settingType == "income"){
                    $sql = 'INSERT INTO incomes_category_assigned_to_users (user_id, name) VALUES';
                } else if ($settingType == "payment"){
                    $sql = 'INSERT INTO payment_methods_assigned_to_users (user_id, name) VALUES';
                }

                $insertQuery = array();
                $insertData = array();
                foreach ($settings as $setting) {
                    if (!count($setting->errors) > 0 ){
                        $insertQuery[] = '(?, ?)';
                        $insertData[] = $this->userId;
                        $insertData[] = $setting->name;
                    }
                }

                $db = $this->getDB();
                if (!empty($insertQuery)) {
                    $sql .= implode(', ', $insertQuery);
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
                    $stmt->execute($insertData);
                }
            }
        }
    }

    /**
     * Prepare and execute query for settings that can be deleted
     *
     * @return void
     */
    private function executeDeleteQueriesForAllSettings($deletedSettings){
        foreach ($deletedSettings as $settingType => $settings){
            if (count($settings) > 0){
                if ($settingType == "expense"){
                    $sql = 'DELETE FROM expenses_category_assigned_to_users WHERE user_id = ? AND id in (';
                } else if ($settingType == "income"){
                    $sql = 'DELETE FROM incomes_category_assigned_to_users WHERE user_id = ? AND id in (';
                } else if ($settingType == "payment"){
                    $sql = 'DELETE FROM payment_methods_assigned_to_users WHERE user_id = ? AND id in (';
                }

                $insertQuery = array();
                $insertData = array();
                $insertData[] = $this->userId;
                foreach ($settings as $setting) {
                    if (!count($setting->errors) > 0 ){
                        $insertQuery[] = '?';
                        $insertData[] = $setting->id;
                    }
                }

                $db = $this->getDB();
                if (!empty($insertQuery)) {
                    $sql .= implode(', ', $insertQuery);
                    $sql .= ')';
                    $stmt = $db->prepare($sql);
                    $stmt->execute($insertData);
                }
            }
        }
    }

    /**
     * Prepare and execute query for all updated settings
     *
     * @return void
     */
    public function executeUpdateQueriesForAllSettings($modifiedSettings){
        foreach ($modifiedSettings as $settingType => $settings){
            if (count($settings) > 0){
                if ($settingType == "expense"){
                    $sql = 'UPDATE expenses_category_assigned_to_users SET name = CASE WHEN ';
                    $idName = 'id';
                } else if ($settingType == "income"){
                    $sql = 'UPDATE incomes_category_assigned_to_users SET name = CASE WHEN ';
                    $idName = 'id';
                } else if ($settingType == "payment"){
                    $sql = 'UPDATE payment_methods_assigned_to_users SET name = CASE WHEN ';
                    $idName = 'id';
                }

                $insertQuery = array();
                $insertData = array();
                foreach ($settings as $setting) {
                    if (!count($setting->errors) > 0 ){
                        $insertQuery[] = $idName.' = ? THEN ?';
                        $insertData[] = $setting->id;
                        $insertData[] = $setting->name;
                    }
                }

                $insertData[] = $this->userId;
                $db = $this->getDB();
                if (!empty($insertQuery)) {
                    $sql .= implode(' WHEN ', $insertQuery);
                    $sql .= ' ELSE name END WHERE user_id = ?';
                    $stmt = $db->prepare($sql);
                    $stmt->execute($insertData);
                }
            }
        }
    }

    /**
     * Order settings by setting type to adjust them to view arrays
     * @param array of UserSetting
     * @return array of UserSetting ordered by setting type
     */
    public function sortSettingsForView($settings){
		$updatedUserSettings = array(
			"expense" => array(),
			"income" => array(),
			"payment" => array()
		);
        foreach($settings as $setting){
            $updatedUserSettings[$setting->settingType][] = $setting;
        }
        return $updatedUserSettings;
    }

    /**
     * Group settings by modification type and setting type to adjust them to queries
     * @param array of UserSetting
     * @return array of UserSetting grouped by modification type and setting type inside modifiaction group
     */
    public function sortSettingsForUpdate($settings){
        $settingsSortedForSave = array(
            "new" => array(
                "expense" => array(),
                "income" => array(),
                "payment" => array()
            ),
            "del" => array(
                "expense" => array(),
                "income" => array(),
                "payment" => array()
            ),
            "mod" => array(
                "expense" => array(),
                "income" => array(),
                "payment" => array()
            )
        );

        foreach ($settings as $setting){
            $settingsSortedForSave[$setting->modificationType][$setting->settingType][] = $setting;
        }

        return $settingsSortedForSave;
    }
}
