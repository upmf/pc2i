<?php
/**
 * @author Patrick Pollet
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package c2ipf V2
 * Importation avec conversion UTF8 des table d'une ancienne PF
 * SAUF la config les questions et les établissements 
 */

$chemin = '..';
$chemin_commun = $chemin."/commun";
$chemin_images = $chemin."/images";

require_once($chemin_commun."/c2i_params.php");					//fichier de paramétres


set_time_limit(0); //important

$ide=optional_param("ide",$USER->id_etab_perso,PARAM_INT); //étab de l'examen, défaut = ici '

$nom_bdd=optional_param("nom_bdd","",PARAM_RAW);


$pass_bdd=optional_param("pass_bdd",$base_mdp,PARAM_RAW);
$serveur_bdd=optional_param("serveur_bdd",$adresse_serveur,PARAM_RAW);
$user_bdd=optional_param("user_bdd",$base_utilisateur,PARAM_RAW);



require_once( $chemin."/templates/class.TemplatePower.inc.php");    //inclusion de moteur de templates
$tpl = new C2IPrincipale();    //cr�er une instance
//inclure d'autre block de templates

$forme1=<<<EOF

<div id="maj_ajax">
<form class="normale" id="monform" method="post" action="importoldbd.php">
<fieldset>
<legend>{importation_plateforme} </legend>

<div class="commentaire1">{info_importation_plateforme} </div>


<p class="double">
<label for="nom_bdd">{form_nom_bdd}<span class="info">{info_import_nom_bd}</span></label>
<input type="text" name="nom_bdd"  id="nom_bdd" value="{nom_bdd}" size="40" class="saisie required" title="js_valeur_manquante"/>
</p>

<p class="double">
<label for="serveur_bdd">{form_serveur_bdd}<span class="info">{info_import_serveur_bd}</span></label>
<input type="text" name="serveur_bdd" id="serveur_bdd" value="{serveur_bdd}" size="40" class="saisie"/>
</p>


<p class="double">
<label for="user_bdd">{form_user_bdd}<span class="info">{info_import_user_bd}</span></label>
<input type="text" name="user_bdd"  id="user_bdd" value="{user_bdd}" size="40" class="saisie"/>
</p>

<p class="double">
<label for="pass_bdd">{form_pass_bdd}<span class="info">{info_import_pass_bd}</span></label>
<input type="password" name="pass_bdd"  id="pass_bdd" value="{pass_bdd}" size="40" class="saisie"/>
</p>

<input name="ide" type="hidden" value="{ide}"/>
<input type="hidden" name="retour_fiche" value="{retour_fiche}" />
<!-- START BLOCK : id_session -->
<input name="{session_nom}" type="hidden" value="{session_id}" />
<!-- END BLOCK : id_session -->



<div class="centre">

{bouton:ok}
</div>

</fieldset>

</form>
</div>

EOF;

$forme2=<<<EOF

{resultats_op}

<div class="information">

</div>
EOF;

//pas encore de login connu
$USER= new StdClass();
$USER->type_plateforme='certification';
$USER->id_user='admin';

if ($nom_bdd) {
    $tpl->assignInclude("corps",$forme2,T_BYVAR);
    $tpl->prepare($chemin);
    $resultats=array(); //états des opérations
	require_once ($chemin."/commun/lib_rapport.php");
	try {
			
		$tpl->assign("_ROOT.titre_popup",traduction("importation_plateforme"));
		$resultats= doImport();
		if (count($resultats))
		    $tpl->assign("resultats_op",print_details($resultats,20));
		else
		    $tpl->assign("resultats_op","xxx");
		$tpl->gotoBlock("_ROOT");
		$tpl->print_boutons_fermeture();
		$tpl->printToScreen();
		die();
	} catch (Exception $e) {
		print_r($e);
	}
}



$CFG->utiliser_validation_js=1;

$tpl->assignInclude("corps",$forme1,T_BYVAR);
$tpl->prepare($chemin);

$tpl->assign("_ROOT.titre_popup",traduction("importation_plateforme"));
$tpl->assign("ide", $ide);



$tpl->gotoBlock("_ROOT");
$tpl->print_boutons_fermeture();


$tpl->printToScreen();										//affichage


