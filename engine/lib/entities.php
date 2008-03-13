<?php
	/**
	 * Elgg entities.
	 * Functions to manage all elgg entities (sites, collections, objects and users).
	 * 
	 * @package Elgg
	 * @subpackage Core
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Marcus Povey <marcus@dushka.co.uk>
	 * @copyright Curverider Ltd 2008
	 * @link http://elgg.org/
	 */

	/**
	 * @class ElggEntity The elgg entity superclass
	 * This class holds methods for accessing the main entities table.
	 * @author Marcus Povey <marcus@dushka.co.uk>
	 */
	abstract class ElggEntity
	{
		/** 
		 * The main attributes of an entity.
		 * Blank entries for all database fields should be created by the constructor.
		 * Subclasses should add to this in their constructors.
		 * Any field not appearing in this will be viewed as a 
		 */
		protected $attributes;
				
		/**
		 * Return the value of a given key.
		 * If $name is a key field (as defined in $this->attributes) that value is returned, otherwise it will
		 * then look to see if the value is in this object's metadata.
		 * 
		 * Q: Why are we not using __get overload here?
		 * A: Because overload operators cause problems during subclassing, so we put the code here and
		 * create overloads in subclasses. 
		 * 
		 * @param string $name
		 * @return mixed Returns the value of a given value, or null.
		 */
		public function get($name)
		{
			// See if its in our base attribute
			if (isset($this->attributes[$name])) {
				return $this->attributes[$name];
			}
			
			// No, so see if its in the meta data for this entity
			$meta = getMetaData($name);
			if ($meta)
				return $meta;
			
			// Can't find it, so return null
			return null;
		}

		/**
		 * Set the value of a given key, replacing it if necessary.
		 * If $name is a base attribute (as defined in $this->attributes) that value is set, otherwise it will
		 * set the appropriate item of metadata.
		 * 
		 * Note: It is important that your class populates $this->attributes with keys for all base attributes, anything
		 * not in their gets set as METADATA.
		 * 
		 * Q: Why are we not using __set overload here?
		 * A: Because overload operators cause problems during subclassing, so we put the code here and
		 * create overloads in subclasses.
		 * 
		 * @param string $name
		 * @param mixed $value  
		 */
		public function set($name, $value)
		{
			if (array_key_exists($name, $this->attributes)) 
				$this->attributes[$name] = $value;
			else
				return setMetaData($name, $value);
			
			return true;
		}
			
		/**
		 * Get a given piece of metadata.
		 * 
		 * @param string $name
		 */
		public function getMetaData($name)
		{
			//TODO: Writeme
		}
		
		/**
		 * Set a piece of metadata.
		 * 
		 * @param string $name
		 * @param string $value
		 * @return bool
		 */
		public function setMetaData($name, $value)
		{
			// TODO: WRITEME
		}
		
		/**
		 * Adds an annotation to an entity. By default, the type is detected automatically; however, 
		 * it can also be set. Note that by default, annotations are private.
		 * 
		 * @param string $name
		 * @param string $value
		 * @param int $access_id
		 * @param int $owner_id
		 * @param string $vartype
		 */
		function annotate($name, $value, $access_id = 0, $owner_id = 0, $vartype = "") 
		{ 
		// TODO: WRITEME
		}
		
		/**
		 * Get the annotations for an entity.
		 *
		 * @param string $name
		 * @param int $limit
		 * @param int $offset
		 */
		function getAnnotations($name, $limit = 50, $offset = 0) 
		{ 
		// TODO: WRITEME
		}
		
		public function getGUID() { return $this->get('guid'); }
		public function getOwner() { return $this->get('owner_guid'); }
		public function getType() { return $this->get('type'); }
		public function getSubtype() { return get_subtype_from_id($this->get('owner_guid')); }
		public function getSite() { return $this->get('site'); }
		public function getTimeCreated() { return $this->get('time_created'); }
		public function getTimeUpdated() { return $this->get('time_updated'); }
		
		
		// TODO: Friends/relationships
		
		
		/**
		 * Save generic attributes to the entities table.
		 */
		public function save()
		{
			if ($this->get('guid') > 0)
				return update_entity(
					$this->get('guid'),
					$this->get('owner_guid'),
					$this->get('site_guid'),
					$this->get('access_id')
				);
			else
			{ 
				$this->attributes['guid'] = create_site($this->title, $this->description, $this->url, $this->owner_id, $this->access_id); // Create a new entity (nb: using attribute array directly 'cos set function does something special!)
				if (!$this->attributes['guid']) throw new IOException("Unable to save new object's base entity information!"); 
				
				return $this->attributes['guid'];
			}
		}
		
		/**
		 * Load the basic entity information and populate base attributes array.
		 * 
		 * @param int $guid 
		 */
		protected function load($guid)
		{
			$row = get_entity_as_row($guid);
			
			if ($row)
			{
				// Create the array if necessary - all subclasses should test before creating
				if (!is_array($this->attributes)) $this->attributes = array();
				
				// Now put these into the attributes array as core values
				$objarray = (array) $row;
				foreach($objarray as $key => $value) 
					$this->attributes[$key] = $value;
				
				return true;
			}
			
			return false;
		}
		
	}

	/**
	 * Return the integer ID for a given subtype, or false.
	 * 
	 * TODO: Move to a nicer place?
	 * 
	 * @param string $subtype
	 */
	function get_subtype_id($subtype)
	{
		global $CONFIG;
		
		$subtype = sanitise_string($subtype);
		
		$result = get_data_row("SELECT * from {$CONFIG->dbprefix}entity_subtypes where subtype='$subtype'");
		if ($result)
			return $result->id;
		
		return false;
	}
	
	/**
	 * For a given subtype ID, return its identifier text.
	 *  
	 * TODO: Move to a nicer place?
	 * 
	 * @param string $subtype_id
	 */
	function get_subtype_from_id($subtype_id)
	{
		global $CONFIG;
		
		$subtype_id = (int)$subtype_id;
		
		$result = get_data_row("SELECT * from {$CONFIG->dbprefix}entity_subtypes where id=$subtype_id");
		if ($result)
			return $result->subtype;
		
		return false;
	}
	
	/**
	 * Update an existing entity.
	 *
	 * @param int $guid
	 * @param int $owner_guid
	 * @param int $site_guid
	 * @param int $access_id
	 */
	function update_entity($guid, $owner_guid, $site_guid, $access_id)
	{
		global $CONFIG;
		
		$guid = (int)$guid;
		$owner_guid = (int)$owner_guid;
		$site_guid = (int)$site_guid;
		$access_id = (int)$access_id;
		$time = time();
		
		$access = get_access_list();
		
		
		return update_data("UPDATE {$CONFIG->dbprefix}entities set owner_guid='$owner_guid', site_guid='$site_guid', access_id='$access_id', time_updated='$time' WHERE guid=$guid and (access_id in {$access} or (access_id = 0 and owner_guid = {$_SESSION['id']}))");
	}
	
	/**
	 * Create a new entity of a given type.
	 * 
	 * @param string $type
	 * @param string $subtype
	 * @param int $owner_guid
	 * @param int $site_guid
	 * @param int $access_id
	 * @return mixed The new entity's GUID or false.
	 */
	function create_entity($type, $subtype, $owner_guid, $site_guid, $access_id)
	{
		global $CONFIG;
		
		$type = sanitise_string($type);
		$subtype = get_subtype_id($subtype);
		$owner_guid = (int)$owner_guid;
		$site_guid = (int)$site_guid;
		$access_id = (int)$access_id;
		$time = time();
		
		if (!$subtype)
			throw new InvalidParameterException("Entity subtype '$subtype' is not supported");
			
		return insert_data("INSERT into {$CONFIG->dbprefix}entities (type,subtype,owner_guid,site_guid,access_id,time_created,time_updated) values ('$type',$subtype, $owner_guid, $site_guid, $access_id, $time, $time)");
	}
	
	/**
	 * Retrieve the entity details for a specific GUID, returning it as a stdClass db row.
	 *
	 * @param int $guid
	 */
	function get_entity_as_row($guid)
	{
		global $CONFIG;
		
		$guid = (int)$guid;
		
		return get_data_row("SELECT * from {$CONFIG->dbprefix}entities where guid=$guid");
	}
	
	/**
	 * Create an Elgg* object from a given entity row. 
	 */
	function entity_row_to_elggstar($row)
	{
		switch ($row->type)
		{
			case 'object' : return new ElggObject($row);
			case 'user' : return new ElggUser($row);
			case 'collection' : return new ElggCollection($row); 
			case 'site' : return new ElggSite($row); 
			default: default : throw new InstallationException("Type {$row->type} is not supported. This indicates an error in your installation, most likely caused by an incomplete upgrade.");
		}
		
		return false;
	}
	
	/**
	 * Return the entity for a given guid as the correct object.
	 * @param $guid
	 * @return a child of ElggEntity appropriate for the type.
	 */
	function get_entity($guid)
	{
		return entity_row_to_elggstar(get_entity_as_row($guid));
	}
	
	/**
	 * Return entities matching a given query.
	 * 
	 * @param string $type
	 * @param string $subtype
	 * @param int $owner_guid
	 * @param int $site_guid
	 * @param string $order_by
	 * @param int $limit
	 * @param int $offset
	 */
	function get_entities($type = "", $subtype = "", $owner_guid = 9, $site_guid = 0, $order_by = "time_created desc", $limit = 10, $offset = 0)
	{
		$type = sanitise_string($type);
		$subtype = get_subtype_id($subtype);
		$owner_guid = (int)$owner_guid;
		$site_guid = (int)$site_guid;
		$order_by = sanitise_string($order_by);
		$limit = (int)$limit;
		$offset = (int)$offset;
		
		$access = get_access_list();
		
		$where = array();
		
		if ($type != "")
			$where .= " type='$type' ";
		if ($subtype)
			$where .= " subtype=$subtype ";
		if ($owner_guid != "")
			$where .= " owner_guid='$owner_guid' ";
		if ($site_guid != "")
			$where .= " site_guid='$site_guid' ";
		
		$query = "SELECT * from {$CONFIG->dbprefix}entities where ";
		foreach ($where as $w)
			$query .= " $w and ";
		$query .= " (access_id in {$access} or (access_id = 0 and owner_guid = {$_SESSION['id']}))"; // Add access controls
		$query .= " order by $order_by limit $offset,$limit"; // Add order and limit
		
		return get_data($query, "entity_row_to_elggstar");
	}
	
	


	
	
	// In annotations/ meta 
	
	
?>