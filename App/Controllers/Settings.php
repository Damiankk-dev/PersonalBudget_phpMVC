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
			return 'Usunięcie kategorii spowoduje usunięcie wszystkich związanych z nią wpisów';
		}

		return "false";
	}

	/**
	 * Checks whether a setting entry can by saved with a given name
	 *
	 * @param UserSetting user setting object containing data from post input
	 *
	 * @return mixed false if name is correct, error string otherwise
	 */
	private function errorWhenNameIsNotCorrect($userSetting){
		$userSettings = new UserSettings();
		if ($userSetting->name == ""){
			return 'Nazwa nie może być pusta';
		} else {
			$userSettings = new UserSettings();
			if ($userSettings->isNameExists($userSetting)){
				if ($userSetting->id != ""){
					$validatedSetting = $userSettings->getSettingByIdAndType($userSetting->id, $userSetting->settingType);
					if ($validatedSetting->name == $userSetting->name){
						return 'void';
					}
				}

				return 'Nazwa '.$userSetting->name.' jest już wykorzystana';
			}
		}

		return "false";
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
	 * Adds setting by a given type
	 *
	 * @return void
	 */
	public function addAction(){
        $data = [];
		$data['type'] = $this->route_params['type'];
		foreach($_POST as $key => $value){
			$data[$key] = $value;
		}
		$setting = new UserSetting($data["modal-setting-name"], $data["type"]);
		$settings = new UserSettings();
		if ($data["type"] === "expense"){
			if (!array_key_exists("add-limit", $data) ){
				$data["modal-limitValue"] = null;
			}
			$settings->addSetting($setting, $data["modal-limitValue"]);
		} else {
			$settings->addSetting($setting);
		}

		Flash::addMessage('Zmiany zostały zapisane pomyślnie');
		$this->redirect('/settings/index');
	}

	/**
	 * Verifies if name exists in database
	 *
	 * @return boolean true if exists false otherwie
	 */
	public function validateSettingNameAction(){
		$data = [];
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$data['type'] = $this->route_params['type'];
		$data['name'] = $this->route_params['name'];
		if ($this->route_params['id'] != 0){
			$data['settingId'] = $this->route_params['id'];
			$setting = new UserSetting($data['name'], $data['type'], $data['settingId']);
		} else {
			$setting = new UserSetting($data['name'], $data['type']);
		}
		$data['name_status'] = "false";
		if (strlen($data['name']) > 0){
			$data['name_status'] = $this->errorWhenNameIsNotCorrect($setting);
		}
		$myJSON = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		echo $myJSON;
	}

	/**
	 * Removes setting
	 * HTTP DELETE?
	 * @return void
	 */
	public function removeAction(){
		$data = [];
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$data['type'] = $this->route_params['type'];
		$data['name'] = $this->route_params['name'];
		$settings = new UserSettings();
		$setting = new UserSetting($data['name'], $data['type']);
		$settings->removeSetting($setting);
		Flash::addMessage('Zmiany zostały zapisane pomyślnie');
		$this->redirect('/settings/index');
	}

	/**
	 * Verify removal
	 * API method
	 */
	public function verifyRemovalAction(){
        $data = [];
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$data['type'] = $this->route_params['type'];
		$status = [];
		foreach ($parts as $part){
			if (strpos($part, "settingId") > 0){
				$elements = explode('=',$part);
				$settingId = $elements[1];
				$data['settingId'] = $settingId;
				$setting = new UserSetting("", $data['type'], $settingId);
				$status["status"] = $this->errorWhenSettingRemoved($setting);
			}
		}

		$myJSON = json_encode($status, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		echo $myJSON;
	}

	/**
	 * Updates setting
	 * HTTP PUT
	 */
	public function updateAction(){
		$data = [];
		$parts = explode('/', $_SERVER['QUERY_STRING']);
		$data['type'] = $this->route_params['type'];
		$data['name'] = $this->route_params['name'];
		$data['settingId'] = $this->route_params['id'];
		$settings = new UserSettings();
		$setting = new UserSetting($data['name'], $data['type'], $data['settingId']);
		$settings->updateSetting($setting);
		Flash::addMessage('Zmiany zostały zapisane pomyślnie');
		$this->redirect('/settings/index');
	}
}
