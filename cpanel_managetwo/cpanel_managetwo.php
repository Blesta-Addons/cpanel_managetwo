<?php
/**
 * Cpanel Managetwo Module
 *
 * @package blesta
 * @subpackage blesta.components.modules.buycpanel
 * @copyright Copyright (c) 2014, Phillips Data, Inc.
 * @license http://www.blesta.com/license/ The Blesta License Agreement
 * @link http://www.blesta.com/ Blesta
 */
class CpanelManagetwo extends Module {
	
	/**
	 * Initializes the module
	 */
	public function __construct() {
        // Load the language required by this module
		Language::loadLang("cpanel_managetwo", null, dirname(__FILE__) . DS . "language" . DS);
        
        // Load config
        $this->loadConfig(dirname(__FILE__) . DS . "config.json");

		// Load components required by this module
		Loader::loadComponents($this, array("Input"));
	}
	

    /**
	 * Performs any necessary bootstraping actions. Sets Input errors on
	 * failure, preventing the module from being added.
	 *
	 * @return array A numerically indexed array of meta data containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 */
	public function install() {
        if (!function_exists("json_encode"))
            $this->Input->setErrors(array('json' => array('unavailable' => Language::_("CpanelManagetwoModule.!error.json.unavailable", true))));
			
        if (!function_exists('curl_init')) 
			$this->Input->setErrors(array('curl_init' => array('unavailable' => Language::_("CpanelManagetwoModule.!error.curl_init.unavailable", true))));
			
        if (!extension_loaded("simplexml"))
            $this->Input->setErrors(array('simplexml' => array('unavailable' => Language::_("CpanelManagetwoModule.!error.simplexml.unavailable", true))));
	}	
	
	/**
	 * Loads the JSON component into this object, making it ready to use
	 */
	private function loadJson() {
		if (!isset($this->Json) || !($this->Json instanceof Json))
			Loader::loadComponents($this, array("Json"));
	}
    
	/**
	 * Initializes the BuycPanel Api and returns an instance of that object with the given account information set
	 *
	 * @param string $email The account email address
	 * @param string $key The API Key
	 * @return BuycpanelApi A BuycpanelApi instance
	 */
	private function getApi($user, $pass) {
		Loader::load(dirname(__FILE__) . DS . "apis" . DS . "cpanel_licensing.php");		
		$api = new cPanelLicensing($user, $pass);
		$api->set_format("simplexml");
	
		return $api;
		
		
		//return new cPanelLicensing($user_name, $key);
	}

	/**
	 * Validates whether or not the connection details are valid by attempting to fetch
	 * the number of accounts that currently reside on the server
	 *
	 * @return boolean True if the connection is valid, false otherwise
	 */
	public function validateConnection($key , $user_name ) {
		// Ready JSON
		$this->loadJson();
		
		try {
			$api = $this->getApi($user_name, $key);		
			// $api = $this->getApi($module_row->meta->email, $module_row->meta->key, ($module_row->meta->test_mode == "true"));			
			$response = $api->fetchPackages();
			$response = json_decode(json_encode($response), true);
			
			if ($response['@attributes']['status']  > 0 ) {
				return true;
			}
		}
		catch (Exception $e) {
			// Trap any errors encountered, could not validate connection
		}
		return false;
	}	

