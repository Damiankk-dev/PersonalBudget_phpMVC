<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\UserSetting;
use \App\Models\UserSettings;
use \App\Models\User;

/**
* Post contrller
*
* PHP version 7.0
*/
class Settings extends Authenticated
{
	/**
	* Before filter - called before each action method
	*
	* @return void
	*/
	protected function before()
	{
		parent::before();
	}
	/**
	 * global controller variable which let change a view when backend error ocurred
	 *  and let to keep modified values when saving form fails
	 * true if no errors false otherwise
	 */
	private $validationStatus = true;

    /**
     * Show the index page
	 * @param array UserSetting in configuration prepared for a view, default null, filled only
	 * 				when form has to be corrected after backend errors occurred
     *
     * @return void
     */
    public function indexAction($settingsForView=null)
    {
		if ($settingsForView==null){
			$userSettings = new UserSettings();
			$userSettings->getUserSettingsForView();
			$settingsForView = $userSettings->userSettingsForView;
		}
		if ($settingsForView) {
			View::renderTemplate('Settings/index.html', [
				'expensesCategories' => $settingsForView["expense"],
				'incomesCategories' => $settingsForView["income"],
				'paymentMethods' => $settingsForView["payment"],
				'validationStatus' => $this->validationStatus
			]);
		} else {
			Flash::addMessage('Nie udało się pobrać ustawień użytkownika, spróbuj ponownie później', Flash::WARNING);
			View::renderTemplate('Settings/index.html');
		}
    }

	/**
	 * Validate and save settings from input form
	 *
	 * @return void
	 */
	public function saveAction(){
		$validatedUserSettings = $this->validateInputData($_POST);

		if($this->validationStatus == true){
			$this->updateSettings($validatedUserSettings);
			Flash::addMessage('Zmiany zostały zapisane pomyślnie');
			$this->indexAction();
		} else {
			$settingsWithErrorForView = $this->prepareSettingsWithErrorsForView($validatedUserSettings);
			$this->indexAction($settingsWithErrorForView);
		}
	}

