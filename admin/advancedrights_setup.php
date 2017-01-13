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

dol_include_once('/advancedrights/class/advancedrightsdef.php');

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
			// var_dump($rem);
			
			$r=new TAdvancedRightDef;
			$r->load($PDOdb, $id_rem);
			$r->set_values($rem);
			 
			$fk_societe=GETPOST('TAdvancedRightDef_'.$r->getId().'_fk_soc');
			$r->fk_societe = $fk_societe > 0 ? $fk_societe : 0 ;

			$fk_user = GETPOST('TAdvancedRightDef_'.$r->getId().'_fk_user');
			$r->fk_user = $fk_user > 0 ? $fk_user : 0 ;
			 
			$r->save($PDOdb);
		}
		 
		 
		setEventMessage('Saved');
	}
	 
}
else if($action == 'delete'){
	$r=new TAdvancedRightDef;
	$r->load($PDOdb, GETPOST('id'));
	$r->delete($PDOdb);
}
else if($action == 'add'){
	$r=new TAdvancedRightDef;
	$r->save($PDOdb);
	 
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
print '<td>'.$langs->trans("hookname").'</td>'."\n";
print '<td>'.$langs->trans("GroupOrUser").'</td>'."\n";

print '<td>'.$langs->trans("Condition").'</td>'."\n";
print '<td>'.$langs->trans("Rights").'</td>'."\n";

print '</tr>';


    $TAdvancedRightDef = TAdvancedRightDef::getAll($PDOdb);

    foreach($TAdvancedRightDef as &$r) {
    	// Gestion affichage type
		
        $class = ($class == 'impair') ? 'pair' : 'impair';
        
        ?>
        <tr class="<?php echo $class  ?>" id="row_<?php echo $r->getId(); ?>">
            <td valign="top">
            	Type<br/>
            	<?php echo $formCore->combo('', 'TAdvancedRightDef['.$r->getId().'][type]', $r->TType, $r->type); ?><br /><br/>
            	Trigger<br/>
            	<?php echo $formCore->texte('','TAdvancedRightDef['.$r->getId().'][trigger_code]' , $r->trigger_code, 25,50, '', 'trigger_code');
                echo '<div class="nbday" style="'.$cssNBDAY.'"><br/>';
                echo $langs->trans('NbDayAfter').'<br/>'.$formCore->texte('','TAdvancedRightDef['.$r->getId().'][nb_day_after]' , $r->nb_day_after, 3,5);
				echo '</div>';
            ?></td>
            <td valign="top">
            	Société (facultatif)<br/>
            	<?php echo $form->select_thirdparty_list($r->fk_societe,'TAdvancedRightDef_'.$r->getId().'_fk_soc', '', 1); ?><br /><br/>
            	Utilisateur (facultatif)<br/>
                <?php echo $form->select_dolusers( (empty($r->fk_user) ? -1 : $r->fk_user)  ,'TAdvancedRightDef_'.$r->getId().'_fk_user' ,1); ?><script type="text/javascript">
                 
                </script></td>
            <td valign="top"><?php 
                echo '<div class="type" style="'.$cssTYPE.'">';
                echo 'Type alert<br/>'.$formCore->combo('', 'TAdvancedRightDef['.$r->getId().'][type_msg]', $r->TTypeMessage, $r->type_msg);
				echo '</div>';
                echo '<div class="titre" style="'.$cssTITRE.'">';
                echo 'Titre<br/>'.$formCore->texte('','TAdvancedRightDef['.$r->getId().'][titre]' , $r->titre, 25,50);
				echo '</div>';
				echo '<div class="message" style="'.$cssMESSAGE.'">';
                echo 'Message<br/>'.$formCore->zonetexte('','TAdvancedRightDef['.$r->getId().'][message]' , $r->message, 50,5);
				echo '<p>
				Codes utilisables :<br/>
				[societe_nom]
				[societe_code_client]
				[ref]
				[ref_client]
				[date]
				</p>';
				echo '</div>';
            ?></td>
            
            <td valign="center"><?php 
                    echo $formCore->zonetexte($langs->trans('CodeToEvalBefore').'<br />','TAdvancedRightDef['.$r->getId().'][message_condition]' , $r->message_condition, 50,2); 
                    echo '<br />'.$formCore->zonetexte($langs->trans('CodeToEvalAfter').'<br />','TAdvancedRightDef['.$r->getId().'][message_code]' , $r->message_code, 50,2);   
             ?></td>
            
            <td valign="bottom"><?php echo '<a href="?action=delete&id='.$r->getId().'">'.img_delete().'</a>';  ?></td>
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