	/**
	 * Returns the rendered view of the manage module page
	 *
	 * @param mixed $module A stdClass object representing the module and its rows
	 * @param array $vars An array of post data submitted to or on the manage module page (used to repopulate fields after an error)
	 * @return string HTML content containing information to display when viewing the manager module page
	 */
	public function manageModule($module, array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("manage", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "cpanel_managetwo" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
			
		$account_rows = count($module->rows);
		
		if ($account_rows > 0) {
			for ($i=0; $i<$account_rows; $i++) {
			
				$this->loadJson();
				
				$api = $this->getApi($module->rows[$i]->meta->user_name, $module->rows[$i]->meta->key);
				$licenses = $api->fetchLicenses();				
				$licenses = $this->Json->decode($this->Json->encode($licenses));
				$module->licenses = $licenses->licenses ;
				
				// $packages = $licenses->licenses ;																		
				// $packages =  $this->getLicenseTypes($module->rows[$i]);				
				// $packages =  $api->fetchPackages();	
				
				$params = array(
						"ip" => "25.142.22.1",
						"groupid" => "G236873",
						"packageid" => "P7050",
						"dryrun" => 1

				);				
				$packages = $api->activateLicense($params);
				$packages = $this->Json->decode($this->Json->encode($packages));

				
				// $packages = json_decode(json_encode($packages), true);
				// $packages = ;		
				// $packages = $this->Json->decode($this->Json->encode($packages));		
			}
		}		
		// $this->view->set("package", $packages->{'@attributes'});
		$this->view->set("package", $packages);
		$this->view->set("module", $module);		
		return $this->view->fetch();
	}
	
	/**
	 * Returns the rendered view of the add module row page
	 *
	 * @param array $vars An array of post data submitted to or on the add module row page (used to repopulate fields after an error)
	 * @return string HTML content containing information to display when viewing the add module row page
	 */
	public function manageAddRow(array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("add_row", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "cpanel_managetwo" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		$this->view->set("vars", (object)$vars);
		return $this->view->fetch();	
	}
	
	/**
	 * Returns the rendered view of the edit module row page
	 *
	 * @param stdClass $module_row The stdClass representation of the existing module row
	 * @param array $vars An array of post data submitted to or on the edit module row page (used to repopulate fields after an error)
	 * @return string HTML content containing information to display when viewing the edit module row page
	 */	
	public function manageEditRow($module_row, array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("edit_row", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "cpanel_managetwo" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		if (empty($vars))
			$vars = $module_row->meta;
		
		$this->view->set("vars", (object)$vars);
		return $this->view->fetch();
	}
	
	/**
	 * Adds the module row on the remote server. Sets Input errors on failure,
	 * preventing the row from being added.
	 *
	 * @param array $vars An array of module info to add
	 * @return array A numerically indexed array of meta fields for the module row containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 */
	public function addModuleRow(array &$vars) {
		$meta_fields = array("account_name", "user_name", "key", "notes");
		$encrypted_fields = array("user_name", "key");
		
		$this->Input->setRules($this->getRowRules($vars));
		
		// Validate module row
		if ($this->Input->validates($vars)) {

			// Build the meta data for this row
			$meta = array();
			foreach ($vars as $key => $value) {
				
				if (in_array($key, $meta_fields)) {
					$meta[] = array(
						'key' => $key,
						'value' => $value,
						'encrypted' => in_array($key, $encrypted_fields) ? 1 : 0
					);
				}
			}
			
			return $meta;
		}
	}
	
	/**
	 * Edits the module row on the remote server. Sets Input errors on failure,
	 * preventing the row from being updated.
	 *
	 * @param stdClass $module_row The stdClass representation of the existing module row
	 * @param array $vars An array of module info to update
	 * @return array A numerically indexed array of meta fields for the module row containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 */
	public function editModuleRow($module_row, array &$vars) {
		// Same as adding
		return $this->addModuleRow($vars);
	}
	
	/**
	 * Deletes the module row on the remote server. Sets Input errors on failure,
	 * preventing the row from being deleted.
	 *
	 * @param stdClass $module_row The stdClass representation of the existing module row
	 */
	public function deleteModuleRow($module_row) {
		// Nothing to do
		return null;
	}
	
	/**
	 * Returns an array of available service delegation order methods. The module
	 * will determine how each method is defined. For example, the method "first"
	 * may be implemented such that it returns the module row with the least number
	 * of services assigned to it.
	 *
	 * @return array An array of order methods in key/value pairs where the key is the type to be stored for the group and value is the name for that option
	 * @see Module::selectModuleRow()
	 */
	public function getGroupOrderOptions() {
		return array('first'=>Language::_("CpanelManagetwo.order_options.first", true));
	}
	
	/**
	 * Determines which module row should be attempted when a service is provisioned
	 * for the given group based upon the order method set for that group.
	 *
	 * @return int The module row ID to attempt to add the service with
	 * @see Module::getGroupOrderOptions()
	 */
	public function selectModuleRow($module_group_id) {
		if (!isset($this->ModuleManager))
			Loader::loadModels($this, array("ModuleManager"));
		
		$group = $this->ModuleManager->getGroup($module_group_id);
		
		if ($group) {
			switch ($group->add_order) {
				default:
				case "first":
					
					foreach ($group->rows as $row) {
						return $row->id;
					}
					
					break;
			}
		}
		return 0;
	}

	/**
	 * Returns all fields used when adding/editing a package, including any
	 * javascript to execute when the page is rendered with these fields.
	 *
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containing the fields to render as well as any additional HTML markup to include
	 */
	public function getPackageFields($vars=null) {
		Loader::loadHelpers($this, array("Html"));
		// $module_row = $this->getModuleRow($vars->module_row);
		$fields = new ModuleFields();
			
		// Fetch all packages available for the given server or server group
		$module_row = null;
		if (isset($vars->module_group) && $vars->module_group == "") {
			if (isset($vars->module_row) && $vars->module_row > 0) {
				$module_row = $this->getModuleRow($vars->module_row);
			}
			else {
				$rows = $this->getModuleRows();
				if (isset($rows[0]))
					$module_row = $rows[0];
				unset($rows);
			}
		}
		else {
			// Fetch the 1st server from the list of servers in the selected group
			$rows = $this->getModuleRows($vars->module_group);

			if (isset($rows[0]))
				$module_row = $rows[0];
			unset($rows);
		}			
			
		if ($module_row) {
			$types = (array)$this->getLicenseTypes($module_row);
			$groups = (array)$this->getGroupInfo($module_row);			
		}			
		
	
		$license_type = $fields->label(Language::_("CpanelManagetwo.package_fields.license_type", true), "license_type");
		$license_type->attach($fields->fieldSelect("meta[license_type]", array('' => Language::_("CpanelManagetwo.package_fields.please_select", true)) + $this->Html->ifSet($types)  ,
			$this->Html->ifSet($vars->meta['license_type']), array('id'=>"license_type")));
		$fields->setField($license_type);

		
		$groupid  = $fields->label(Language::_("CpanelManagetwo.package_fields.groupid", true), "groupid");
		$groupid->attach($fields->fieldSelect("meta[groupid]", array('' => Language::_("CpanelManagetwo.package_fields.please_select", true)) + $this->Html->ifSet($groups)  ,
			$this->Html->ifSet($vars->meta['groupid']), array('id'=>"groupid")));
		$fields->setField($groupid);
		
		
		return $fields;
	}
	
	/**
	 * Validates input data when attempting to add a package, returns the meta
	 * data to save when adding a package. Performs any action required to add
	 * the package on the remote server. Sets Input errors on failure,
	 * preventing the package from being added.
	 *
	 * @param array An array of key/value pairs used to add the package
	 * @return array A numerically indexed array of meta fields to be stored for this package containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function addPackage(array $vars=null) {
        // Set rules to validate input data
		$this->Input->setRules($this->getPackageRules($vars));
		
		// Build meta data to return
		$meta = array();
		if ($this->Input->validates($vars)) {
			// Return all package meta fields
			foreach ($vars['meta'] as $key => $value) {
				$meta[] = array(
					'key' => $key,
					'value' => $value,
					'encrypted' => 0
				);
			}
		}
		return $meta;
	}
	
	/**
	 * Validates input data when attempting to edit a package, returns the meta
	 * data to save when editing a package. Performs any action required to edit
	 * the package on the remote server. Sets Input errors on failure,
	 * preventing the package from being edited.
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param array An array of key/value pairs used to edit the package
	 * @return array A numerically indexed array of meta fields to be stored for this package containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function editPackage($package, array $vars=null) {
        // Same as adding a package
		return $this->addPackage($vars);
	}
	
	/**
	 * Deletes the package on the remote server. Sets Input errors on failure,
	 * preventing the package from being deleted.
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function deletePackage($package) {
		// Nothing to do
		return null;
	}
	
	/**
	 * Returns an array of key values for fields stored for a module, package,
	 * and service under this module, used to substitute those keys with their
	 * actual module, package, or service meta values in related emails.
	 *
	 * @return array A multi-dimensional array of key/value pairs where each key is one of 'module', 'package', or 'service' and each value is a numerically indexed array of key values that match meta fields under that category.
	 * @see Modules::addModuleRow()
	 * @see Modules::editModuleRow()
	 * @see Modules::addPackage()
	 * @see Modules::editPackage()
	 * @see Modules::addService()
	 * @see Modules::editService()
	 */
	public function getEmailTags() {
		return array(
			'module' => array(),
			'package' => array(),
			'service' => array("manage2_ipaddress")
		);
	}
	
	/**
	 * Attempts to validate service info. This is the top-level error checking method. Sets Input errors on failure.
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param array $vars An array of user supplied info to satisfy the request
	 * @return boolean True if the service validates, false otherwise. Sets Input errors when false.
	 */
	public function validateService($package, array $vars=null) {
        // Set rule to validate IP addresses
        $ip_address_rule = (function_exists("filter_var") ? array("filter_var", FILTER_VALIDATE_IP) : "");
        if (empty($ip_address_rule)) {
            $range = "(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])";
            $ip_address_rule = array(array("matches", "/^(?:" . $range . "\." . $range . "\." . $range . "\." . $range . ")$/"));
        }

		// Set rules
		$rules = array(
            'manage2_ipaddress' => array(
                'format' => array(
                    'rule' => $ip_address_rule,
                    'message' => Language::_("CpanelManagetwo.!error.manage2_ipaddress.format", true)
                )
            )
		);

		$this->Input->setRules($rules);
		return $this->Input->validates($vars);
	}
	

    /**
     * Fetches accepted license types
     *
     * @return array A list of key/value pairs representing the license type and it's name
     */
    private function getLicenseTypes($module_row) {
	
		$this->loadJson();	
		
		$api = $this->getApi($module_row->meta->user_name, $module_row->meta->key);
		$license_types =  $api->fetchPackages();			
		$license_types = $this->Json->decode($this->Json->encode($license_types));	
		
	
        return $license_types->packages->{'@attributes'};
    }

    /**
     * Fetches Groups List
     *
     * @return array A list of key/value pairs of the groups associated with your Manage2 account
     */
    private function getGroupInfo($module_row) {
	
		$this->loadJson();	
		
		$api = $this->getApi($module_row->meta->user_name, $module_row->meta->key);
		$groups =  $api->fetchGroups();			
		$groups = $this->Json->decode($this->Json->encode($groups));		
	
        return $groups->groups->{'@attributes'};
    }
	
    /**
	 * Returns an array of service fields to set for the service using the given input
	 *
	 * @param array $vars An array of key/value input pairs
	 * @param stdClass $package A stdClass object representing the package for the service
	 * @return array An array of key/value pairs representing service fields
	 */
	private function getFieldsFromInput(array $vars, $package) {
		
		Loader::loadHelpers($this, array("Html"));
		
		$fields = array(
            'ip' => isset($vars['manage2_ipaddress']) ? $vars['manage2_ipaddress']: null ,
            'groupid' => $this->Html->ifSet($package->meta->groupid) ,
            'packageid' => $this->Html->ifSet($package->meta->license_type)
			// 'domain' => isset($vars['manage2_domain']) ? $vars['manage2_domain'] : null
		);
		return $fields;
	}

	/**
	 * Builds and returns the rules required to add/edit a module row (e.g. server)
	 *
	 * @param array $vars An array of key/value data pairs
	 * @return array An array of Input rules suitable for Input::setRules()
	 */
	private function getRowRules(&$vars) {
		$rules = array(
			'account_name'=>array(
				'valid'=>array(
					'rule'=>"isEmpty",
					'negate'=>true,
					'message'=>Language::_("CpanelManagetwo.!error.account_name_valid", true)
				)
			),
			'user_name'=>array(
				'valid'=>array(
					'rule'=>"isEmpty",
					'negate'=>true,
					'message'=>Language::_("CpanelManagetwo.!error.user_name_valid", true)
				)
			),
			'key'=>array(
				'valid'=>array(
					'last'=>true,
					'rule'=>"isEmpty",
					'negate'=>true,
					'message'=>Language::_("CpanelManagetwo.!error.remote_key_valid", true)
				),
				'valid_connection'=>array(
					'rule'=>array(array($this, "validateConnection"), $vars['user_name'], $vars['key']),
					'message'=>Language::_("CpanelManagetwo.!error.remote_key_valid_connection", true)
				)
			)
		);
		
		return $rules;
	}

    /**
     * Bulids and returns the rules required for validating packages
     *
     * @param array $vars An array of key/value pairs
     * @return array An array of Input rules suitable for Input::setRules()
     */
    private function getPackageRules($vars) {
	
		$module_row = $this->getModuleRow($vars['module_row']);	
		
		$license_types = array_keys((array)($this->getLicenseTypes($module_row)));
		$groups = array_keys((array)($this->getGroupInfo($module_row)));
		
        foreach ($license_types as &$type)
            $type = (string)$type;

		foreach ($groups as &$type)
            $type = (string)$type;
			
        return array(
			'meta[license_type]' => array(
				'valid' => array(
					'rule' => array("in_array", $license_types),
					'message' => Language::_("CpanelManagetwo.!error.meta[license_type].valid", true)
				)
			),
			'meta[groupid]' => array(
				'valid' => array(
					'rule' => array("in_array", $groups),
					'message' => Language::_("CpanelManagetwo.!error.meta[groupid].valid", true)
				)
			)			
        );
    }	
	

	/**
	 * Parses the response from the API into a stdClass object
	 *
	 * @param string $response The response from the API
	 * @return stdClass A stdClass object representing the response, void if the response was an error
	 */
	private function parseResponse($response) {
	
		$this->loadJson();
		
		$module_row = $this->getModuleRow();

		$command = $this->Json->decode($this->Json->encode($response));	
		$result = $command->{'@attributes'};
		
		$success = true;
		
		// Set internal error
		if (!$result) {
			$this->Input->setErrors(array('api' => array('internal' => Language::_("CpanelManagetwo.!error.api.internal", true))));
			$success = false;
		}
		
		// Only some API requests return status, so only use it if its available
		if (isset($result->status) && $result->status == 0) {
			$this->Input->setErrors(array('api' => array('result' => $result->reason)));
			$success = false;
		}
		// Log the response
		$this->log($module_row->meta->user_name, $result->reason , "output", $success);
		
		// Return if any errors encountered
		if (!$success)
			return;
		
		return $command;
	}

    /**
     * Raw Lookup  
     *
     * @param string $ip The current service IP address
     * @param string $package The package fields
     */
    private function fetchLicenseRaw($ip, $package) {
	
		$module_row = $this->getModuleRow();
		$api = $this->getApi($module_row->meta->user_name, $module_row->meta->key);
		
        // Only change IP address
        $params = array(
            'ip' => $ip,
            'packageid' => $package->meta->license_type
        );

        try {		
			
			$this->log($module_row->meta->user_name . "|fetchLicenseRaw", serialize($params), "input", true);
			$result = $this->parseResponse($api->fetchLicenseRaw($params));
			
        }
        catch (Exception $e) {
            // Internal Error
            $this->Input->setErrors(array('api' => array('internal' => Language::_("CpanelManagetwo.!error.api.internal", true))));
        }
		return $result ;
    }	

    /**
     * Change a License's IP Address 
     *
     * @param string $current_ip The original IP address of the license
     * @param string $new_ip The The new IP address of the license. 
     */
    private function changeIp($current_ip, $new_ip) {
	
		$module_row = $this->getModuleRow();
		$api = $this->getApi($module_row->meta->user_name, $module_row->meta->key);
		
        // Only change IP address
        $params = array(
            'oldip' => $current_ip,
            'newip' => $new_ip
        );

        try {		
			
			$this->log($module_row->meta->user_name . "|changeip", serialize($params), "input", true);
			$result = $this->parseResponse($api->changeip($params));
			
        }
        catch (Exception $e) {
            // Internal Error
            $this->Input->setErrors(array('api' => array('internal' => Language::_("CpanelManagetwo.!error.api.internal", true))));
        }
    }	

    /**
     * Expire Licenses 
     *
     * @param string $ip The current service IP address
     * @param string $package The package fields
     */
    private function expireLicense($ip , $package) {
		
		$module_row = $this->getModuleRow();
		$api = $this->getApi($module_row->meta->user_name, $module_row->meta->key);
			

		$params = array( 
			"ip" => $ip ,
			"packageid" => $package->meta->license_type
			) ;
		
		$this->log($module_row->meta->user_name . "|fetchLicenseId", serialize($params), "input", true);
		$command = $this->parseResponse($api->fetchLicenseId( $params )) ;
		
        if ($this->Input->errors())
			return;		

		unset($params);
		
		try {			
			// Only change IP address
			$params = array(
				'liscid' => $command->licenseid,
				'reason' => "Automagic Expiration (Blesta)", 
				'expcode' => "normal"
			);					
			
			$this->log($module_row->meta->user_name . "|expireLicense", serialize($params), "input", true);
			$result = $this->parseResponse($api->expireLicense($params));
			
			if ($this->Input->errors())
				return;			

		}
		catch (Exception $e) {
			// Internal Error
			$this->Input->setErrors(array('api' => array('internal' =>  Language::_("CpanelManagetwo.!error.api.internal", true))));
		}

    }	
	
    /**
     * Reactivate expired licenses 
     *
     * @param string $ip The current service IP address
     * @param string $package The package fields	 
     */
    private function reactivateLicense($ip , $package) {
		
		$module_row = $this->getModuleRow();
		$api = $this->getApi($module_row->meta->user_name, $module_row->meta->key);
			

		$params = array( 
			"ip" => $ip ,
			"packageid" => $package->meta->license_type
			) ;
		
		$this->log($module_row->meta->user_name . "|fetchLicenseId", serialize($params), "input", true);
		$command = $this->parseResponse($api->fetchLicenseId( $params )) ;
		
        if ($this->Input->errors())
			return;		

		unset($params);
		
		try {			
			// Only change IP address
			$params = array(
				'liscid' => $command->licenseid
			);					
			
			$this->log($module_row->meta->user_name . "|reactivateLicense", serialize($params), "input", true);
			$result = $this->parseResponse($api->reactivateLicense($params));
			
			if ($this->Input->errors())
				return;			

		}
		catch (Exception $e) {
			// Internal Error
			$this->Input->setErrors(array('api' => array('internal' =>  Language::_("CpanelManagetwo.!error.api.internal", true))));
		}

    }	
	
	/**
	 * Adds the service to the remote server. Sets Input errors on failure,
	 * preventing the service from being added.
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param array $vars An array of user supplied info to satisfy the request
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being added (if the current service is an addon service service and parent service has already been provisioned)
	 * @param string $status The status of the service being added. These include:
	 * 	- active
	 * 	- canceled
	 * 	- pending
	 * 	- suspended
	 * @param array $options A set of options for the service (optional)
	 * @return array A numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function addService($package, array $vars=null, $parent_package=null, $parent_service=null, $status="pending", $options = array()) {
		// Get module row and API
		$result = null;
		$module_row = $this->getModuleRow();
		$api = $this->getApi($module_row->meta->user_name, $module_row->meta->key);

        // Get fields
        $params = $this->getFieldsFromInput((array)$vars, $package);

		$this->validateService($package, $vars);

        if ($this->Input->errors())
			return;
			
		$params['dryrun'] = 0; 

        // Only provision the service if 'use_module' is true
		if ($vars['use_module'] == "true") {
            try {
				$this->loadJson();
				$this->log($module_row->meta->user_name . "|activateLicense", serialize($params), "input", true);
				$result = $this->parseResponse($api->activateLicense($params));

				if ($this->Input->errors())
					return ;				
				
            }
            catch (Exception $e) {
                // Internal Error
				$this->Input->setErrors(array('api' => array('internal' => Language::_("CpanelManagetwo.!error.api.internal", true))));
            }
            
            if ($this->Input->errors())
				return;
        }

        
		// Return service fields
		return array(
			array(
				'key' => "manage2_ipaddress",
				'value' => $params['ip'],
				'encrypted' => 0
			)		
        );
	}

	/**
	 * Edits the service on the remote server. Sets Input errors on failure,
	 * preventing the service from being edited.
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param array $vars An array of user supplied info to satisfy the request
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being edited (if the current service is an addon service)
	 * @return array A numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function editService($package, $service, array $vars=null, $parent_package=null, $parent_service=null) {

        // Validate the service-specific fields
		$this->validateService($package, $vars);

        if ($this->Input->errors())
			return;
       
        // Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		// Check for fields that changed
		$delta = array();
		foreach ($vars as $key => $value) {
			if (!array_key_exists($key, $service_fields) || $vars[$key] != $service_fields->$key)
				$delta[$key] = $value;
		}

        // Only provision the service if 'use_module' is true
		if ($vars['use_module'] == "true") {
            // Only change IP address
            $current_ip = (isset($service_fields->manage2_ipaddress) ? $service_fields->manage2_ipaddress : "");
            $new_ip = (isset($delta['manage2_ipaddress']) ? $delta['manage2_ipaddress'] : $current_ip);
			
			$this->changeIp($current_ip, $new_ip);		
            
            if ($this->Input->errors())
				return;
        }
        
        // Return all the service fields
		$fields = array();
		foreach ($service_fields as $key => $value)
			$fields[] = array('key' => $key, 'value' => (isset($delta[$key]) ? $delta[$key] : $value), 'encrypted' => 0);

		return $fields;
	}
	

	/**
	 * Cancels the service on the remote server. Sets Input errors on failure,
	 * preventing the service from being canceled.
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being canceled (if the current service is an addon service)
	 * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function cancelService($package, $service, $parent_package=null, $parent_service=null) {
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$ip = (isset($service_fields->manage2_ipaddress) ? $service_fields->manage2_ipaddress : "");
		
		try {
			
			$this->expireLicense($ip, $package);		
			
			if ($this->Input->errors())
				return;			

		}
		catch (Exception $e) {
			// Internal Error
			$this->Input->setErrors(array('api' => array('internal' => print_r($e))));
		}
		
		return null;
	}

	/**
	 * Suspends the service on the remote server. Sets Input errors on failure,
	 * preventing the service from being suspended.
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being suspended (if the current service is an addon service)
	 * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function suspendService($package, $service, $parent_package=null, $parent_service=null) {
		// Suspend the service by cancelling it
		$this->cancelService($package, $service, $parent_package, $parent_service);
	}
	
	/**
	 * Unsuspends the service on the remote server. Sets Input errors on failure,
	 * preventing the service from being unsuspended.
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being unsuspended (if the current service is an addon service)
	 * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function unsuspendService($package, $service, $parent_package=null, $parent_service=null) {
	
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$ip = (isset($service_fields->manage2_ipaddress) ? $service_fields->manage2_ipaddress : "");
		
		// Reactivate expired licenses since suspending the service expired it
		
		try {
			
			$this->reactivateLicense($ip, $package);		
			
			if ($this->Input->errors())
				return;			

		}
		catch (Exception $e) {
			// Internal Error
			$this->Input->setErrors(array('api' => array('internal' => print_r($e))));
		}
		
		return null;
		
	}
	
	/**
	 * Allows the module to perform an action when the service is ready to renew.
	 * Sets Input errors on failure, preventing the service from renewing.
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being renewed (if the current service is an addon service)
	 * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function renewService($package, $service, $parent_package=null, $parent_service=null) {
		// Nothing to do
		return null;
	}
	
	/**
	 * Updates the package for the service on the remote server. Sets Input
	 * errors on failure, preventing the service's package from being changed.
	 *
	 * @param stdClass $package_from A stdClass object representing the current package
	 * @param stdClass $package_to A stdClass object representing the new package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being changed (if the current service is an addon service)
	 * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function changeServicePackage($package_from, $package_to, $service, $parent_package=null, $parent_service=null) {
		// Nothing to do
		return null;
	}
	

	/**
	 * Returns all fields to display to an admin attempting to add a service with the module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 */
	public function getAdminAddFields($package, $vars=null) {
		Loader::loadHelpers($this, array("Html"));
		
		$fields = new ModuleFields();

        // Set the IP address as selectable options
		$ip = $fields->label(Language::_("CpanelManagetwo.service_fields.ipaddress", true), "manage2_ipaddress");
		$ip->attach($fields->fieldText("manage2_ipaddress", $this->Html->ifSet($vars->manage2_ipaddress), array('id'=>"manage2_ipaddress")));
        // Add tooltip
		$tooltip = $fields->tooltip(Language::_("CpanelManagetwo.service_field.tooltip.ipaddress", true));
		$ip->attach($tooltip);
		$fields->setField($ip);
		
		return $fields;
	}
	
	/**
	 * Returns all fields to display to a client attempting to add a service with the module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 */	
	public function getClientAddFields($package, $vars=null) {
		// Same as admin fields
        return $this->getAdminAddFields($package, $vars);
	}
	
	/**
	 * Returns all fields to display to an admin attempting to edit a service with the module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 */	
	public function getAdminEditFields($package, $vars=null) {
		Loader::loadHelpers($this, array("Html"));

		$fields = new ModuleFields();


        // Set the IP address as selectable options
		$ip = $fields->label(Language::_("CpanelManagetwo.service_fields.ipaddress", true), "manage2_ipaddress");
		$ip->attach($fields->fieldText("manage2_ipaddress", $this->Html->ifSet($vars->manage2_ipaddress), array('id'=>"manage2_ipaddress")));
        // Add tooltip
		$tooltip = $fields->tooltip(Language::_("CpanelManagetwo.service_field.tooltip.ipaddress", true));
		$ip->attach($tooltip);
		$fields->setField($ip);

		return $fields;
	}
	
	/**
	 * Fetches the HTML content to display when viewing the service info in the
	 * admin interface.
	 *
	 * @param stdClass $service A stdClass object representing the service
	 * @param stdClass $package A stdClass object representing the service's package
	 * @return string HTML content containing information to display when viewing the service info
	 */
	public function getAdminServiceInfo($service, $package) {
		$module_row = $this->getModuleRow();
		
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("admin_service_info", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "cpanel_managetwo" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));
		
		$this->view->set("module_row", $module_row);
		$this->view->set("package", $package);
		$this->view->set("service", $service);
		$this->view->set("service_fields", $this->serviceFieldsToObject($service->fields));
		
		return $this->view->fetch();
	}
	
	/**
	 * Fetches the HTML content to display when viewing the service info in the
	 * client interface.
	 *
	 * @param stdClass $service A stdClass object representing the service
	 * @param stdClass $package A stdClass object representing the service's package
	 * @return string HTML content containing information to display when viewing the service info
	 */
	public function getClientServiceInfo($service, $package) {
		$module_row = $this->getModuleRow();
		
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("client_service_info", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "cpanel_managetwo" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));

		$this->view->set("module_row", $module_row);
		$this->view->set("package", $package);
		$this->view->set("service", $service);
		$this->view->set("service_fields", $this->serviceFieldsToObject($service->fields));
		
		return $this->view->fetch();
	}

    /**
	 * Returns all tabs to display to a client when managing a service whose
	 * package uses this module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @return array An array of tabs in the format of method => title. Example: array('methodName' => "Title", 'methodName2' => "Title2")
	 */
	public function getClientTabs($package) {
		return array(
            'tabClientIp' => array('name' => Language::_("CpanelManagetwo.tab_ip", true), 'icon' => "fa fa-edit")
		);
	}

    /**
	 * Tab To list info licence for clients
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param array $get Any GET parameters
	 * @param array $post Any POST parameters
	 * @param array $files Any FILES parameters
	 * @return string The string representing the contents of this tab
	 */
	public function tabClientIp($package, $service, array $get=null, array $post=null, array $files=null) {
        $this->view = new View("tab_client_ip", "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Date"));

        // Fetch the service fields
        $service_fields = $this->serviceFieldsToObject($service->fields);
 		$license_info = $this->fetchLicenseRaw($service_fields->manage2_ipaddress , $package) ;
		
		$this->view->set("service_fields", $service_fields);
		$this->view->set("license_info", $license_info->license->{'@attributes'} );

		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "cpanel_managetwo" . DS);
		return $this->view->fetch();
    }

	/**
	 * Returns all tabs to display to an admin when managing a service whose
	 * package uses this module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @return array An array of tabs in the format of method => title. Example: array('methodName' => "Title", 'methodName2' => "Title2")
	 */
	public function getAdminTabs($package) {
		return array(
			'tabAdminIp' => Language::_("CpanelManagetwo.tab_ip", true)
		);
	}

    /**
	 * Tab To list info licence for Admins
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param array $get Any GET parameters
	 * @param array $post Any POST parameters
	 * @param array $files Any FILES parameters
	 * @return string The string representing the contents of this tab
	 */
	public function tabAdminIp($package, $service, array $get=null, array $post=null, array $files=null) {
        $this->view = new View("tab_admin_ip", "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Date"));

        // Fetch the service fields
        $service_fields = $this->serviceFieldsToObject($service->fields);
 		$license_info = $this->fetchLicenseRaw($service_fields->manage2_ipaddress , $package) ;
		
		$this->view->set("service_fields", $service_fields);
		$this->view->set("license_info", $license_info->license->{'@attributes'} );

		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "cpanel_managetwo" . DS);
		return $this->view->fetch();
    }	
	
}
?>