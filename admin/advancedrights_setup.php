<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/advancedrights.php
 * 	\ingroup	advancedrights
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment

require '../config.php';

dol_include_once('/advancedrights/class/advancedrightsdef.class.php');

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/advancedrights.lib.php';

// Translations
$langs->load("advancedrights@advancedrights");

// Access control
if (! $user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'alpha');
$PDOdb=new TPDOdb;
/*
 * Actions
 */

unset($_SESSION['CacheARD']); // purge session en cas de modif

if($action == 'save') {
	 
	if(!empty($_REQUEST['TAdvancedRightDef'])) {
		 
		foreach($_REQUEST['TAdvancedRightDef'] as $id_rem => &$rem) {
			
			$o=new TAdvancedRightDef;
			$o->load($PDOdb, $id_rem);
			$o->set_values($rem);
			 
			$groups=GETPOST('TAdvancedRightDef_'.$o->getId().'_fk_usergroup');
			if(!empty($groups)){
				$o->groups = implode('|',$groups) ;
			} else {
				$o->groups = null;
			}
			
			$users = GETPOST('TAdvancedRightDef_'.$o->getId().'_fk_user');
			if(!empty($users)){
				$o->users = implode('|',$users) ;;
			}else{
				$o->users = null;
			}
			 
			$o->rightstoavoid = GETPOST('TAdvancedRightDef_'.$o->getId().'_rightstoavoid');
			
			$o->save($PDOdb);
		}
		 
		 
		setEventMessage('Saved');
	}
	 
}
else if($action == 'delete'){
	$o=new TAdvancedRightDef;
	$o->load($PDOdb, GETPOST('id'));
	$o->delete($PDOdb);
}
else if($action == 'add'){
	$o=new TAdvancedRightDef;
	$o->save($PDOdb);
	 
}



/*
 * View
 */
$page_name = "AdvancedrightsSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = advancedrightsAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104038Name"),
    0,
    "advancedrights@advancedrights"
);

// Setup page goes here
$form=new Form($db);
$formCore = new TFormCore('auto','formSave', 'post');
echo $formCore->hidden('action', 'save');

// MANAGEMENT JAVASCRIPT BLOCKS
$var=false;

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$form->textwithpicto($langs->trans('ObjectType'), $langs->trans('ObjectTypeHelp')).'</td>'."\n";
print '<td>'.$form->textwithpicto($langs->trans('GroupOrUser'), $langs->trans('GroupOrUserHelp')).'</td>'."\n";

print '<td>'.$langs->trans("Condition").' / '.$langs->trans("Rights").'</td>'."\n";
print '<td></td>'."\n";

print '</tr>';


	$TGroup = TAdvancedRightDef::getGroup($PDOdb);
	$TUser = TAdvancedRightDef::getUser($PDOdb);
	
	$TGroupSelected = $TUserSelected = array();
	
    $TAdvancedRightDef = TAdvancedRightDef::getAll($PDOdb);

    $TRights = TAdvancedRightDef::getAllRights();
    
    
    foreach($TAdvancedRightDef as &$o) {
    	// Gestion affichage type
		
        $class = ($class == 'impair') ? 'pair' : 'impair';
        
        $TGroupSelected = explode('|',$o->groups);
        $TUserSelected = explode('|',$o->users);
        
        $TRightsSelected = $o->rightstoavoid;
        
        ?>
        <tr class="<?php echo $class  ?>" id="row_<?php echo $o->getId(); ?>">
            <td valign="top">
            	<?php echo $formCore->texte('', 'TAdvancedRightDef['.$o->getId().'][object_type]', $o->object_type, 50,255, '', 'object_type'); ?>
            	
            </td>
            <td valign="top">
            	Groupes (facultatif)<br/>
            	<?php echo $form->multiselectarray('TAdvancedRightDef_'.$o->getId().'_fk_usergroup', $TGroup, $TGroupSelected,0,0,'minwidth100') ?>
				<br />            	
            	Utilisateur (facultatif)<br/>
                <?php echo $form->multiselectarray('TAdvancedRightDef_'.$o->getId().'_fk_user', $TUser, $TUserSelected,0,0,'minwidth100');
				echo '</br>';
				echo $formCore->texte('Action (facultatif)<br />','TAdvancedRightDef['.$o->getId().'][action]' , $o->action, 50,255,'','object_type');?>
           </td>
           <td valign="center"><?php 
           echo $formCore->zonetexte($langs->trans('CodeToEval').'<br />','TAdvancedRightDef['.$o->getId().'][code_eval]' , $o->code_eval, 50,2);
           echo '<hr />';
           echo $form->textwithpicto($langs->trans('RightsToRemove'), $langs->trans('RightsToRemoveHelp')).'<br />';
           echo $form->multiselectarray('TAdvancedRightDef_'.$o->getId().'_rightstoavoid' , $TRights, $TRightsSelected,0,0,'minwidth300' );
           
          // echo $formCore->zonetexte($langs->trans('RightsToRemove').'<br />','TAdvancedRightDef['.$o->getId().'][rightstoavoid]' , $o->rightstoavoid, 50,2);
            ?></td>
            
            <td valign="bottom"><?php echo '<a href="?action=delete&id='.$o->getId().'">'.img_delete().'</a>';  ?></td>
        </tr>
        
        <?php
        
        
    }
    

print '</table>';


echo '<div class="tabsAction">
 <a href="?action=add" class="butAction">'.$langs->trans('Add').'</a>
 <input type="submit" class="butAction" value="'.$langs->trans('Save').'" name="bt_save" />
</div>
';

$formCore->end();

llxFooter();

$db->close();