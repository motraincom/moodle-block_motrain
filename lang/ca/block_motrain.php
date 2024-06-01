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
$string['accountidmismatch'] = 'Els identificadors dels comptes no coincideixen.';
$string['addactivityellipsis'] = 'Afegeix activitat...';
$string['addcourseellipsis'] = 'Afegir curs...';
$string['addon'] = 'Complement';
$string['addons'] = 'Complements';
$string['addonstate'] = 'Estat';
$string['addonstate_desc'] = 'L’estat del complement. Alguns complements requereixen que es configurin alguns paràmetres abans de poder-los habilitar o que s\'activin sols.';
$string['addprogramellipsis'] = 'Afegeix programa...';
$string['adminscanearn'] = 'Els administradors poden guanyar monedes';
$string['adminscanearn_desc'] = 'Quan està activat, això permet als administradors guanyar monedes.';
$string['apihost'] = 'Punt final de l\'API';
$string['apihost_desc'] = 'L’URL en què es troba l’API.';
$string['apikey'] = 'Clau de l\'API';
$string['apikey_desc'] = 'La clau de l’API per autenticar -se amb l’API.';
$string['areyousurerefreshallinteam'] = 'Estàs segur que vols actualitzar tots els jugadors de l\'equip? Això eliminarà totes les assignacions.';
$string['areyousurerefreshandpushallinteam'] = 'Estàs segur que vols actualitzar tots els jugadors de l\'equip? Això eliminarà tots els mapes i enviarà tots els usuaris al tauler de control.';
$string['autopush'] = 'Afegir automàticament els usuaris';
$string['autopush_help'] = 'Quan està habilitat, els usuaris s’afegiran automàticament com a jugadors del Motrain Dashboard, fins i tot abans que comencin a guanyar monedes. Aquest procés es fa quan passa el cron, els jugadors poden trigar diversos minuts a aparèixer al tauler. Tingueu en compte que això no s\'aplica a les associacions d\'equips de cohort existents, només els nous membres i les noves associacions.';
$string['blocked'] = 'Bloquejat';
$string['butpaused'] = 'Però s\'ha aturat';
$string['cachedef_coins'] = 'Monedes d\'usuari';
$string['cachedef_comprules'] = 'Normes de finalització';
$string['cachedef_metadata'] = 'Metadades';
$string['cachedef_programrules'] = 'Regles del programa';
$string['cachedef_purchasemetadata'] = 'Metadades de les compres';
$string['cachepurged'] = 'S\'ha purgat la memòria cau';
$string['cohort'] = 'Cohort';
$string['cohort_help'] = 'La cohort per associar -se a l\'equip, o "tots els usuaris" quan no es requereix cap cohort específica.';
$string['coinrules'] = 'Normes de la moneda';
$string['coins'] = 'Monedes';
$string['coinsimage'] = 'Imatge de monedes';
$string['coinsimage_help'] = 'Utilitzeu aquesta configuració per utilitzar una imatge alternativa que representi les monedes que es mostren al bloc.';
$string['completingacourse'] = 'Completant un curs';
$string['completingaprogram'] = 'Completant un programa';
$string['completingn'] = 'Completant {$a}';
$string['configaccentcolor'] = 'Color d\'accent';
$string['configaccentcolor_help'] = 'El color d\'accent que s\'utilitzarà al bloc, accepta qualsevol valor CSS.';
$string['configbgcolor'] = 'Color de fons';
$string['configbgcolor_help'] = 'El color de fons principal que s\'utilitzarà al bloc, accepta qualsevol valor CSS.';
$string['configfootercontent'] = 'Contingut de peu de pàgina';
$string['configfootercontent_help'] = 'El contingut a mostrar a la part inferior del bloc.';
$string['configtitle'] = 'Títol';
$string['connect'] = 'Connectar';
$string['courseandactivitycompletion'] = 'Finalització del curs i de l\'activitat';
$string['coursecompletion'] = 'Finalització del curs';
$string['createassociation'] = 'Crear associació';
$string['dashboard'] = 'Dashboard';
$string['defaultparens'] = '(per defecte)';
$string['defaulttitle'] = 'Motrain';
$string['disabled'] = 'Desactivat';
$string['disconnect'] = 'Desconnectar';
$string['editassociation'] = 'Editar l\'associació';
$string['emailtemplate'] = 'Plantilla de correu electrònic';
$string['enableaddon'] = 'Activa el complement';
$string['enableaddon_help'] = 'S\'ha d\'activar un complement per funcionar.';
$string['enabled'] = 'Activat';
$string['errorconnectingwebhookslocalnotificationsdisabled'] = 'S\'ha produït un error en connectar els webhooks. S\'ha desactivat l\'enviament de notificacions locals.';
$string['errorwhiledisconnectingwebhook'] = 'S\'ha produït un error en desconnectar el webhook: {$a}.';
$string['eventcoinsearned'] = 'Monedes guanyades';
$string['globalsettings'] = 'Configuració global';
$string['infopagetitle'] = 'Informació';
$string['inspect'] = 'Inspeccionar';
$string['inspectuser'] = 'Inspecciona l\'usuari';
$string['invalidcoinamount'] = 'Quantitat no vàlida de monedes';
$string['isenabled'] = 'Plugin habilitat';
$string['isenabled_desc'] = 'Per habilitar el complement, empleneu la configuració següent amb la informació correcta. Sempre que els detalls de l’API siguin correctes, el complement s’activarà.';
$string['ispaused'] = 'Pausar el plugin';
$string['ispaused_help'] = 'Quan es pausi, el plugin no enviarà informació al tauler de Motrain. No s’atorgaran monedes i els jugadors no es crearan. A més, els usuaris no poden veure el bloc i no poden accedir a la botiga. És possible que vulgueu marcar el complement com a pausa fins que s\'hagi configurat completament (regles, mapatges) o durant una migració del compte.';
$string['lastwebhooktime'] = 'Darrer webhook rebut: {$a}';
$string['leaderboard'] = 'Taula de classificació';
$string['level'] = 'Nivell';
$string['leveln'] = 'Nivell {$a}';
$string['localteammgmt'] = 'Gestió d\'equips locals';
$string['localteammgmt_help'] = 'Quan s\'habilita, l\'equip d\'un jugador serà dictat per la configuració a Moodle i es reflectirà al tauler de control de Motrain. Això permet gestionar els equips localment. Tingues en compte que, en algunes circumstàncies, quan els jugadors migren a un equip diferent, poden perdre el seu lloc dins del rànquing de classificació.';
$string['manageaddons'] = 'Gestiona els complements';
$string['maximumlevel'] = 'Nivell màxim';
$string['messageprovider:notification'] = 'Notificacions en relació a Motrain';
$string['messagetemplates'] = 'Plantilles de missatges';
$string['messagetemplatesintro'] = 'Les plantilles següents s\'utilitzen quan s\'envien notificacions als jugadors des d\'aquesta pàgina. El sistema triarà l\'idioma de la plantilla que millor s\'ajusti a l\'idioma del destinatari o utilitzarà la plantilla alternativa quan no n\'hi hagi cap que encaixi.';
$string['metadatacache'] = 'Cache de metadades';
$string['metadatacache_help'] = 'El plugin manté una memòria cau d\'alguns metadades de l\'API per millorar el rendiment. Emmagatzema informació com ara quines taules de classificació estan habilitades, etc. Després de fer alguns canvis al tauler de Motrain, potser haureu de purgar manualment aquesta memòria cau. Podeu fer-ho fent clic a l’enllaç anterior.';
$string['metadatasyncdisabled'] = 'La sincronització de metadades està desactivada.';
$string['motrain:accessdashboard'] = 'Accedir al tauler de Motrain';
$string['motrain:addinstance'] = 'Afegir un nou bloc de Motrain';
$string['motrain:awardcoins'] = 'Afegir monedes a altres usuaris';
$string['motrain:earncoins'] = 'Guanyar monedes';
$string['motrain:manage'] = 'Gestiona aspectes de la integració de Motrain.';
$string['motrain:myaddinstance'] = 'Afegir el bloc Motrain al tauler';
$string['motrain:view'] = 'Consultar el contingut del bloc Motrain';
$string['motrainaddons'] = 'Complements Motrain';
$string['motrainemaillookup'] = 'Motrain (cerca de correu electrònic)';
$string['motrainidlookup'] = 'Motrain (cerca d\'identificador)';
$string['multiteamsusers'] = 'Usuaris de múltiples equips';
$string['nextlevelin'] = 'Següent nivell en';
$string['noaddoninstalled'] = 'Encara no hi ha complements instal·lats.';
$string['nocohortallusers'] = 'Tots els usuaris';
$string['nooptions'] = 'Sense opcions';
$string['noredemptionessagefound'] = '[No s\'ha trobat informació]';
$string['noteamsyetcreatefirst'] = 'Encara no existeix cap associació d\'equips, comenceu a crear-ne una.';
$string['notenabled'] = 'El plugin no està habilitat, poseu-vos en contacte amb el vostre administrador.';
$string['notfound'] = 'No s\'ha trobat';
$string['pendingorders'] = 'Comandes pendents';
$string['placeholdercoins'] = 'El nombre de monedes associades.';
$string['placeholderitemname'] = 'El nom de l\'element associat.';
$string['placeholderitemnameexample'] = 'Nom de l\'element fictici';
$string['placeholderitems'] = 'Els noms (i quantitat) dels elements associats.';
$string['placeholderitemsexample'] = '1x Nom de l\'element fictici, 2x Un altre nom de l\'element';
$string['placeholdermessage'] = 'Un missatge associat a aquest esdeveniment.';
$string['placeholdermessageexample'] = 'Un missatge associat a aquest esdeveniment.';
$string['placeholderoptionalmessagefromadmin'] = 'Un missatge opcional de l\'administrador.';
$string['placeholdervouchercode'] = 'El codi del cupó que s\'ha reclamat.';
$string['placeholdervouchercodeexample'] = 'ABC123';
$string['playerid'] = 'ID del jugador';
$string['playeridnotfound'] = 'No s\'ha pogut trobar el jugador associat a l\'usuari actual, poseu-vos en contacte amb l\'administrador.';
$string['playermapping'] = 'Mapping dels jugadors';
$string['playermappingintro'] = 'Un mapping de jugadors és l’associació entre un usuari local i un jugador al tauler de Motrain. Podeu trobar la llista de associacions conegudes a continuació. Les associacions amb un error no es tornaran a intentar, solucioneu el problema i restableixi l\'associació.';
$string['playersmapping'] = 'Mapping dels jugadors';
$string['pleasewait'] = 'Si us plau, espereu...';
$string['pluginispaused'] = 'Actualment, el plugin està en pausa.';
$string['pluginname'] = 'Motrain';
$string['pluginnotenabledseesettings'] = 'El plugin no està habilitat, consulteu la seva configuració.';
$string['previewemail'] = 'Envia un correu electrònic de previsualització a';
$string['previewemail_help'] = 'El correu electrònic al qual s\'envia la previsualització. Aquest valor no s\'ha desat.';
$string['previewnotsent'] = 'No s\'ha pogut enviar el correu electrònic de previsualització.';
$string['previewsent'] = 'El correu electrònic de previsualització s\'ha enviat amb el contingut següent. Tingues en compte que la plantilla encara no s\'ha desat.';
$string['primaryteam'] = 'Equip primari';
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
$string['programcompletion'] = 'Finalització del programa';
$string['purchases'] = 'Les meves compres';
$string['purgecache'] = 'Purga la memòria cau';
$string['reallydeleteassociation'] = 'De debò voleu eliminar l\'associació?';
$string['refresh'] = 'Refresca';
$string['result'] = 'Resultat';
$string['resyncnow'] = 'Resincronitza ara';
$string['saverules'] = 'Desar les regles';
$string['saving'] = 'Guardant ...';
$string['secondaryteam'] = 'Equip secundari {$a}';
$string['sendlocalnotifications'] = 'Envia notificacions locals';
$string['sendlocalnotifications_help'] = 'Quan s\'habilita, Moodle lliurarà missatges als jugadors en lloc de Motrain. Per evitar que els missatges s\'enviïn dues vegades, cal desactivar les "Comunicacions sortints al jugador" des del tauler de control de Motrain. Els missatges enviats als jugadors es poden personalitzar a la pàgina de configuració "Plantilles de missatges". Els webhooks són necessaris perquè això funcioni.';
$string['sendlocalnotificationsdisabledrequiresenabled'] = 'L\'enviament de notificacions locals s\'ha desactivat, no es pot activar abans que el propi connector estigui habilitat.';
$string['sendlocalnotificationsdisabledwithwebhooks'] = 'L\'enviament de notificacions locals s\'ha desactivat, requereix webhooks per funcionar.';
$string['sendlocalnotificationsnotenabledseesettings'] = 'L\'enviament de notificacions locals no està activat; revisa, si us plau, la configuració.';
$string['sendpreview'] = 'Envia una previsualització';
$string['setup'] = 'Configuració';
$string['sourcex'] = 'Font: {$a}';
$string['spend'] = 'Gastar';
$string['store'] = 'Botiga';
$string['taskpushusers'] = 'Empènyer els usuaris al tauler';
$string['team'] = 'Equip';
$string['team_help'] = 'L’equip de Motrain al qual s’associarà la cohort.';
$string['teamassociationcreated'] = 'Associació d\'equip creada.';
$string['teamassociations'] = 'Associacions d’equips';
$string['teams'] = 'Equips';
$string['templatecontent'] = 'Contingut';
$string['templatecontent_help'] = 'El contingut del missatge a enviar.
 
 Els següents marcadors de posició estan disponibles:
 
 - `[monedes]`: el nombre de monedes rebudes, si n\'hi ha.
 - `[itemname]`: el nom de l\'element associat, si n\'hi ha.
 - `[missatge]`: un missatge associat opcional (per exemple, aprovació de la comanda, comanda automàtica), si n\'hi ha.
 - `[vouchercode]`: el codi del cupó que s\'ha reclamat, si n\'hi ha.
 - `[firstname]`: el primer nom del jugador.
 - `[cognom]`: el cognom del jugador.
 - `[nom complet]`: el nom complet del jugador.';
