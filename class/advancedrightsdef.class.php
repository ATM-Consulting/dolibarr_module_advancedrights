<?php

class TAdvancedRightDef extends TObjetStd {
	
	static $table = MAIN_DB_PREFIX.'advanced_right_def';
	
	function __construct() {
		$this->set_table(self::$table);
		 
		$this->add_champs('entity',array('type'=>'integer','index'=>true));
		$this->add_champs('groups,users,object_type',array('type'=>'string'));
		$this->add_champs('code_eval,rightstoavoid',array('type'=>'text'));
	
		$this->_init_vars();
	
		$this->start();
	}
	
	function getStdClass() {
		
		$r=new stdClass;
		
		$r->entity = $this->entity;
		$r->groups = $this->groups;
		$r->users = $this->users;
		$r->object_type = $this->object_type;
		$r->code_eval = $this->code_eval;
		$r->rightstoavoid = $this->rightstoavoid;
		
		return $r;
		
	}
	
	function save(&$PDOdb) {
		global $conf;
		
		$this->entity = $conf->entity;
		
		parent::save($PDOdb);
	}

	static function getGroupForUser(&$user) {
		global $db;
		
		dol_include_once('/user/class/usergroup.class.php');
		
		$g = new UserGroup($db);
		$TGroup = $g->listGroupsForUser($user->id);
		$Tab = array();
		foreach($TGroup as &$group) {
			$Tab[] = $group->id;
		}
		
		return $Tab;
	}
	
	/*
	 * unset $user->rights->... if not respect the condition eval
	 */
	static function run(&$PDOdb,&$object,User &$user) {
		
		$TRules = self::fetchAllForObject($PDOdb,$object);
		if(!empty($TRules)) {
		
			$TGroupOfUser = self::getGroupForUser($user);
			
			foreach($TRules as &$rightdef) {
				
				$TGroupOk = explode('|', $rightdef->groups);
				$TUserOk =  explode('|', $rightdef->users);
				
				$unset = true;
				if(!empty($rightdef->code_eval) && 
						(in_array($user->id, $TUserOk) || !empty(array_intersect($TGroupOk, $TGroupOfUser)))
				) {
					if(strpos($eval,'return ')===false)$eval = 'return ('.$eval.');';
					if(eval($eval)) {
						$unset = false;
					}
				}
				var_dump($unset);
				if($unset) eval('unset($user->rights->'.$rightdef->rightstoavoid.')');
					
				
				
			}
			
		}
		
	}

	static function getGroup(&$PDOdb) {
		$TGroup=array();
		$Tab = $PDOdb->ExecuteAsArray("SELECT rowid, nom
			FROM ".MAIN_DB_PREFIX."usergroup
			WHERE entity IN (".getEntity('usergroup',1).")");
		foreach($Tab as &$row) {
			$TGroup[$row->rowid] = $row->nom;
		}
	
		return $TGroup;
	}
	

	static function getUser(&$PDOdb) {
		$TUser=array();
		$Tab = $PDOdb->ExecuteAsArray("SELECT rowid, login
			FROM ".MAIN_DB_PREFIX."user
			WHERE entity IN (".getEntity('user',1).") AND statut = 1");
		foreach($Tab as &$row) {
			$TUser[$row->rowid] = $row->login;
		}
	
		return $TUser;
	}
	
	static function fetchAllForObject(&$PDOdb, &$object) {
		global $TCacheObject;
		
		$TRes = array();
		if(isset($object))
		{
			
			$type_object=$object->element;
				
			if(empty($TCacheObject))$TCacheObject=array();
			if(empty($TCacheObject[$type_object]))$TCacheObject[$type_object]=array();
			
			if(empty($_SESSION['CacheARD']))$_SESSION['CacheARD']=array();
			if(!empty($_SESSION['CacheARD'][$type_object])) return $_SESSION['CacheARD'][$type_object];
			
			$sql = "SELECT rowid FROM ".self::$table;
			$sql.= " WHERE object_type LIKE '%".$type_object."%'";
			$sql.= " ORDER BY date_cre ASC";
			
			$Tab = $PDOdb->ExecuteAsArray($sql);
			$TRes=array();
			foreach($Tab as $row) {
				$r=new TAdvancedRightDef;
				$r->load($PDOdb, $row->rowid);
				
				$TRes[] = $r->getStdClass();
				 
			}
			
			$_SESSION['CacheARD'][$type_object] = $TRes;
			
		}
		return $TRes;
	}
	

	static function getAll(&$PDOdb) {
	
		$sql = "SELECT rowid FROM ".self::$table." WHERE 1 ";
		$sql.=" ORDER BY date_cre ";
	
		$Tab = $PDOdb->ExecuteAsArray($sql);
	
		$TRes = array();
		foreach($Tab as $row) {
	
			$r=new TAdvancedRightDef;
			$r->load($PDOdb, $row->rowid);
	
			$TRes[] = $r;
		}
	
		return $TRes ;
	}
	
}