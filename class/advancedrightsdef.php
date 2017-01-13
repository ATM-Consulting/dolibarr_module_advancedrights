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

	/*
	 * unset $user->rights->... if not respect the condition eval
	 */
	static function run(&$PDOdb,&$object,&$user) {
		
		
		
	}
	
	static function fetchAllForObject(&$PDOdb, $object) {
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
			 
			$Tab = $PDOdb->TAdvancedRightDef($sql);
			$TRes=array();
			foreach($Tab as $row) {
				$r=new TAdvancedRightDef;
				$r->load($PDOdb, $row->rowid);
				
				$TRes[] = $r;
				 
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