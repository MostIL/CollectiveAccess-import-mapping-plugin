<?php
/* ----------------------------------------------------------------------
 * app/plugins/museumAdmin/modules/Museums.php :
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


include_once(__CA_APP_DIR__."/helpers/utilityHelpers.php");


BaseModel::$s_ca_models_definitions['mana_museums'] = array(
 	'NAME_SINGULAR' 	=> _t('museum'),
 	'NAME_PLURAL' 		=> _t('museums'),
 	'FIELDS' 			=> array(
 		'museum_id' => array(
				'FIELD_TYPE' => FT_NUMBER, 'DISPLAY_TYPE' => DT_HIDDEN, 
				'IDENTITY' => true, 'DISPLAY_WIDTH' => 10, 'DISPLAY_HEIGHT' => 1,
				'IS_NULL' => false, 
				'DEFAULT' => '',
				'LABEL' => _t('Museum id'), 'DESCRIPTION' => _t('Unique numeric identifier used by CollectiveAccess internally to identify this Museum')
		),
		'museum_name' => array(
				'FIELD_TYPE' => FT_TEXT, 'DISPLAY_TYPE' => DT_FIELD, 
				'DISPLAY_WIDTH' => 40, 'DISPLAY_HEIGHT' => 1,
				'IS_NULL' => false, 
				'DEFAULT' => '',
				'LABEL' => _t('Museum name'), 'DESCRIPTION' => _t('The museum name'),
				'BOUNDS_LENGTH' => array(1,255)
		),
		'idno' => array(
				'FIELD_TYPE' => FT_TEXT, 'DISPLAY_TYPE' => DT_FIELD, 
				'DISPLAY_WIDTH' => 60, 'DISPLAY_HEIGHT' => 1,
				'IS_NULL' => false, 
				'DEFAULT' => '',
				'LABEL' => _t('idno'), 'DESCRIPTION' => _t('The idno of this museum.'),
				'BOUNDS_LENGTH' => array(1,255)
		),
 	)
);

class museums extends BaseModel {
	use SyncableBaseModel;
	
	# ---------------------------------
	# --- Object attribute properties
	# ---------------------------------
	# Describe structure of content object's properties - eg. database fields and their
	# associated types, what modes are supported, et al.
	#
	private $_museum_pref_defs;
	# ------------------------------------------------------
	# --- Basic object parameters
	# ------------------------------------------------------
	# what table does this class represent?
	protected $TABLE = 'mana_museums';
	      
	# what is the primary key of the table?
	protected $PRIMARY_KEY = 'museum_id';

	# ------------------------------------------------------
	# --- Properties used by standard editing scripts
	# 
	# These class properties allow generic scripts to properly display
	# records from the table represented by this class
	#
	# ------------------------------------------------------

	# Array of fields to display in a listing of records from this table
	protected $LIST_FIELDS = array('museum_name');

	# When the list of "list fields" above contains more than one field,
	# the LIST_DELIMITER text is displayed between fields as a delimiter.
	# This is typically a comma or space, but can be any string you like
	protected $LIST_DELIMITER = ' ';

	# What you'd call a single record from this table (eg. a "person")
	protected $NAME_SINGULAR;

	# What you'd call more than one record from this table (eg. "people")
	protected $NAME_PLURAL;

	# List of fields to sort listing of records by; you can use 
	# SQL 'ASC' and 'DESC' here if you like.
	protected $ORDER_BY = array('museum_name');

	# Maximum number of record to display per page in a listing
	protected $MAX_RECORDS_PER_PAGE = 20; 

	# How do you want to page through records in a listing: by number pages ordered
	# according to your setting above? Or alphabetically by the letters of the first
	# LIST_FIELD?
	protected $PAGE_SCHEME = 'alpha'; # alpha [alphabetical] or num [numbered pages; default]

	# If you want to order records arbitrarily, add a numeric field to the table and place
	# its name here. The generic list scripts can then use it to order table records.
	protected $RANK = '';
	
	
	# ------------------------------------------------------
	# Hierarchical table properties
	# ------------------------------------------------------
	protected $HIERARCHY_TYPE				=	null;
	protected $HIERARCHY_LEFT_INDEX_FLD 	= 	null;
	protected $HIERARCHY_RIGHT_INDEX_FLD 	= 	null;
	protected $HIERARCHY_PARENT_ID_FLD		=	null;
	protected $HIERARCHY_DEFINITION_TABLE	=	null;
	protected $HIERARCHY_ID_FLD				=	null;
	protected $HIERARCHY_POLY_TABLE			=	null;
	
	# ------------------------------------------------------
	# Change logging
	# ------------------------------------------------------
	protected $UNIT_ID_FIELD = null;
	protected $LOG_CHANGES_TO_SELF = true;
	protected $LOG_CHANGES_USING_AS_SUBJECT = array(
		"FOREIGN_KEYS" => array(
		
		),
		"RELATED_TABLES" => array(
		
		)
	);
	
	/** 
	 * Container for persistent museum-specific variables
	 */
	private $opa_museum_vars;
	private $opa_museum_vars_have_changed = false;
	
	/** 
	 * Container for volatile (often changing) persistent museum-specific variables
	 * of limited size. This is meant for storage of values that change on every request. By
	 * segregating these values from less volatile (and often much larger) museum var data we can
	 * avoid the cost of writing large blocks of data to the database on every request
	 */
	private $opa_volatile_museum_vars;
	private $opa_volatile_museum_vars_have_changed = false;
	
	
	# ------------------------------------------------------
	# Search
	# ------------------------------------------------------
	protected $SEARCH_CLASSNAME = 'museumSearch';
	protected $SEARCH_RESULT_CLASSNAME = 'museumSearchResult';
	
	
	# ------------------------------------------------------
	# $FIELDS contains information about each field in the table. The order in which the fields
	# are listed here is the order in which they will be returned using getFields()

	protected $FIELDS;
	
	/**
	 * authentication configuration
	 */
	protected $opo_auth_config = null;

	private $opo_log = null;
	
	/**
	 * List of tables that can have bundle- or type-level access control
	 */
	static $s_bundlable_tables = array(
		'ca_objects', 'ca_object_lots', 'ca_entities', 'ca_places', 'ca_occurrences',
		'ca_collections', 'ca_storage_locations', 'ca_loans', 'ca_movements',
		'ca_object_representations', 'ca_representation_annotations', 'ca_sets', 
		'ca_set_items', 'ca_lists', 'ca_list_items', 'ca_tours', 'ca_tour_stops'
	);
	
	# ------------------------------------------------------
	# --- Constructor
	#
	# This is a function called when a new instance of this object is created. This
	# standard constructor supports three calling modes:
	#
	# 1. If called without parameters, simply creates a new, empty objects object
	# 2. If called with a single, valid primary key value, creates a new objects object and loads
	#    the record identified by the primary key value
	#
	# ------------------------------------------------------
	public function __construct($pn_id=null, $pb_use_cache=false) {
		parent::__construct($pn_id, $pb_use_cache);	# call superclass constructor	

		$this->opo_log = new Eventlog();
	}
	# ----------------------------------------
	/**
	 * Loads museum record.
	 *
	 * @access public
	 * @param integer $pm_museum_id museum id to load. If you pass a string instead of an integer, the record with a museum name matching the string will be loaded.
	 * @return bool Returns true if no error, false if error occurred
	 */	
	public function load($pm_museum_id=null, $pb_use_cache=false) {
		if (is_numeric($pm_museum_id)) {
			$vn_rc = parent::load($pm_museum_id);
		} else {
			if (is_array($pm_museum_id)) {
				$vn_rc = parent::load($pm_museum_id);
			} else {
				$vn_rc = parent::load(array("museum_name" => $pm_museum_id));
			}
		}
		
		# load museum vars (the get() method automatically unserializes the data)
		$this->opa_museum_vars = $this->get("vars");
		$this->opa_volatile_museum_vars = $this->get("volatile_vars");
		
		if (!isset($this->opa_museum_vars) || !is_array($this->opa_museum_vars)) {
			$this->opa_museum_vars = array();
		}
		if (!isset($this->opa_volatile_museum_vars) || !is_array($this->opa_volatile_museum_vars)) {
			$this->opa_volatile_museum_vars = array();
		}
		return $vn_rc;
	}
	# ----------------------------------------
	/**
	 * Creates new museum record. You must set all required museum fields before calling this method. If errors occur you can use the standard Table class error handling methods to figure out what went wrong.
	 *
	 * Required fields are museum_name, fname and idno.
	 *
	 * @access public 
	 * @return bool Returns true if no error, false if error occurred
	 */	
	public function insert($pa_options=null) {

		# set museum vars (the set() method automatically serializes the vars array)
		$this->set("vars",$this->opa_museum_vars);
		$this->set("volatile_vars",$this->opa_volatile_museum_vars);
		$this->set('table_num',1000);
		$pa_options['dont_do_search_indexing'] = true;
		$pa_options['dontLogChange'] = true;
		if ($vn_rc = parent::insert($pa_options)) {
			$this->setGUID($pa_options);
		}
		return $vn_rc;
	}
	# ----------------------------------------
	/**
	 * Saves changes to museum record. You must make sure all required museum fields are set before calling this method. If errors occur you can use the standard Table class error handling methods to figure out what went wrong.
	 *
	 * Required fields are museum_name, fname and idno.
	 *
	 * If you do not call this method at the end of your request changed museum vars will not be saved! If you are also using the Auth class, the Auth->close() method will call this for you.
	 *
	 * @access public
	 * @return bool Returns true if no error, false if error occurred
	 */	
	public function update($pa_options=null) {
		$this->clearErrors();
		
		# set museum vars (the set() method automatically serializes the vars array)
		if ($this->opa_museum_vars_have_changed) {
			$this->set("vars",$this->opa_museum_vars);
		}
		if ($this->opa_volatile_museum_vars_have_changed) {
			$this->set("volatile_vars",$this->opa_volatile_museum_vars);
		}
		
		$va_changed_fields = $this->getChangedFieldValuesArray();
		unset($va_changed_fields['vars']);
		unset($va_changed_fields['volatile_vars']);
		
		$pa_options['dont_do_search_indexing'] = true;
		$pa_options['dontLogChange'] = true;

		return parent::update($pa_options);
	}
	# ----------------------------------------
	/**
	 * Deletes museum. Unlike standard model rows, museums rows should never actually be deleted.
	 * So this version of delete() marks the row as deleted by setting museums.museumclass = 255 and *not* invoking to BaseModel::delete()
	 * @access public
	 * @return bool Returns true if no error, false if error occurred
	 */	
	public function delete($pb_delete_related=false, $pa_options=null, $pa_fields=null, $pa_table_list=null) {
		$this->clearErrors();
		//$vn_primary_key = $this->getPrimaryKey();

		//$vn_rc = $this->update($pa_options);
		
		/*if($vn_primary_key && $vn_rc && caGetOption('hard', $pa_options, false)) {
			$this->removeGUID($vn_primary_key);
		}*/
		if ($this->delete(true, array('hard' => true))){
			return true;
		}else{
			return false;
		}
	}
	# ----------------------------------------
	public function set($pa_fields, $pm_value="", $pa_options=null) {
		if (!is_array($pa_fields)) {
			$pa_fields = array($pa_fields => $pm_value);
		}

		return parent::set($pa_fields,$pm_value,$pa_options);
	}
	# ----------------------------------------
	# --- Utility
	# ----------------------------------------
	/**
	 *
	 */
	public function getMuseumNameFormattedForLookup() {
		if (!($this->getPrimaryKey())) { return null; }
		
		$va_values = $this->getFieldValuesArray();
		foreach($va_values as $vs_key => $vs_val) {
			$va_values["mana_museums.{$vs_key}"] = $vs_val;
		}
		
		return caProcessTemplate(join($this->getAppConfig()->getList('ca_museums_lookup_delimiter'), $this->getAppConfig()->getList('ca_museums_lookup_settings')), $va_values, array());
	}
	# ----------------------------------------
	# --- museum variables
	# ----------------------------------------
	/**
	 * Sets museum variable. museum variables are names ("keys") with associated values (strings, numbers or arrays).
	 * Once a museum variable is set its value persists across instantiations until deleted or changed.
	 *
	 * Changes to museum variables are saved when the insert() (for new museum records) or update() (for existing museum records)
	 * method is called. If you do not call either of these any changes will be lost when the request completes.
	 *
	 * @access public
	 * @param string $ps_key Name of museum variable
	 * @param mixed $pm_val Value of museum variable. Can be string, number or array.
	 * @param array $pa_options Associative array of options. Supported options are:
	 *		- ENTITY_ENCODE_INPUT = Convert all "special" HTML characters in variable value to entities; default is true
	 *		- URL_ENCODE_INPUT = Url encodes variable value; default is  false
	 *		- volatile = Places value in "volatile" variable storage, which is usually faster. Only store small values, not large blocks of text or binary data, that are expected to frequently as volatile.
	 * @return bool Returns true on successful save, false if the variable name or value was invalid
	 */	
	public function setVar ($ps_key, $pm_val, $pa_options=null) {
		if (is_object($pm_val)) { return false; }
		
		if (!is_array($pa_options)) { $pa_options = array(); }
		
		$this->clearErrors();
		if ($ps_key) {			
			if (isset($pa_options['volatile']) && $pa_options['volatile']) {
				$va_vars =& $this->opa_volatile_museum_vars;
				$vb_has_changed =& $this->opa_volatile_museum_vars_have_changed;
				
				unset($this->opa_museum_vars[$ps_key]);
			} else {
				$va_vars =& $this->opa_museum_vars;
				$vb_has_changed =& $this->opa_museum_vars_have_changed;
				
				unset($this->opa_volatile_museum_vars_have_changed[$ps_key]);
			}
			
			if (isset($pa_options["ENTITY_ENCODE_INPUT"]) && $pa_options["ENTITY_ENCODE_INPUT"]) {
				if (is_string($pm_val)) {
					$vs_proc_val = htmlentities(html_entity_decode($pm_val));
				} else {
					$vs_proc_val = $pm_val;
				}
			} else {
				if (isset($pa_options["URL_ENCODE_INPUT"]) && $pa_options["URL_ENCODE_INPUT"]) {
					$vs_proc_val = urlencode($pm_val);
				} else {
					$vs_proc_val = $pm_val;
				}
			}
			
			if (
				(
					(is_array($vs_proc_val) && !is_array($va_vars[$ps_key]))
					||
					(!is_array($vs_proc_val) && is_array($va_vars[$ps_key]))
					||
					(is_array($vs_proc_val) && (is_array($va_vars[$ps_key])) && (sizeof($vs_proc_val) != sizeof($va_vars[$ps_key])))
					||
					(md5(print_r($vs_proc_val, true)) != md5(print_r($va_vars[$ps_key], true)))
				)
			) {
				$vb_has_changed = true;
				$va_vars[$ps_key] = $vs_proc_val;
			} else {
				if ((string)$vs_proc_val != (string)$va_vars[$ps_key]) {
					$vb_has_changed = true;
					$va_vars[$ps_key] = $vs_proc_val;
				}
			}
			return true;
		}
		return false;
	}
	# ----------------------------------------
	/**
	 * Deletes museum variable. Once deleted, you must call insert() (for new museum records) or update() (for existing museum records)
	 * to make the deletion permanent.
	 *
	 * @access public
	 * @param string $ps_key Name of museum variable
	 * @return bool Returns true if variable was defined, false if it didn't exist
	 */	
	public function deleteVar ($ps_key) {
		$this->clearErrors();
		
		if (isset($this->opa_museum_vars[$ps_key])) {
			unset($this->opa_museum_vars[$ps_key]);
			$this->opa_museum_vars_have_changed = true;
			return true;
		} else {
			if (isset($this->opa_volatile_museum_vars[$ps_key])) {
				unset($this->opa_volatile_museum_vars[$ps_key]);
				$this->opa_volatile_museum_vars_have_changed = true;
				return true;
			} else {
				return false;
			}
		}
	}
	# ----------------------------------------
	/**
	 * Returns value of museum variable. Returns null if variable does not exist.
	 *
	 * @access public
	 * @param string $ps_key Name of museum variable
	 * @return mixed Value of variable (string, number or array); null is variable is not defined.
	 */	
	public function getVar ($ps_key) {
		$this->clearErrors();
		if (isset($this->opa_museum_vars[$ps_key])) {
			return (is_array($this->opa_museum_vars[$ps_key])) ? $this->opa_museum_vars[$ps_key] : stripSlashes($this->opa_museum_vars[$ps_key]);
		} else {
			if (isset($this->opa_volatile_museum_vars[$ps_key])) {
				return (is_array($this->opa_volatile_museum_vars[$ps_key])) ? $this->opa_volatile_museum_vars[$ps_key] : stripSlashes($this->opa_volatile_museum_vars[$ps_key]);
			}
		}
		return null;
	}
	# ----------------------------------------
	/**
	 * Returns list of museum variable names
	 *
	 * @access public
	 * @return array Array of museumvar names, or empty array if none are defined
	 */	
	public function getVarKeys() {
		$va_keys = array();
		if (isset($this->opa_museum_vars) && is_array($this->opa_museum_vars)) {
			$va_keys = array_keys($this->opa_museum_vars);
		}
		if (isset($this->opa_volatile_museum_vars) && is_array($this->opa_volatile_museum_vars)) {
			$va_keys = array_merge($va_keys, array_keys($this->opa_volatile_museum_vars));
		}
		
		return $va_keys;
	}
	# ----------------------------------------
	/** 
	 * Returns list of museums
	 *
	 * @param array $pa_options Optional array of options. Options include:
	 *		sort
	 *		sort_direction
	 *		museumclass
	 *	@return array List of museums. Array is keyed on museum_id and value is array with all mana_museum fields + the last_login time as a unix timestamp
	 *
	 */
	public function getMuseumList($pa_options=null) {
		$ps_sort_field= isset($pa_options['sort']) ? $pa_options['sort'] : '';
		$ps_sort_direction= isset($pa_options['sort_direction']) ? $pa_options['sort_direction'] : 'asc';
		$pa_museumclass= isset($pa_options['museumclass']) ? $pa_options['museumclass'] : array();

		if(!is_array($pa_museumclass)) { $pa_museumclass = array($pa_museumclass); }

		$o_db = $this->getDb();
		
		$va_valid_sorts = array('idno', 'museum_name');
		if (!in_array($ps_sort_field, $va_valid_sorts)) {
			$ps_sort_field = 'idno';
		}
		
		if($ps_sort_direction != 'desc') {
			$ps_sort_direction = 'asc';
		}
		
		$va_query_params = array();
		$vs_museum_class_sql = '';
		if (is_array($pa_museumclass) && sizeof($pa_museumclass)) {
			$vs_museum_class_sql = " WHERE museumclass IN (?)";
			$va_query_params[] = $pa_museumclass;
		}
		
		$vs_sort = "ORDER BY {$ps_sort_field} {$ps_sort_direction}";

		$qr_museums = $o_db->query("
			SELECT *
			FROM mana_museums
				{$vs_museum_class_sql}
			{$vs_sort}
		", $va_query_params);
		
		$va_museums = array();
		while($qr_museums->nextRow()) {
			if (!is_array($va_vars = $qr_museums->getVars('vars'))) { $va_vars = array(); }
			
			if (is_array($va_volatile_vars = $qr_museums->getVars('volatile_vars'))) {
				$va_vars = array_merge($va_vars, $va_volatile_vars);
			}
 			$va_museums[$qr_museums->get('museum_id')] = array_merge($qr_museums->getRow());
 		}
		
		return $va_museums;
	}
	# ----------------------------------------
	/**
	 * Returns HTML multiple <select> with list of "full" museums
	 *
	 * @param array $pa_options (optional) array of options. Keys are:
	 *		size = height of multiple select, in rows; default is 8
	 *		name = HTML form element name to apply to role <select>; default is 'groups'
	 *		id = DOM id to apply to role <select>; default is no id
	 *		label = String to label form element with
	 *		selected = museum_id values to select
	 * @return string Returns HTML containing form element and form label
	 */
	public function museumListAsHTMLFormElement($pa_options=null) {
		$vn_size = (isset($pa_options['size']) && ($pa_options['size'] > 0)) ? $pa_options['size'] : 8;
		$vs_name = (isset($pa_options['name'])) ? $pa_options['name'] : 'museums';
		$vs_id = (isset($pa_options['id'])) ? $pa_options['id'] : '';
		$vs_label = (isset($pa_options['label'])) ? $pa_options['label'] : _t('museums');
		$va_selected = (isset($pa_options['selected']) && is_array($pa_options['selected'])) ? $pa_options['selected'] : array();
		
		$va_museums = $this->getMuseumList($pa_options);
		$vs_buf = '';
		
		if (sizeof($va_museums)) {
			$vs_buf .= "<select multiple='1' name='{$vs_name}[]' size='{$vn_size}' id='{$vs_id}'>\n";
			foreach($va_museums as $vn_museum_id => $va_museum_info) {
				$SELECTED = (in_array($vn_museum_id, $va_selected)) ? "SELECTED='1'" : "";
				$vs_buf .= "<option value='{$vn_museum_id}' {$SELECTED}>".$va_museum_info['museum_name'].' '.$va_museum_info['idno'].($va_museum_info['email'] ? " (".$va_museum_info['email'].")" : "")."</option>\n";
			}
			$vs_buf .= "</select>\n";
		}
		if ($vs_buf && ($vs_format = $this->_CONFIG->get('form_element_display_format'))) {
			$vs_format = str_replace("^ELEMENT", $vs_buf, $vs_format);
			$vs_format = str_replace("^LABEL", $vs_label, $vs_format);
			$vs_format = str_replace("^ERRORS", '', $vs_format);
			$vs_buf = str_replace("^EXTRA", '', $vs_format);
		}
		
		return $vs_buf;
	}
	# ----------------------------------------
	# --- Museum preferences
	# ----------------------------------------
	/**
	 * Returns value of museum preference. Returns null if preference does not exist.
	 *
	 * @access public
	 * @param string $ps_pref Name of museum preference
	 * @return mixed Value of variable (string, number or array); null is variable is not defined.
	 */	
	public function getPreference($ps_pref) {
		if ($this->isValidPreference($ps_pref)) {
			$va_prefs = $this->getVar("_museum_preferences");
			
			$va_pref_info = $this->getPreferenceInfo($ps_pref);
			
			if (!isset($va_prefs)) {
				return $this->getPreferenceDefault($ps_pref);
			}
			if(isset($va_prefs[$ps_pref])) {
				return (!is_null($va_prefs[$ps_pref])) ? $va_prefs[$ps_pref] : $this->getPreferenceDefault($ps_pref);
			}
			return $this->getPreferenceDefault($ps_pref);
		} else {
			//$this->postError(920, _t("%1 is not a valid museum preference", $ps_pref),"Museum->getPreference()");
			return null;
		}
	}
	# ----------------------------------------
	/**
	 * Returns default value for a preference
	 *
	 * @param string $ps_pref Preference code
	 * @param array $pa_options No options supported yet
	 * @return mixed Type returned varies by preference
	 */
	public function getPreferenceDefault($ps_pref, $pa_options=null) {
		if (!is_array($va_pref_info = $this->getPreferenceInfo($ps_pref))) { return null; }
		switch($va_pref_info["formatType"]) {
				# ---------------------------------
				case 'FT_OBJECT_EDITOR_UI':
				case 'FT_OBJECT_LOT_EDITOR_UI':
				case 'FT_ENTITY_EDITOR_UI':
				case 'FT_PLACE_EDITOR_UI':
				case 'FT_OCCURRENCE_EDITOR_UI':
				case 'FT_COLLECTION_EDITOR_UI':
				case 'FT_STORAGE_LOCATION_EDITOR_UI':
				case 'FT_OBJECT_REPRESENTATION_EDITOR_UI':
				case 'FT_REPRESENTATION_ANNOTATION_EDITOR_UI':
				case 'FT_SET_EDITOR_UI':
				case 'FT_SET_ITEM_EDITOR_UI':
				case 'FT_LIST_EDITOR_UI':
				case 'FT_LIST_ITEM_EDITOR_UI':
				case 'FT_LOAN_EDITOR_UI':
				case 'FT_MOVEMENT_EDITOR_UI':
				case 'FT_TOUR_EDITOR_UI':
				case 'FT_TOUR_STOP_EDITOR_UI':
				case 'FT_SEARCH_FORM_EDITOR_UI':
				case 'FT_BUNDLE_DISPLAY_EDITOR_UI':
				case 'FT_RELATIONSHIP_TYPE_EDITOR_UI':
				case 'FT_MUSEUM_INTERFACE_EDITOR_UI':
				case 'FT_MUSEUM_INTERFACE_SCREEN_EDITOR_UI':
				case 'FT_IMPORT_EXPORT_MAPPING_EDITOR_UI':
					$vn_type_id = (is_array($pa_options) && isset($pa_options['type_id']) && (int)$pa_options['type_id']) ? (int)$pa_options['type_id'] : null;
					$vn_table_num = $this->_editorPrefFormatTypeToTableNum($va_pref_info["formatType"]);
					//$va_uis = $this->_getUIListByType($vn_table_num);
					
					$va_defaults = array();
					/*foreach($va_uis as $vn_type_id => $va_editor_info) {
						foreach($va_editor_info as $vn_ui_id => $va_editor_labels) {
							$va_defaults[$vn_type_id] = $vn_ui_id;
						}
					}*/
					return $va_defaults;
					break;
				case 'FT_TEXT':
					if ($va_pref_info['displayType'] == 'DT_CURRENCIES') {
						// this respects the global UI locale which is set using Zend_Locale
						$o_currency = new Zend_Currency();
						return ($vs_currency_specifier = $o_currency->getShortName()) ? $vs_currency_specifier : "CAD";
					}
					return $va_pref_info["default"] ? $va_pref_info["default"] : null;
					break;
				# ---------------------------------
				default:
					return $va_pref_info["default"] ? $va_pref_info["default"] : null;
					break;
				# ---------------------------------
			}
	}
	# ----------------------------------------
	/**
	 * Sets value of museum preference. Returns false if preference or value is invalid.
	 *
	 * @access public
	 * @param string $ps_pref Name of museum preference
	 * @param mixed $ps_val Value of preference
	 * @return bool True if preference was set; false if it could not be set.
	 */	
	public function setPreference($ps_pref, $ps_val) {
		if ($this->isValidPreference($ps_pref)) {
			if ($this->purify()) {
				if (!BaseModel::$html_purifier) { BaseModel::$html_purifier = new HTMLPurifier(); }
				if(!is_array($ps_val)) { $ps_val = BaseModel::$html_purifier->purify($ps_val); }
			}
			if ($this->isValidPreferenceValue($ps_pref, $ps_val, 1)) {
				$va_prefs = $this->getVar("_museum_preferences");
				$va_prefs[$ps_pref] = $ps_val;
				$this->setVar("_museum_preferences", $va_prefs);
				return true;
			} else {
				return false;
			}
		} else {
			$this->postError(920, _t("%1 is not a valid museum preference", $ps_pref),"museum->getPreference()");
			return false;
		}
	}
	# ----------------------------------------
	/**
	 * Returns list of supported preference names. If the $ps_group_name is provided, then only
	 * preference names for the specified group are returned. Otherwise all supported preference 
	 * names are returned.
	 *
	 * @access public
	 * @param string $ps_group_name Name of user preference group
	 * @return array List of valid preferences
	 */	
	public function getValidPreferences($ps_group_name="") {
		if ($ps_group_name) {
			if ($va_group = $this->getPreferenceGroupInfo($ps_group_name)) {
				return array_keys($va_group["preferences"]);
			} else {
				return array();
			}
		} else {
			$this->loadMuseumPrefDefs();
			return array_keys($this->_museum_pref_defs->getAssoc("preferenceDefinitions"));
		}
	}
	# ----------------------------------------
	/**
	 * Tests whether a preference name is supported or not.
	 *
	 * @access public
	 * @param string $ps_pref Name of museum preference
	 * @return bool Returns true if preference is supports; false if it is not supported.
	 */	
	public function isValidPreference($ps_pref) {
		return (in_array($ps_pref, $this->getValidPreferences())) ? true : false;
	}
	# ----------------------------------------
	/**
	 * Tests whether a value is valid for a given preference
	 *
	 * @access public
	 * @param string $ps_pref Name of museum preference
	 * @param mixed $ps_value Preference value to test
	 * @param bool $pb_post_errors If true, invalid parameter causes errors to be thrown; if false, error messages are supressed. Default is false.
	 * @return bool Returns true if value is valid; false if value is invalid.
	 */	
	public function isValidPreferenceValue($ps_pref, $ps_value, $pb_post_errors=false) {
		if ($this->isValidPreference($ps_pref)) {
			$va_pref_info = $this->getPreferenceInfo($ps_pref);
			
			# check number of picks for checkboxes
			if (is_array($ps_value) && isset($va_pref_info["picks"])) {
				if (!((sizeof($ps_value) >= $va_pref_info["picks"]["minimum"]) && (sizeof($ps_value) <= $va_pref_info["picks"]["maximum"]))) {
					if ($pb_post_errors) {
						if ($va_pref_info["picks"]["minimum"] < $va_pref_info["picks"]["maximum"]) {
							$this->postError(921, _t("You must select between %1 and %2 choices for %3", $va_pref_info["picks"]["minimum"], $va_pref_info["picks"]["maximum"], $va_pref_info["label"]),"Museum->isValidPreferenceValue()");
						} else {
							$this->postError(921, _t("You must select %1 choices for %2", $va_pref_info["picks"]["minimum"], $va_pref_info["label"]),"Museum->isValidPreferenceValue()");
						}
					}
					return false;
				}
			}
			
			# make sure value is in choice list
			if (isset($va_pref_info["choiceList"]) && is_array($va_pref_info["choiceList"])) {
				if (is_array($ps_value)) {
					foreach($ps_value as $vs_value) {
						if (!in_array($vs_value, array_values($va_pref_info["choiceList"]))) {
							if ($pb_post_errors) {
								$this->postError(921, _t("%1 is not a valid value for %2", $vs_value, $va_pref_info["label"]),"Museum->isValidPreferenceValue()");
							}
							return false;
						}
					}
				} else {
					if (!in_array($ps_value, array_values($va_pref_info["choiceList"]))) {
						if ($pb_post_errors) {
							$this->postError(921, _t("%1 is not a valid value for %2", $ps_value, $va_pref_info["label"]),"Museum->isValidPreferenceValue()");
						}
						return false;
					}
				}
			}
			
			switch($va_pref_info["formatType"]) {
				# ---------------------------------
				case 'FT_NUMBER':
					if (isset($va_pref_info["value"]) && is_array($va_pref_info["value"])) {
						# make sure value within length bounds
						
						if (strlen($va_pref_info["value"]["minimum"]) && ($va_pref_info["value"]["maximum"])) {
							if (!(($ps_value >= $va_pref_info["value"]["minimum"]) && ($ps_value <= $va_pref_info["value"]["maximum"]))) {
								if ($pb_post_errors) {
									$this->postError(921, _t("Value for %1 must be between %2 and %3", $va_pref_info["label"], $va_pref_info["value"]["minimum"], $va_pref_info["value"]["maximum"]),"Museum->isValidPreferenceValue()");
								}
								return false;
							}
						} else {
							if (strlen($va_pref_info["value"]["minimum"])) {
								if ($ps_value < $va_pref_info["value"]["minimum"]) {
									if ($pb_post_errors) {
										if($va_pref_info["value"]["minimum"] == 1) {
											$this->postError(921, _t("%1 must be set", $va_pref_info["label"], $va_pref_info["value"]["minimum"], $va_pref_info["value"]["maximum"]),"Museum->isValidPreferenceValue()");
										} else {
											$this->postError(921, _t("Value for %1 must be greater than %2", $va_pref_info["label"], $va_pref_info["value"]["minimum"]),"Museum->isValidPreferenceValue()");
										}
									}
									return false;
								}
							} else {
								if ($ps_value > $va_pref_info["value"]["maximum"]) {
									if ($pb_post_errors) {
										$this->postError(921, _t("Value for %1 must be less than %2", $va_pref_info["label"], $va_pref_info["value"]["maximum"]),"Museum->isValidPreferenceValue()");
									}
									return false;
								}
							}
						}
					}
					break;
				# ---------------------------------
				case 'FT_TEXT':
					if ($va_pref_info['displayType'] == 'DT_CURRENCIES') {
						if (!is_array($va_currencies = caAvailableCurrenciesForConversion())) {
							return false;
						}
						if (!in_array($ps_value, $va_currencies)) {
							return false;
						}	
					}
					if (isset($va_pref_info["length"]) && is_array($va_pref_info["length"])) { 
						# make sure value within length bounds
						
						if (strlen($va_pref_info["length"]["minimum"]) && ($va_pref_info["length"]["maximum"])) {
							if (!((strlen($ps_value) >= $va_pref_info["length"]["minimum"]) && (strlen($ps_value) <= $va_pref_info["length"]["maximum"]))){
								if ($pb_post_errors) {
									$this->postError(921, _t("Value for %1 must be between %2 and %3 characters", $va_pref_info["label"], $va_pref_info["length"]["minimum"], $va_pref_info["length"]["maximum"]),"Museum->isValidPreferenceValue()");
								}
								return false;
							}
						} else {
							if (strlen($va_pref_info["length"]["minimum"])) {
								if ($ps_value < $va_pref_info["length"]["minimum"]) {
									if ($pb_post_errors) {
										if($va_pref_info["length"]["minimum"] == 1) {
											$this->postError(921, _t("%1 must be set", $va_pref_info["label"], $va_pref_info["length"]["minimum"], $va_pref_info["length"]["maximum"]),"Museum->isValidPreferenceValue()");
										} else {
											$this->postError(921, _t("Value for %1 must be greater than %2 characters", $va_pref_info["label"], $va_pref_info["length"]["minimum"]),"Museum->isValidPreferenceValue()");
										}
									}
									return false;
								}
							} else {
								if ($ps_value > $va_pref_info["length"]["maximum"]) {
									if ($pb_post_errors) {
										$this->postError(921, _t("Value for %1 must be less than %2 characters", $va_pref_info["label"], $va_pref_info["length"]["maximum"]),"Museum->isValidPreferenceValue()");
									}
									return false;
								}
							}
						}
					}
					break;
				# ---------------------------------
				case 'FT_OBJECT_EDITOR_UI':
				case 'FT_OBJECT_LOT_EDITOR_UI':
				case 'FT_ENTITY_EDITOR_UI':
				case 'FT_PLACE_EDITOR_UI':
				case 'FT_OCCURRENCE_EDITOR_UI':
				case 'FT_COLLECTION_EDITOR_UI':
				case 'FT_STORAGE_LOCATION_EDITOR_UI':
				case 'FT_OBJECT_REPRESENTATION_EDITOR_UI':
				case 'FT_REPRESENTATION_ANNOTATION_EDITOR_UI':
				case 'FT_SET_EDITOR_UI':
				case 'FT_SET_ITEM_EDITOR_UI':
				case 'FT_LIST_EDITOR_UI':
				case 'FT_LIST_ITEM_EDITOR_UI':
				case 'FT_LOAN_EDITOR_UI':
				case 'FT_MOVEMENT_EDITOR_UI':
				case 'FT_TOUR_EDITOR_UI':
				case 'FT_TOUR_STOP_EDITOR_UI':
				case 'FT_SEARCH_FORM_EDITOR_UI':
				case 'FT_BUNDLE_DISPLAY_EDITOR_UI':
				case 'FT_RELATIONSHIP_TYPE_EDITOR_UI':
				case 'FT_MUSEUM_INTERFACE_EDITOR_UI':
				case 'FT_MUSEUM_INTERFACE_SCREEN_EDITOR_UI':
				case 'FT_IMPORT_EXPORT_MAPPING_EDITOR_UI':
					$vn_table_num = $this->_editorPrefFormatTypeToTableNum($va_pref_info["formatType"]);
					
					$t_instance = Datamodel::getInstanceByTableNum($vn_table_num, true);
					
					//$va_valid_uis = $this->_getUIListByType($vn_table_num);
					if (is_array($ps_value)) {
						foreach($ps_value as $vn_type_id => $vn_ui_id) {
							if (!isset($va_valid_uis[$vn_type_id][$vn_ui_id])) {
								if ($t_instance && (bool)$t_instance->getFieldInfo($t_instance->getTypeFieldName(), 'IS_NULL') && ($vn_type_id === '_NONE_')) {
									return true;
								}
								if (!isset($va_valid_uis['__all__'][$vn_ui_id])) {
									return false;
								}
							}
						}
					}
					
					return true;
					break;
				# ---------------------------------
				default:
					// No checking performed
					return true;
					break;
				# ---------------------------------
			}
			return true;
		} else {
			return false;
		}
	}
	# ----------------------------------------
	/**
	 * Generates HTML form element widget for preference based upon settings in preference definition file.
	 * By calling this method for a series of preference names, one can quickly create an HTML-based configuration form.
	 *
	 * @access public
	 * @param string $ps_pref Name of museum preference
	 * @param string $ps_format Format string containing simple tags to be replaced with preference information. Tags supported are:
	 *		^LABEL = name of preference
	 *		^ELEMENT = HTML code to generate form widget
	 * 		If you omit $ps_format, the element code alone (content of ^ELEMENT) is returned.
	 * @param array $pa_options Array of options. Support options are:
	 *		field_errors = array of error messages to display on preference element
	 *		useTable = if true and displayType for element in DT_CHECKBOXES checkboxes will be formatted in a table with numTableColumns columns
	 *		numTableColumns = Number of columns to use when formatting checkboxes as a table. Default, if omitted, is 3
	 *		genericUIList = forces FT_*_EDITOR_UI to return single UI list for table rather than by type
	 *		classname = class to assign to form element
	 * @return string HTML code to generate form widget
	 */	
	public function preferenceHtmlFormElement($ps_pref, $ps_format=null, $pa_options=null) {
		if ($this->isValidPreference($ps_pref)) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			$o_db = $this->getDb();
			
			$va_pref_info = $this->getPreferenceInfo($ps_pref);
			
			if (is_null($vs_current_value = $this->getPreference($ps_pref))) { $vs_current_value = $this->getPreferenceDefault($ps_pref); }
			$vs_output = "";
			$vs_class = "";
			$vs_classname = "";
			if(isset($pa_options['classname']) && $pa_options['classname']){
				$vs_classname = $pa_options['classname'];
				$vs_class = " class='".$pa_options['classname']."'";
			}
			
			foreach(array(
				'displayType', 'displayWidth', 'displayHeight', 'length', 'formatType', 'choiceList',
				'label', 'description'
			) as $vs_k) {
				if (!isset($va_pref_info[$vs_k])) { $va_pref_info[$vs_k] = null; }
			}
			
			switch($va_pref_info["displayType"]) {
				# ---------------------------------
				case 'DT_FIELD':
					if (($vn_display_width = $va_pref_info["displayWidth"]) < 1) {
						$vn_display_width = 20;
					}
					if (($vn_display_height = $va_pref_info["displayHeight"]) < 1) {
						$vn_display_height = 1;
					}
					
					if (isset($va_pref_info["length"]["maximum"])) {
						$vn_max_input_length = $va_pref_info["length"]["maximum"];
					} else {
						$vn_max_input_length = $vn_display_width;
					}
					
					if ($vn_display_height > 1) {
						$vs_output = "<textarea name='pref_$ps_pref' rows='".$vn_display_height."' cols='".$vn_display_width."'>".htmlspecialchars($vs_current_value, ENT_QUOTES, 'UTF-8')."</textarea>\n";
					} else {
						$vs_output = "<input type='text' name='pref_$ps_pref' size='$vn_display_width' maxlength='$vn_max_input_length'".$vs_class." value='".htmlspecialchars($vs_current_value, ENT_QUOTES, 'UTF-8')."'/>\n";
					}
					break;
				# ---------------------------------
				case 'DT_SELECT':
					switch($va_pref_info['formatType']) {
						case 'FT_UI_LOCALE':
							$va_locales = array();
							if ($r_dir = opendir(__CA_APP_DIR__.'/locale/')) {
								while (($vs_locale_dir = readdir($r_dir)) !== false) {
									if ($vs_locale_dir{0} == '.') { continue; }
									if (sizeof($va_tmp = explode('_', $vs_locale_dir)) == 2) {
										$va_locales[$vs_locale_dir] = $va_tmp;
									}
								}
							}
							
							$va_restrict_to_ui_locales = $this->getAppConfig()->getList('restrict_to_ui_locales');
							
							$va_opts = array();
							$t_locale = new ca_locales();
							foreach($va_locales as $vs_code => $va_parts) {
								if (is_array($va_restrict_to_ui_locales) && sizeof($va_restrict_to_ui_locales) && !in_array($vs_code, $va_restrict_to_ui_locales)) { continue; }
								try {
									$vs_lang_name = Zend_Locale::getTranslation(strtolower($va_parts[0]), 'language', strtolower($va_parts[0]));
									$vs_country_name = Zend_Locale::getTranslation($va_parts[1], 'Country', $vs_code);
								} catch (Exception $e) {
									$vs_lang_name = strtolower($va_parts[0]);
									$vs_country_name = $vs_code;
								}
								$va_opts[($vs_lang_name ? $vs_lang_name : $vs_code).($vs_country_name ? ' ('.$vs_country_name.')':'')] = $vs_code;
							}
							break;
						case 'FT_LOCALE':
							$qr_locales = $o_db->query("
								SELECT *
								FROM ca_locales
								ORDER BY 
									name
							");
							$va_opts = array();
							while($qr_locales->nextRow()) {
								$va_opts[$qr_locales->get('name')] = $qr_locales->get('language').'_'.$qr_locales->get('country');
							}
							break;
						case 'FT_THEME':
							if ($r_dir = opendir($this->_CONFIG->get('themes_directory'))) {
								$va_opts = array();
								while (($vs_theme_dir = readdir($r_dir)) !== false) {
									if ($vs_theme_dir{0} == '.') { continue; }
										$o_theme_info = Configuration::load($this->_CONFIG->get('themes_directory').'/'.$vs_theme_dir.'/themeInfo.conf');
										$va_opts[$o_theme_info->get('name')] = $vs_theme_dir;
								}
							}
							break;
						case 'FT_OBJECT_EDITOR_UI':
						case 'FT_OBJECT_LOT_EDITOR_UI':
						case 'FT_ENTITY_EDITOR_UI':
						case 'FT_PLACE_EDITOR_UI':
						case 'FT_OCCURRENCE_EDITOR_UI':
						case 'FT_COLLECTION_EDITOR_UI':
						case 'FT_STORAGE_LOCATION_EDITOR_UI':
						case 'FT_OBJECT_REPRESENTATION_EDITOR_UI':
						case 'FT_REPRESENTATION_ANNOTATION_EDITOR_UI':
						case 'FT_SET_EDITOR_UI':
						case 'FT_SET_ITEM_EDITOR_UI':
						case 'FT_LIST_EDITOR_UI':
						case 'FT_LIST_ITEM_EDITOR_UI':
						case 'FT_LOAN_EDITOR_UI':
						case 'FT_MOVEMENT_EDITOR_UI':
						case 'FT_TOUR_EDITOR_UI':
						case 'FT_TOUR_STOP_EDITOR_UI':
						case 'FT_SEARCH_FORM_EDITOR_UI':
						case 'FT_BUNDLE_DISPLAY_EDITOR_UI':
						case 'FT_RELATIONSHIP_TYPE_EDITOR_UI':
						case 'FT_MUSEUM_INTERFACE_EDITOR_UI':
						case 'FT_MUSEUM_INTERFACE_SCREEN_EDITOR_UI':
						case 'FT_IMPORT_EXPORT_MAPPING_EDITOR_UI':
						
							$vn_table_num = $this->_editorPrefFormatTypeToTableNum($va_pref_info['formatType']);
							$t_instance = Datamodel::getInstanceByTableNum($vn_table_num, true);
							
							$va_values = $this->getPreference($ps_pref);
							if (!is_array($va_values)) { $va_values = array(); }
							
							if (method_exists($t_instance, 'getTypeFieldName') && ($t_instance->getTypeFieldName()) && (!isset($pa_options['genericUIList']) || !$pa_options['genericUIList'])) {
								
								$vs_output = '';
								//$va_ui_list_by_type = $this->_getUIListByType($vn_table_num);
								
								$va_types = array();
								if ((bool)$t_instance->getFieldInfo($t_instance->getTypeFieldName(), 'IS_NULL')) {
									$va_types['_NONE_'] = array('LEVEL' => 0, 'name_singular' => _t('NONE'),  'name_plural' => _t('NONE'));
								}
								$va_types += $t_instance->getTypeList(array('returnHierarchyLevels' => true));
								
								if(!is_array($va_types) || !sizeof($va_types)) { $va_types = array(1 => array()); }	// force ones with no types to get processed for __all__
								
								foreach($va_types as $vn_type_id => $va_type) {
									$va_opts = array();
									
									// print out type-specific
									if (is_array($va_ui_list_by_type[$vn_type_id])) {
										foreach(caExtractValuesByMuseumLocale($va_ui_list_by_type[$vn_type_id]) as $vn_ui_id => $vs_label) {
											$va_opts[$vn_ui_id] = $vs_label;
										}
									}
									
									// print out generic
									if (is_array($va_ui_list_by_type['__all__'])) {
										foreach(caExtractValuesByMuseumLocale($va_ui_list_by_type['__all__']) as $vn_ui_id => $vs_label) {
											$va_opts[$vn_ui_id] = $vs_label;
										}
									}
									
									if (!is_array($va_opts) || (sizeof($va_opts) == 0)) { continue; }
				
									$vs_output .= "<tr><td>".str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", (int)$va_type['LEVEL']).$va_type['name_singular']."</td><td><select name='pref_{$ps_pref}_{$vn_type_id}'>\n";
									foreach($va_opts as $vs_val => $vs_opt) {
										$vs_selected = ($vs_val == $va_values[$vn_type_id]) ? "SELECTED" : "";
										$vs_output .= "<option value='".htmlspecialchars($vs_val, ENT_QUOTES, 'UTF-8')."' {$vs_selected}>{$vs_opt}</option>\n";	
									}
									$vs_output .= "</select></td></tr>\n";
								}
							} else {
								
								//$va_opts = $this->_getUIList($vn_table_num);
								
								if (!is_array($va_opts) || (sizeof($va_opts) == 0)) { $vs_output = ''; break(2); }
								
								$vs_output = "<tr><td> </td><td><select name='pref_$ps_pref'>\n";
								foreach($va_opts as $vs_val => $vs_opt) {
									$vs_selected = ($vs_val == $vs_current_value) ? "SELECTED" : "";
									$vs_output .= "<option value='".htmlspecialchars($vs_val, ENT_QUOTES, 'UTF-8')."' $vs_selected>".$vs_opt."</option>\n";	
								}
								$vs_output .= "</select></td></tr>\n";
							}
							
							break(2);
						default:
							$va_opts = $va_pref_info["choiceList"];
							break;
					}
					if (!is_array($va_opts) || (sizeof($va_opts) == 0)) { $vs_output = ''; break; }
					
					
					$vs_output = "<select name='pref_{$ps_pref}'".$vs_class.">\n";
					foreach($va_opts as $vs_opt => $vs_val) {
						$vs_selected = ($vs_val == $vs_current_value) ? "selected='1'" : "";
						$vs_output .= "<option value='".htmlspecialchars($vs_val, ENT_QUOTES, 'UTF-8')."' $vs_selected>".$vs_opt."</option>\n";	
					}
					$vs_output .= "</select>\n";
					break;
				# ---------------------------------
				case 'DT_CHECKBOXES':
					if ($va_pref_info["formatType"] == 'FT_BIT') {
						$vs_selected = ($vs_current_value) ? "CHECKED" : "";
						$vs_output .= "<input type='checkbox' name='pref_$ps_pref' value='1'".$vs_class." $vs_selected>\n";	
					} else {
						if ($vb_use_table = (isset($pa_options['useTable']) && (bool)$pa_options['useTable'])) {
							$vs_output .= "<table width='100%'>";
						}
						$vn_num_table_columns = (isset($pa_options['numTableColumns']) && ((int)$pa_options['numTableColumns'] > 0)) ? (int)$pa_options['numTableColumns'] : 3;
						
						$vn_c = 0;
						foreach($va_pref_info["choiceList"] as $vs_opt => $vs_val) {
							if (is_array($vs_current_value)) {
								$vs_selected = (in_array($vs_val, $vs_current_value)) ? "CHECKED" : "";
							} else {
								$vs_selected = '';
							}
							
							if ($vb_use_table && ($vn_c == 0)) { $vs_output .= "<tr>"; }
							if ($vb_use_table) { $vs_output .= "<td width='".(floor(100/$vn_num_table_columns))."%'>"; }
							$vs_output .= "<input type='checkbox' name='pref_".$ps_pref."[]' value='".htmlspecialchars($vs_val, ENT_QUOTES, 'UTF-8')."'".$vs_class." $vs_selected> ".$vs_opt." \n";	
							
							if ($vb_use_table) { $vs_output .= "</td>"; }
							$vn_c++;
							if ($vb_use_table && !($vn_c % $vn_num_table_columns)) { $vs_output .= "</tr>\n"; $vn_c = 0; }
						}
						if ($vb_use_table) {
							$vs_output .= "</table>";
						}
					}
					break;
				# ---------------------------------
				case 'DT_STATEPROV_LIST':
					$vs_output .= caHTMLSelect("pref_{$ps_pref}_select", array(), array('id' => "pref_{$ps_pref}_select", 'class' => $vs_classname), array('value' => $vs_current_value));
					$vs_output .= caHTMLTextInput("pref_{$ps_pref}_name", array('id' => "pref_{$ps_pref}_text", 'value' => $vs_current_value, 'class' => $vs_classname));
					
					break;
				# ---------------------------------
				case 'DT_COUNTRY_LIST':
					$vs_output .= caHTMLSelect("pref_{$ps_pref}", caGetCountryList(), array('id' => "pref_{$ps_pref}", 'class' => $vs_classname), array('value' => $vs_current_value));
						
					if ($va_pref_info['stateProvPref']) {
						$vs_output .="<script type='text/javascript'>\n";
						$vs_output .= "var caStatesByCountryList = ".json_encode(caGetStateList()).";\n";
						
						$vs_output .= "
							jQuery('#pref_{$ps_pref}').click({countryID: 'pref_{$ps_pref}', stateProvID: 'pref_".$va_pref_info['stateProvPref']."', value: '".addslashes($this->getPreference($va_pref_info['stateProvPref']))."', statesByCountryList: caStatesByCountryList}, caUI.utils.updateStateProvinceForCountry);
							jQuery(document).ready(function() {
								caUI.utils.updateStateProvinceForCountry({data: {countryID: 'pref_{$ps_pref}', stateProvID: 'pref_".$va_pref_info['stateProvPref']."', value: '".addslashes($this->getPreference($va_pref_info['stateProvPref']))."', statesByCountryList: caStatesByCountryList}});
							});
						";
						
						$vs_output .="</script>\n";
					}
					break;
				# ---------------------------------
				case 'DT_CURRENCIES':
					$vs_output .= caHTMLSelect("pref_{$ps_pref}", caAvailableCurrenciesForConversion(), array('id' => "pref_{$ps_pref}", 'class' => $vs_classname), array('value' => $vs_current_value));
					break;
				# ---------------------------------
				case 'DT_RADIO_BUTTONS':
					foreach($va_pref_info["choiceList"] as $vs_opt => $vs_val) {
						$vs_selected = ($vs_val == $vs_current_value) ? "CHECKED" : "";
						$vs_output .= "<input type='radio' name='pref_$ps_pref'".$vs_class." value='".htmlspecialchars($vs_val, ENT_QUOTES, 'UTF-8')."' $vs_selected> ".$vs_opt." \n";	
					}
					break;
				# ---------------------------------
				case 'DT_HIDDEN':
					// noop
					break;
				# ---------------------------------
				default:
					return "Configuration error: Invalid display type for $ps_pref";
				# ---------------------------------
			}
			
			if (is_null($ps_format)) {
				if (isset($pa_options['field_errors']) && is_array($pa_options['field_errors']) && sizeof($pa_options['field_errors'])) {
					$ps_format = $this->_CONFIG->get('form_element_error_display_format');
					$va_field_errors = array();
					foreach($pa_options['field_errors'] as $o_e) {
						$va_field_errors[] = $o_e->getErrorDescription();
					}
					$vs_errors = join('; ', $va_field_errors);
				} else {
					$ps_format = $this->_CONFIG->get('form_element_display_format');
					$vs_errors = '';
				}
			}
			if ($ps_format && $vs_output) {
				$vs_format = $ps_format;
				$vs_format = str_replace("^ELEMENT", $vs_output, $vs_format);
			} else {
				$vs_format = $vs_output;
			}
			
			$vs_format = str_replace("^EXTRA", '',  $vs_format);
			if (preg_match("/\^DESCRIPTION/", $vs_format)) {
				$vs_format = str_replace("^LABEL", _t($va_pref_info["label"]), $vs_format);
				$vs_format = str_replace("^DESCRIPTION", _t($va_pref_info["description"]), $vs_format);
			} else {
				// no explicit placement of description text, so...
				$vs_field_id = "pref_{$ps_pref}_container";
				$vs_format = str_replace("^LABEL",'<span id="'.$vs_field_id.'">'._t($va_pref_info["label"]).'</span>', $vs_format);
				
				TooltipManager::add('#'.$vs_field_id, "<h3>".$va_pref_info["label"]."</h3>".$va_pref_info["description"]);
			}
			return $vs_format;

		} else {
			return "";
		}
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function _getUIListByType($pn_table_num) {
		if(!$this->getPrimaryKey()) { return false; }
		
		$o_db = $this->getDb();
		$qr_uis = $o_db->query("
			SELECT ceui.ui_id, ceuil.name, ceuil.locale_id, ceuitr.type_id
			FROM ca_editor_uis ceui
			INNER JOIN ca_editor_ui_labels AS ceuil ON ceui.ui_id = ceuil.ui_id
			LEFT JOIN ca_editor_ui_type_restrictions AS ceuitr ON ceui.ui_id = ceuitr.ui_id 
			WHERE
				(
					ceui.museum_id = ? OR 
					ceui.is_system_ui = 1 OR
					(ceui.ui_id IN (
							SELECT ui_id 
							FROM ca_editor_uis_x_museums 
							WHERE 
								museum_id = ?
						)
					)
				) 
				AND (ceui.editor_type = ?)
		", (int)$this->getPrimaryKey(), (int)$this->getPrimaryKey(), (int)$pn_table_num);
		
		$va_ui_list_by_type = array();
		while($qr_uis->nextRow()) {
			if (!($vn_type_id = $qr_uis->get('type_id'))) { $vn_type_id = '__all__'; }
			$va_ui_list_by_type[$vn_type_id][$qr_uis->get('ui_id')][$qr_uis->get('locale_id')] = $qr_uis->get('name');
		}
		
		return $va_ui_list_by_type;
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function _getUIList($pn_table_num) {
		if(!$this->getPrimaryKey()) { return false; }
		
		$o_db = $this->getDb();
		$qr_uis = $o_db->query("
			SELECT *
			FROM ca_editor_uis ceui
			INNER JOIN ca_editor_ui_labels AS ceuil ON ceui.ui_id = ceuil.ui_id
			WHERE
				(
					ceui.museum_id = ? OR 
					ceui.is_system_ui = 1 OR
					(ceui.ui_id IN (
							SELECT ui_id 
							FROM ca_editor_uis_x_museums 
							WHERE 
								museum_id = ?
						)
					)
				) AND (ceui.editor_type = ?)
		", (int)$this->getPrimaryKey(), (int)$this->getPrimaryKey(), (int)$pn_table_num);
		$va_opts = array();
		while($qr_uis->nextRow()) {
			$va_opts[$qr_uis->get('ui_id')][$qr_uis->get('locale_id')] = $qr_uis->get('name');
		}
		
		return caExtractValuesByMuseumLocale($va_opts);
	}
	# ----------------------------------------
	/**
	 *
	 */
	private function _editorPrefFormatTypeToTableNum($ps_pref_format_type) {
		switch($ps_pref_format_type) {
			case 'FT_OBJECT_EDITOR_UI':
				$vn_table_num = 57;
				break;
			case 'FT_OBJECT_LOT_EDITOR_UI':
				$vn_table_num = 51;
				break;
			case 'FT_ENTITY_EDITOR_UI':
				$vn_table_num = 20;
				break;
			case 'FT_PLACE_EDITOR_UI':
				$vn_table_num = 72;
				break;
			case 'FT_OCCURRENCE_EDITOR_UI':
				$vn_table_num = 67;
				break;
			case 'FT_COLLECTION_EDITOR_UI':
				$vn_table_num = 13;
				break;
			case 'FT_STORAGE_LOCATION_EDITOR_UI':
				$vn_table_num = 89;
				break;
			case 'FT_OBJECT_REPRESENTATION_EDITOR_UI':
				$vn_table_num = 56;
				break;
			case 'FT_REPRESENTATION_ANNOTATION_EDITOR_UI':
				$vn_table_num = 82;
				break;
			case 'FT_SET_EDITOR_UI':
				$vn_table_num = 103;
				break;
			case 'FT_SET_ITEM_EDITOR_UI':
				$vn_table_num = 105;
				break;
			case 'FT_LIST_EDITOR_UI':
				$vn_table_num = 36;
				break;
			case 'FT_LIST_ITEM_EDITOR_UI':
				$vn_table_num = 33;
				break;
			case 'FT_LOAN_EDITOR_UI':
				$vn_table_num = 133;
				break;
			case 'FT_MOVEMENT_EDITOR_UI':
				$vn_table_num = 137;
				break;
			case 'FT_TOUR_EDITOR_UI':
				$vn_table_num = 153;
				break;
			case 'FT_TOUR_STOP_EDITOR_UI':
				$vn_table_num = 155;
				break;
			case 'FT_SEARCH_FORM_EDITOR_UI':
				$vn_table_num = 121;
				break;
			case 'FT_BUNDLE_DISPLAY_EDITOR_UI':
				$vn_table_num = 124;
				break;
			case 'FT_RELATIONSHIP_TYPE_EDITOR_UI':
				$vn_table_num = 79;
				break;
			case 'FT_MUSEUM_INTERFACE_EDITOR_UI':
				$vn_table_num = 101;
				break;
			case 'FT_MUSEUM_INTERFACE_SCREEN_EDITOR_UI':
				$vn_table_num = 100;
				break;
			case 'FT_IMPORT_EXPORT_MAPPING_EDITOR_UI':
				$vn_table_num = 128;
				break;
			default:
				$vn_table_num = null;
				break;
		}
		return $vn_table_num;
	}
	# ----------------------------------------
/**
 * Returns preference information array for specified preference directly from definition file.
 *
 * @access public
 * @param string $ps_pref Name of museum preference
 * @return array Information array, directly from definition file
 */	
	public function getPreferenceInfo($ps_pref) {
		$this->loadMuseumPrefDefs();
		$va_prefs = $this->_museum_pref_defs->getAssoc("preferenceDefinitions");
		return $va_prefs[$ps_pref];
	}
	# ----------------------------------------
/**
 * Loads museum_pref_defs config file
 *
 * @access public
 * @param boolean $pb_force_reload If true, load defs file even if it has already been loaded
 * @return void
 */	
	
	public function loadMuseumPrefDefs($pb_force_reload=false) {
		if (!$this->_museum_pref_defs || $pb_force_reload) {
			if ($vs_museum_pref_def_path = __CA_APP_DIR__.'/plugins/museums/conf/museum_pref_defs.conf') {
				$this->_museum_pref_defs = Configuration::load($vs_museum_pref_def_path, $pb_force_reload);
				return true;
			}
		}
		return false;
	}
	# ----------------------------------------
	/**
	 * Returns preference group information array for specified preference directly from definition file.
	 *
	 * @access public
	 * @param string $ps_pref_group Name of museum preference group
	 * @return array Information array, directly from definition file
	 */	
	public function getPreferenceGroupInfo($ps_pref_group) {
		$this->loadMuseumPrefDefs();
		$va_groups = $this->_museum_pref_defs->getAssoc("preferenceGroups");
		return $va_groups[$ps_pref_group];
	}
	# ----------------------------------------
	# Utils
	# ----------------------------------------
	/**
	 * Check if a museum name exists
	 *
	 * @param mixed $ps_museum_name_or_id The museum name or numeric museum_id of the museum
	 * @return boolean True if museum exists, false if not
	 */
	 static public function exists($ps_museum_name_or_id, $pa_options=null) {
		if (parent::exists($ps_museum_name_or_id)) {
			return true;
		}
		return false;
	}
	# ----------------------------------------
	# Auth API methods
	# ----------------------------------------
	/**
	 *
	 */
	public function close() {
		if($this->getPrimaryKey()) {
			$this->setMode(ACCESS_WRITE);
			$this->update(['dontLogChange' => true]);
		}
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function getMuseumID() {
		return $this->getPrimaryKey();
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function getName() {
		return $this->get("fname")." ". $this->get("museum_name");
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function isActive() {
		return ($this->get("active") && ($this->get("museumclass") != 255)) ? true : false;
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function getClassName() {
		return "mana_museums";
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function getPreferredUILocale() {
		if (!(defined("__CA_DEFAULT_LOCALE__"))) { 
			define("__CA_DEFAULT_LOCALE__", "en_US"); // if all else fails...
		}
		$t_locale = new ca_locales();
		if ($vs_locale = $this->getPreference('ui_locale')) {
			return $vs_locale;
		} 
		
		$va_default_locales = $this->getAppConfig()->getList('locale_defaults');
		if (sizeof($va_default_locales)) {
			return $va_default_locales[0];
		}
		
		return __CA_DEFAULT_LOCALE__;
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function getPreferredUILocaleID() {
		if (!(defined("__CA_DEFAULT_LOCALE__"))) {
			define("__CA_DEFAULT_LOCALE__", "en_US"); // if all else fails...
		}
		$t_locale = new ca_locales();
		if ($vs_locale = $this->getPreference('ui_locale')) {
			if ($vn_locale_id = $t_locale->localeCodeToID($vs_locale)) {
				return $vn_locale_id;
			}
		} 
		
		$va_default_locales = $this->getAppConfig()->getList('locale_defaults');
		if (sizeof($va_default_locales) && $vn_locale_id = $t_locale->localeCodeToID($va_default_locales[0])) {
			return $vn_locale_id;
		}
		
		return $t_locale->localeCodeToID(__CA_DEFAULT_LOCALE__);
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function getPreferredDisplayLocaleIDs($pn_item_locale_id=null) {
		$vs_mode = $this->getPreference('cataloguing_display_label_mode');
		
		$va_locale_ids = array();
		switch($vs_mode) {
			case 'cataloguing_locale':
				if ($vs_locale = $this->getPreference('cataloguing_locale')) {
					$t_locale = new ca_locales();
					if ($t_locale->loadLocaleByCode($vs_locale)) {
						$va_locale_ids[$t_locale->getPrimaryKey()] = true;
					}
				}
				break;
			case 'item_locale':
				if ($pn_item_locale_id) { 
					$va_locale_ids[$pn_item_locale_id] = true;
				}
				break;
			case 'cataloguing_and_item_locale':
			default:
				if ($vs_locale = $this->getPreference('cataloguing_locale')) {
					$t_locale = new ca_locales();
					if ($t_locale->loadLocaleByCode($vs_locale)) {
						$va_locale_ids[$t_locale->getPrimaryKey()] = true;
					}
				}
				if ($pn_item_locale_id) { 
					$va_locale_ids[$pn_item_locale_id] = true;
				}
				break;
		}
		return array_keys($va_locale_ids);
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function isStandardMuseum() {
		return (((int)$this->get('museumclass') === 0) ?  true : false);
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function isPublicMuseum() {
		return (((int)$this->get('museumclass') === 1) ?  true : false);
	}
	# ----------------------------------------
	/**
	 *
	 */
	public function isDeletedMuseum() {
		return (((int)$this->get('museumclass') === 255) ?  true : false);
	}
}