	/**
	 * Publish JSON data false if there is no error with removal, userSetting object otherwise
	 */
	public function validateRemovalAction(){
		$settingLabel = $this->getQueryStringParams()['setting'];
		$settingValues = explode('_',$settingLabel);
		$settingType = $settingValues[0];
		$settingId = $settingValues[1];
		$setting = new UserSetting($settingId, "", $settingType, 'del');
		$myJSON = json_encode($this->errorWhenSettingRemoved($setting), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		echo $myJSON;
	}

	/**
	 * Publish JSON data false if there is no error with given name, error message otherwise
	 */
	public function validateNameAction(){
		$settingLabel = $this->getQueryStringParams()['setting'];
		$settingValues = explode('_',$settingLabel);
		$settingType = $settingValues[0];
		$settingId = $settingValues[1];
		$modificationType = $settingValues[2];
		$settingName = $settingValues[3];
		$setting = new UserSetting($settingId, $settingName, $settingType, $modificationType);
		$myJSON = json_encode($this->errorWhenNameIsNotCorrect($setting), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		echo $myJSON;
	}

	/**
	 * Updates user password action
	 */
	public function updatePasswordAction(){
		$password = $_POST['password'];
		$user = Auth::getUser();
		if (User::updatePassword($user->id, $password)){
			Flash::addMessage('Zmiany zostały zapisane pomyślnie');
			$this->indexAction();
		} else {
			Flash::addMessage('Zmiana hasła nie powiodła się', Flash::WARNING);
		}
	}

	/**
	 * Checks whether a setting entry can be safely removed
	 *
	 * @param UserSetting user setting object containing data from post input
	 *
	 * @return mixed false if setting can be removed directly error string if demands confirmation
	 */
	private function errorWhenSettingRemoved($userSetting){
		$userSettings = new UserSettings();
		if ($userSettings->isSettingInUse($userSetting)){
			return 'Usunięcie kategorii '.$userSetting->name.' spowoduje usunięcie wszystkich wpisów związanych z kategorią';
		}

		return false;
	}

	/**
	 * Checks whether a setting entry can by saved with a given name
	 *
	 * @param UserSetting user setting object containing data from post input
	 *
	 * @return mixed false if name is correct, error string otherwise
	 */
	private function errorWhenNameIsNotCorrect($userSetting){
		if ($userSetting->name == ""){
			return 'Nazwa nie może być pusta';
		} else {
			$userSettings = new UserSettings();
			if ($userSettings->isNameExists($userSetting))
			return 'Nazwa '.$userSetting->name.' jest już wykorzystana';
		}

		return false;
	}

	/**
	 * Updates user settings
	 *
	 * @param array user settings without errors divided by setting type
	 *
	 * @return array user settings for a view
	 */
	private function updateSettings($settings){
		$userSettings = new UserSettings();
		$userSettings->updateSettings($settings);
	}

	/**
	 * Changes format of user input data for UserSetting objects array
	 * Values format is defined by JS script that creates inputs, example key: settingType_id_modificationType; value is an input value
	 *
	 * @param $_POST array from user settings form
	 *
	 * @return void
	 */
	private function validateInputData($inputForm){

		$updatedUserSettings = array();
		foreach($inputForm as $key => $value){
			$keyParts = explode('_',$key);
			if (count($keyParts) < 3 ){
				$settingType = $keyParts[0];
				$settingId = $keyParts[1];
				$modificationType = "notChanged";
				$userSetting = new UserSetting($settingId, $value, $settingType, $modificationType);
			} else {
				$settingType = $keyParts[0];
				$settingId = $keyParts[1];
				$modificationType = $keyParts[2];
				$userSetting = new UserSetting($settingId, $value, $settingType, $modificationType);
			}

			$userSetting = $this->validateSetting($userSetting);
			if(count($userSetting->errors) > 0 ){
				$this->validationStatus = false;
			}

			$updatedUserSettings[] = $userSetting;
		}

		return $updatedUserSettings;
	}

	/**
	 * Prepares settings array for a view after backend errors occurred
	 * moves modofocationType param to name param, to use modification type as input name
	 * to a proper view order
	 *
	 * @param array UserSetting after validation with any error
	 * @return array settings gruped by type and with name changed for view with errors
	 */
	private function prepareSettingsWithErrorsForView($settingsWithError){
		foreach($settingsWithError as $setting){
			$nameOnView = [$setting->settingType, $setting->id];
			if ($setting->modificationType != "notChanged"){
				$nameOnView[] = $setting->modificationType;
			}

			$nameOnView = implode('_', $nameOnView);
			$setting->modificationType = $nameOnView;

		}

		$userSettings = new UserSettings();
		return $userSettings->sortSettingsForView($settingsWithError);
	}

	/**
	 * Validates settings by name, usage and modification type
	 *
	 * @param UserSetting $userSetting
	 *
	 * @return UserSetting with error and/or changed modification type
	 */
	private function validateSetting($userSetting){
		$userSetting = $this->validateName($userSetting);
		$userSetting = $this->validateUsage($userSetting);
		$userSetting = $this->confirmRemoval($userSetting);
		return $userSetting;
	}

	/**
	 * Assigns error to a userSetting when name is empty or exists
	 *
	 * @param UserSetting $userSetting
	 *
	 * @return UserSetting when error ocurrs error is added
	 */
	private function validateName($userSetting){
		if ($userSetting->modificationType == "mod" || $userSetting->modificationType == "new"){
			$error = $this->errorWhenNameIsNotCorrect($userSetting);
			if ($error){
				$userSetting->errors[] = $error;
			}
		}

		return $userSetting;
	}

	/**
	 * Assigns error to a userSetting when setting is in use
	 *
	 * @param UserSetting $userSetting
	 *
	 * @return UserSetting when error ocurrs error is added
	 */
	private function validateUsage($userSetting){
		if ($userSetting->modificationType == "del"){
			$error = $this->errorWhenSettingRemoved($userSetting);
			if ($error) {
				$userSetting->modificationType = "confirm";
				$userSetting->errors[] = $error;
			}
		}

		return $userSetting;
	}

	/**
	 * Changes setting modificationType flag from confirmed to del that allow model
	 * methods to delete setting and its ocurrences in a whole db
	 *
	 * @param UserSetting $userSetting
	 *
	 * @return UserSetting when modification type is confirmed it changes to del
	 */
	private function confirmRemoval($userSetting){
		if ($userSetting->modificationType == "confirmed"){
			$userSetting->modificationType = "del";
		}

		return $userSetting;
	}

	/**
	 * Gets limit amount for category ID
	 *
	 * @param int $categoryId
	 *
	 * @return void?
	 */
	public function limitAction(){
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$categoryId = $parts[2];
		$userSettings = new UserSettings();
		$data = $userSettings->getLimitValueById($categoryId);
		$myJSON = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		echo $myJSON;
	}

	/**
	 * Sets expense category limit value by category id
     * @param int $categoryId expense category id
     * @param int $limitValue value of chosen limit
	 * @return void
	 */
	public function setLimitAction(){
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$queryParams = explode('&', $parts[2]);
		$categoryId = $queryParams[0];
		$limitValue = $queryParams[1];
		$userSettings = new UserSettings();
		$userSettings->updateLimitById($categoryId, $limitValue);
		Flash::addMessage('Zmiany zostały zapisane pomyślnie');
		$this->redirect('/settings/index');
	}

	/**
	 * Gets limit value for given month and category
	 *
	 * @param int $categoryId
	 * @param string $dateOfExpense
	 *
	 * @return void?
	 */
	public function monthlyLimitAction(){
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$queryParams = explode('&', $parts[2]);
		$categoryId = $queryParams[0];
		$expenseDate = $queryParams[1];
		$dataJSON = "Monthly Limit for category: " . $categoryId . " from date: " . $expenseDate ;
		echo $dataJSON;
	}
}
