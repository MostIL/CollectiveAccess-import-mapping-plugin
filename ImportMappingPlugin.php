<?php
/* ----------------------------------------------------------------------
 * ImportMappingPlugin.php 
 * ----------------------------------------------------------------------
 * Israel Ministry of Sports and Culture 
 * 
 * Plugin for CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * For more information about Israel Ministry of Sports and Culture visit:
 * https://www.gov.il/en/Departments/ministry_of_culture_and_sport
 *
 * For more information about CollectiveAccess visit:
 * http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license.
 *
 * This plugin for CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details. 
 * ----------------------------------------------------------------------
 */

	class ImportMappingPlugin extends BaseApplicationPlugin {
		# -------------------------------------------------------
		private $opo_config;
		# -------------------------------------------------------
		public function __construct($ps_plugin_path) {
			$this->description = _t('Chack data in list module in CollectiveAccess');
			$this->opo_config = Configuration::load($ps_plugin_path . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'ImportMapping.conf');

			parent::__construct();
		}
		# -------------------------------------------------------
		/**
		 * Override checkStatus() to return true
		 */
		public function checkStatus() {
			return array(
				'description' => $this->getDescription(),
				'errors' => array(),
				'warnings' => array(),
				'available' => true
			);
		}
		# -------------------------------------------------------
		/**
		 * Record save activity
		 */
		public function hookSaveItem($pa_params) {
			return $pa_params;
		}
		# -------------------------------------------------------
		/**
		 * Insert ImportMapping configuration option into "import" menu
		 */
		 public function hookRenderMenuBar($pa_menu_bar) {
			if ($o_req = $this->getRequest()) {
				if (isset($pa_menu_bar['Import'])) {
					$va_menu_items = $pa_menu_bar['Import']['navigation'];
					if (!is_array($va_menu_items)) 
					{ 
						$va_menu_items = array(); 
					}
				} else {
					$va_menu_items = array();
				}
				$va_menu_items['ImportMapping'] = array(
					'displayName' => _t('Import Mapping'),
					"default" => array(
						'module' => 'ImportMapping', 
						'controller' => 'ImportMapping', 
						'action' => 'Index'
					)
				);
				
				$pa_menu_bar['Import']['navigation'] = $va_menu_items;
			} 
			return $pa_menu_bar;
		}
		# -------------------------------------------------------
		/**
		 * Get plugin user actions
		 */
		static public function getRoleActionList() {
			return array();
		}
		# -------------------------------------------------------

//==================================================================//
 
	}
?>