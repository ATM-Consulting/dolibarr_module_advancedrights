<?php

class TAdvancedRightDef extends TObjetStd {

	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'advanced_right_def');

		$this->add_champs('entity',array('type'=>'integer','index'=>true));
		$this->add_champs('groups,users,object_type,action',array('type'=>'string'));
		$this->add_champs('code_eval',array('type'=>'text'));
		$this->add_champs('rightstoavoid',array('type'=>'array'));

		$this->_init_vars();

		$this->start();
	}

	public function getStdClass() {

		$r=new stdClass;

		$r->entity = $this->entity;
		$r->groups = $this->groups;
		$r->users = $this->users;
		$r->object_type = $this->object_type;
		$r->code_eval = $this->code_eval;
		$r->rightstoavoid = $this->rightstoavoid;
		$r->action = $this->action;

		return $r;

	}

	public function save(&$PDOdb) {
		global $conf;

		$this->entity = $conf->entity;

		parent::save($PDOdb);
	}

	static public function getGroupForUser(&$user) {
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
	static function run(&$PDOdb,&$object,User &$user, &$action = null, &$parameters= array()) {
		global $conf;
		$TRules = self::fetchAllForObject($PDOdb,$object);
		if(!empty($TRules)) {
		//var_dump($TRules);
			$TGroupOfUser = self::getGroupForUser($user);

			foreach($TRules as &$rightdef) {

				$TGroupOk = explode('|', $rightdef->groups);
				$TUserOk =  explode('|', $rightdef->users);

				$unset = false;
				$intersect_test = array_intersect($TGroupOk, $TGroupOfUser);

				if(!empty($rightdef->code_eval) &&
						(in_array($user->id, $TUserOk) || !empty($intersect_test)) &&
					(($action==$rightdef->action) || empty($rightdef->action))
				) {
					$eval = $rightdef->code_eval;

					if(strpos($eval,'return ')===false)$eval = 'return ('.$eval.');';
					$ret = eval($eval);

					if($ret) {
						$unset = true;
					}
				}

				if($unset) {
					foreach($rightdef->rightstoavoid as $r) {
						eval('unset($user->rights->'.$r.');');
					}
				}



			}

		}

	}

	static public function getAllRights() {
		global $conf, $db,$user, $langs;

		$permsuser = array();

		$sql = "SELECT r.module, r.perms, r.subperms,r.libelle";
		$sql.= " FROM ".MAIN_DB_PREFIX."rights_def as r";
		$sql.= " WHERE 1";
		$sql.= " AND r.entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.")";
		$sql.= " AND r.perms IS NOT NULL";

		dol_syslog(get_class($this).'::getrights', LOG_DEBUG);
		$resql = $db->query($sql);
		if ($resql)
		{
			while ($obj = $db->fetch_object($resql))
			{
				$module=$obj->module;
				$perms=$obj->perms;
				$subperms=$obj->subperms;


				if ($perms)
				{
					if ($subperms)
					{
						$permsuser[$module.'->'.$perms.'->'.$subperms] = ucfirst($langs->trans($module)).' : '. $langs->trans($obj->libelle);
					}
					else
					{
						$permsuser[$module.'->'.$perms] = ucfirst($langs->trans($module)).' : '.$langs->trans($obj->libelle);
					}

				}

			}

		}

		return $permsuser;
	}

	static public function getGroup(&$PDOdb) {
		$TGroup=array();
		$Tab = $PDOdb->ExecuteAsArray("SELECT rowid, nom
			FROM ".MAIN_DB_PREFIX."usergroup
			WHERE entity IN (".getEntity('usergroup',1).")");
		foreach($Tab as &$row) {
			$TGroup[$row->rowid] = $row->nom;
		}

		return $TGroup;
	}


	static public function getUser(&$PDOdb) {
		$TUser=array();
		$Tab = $PDOdb->ExecuteAsArray("SELECT rowid, login
			FROM ".MAIN_DB_PREFIX."user
			WHERE entity IN (".getEntity('user',1).") AND statut = 1");
		foreach($Tab as &$row) {
			$TUser[$row->rowid] = $row->login;
		}

		return $TUser;
	}

	static public function fetchAllForObject(&$PDOdb, &$object) {
		global $TCacheObject;

		$TRes = array();

		if(isset($object))
		{

			$type_object=$object->element;

			if(empty($TCacheObject))$TCacheObject=array();
			if(empty($TCacheObject[$type_object]))$TCacheObject[$type_object]=array();

			if(empty($_SESSION['CacheARD']))$_SESSION['CacheARD']=array();
			if(!empty($_SESSION['CacheARD'][$type_object])) return $_SESSION['CacheARD'][$type_object];

			$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.'advanced_right_def';
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


	static public function getAll(&$PDOdb) {
		global $conf;
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.'advanced_right_def'." WHERE entity=".$conf->entity;
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
