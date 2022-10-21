<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language file.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accountid'] = 'Identificador del compte';
$string['accountid_desc'] = 'L\'identificador del compte.';
$string['addactivityellipsis'] = 'Afegeix activitat...';
$string['addcourseellipsis'] = 'Afegir curs...';
$string['addon'] = 'Complement';
$string['addons'] = 'Complements';
$string['addonstate'] = 'Estat';
$string['addonstate_desc'] = 'L’estat del complement. Alguns complements requereixen que es configurin alguns paràmetres abans de poder-los habilitar o que s\'activin sols.';
$string['adminscanearn'] = 'Els administradors poden guanyar monedes';
$string['adminscanearn_desc'] = 'Quan està activat, això permet als administradors guanyar monedes.';
$string['apihost'] = 'Punt final de l\'API';
$string['apihost_desc'] = 'L’URL en què es troba l’API.';
$string['apikey'] = 'Clau de l\'API';
$string['apikey_desc'] = 'La clau de l’API per autenticar -se amb l’API.';
$string['autopush'] = 'Afegir automàticament els usuaris';
$string['autopush_help'] = 'Quan està habilitat, els usuaris s’afegiran automàticament com a jugadors del Motrain Dashboard, fins i tot abans que comencin a guanyar monedes. Aquest procés es fa quan passa el cron, els jugadors poden trigar diversos minuts a aparèixer al tauler. Tingueu en compte que això no s\'aplica a les associacions d\'equips de cohort existents, només els nous membres i les noves associacions.';
$string['butpaused'] = 'Però s\'ha aturat';
$string['cachedef_coins'] = 'Monedes d\'usuari';
$string['cachedef_comprules'] = 'Normes de finalització';
$string['cachedef_metadata'] = 'Metadades';
$string['cohort'] = 'Cohort';
$string['cohort_help'] = 'La cohort per associar -se a l\'equip, o "tots els usuaris" quan no es requereix cap cohort específica.';
$string['coinrules'] = 'Normes de la moneda';
$string['coins'] = 'Monedes';
$string['coinsimage'] = 'Imatge de monedes';
$string['coinsimage_help'] = 'Utilitzeu aquesta configuració per utilitzar una imatge alternativa que representi les monedes que es mostren al bloc.';
$string['completingacourse'] = 'Completant un curs';
$string['completingn'] = 'Completant {$a}';
$string['configfootercontent'] = 'Contingut de peu de pàgina';
$string['configfootercontent_help'] = 'El contingut a mostrar a la part inferior del bloc.';
$string['configtitle'] = 'Títol';
$string['coursecompletion'] = 'Finalització del curs';
$string['createassociation'] = 'Crear associació';
$string['dashboard'] = 'Dashboard';
$string['defaultparens'] = '(per defecte)';
$string['defaulttitle'] = 'Motrain';
$string['disabled'] = 'Desactivat';
$string['editassociation'] = 'Editar l\'associació';
$string['enableaddon'] = 'Activa el complement';
$string['enableaddon_help'] = 'S\'ha d\'activar un complement per funcionar.';
$string['enabled'] = 'Activat';
$string['eventcoinsearned'] = 'Monedes guanyades';
$string['globalsettings'] = 'Configuració global';
$string['infopagetitle'] = 'Informació';
$string['invalidcoinamount'] = 'Quantitat no vàlida de monedes';
$string['isenabled'] = 'Plugin habilitat';
$string['isenabled_desc'] = 'Per habilitar el complement, empleneu la configuració següent amb la informació correcta. Sempre que els detalls de l’API siguin correctes, el complement s’activarà.';
$string['ispaused'] = 'Pausar el plugin';
$string['ispaused_help'] = 'Quan es pausi, el plugin no enviarà informació al tauler de Motrain. No s’atorgaran monedes i els jugadors no es crearan. A més, els usuaris no poden veure el bloc i no poden accedir a la botiga. És possible que vulgueu marcar el complement com a pausa fins que s\'hagi configurat completament (regles, mapatges) o durant una migració del compte.';
$string['leaderboard'] = 'Taula de classificació';
$string['manageaddons'] = 'Gestiona els complements';
$string['metadatacache'] = 'Cache de metadades';
$string['metadatacache_help'] = 'El plugin manté una memòria cau d\'alguns metadades de l\'API per millorar el rendiment. Emmagatzema informació com ara quines taules de classificació estan habilitades, etc. Després de fer alguns canvis al tauler de Motrain, potser haureu de purgar manualment aquesta memòria cau. Podeu fer-ho fent clic a l’enllaç anterior.';
$string['motrain:accessdashboard'] = 'Accedir al tauler de Motrain';
$string['motrain:addinstance'] = 'Afegir un nou bloc de Motrain';
$string['motrain:awardcoins'] = 'Afegir monedes a altres usuaris';
$string['motrain:earncoins'] = 'Guanyar monedes';
$string['motrain:myaddinstance'] = 'Afegir el bloc Motrain al tauler';
$string['motrain:view'] = 'Consultar el contingut del bloc Motrain';
$string['motrainaddons'] = 'Complements Motrain';
$string['noaddoninstalled'] = 'Encara no hi ha complements instal·lats.';
$string['nocohortallusers'] = 'Tots els usuaris';
$string['nooptions'] = 'Sense opcions';
$string['noteamsyetcreatefirst'] = 'Encara no existeix cap associació d\'equips, comenceu a crear-ne una.';
$string['notenabled'] = 'El plugin no està habilitat, poseu-vos en contacte amb el vostre administrador.';
$string['playerid'] = 'ID del jugador';
$string['playeridnotfound'] = 'No s\'ha pogut trobar el jugador associat a l\'usuari actual, poseu-vos en contacte amb l\'administrador.';
$string['playermappingintro'] = 'Un mapping de jugadors és l’associació entre un usuari local i un jugador al tauler de Motrain. Podeu trobar la llista de associacions conegudes a continuació. Les associacions amb un error no es tornaran a intentar, solucioneu el problema i restableixi l\'associació.';
$string['playersmapping'] = 'Mapping dels jugadors';
$string['pleasewait'] = 'Si us plau, espereu...';
$string['pluginispaused'] = 'Actualment, el plugin està en pausa.';
$string['pluginname'] = 'Motrain';
$string['pluginnotenabledseesettings'] = 'El plugin no està habilitat, consulteu la seva configuració.';
$string['privacy:metadata:coinsgained'] = 'Informació enviada en adjudicar monedes.';
$string['privacy:metadata:coinsgained:coins'] = 'El nombre de monedes a concedir';
$string['privacy:metadata:coinsgained:reason'] = 'El motiu del premi';
$string['privacy:metadata:log'] = 'Registre de monedes';
$string['privacy:metadata:log:actionhash'] = 'El hash de l’acció';
$string['privacy:metadata:log:actionname'] = 'El nom d’acció';
$string['privacy:metadata:log:broadcasterror'] = 'L\'error per a les emissions fallides';
$string['privacy:metadata:log:coins'] = 'El nombre de monedes';
$string['privacy:metadata:log:contextid'] = 'L\'identificador de context';
$string['privacy:metadata:log:timebroadcasted'] = 'El moment en què es va emetre el registre';
$string['privacy:metadata:log:timecreated'] = 'El moment en què es va crear el registre';
$string['privacy:metadata:log:userid'] = 'L\'identificador d\'usuari';
$string['privacy:metadata:playermap'] = 'Mapping d’usuaris locals i jugadors Motrain';
$string['privacy:metadata:playermap:accountid'] = 'L\'identificador del compte Motrain';
$string['privacy:metadata:playermap:blocked'] = 'Si aquest mapping està bloquejat';
$string['privacy:metadata:playermap:blockedreason'] = 'El motiu del bloqueig';
$string['privacy:metadata:playermap:playerid'] = 'L\'identificador del jugador Motrain';
$string['privacy:metadata:playermap:userid'] = 'L\'identificador d\'usuari';
$string['privacy:metadata:remoteplayer'] = 'Informació intercanviada o identificar un jugador Motrain.';
$string['privacy:metadata:remoteplayer:email'] = 'Correu electrònic';
$string['privacy:metadata:remoteplayer:firstname'] = 'Nom';
$string['privacy:metadata:remoteplayer:lastname'] = 'Cognom';
$string['privacy:metadata:userspush'] = 'Una cua d’usuaris per ser afegits al tauler de Motrain.';
$string['privacy:metadata:userspush:userid'] = 'L\'identificador d\'usuari';
$string['privacy:path:logs'] = 'Registres';
$string['privacy:path:mappings'] = 'Mappings';
$string['purchases'] = 'Les meves compres';
$string['purgecache'] = 'Purga la memòria cau';
$string['reallydeleteassociation'] = 'De debò voleu eliminar l\'associació?';
$string['saverules'] = 'Desar les regles';
$string['saving'] = 'Guardant ...';
$string['setup'] = 'Configuració';
$string['store'] = 'Botiga';
$string['taskpushusers'] = 'Empènyer els usuaris al tauler';
$string['team'] = 'Equip';
$string['team_help'] = 'L’equip de Motrain al qual s’associarà la cohort.';
$string['teamassociationcreated'] = 'Associació d\'equip creada.';
$string['teamassociations'] = 'Associacions d’equips';
$string['teams'] = 'Equips';
$string['unknownactivityn'] = 'Activitat desconeguda {$a}';
$string['unknowncoursen'] = 'Curs desconegut {$a}';
$string['usecohorts'] = 'Utilitzeu cohorts';
$string['usecohorts_help'] = 'Quan estiguin habilitats, els usuaris es poden organitzar en diferents equips mitjançant cohorts. Tingueu en compte que quan no s’utilitzin les cohorts, tots els usuaris de Moodle seran considerats com a jugadors potencials.';
$string['userecommended'] = 'Ús recomanat';
$string['usernotplayer'] = 'L’usuari no compleix els criteris per ser un jugador.';
$string['userteamnotfound'] = 'No s\'ha pogut trobar l\'equip de l\'usuari actual, poseu -vos en contacte amb l\'administrador.';