$string['templatedeleted'] = 'S\'ha suprimit la plantilla';
$string['templateenabled'] = 'Habilitat';
$string['templateenabled_help'] = 'Habilita la plantilla quan estigui llesta per a enviar-la als jugadors.';
$string['templateforlangalreadyexists'] = 'Ja existeix una plantilla per a aquest idioma.';
$string['templatelanguage'] = 'Idioma de la plantilla';
$string['templatelanguage_help'] = 'L\'idioma de la plantilla coincidirà amb l\'idioma del destinatari previst.';
$string['templatelanguageanyfallback'] = 'Tots els idiomes (alternativa)';
$string['templatesaved'] = 'S\'ha desat la plantilla.';
$string['templatesubject'] = 'Assumpte';
$string['templatesubject_help'] = 'L\'assumpte del missatge a enviar.
 
 Els següents marcadors de posició estan disponibles:
 
 - `[itemname]`: el nom de l\'element associat, si n\'hi ha.
 - `[firstname]`: el primer nom del jugador.
 - `[cognom]`: el cognom del jugador.
 - `[nom complet]`: el nom complet del jugador.';
$string['templatetypeauctionwon'] = 'La subhasta guanyada';
$string['templatetypemanualaward'] = 'Jugador premiat manualment';
$string['templatetyperafflewon'] = 'Sorteig guanyat';
$string['templatetyperedemptionrequestaccepted'] = 'Sol·licitud de comanda aprovada';
$string['templatetyperedemptionselfcompleted'] = 'Comanda automàtica completada';
$string['templatetyperedemptionshippingordersubmitted'] = 'S\'ha enviat la comanda';
$string['templatetyperedemptionvoucherclaimed'] = 'Cupó reclamat';
$string['templatetypesweepstakeswon'] = 'Sorteig guanyat';
$string['templatex'] = 'Plantilla: {$a}';
$string['therearexusersinmultipleteams'] = 'Hi ha {$a} usuaris en diversos equips.';
$string['tickets'] = 'Entrades';
$string['unknownactivityn'] = 'Activitat desconeguda {$a}';
$string['unknowncoursen'] = 'Curs desconegut {$a}';
$string['unknownprogramn'] = 'Programa desconegut {$a}';
$string['unknowntemplatecode'] = 'Codi de plantilla desconegut "{$a}"';
$string['usecohorts'] = 'Utilitzeu cohorts';
$string['usecohorts_help'] = 'Quan estiguin habilitats, els usuaris es poden organitzar en diferents equips mitjançant cohorts. Tingueu en compte que quan no s’utilitzin les cohorts, tots els usuaris de Moodle seran considerats com a jugadors potencials.';
$string['userdoesnotexist'] = 'L\'usuari no existeix.';
$string['userecommended'] = 'Ús recomanat';
$string['useridoremail'] = 'ID d\'usuari o correu electrònic';
$string['usernotplayer'] = 'L’usuari no compleix els criteris per ser un jugador.';
$string['userteamnotfound'] = 'No s\'ha pogut trobar l\'equip de l\'usuari actual, poseu -vos en contacte amb l\'administrador.';
$string['viewlist'] = 'Veure llista';
$string['webhooksconnected'] = 'Webhooks connectats';
$string['webhooksconnected_help'] = 'Els webhooks s\'utilitzen per permetre que Motrain es comuniqui directament amb Moodle. Per exemple, s\'utilitzen per enviar notificacions locals als jugadors. Els webhooks no es processen quan el connector no està habilitat o està en pausa.';
$string['webhooksdisconnected'] = 'Webhooks desconnectats';