function doImport() {
    global $USER, $CFG,  $connexion; 
     $resultats = array();
    global $nom_bdd,$serveur_bdd,$user_bdd,$pass_bdd;
    
    // on force le name space latin ET une nouvelle connexion
    // voir http://fr.php.net/mysql_connect#82040 (important si meme login/passe 
    // sur les 2 BD
    // notez que mysql_error() DOIT ici connaitre LA connexion utilisée  
    
    
    $oldConnexion =Connexion($user_bdd, $pass_bdd, $nom_bdd, $serveur_bdd,'latin1',true);
    set_ok ("Connexion établie avec l'ancienne base de données $nom_bdd",$resultats);
   
   
    
    if ($ligne = get_old_record('config', "cle='c2i'", $oldConnexion)) {
        if ($ligne->valeur === $CFG->c2i)
            set_ok ("controle type de C2I {$ligne->valeur} OK.",$resultats);
        else {
            set_erreur ("cette plateforme n'est pas pour le {$CFG->c2i}",$resultats);
            return $resultats;
        }
    } else {
        set_erreur (mysql_error($oldConnexion),$resultats);
        return $resultats;
    }
    
    if ($ligne = get_old_record('config', "cle='encodage'", $oldConnexion)) {
        if ($ligne->valeur === 'ISO-8859-1')
        set_ok ("controle encodage {$ligne->valeur} OK.",$resultats);
        else {
            set_erreur ("cette plateforme n'est pas en encodage ISO-8859-1",$resultats);
            return $resultats;
        }
    } else {
        set_erreur (mysql_error($oldConnexion),$resultats);
        return $resultats;
    }
    
    
    $tables=array (
        // MBV = la table cible must be vide
        // DEL tableau des champs n'existant plus en V2
        // NOACC tableau des champs donc les valeurs ne doivent plus avoir d'accents comme
        // (création, validée ...)  
        'droits'=> array('MBV'=>0, 'DEL'=>array(),'NOACC'=>array() ),
        'plagesip'=> array('MBV'=>0, 'DEL'=>array(),'NOACC'=>array() ),
        'preferences'=> array('MBV'=>0, 'DEL'=>array(),'NOACC'=>array() ),
    	'profils'=> array('MBV'=>0, 'DEL'=>array(),'NOACC'=>array() ),
        'qcm'   => array('MBV'=>0, 'DEL'=>array(),'NOACC'=>array() ),
    	'webservices_clients_allow'   => array('MBV'=>0, 'DEL'=>array(),'NOACC'=>array() ),
    	//'webservices_sessions'   => array('MBV'=>0, 'DEL'=>array(),'NOACC'=>array() ),
    
    );
    
    foreach ($tables as $tableNom=>$actions) {
        set_ok ("traitement de {$CFG->prefix}$tableNom",$resultats);
        
        if ($actions['MBV'] && (count_records($tableNom)!=0)) {
            set_erreur("La table $tableNom n'est pas vide dans la nouvelle base. ",$resultats);
            continue;
        }
        
        if (($oldRecords = get_old_records($tableNom,'', $oldConnexion))!==false) {
            $cnt=count($oldRecords);
            $nb = 0;
            set_ok ("traitement de $cnt lignes depuis {$CFG->prefix}$tableNom",$resultats);
            $enteteErreur=false; //drapeau pour nom de la table dans les erreurs  
            foreach($oldRecords as $oldRecord) {
                if (!empty($actions['DEL'])) {
                    foreach($actions['DEL'] as $field)
                        unset( $oldRecord->$field);
                }
                if (!empty($actions['NOACC'])) {
                    foreach($actions['NOACC'] as $field)
                    $oldRecord->$field = stripAccents($oldRecord->$field);
                }
                
                $oldRecord =encodeutf8_object($oldRecord);
                
                if (insert_record($tableNom,$oldRecord,false,false,false))
                    $nb++;
                else {
                    if (!$enteteErreur) {
                        set_erreur ("---------",$resultats);
                        set_erreur ("traitement de $cnt lignes depuis {$CFG->prefix}$tableNom",$resultats);
                        set_erreur ("---------",$resultats);
                        
                        $enteteErreur=true;
                    }
                    set_erreur (mysql_error($connexion),$resultats);
                    //return $resultats;
                }
                
            }
            set_ok ("Importation de {$nb}/{$cnt} lignes depuis {$CFG->prefix}$tableNom OK",$resultats);
            unset($oldRecords);
            
            
        } else {
            set_erreur (mysql_error($oldConnexion),$resultats);
            return $resultats;
        }
        
        
        
    }
    set_ok ("Fin du traitement de $nom_bdd",$resultats);
    return $resultats;

}

/**
 * 
 * lecture d'une ligne de l'ancienne BD
 * @param string $tablename  nom de la table SANS le prefixe
 * @param string $critere  clause where optionnelle
 * @param  $oldConnexion  connexion BD a utiliser
 * @return object avec les slashes retirés
 */
function get_old_record ($tablename, $critere, $oldConnexion) {
    
    global $connexion ; 
    
    $savConnexion = $connexion; 
    $connexion = $oldConnexion;
    $res = get_record ($tablename,$critere,false);
    $connexion = $savConnexion;
    return $res;
}

/**
*
* lecture de lignes de l'ancienne BD
* @param string $tablename  nom de la table SANS le prefixe
* @param string $critere  clause where optionnelle
* @param  $oldConnexion  connexion BD a utiliser
* * @return array of object avec les slashes retirés
*/
function get_old_records ($tablename, $critere, $oldConnexion) {

    global $connexion ;

    $savConnexion = $connexion;
    $connexion = $oldConnexion;
    $res = get_records ($tablename,$critere,false,false,false,false);
    $connexion = $savConnexion;
    return $res;
}

/**
* conversion UTF8 d'un data record
*
* $dataobject is an object containing needed data
*
* @param $dataobject Object containing the database record
* @return object Same object with neccessary characters converted
*/
function encodeutf8_object( $dataobject ) {
    $a = get_object_vars( $dataobject);
    foreach ($a as $key=>$value) {
        if (is_string($value))
            $a[$key] =  utf8_encode ( $value );
    }
    return (object)$a;
}

function stripAccents($string){
    return strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}
