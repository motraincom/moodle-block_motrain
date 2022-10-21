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

$string['accountid'] = 'ID de la cuenta';
$string['accountid_desc'] = 'El ID de la cuenta.';
$string['addactivityellipsis'] = 'Agregar actividad...';
$string['addcourseellipsis'] = 'Agregar curso...';
$string['addon'] = 'Complemento';
$string['addons'] = 'Complementos';
$string['addonstate'] = 'Estado';
$string['addonstate_desc'] = 'El estado del complemento. Algunos complementos requieren que se configuren algunas configuraciones antes de poderlos habilitar o que se habiliten solos.';
$string['adminscanearn'] = 'Los administradores pueden ganar monedas';
$string['adminscanearn_desc'] = 'Cuando está habilitado, esto permite a los administradores ganar monedas.';
$string['apihost'] = 'Punto final de la API';
$string['apihost_desc'] = 'La URL en la que se encuentra la API.';
$string['apikey'] = 'Clave API';
$string['apikey_desc'] = 'La clave API para autenticar con la API.';
$string['autopush'] = 'Añadir automáticamente a los usuarios';
$string['autopush_help'] = 'Cuando está habilitado, los usuarios se añadiran automáticamente como jugadores en Motrain Dashboard, incluso antes de que comiencen a ganar monedas. Este proceso se realiza cuando pasa el cron, por lo que los jugadores pueden tardar varios minutos en aparecer en el tablero. Tenga en cuenta que esto no se aplica a las asociaciones existentes del equipo de cohorte, solo nuevos miembros y nuevas asociaciones.';
$string['butpaused'] = 'Pero se detuvo';
$string['cachedef_coins'] = 'Monedas de usuario';
$string['cachedef_comprules'] = 'Reglas de finalización';
$string['cachedef_metadata'] = 'Metadatos';
$string['cohort'] = 'Cohorte';
$string['cohort_help'] = 'La cohorte para asociarse con el equipo, o "todos los usuarios" cuando no se requiere una cohorte específica.';
$string['coinrules'] = 'Reglas de moneda';
$string['coins'] = 'Monedas';
$string['coinsimage'] = 'Imagen de monedas';
$string['coinsimage_help'] = 'Use esta configuración para usar una imagen alternativa que represente las monedas que se muestran en el bloque.';
$string['completingacourse'] = 'Completando un curso';
$string['completingn'] = 'Completando {$a}';
$string['configfootercontent'] = 'Contenido de pie de página';
$string['configfootercontent_help'] = 'El contenido para mostrar en la parte inferior del bloque.';
$string['configtitle'] = 'Título';
$string['coursecompletion'] = 'Finalización del curso';
$string['createassociation'] = 'Crear asociación';
$string['dashboard'] = 'Dashboard';
$string['defaultparens'] = '(defecto)';
$string['defaulttitle'] = 'Motrain';
$string['disabled'] = 'Desactivado';
$string['editassociation'] = 'Editar asociación';
$string['enableaddon'] = 'Habilitar complemento';
$string['enableaddon_help'] = 'Se debe habilitar un complemento para funcionar.';
$string['enabled'] = 'Activado';
$string['eventcoinsearned'] = 'Monedas ganadas';
$string['globalsettings'] = 'Configuración global';
$string['infopagetitle'] = 'Información';
$string['invalidcoinamount'] = 'Cantidad no válida de monedas';
$string['isenabled'] = 'Plugin habilitado';
$string['isenabled_desc'] = 'Para habilitar el complemento, complete la configuración a continuación con la información correcta. Mientras los detalles de la API sean correctos, el complemento se habilitará.';
$string['ispaused'] = 'Pausar el plugin';
$string['ispaused_help'] = 'Cuando se detenga, el plugin no enviará información al tablero de Motrain. No se otorgarán monedas, y los jugadores no serán creados. Además, los usuarios no pueden ver el bloque y no pueden acceder a la tienda. Es posible que desee marcar el plugin en pausa hasta que se haya configurado completamente (reglas, asignaciones) o durante una migración de cuenta.';
$string['leaderboard'] = 'Tabla de clasificación';
$string['manageaddons'] = 'Administrar complementos';
$string['metadatacache'] = 'Caché de metadatos';
$string['metadatacache_help'] = 'El plugin mantiene un caché de algunos metadatos de la API para mejorar el rendimiento. Almacena información, como qué tablas de clasificación están habilitadas, etc. Después de hacer algunos cambios en el tablero de Motrain, es posible que deba purgar manualmente este caché. Puede hacerlo haciendo clic en el enlace de arriba.';
$string['motrain:accessdashboard'] = 'Acceder al tablero Motrain';
$string['motrain:addinstance'] = 'Agregar un nuevo bloque de Motrain';
$string['motrain:awardcoins'] = 'Añadir monedas a otros usuarios';
$string['motrain:earncoins'] = 'Ganar monedas';
$string['motrain:myaddinstance'] = 'Agregar el bloque Motrain en el tablero';
$string['motrain:view'] = 'Ver el contenido del bloque Motrain';
$string['motrainaddons'] = 'Complementos de Motrain';
$string['noaddoninstalled'] = 'Todavía no se han instalado complementos.';
$string['nocohortallusers'] = 'Todos los usuarios';
$string['nooptions'] = 'Sin opciones';
$string['noteamsyetcreatefirst'] = 'Todavía no existe una asociación de equipo, comience por crear una.';
$string['notenabled'] = 'El plugin no está habilitado, comuníquese con su administrador.';
$string['playerid'] = 'ID del jugador';
$string['playeridnotfound'] = 'No se pudo encontrar el jugador asociado con el usuario actual, comuníquese con el administrador.';
$string['playermappingintro'] = 'Un mapping de jugadores es la asociación entre un usuario local y un jugador en el tablero de Motrain. Puede encontrar la lista de asignaciones conocidas a continuación. Las asignaciones con un error no se volverán a completar, solucione el problema y restablezca la asignación.';
$string['playersmapping'] = 'Mapping de jugadores';
$string['pleasewait'] = 'Espere por favor...';
$string['pluginispaused'] = 'El plugin está en pausa actualmente.';
$string['pluginname'] = 'Motrain';
$string['pluginnotenabledseesettings'] = 'El plugin no está habilitado, consulte su configuración.';
$string['privacy:metadata:coinsgained'] = 'Información enviada al otorgar monedas.';
$string['privacy:metadata:coinsgained:coins'] = 'El número de monedas para otorgar';
$string['privacy:metadata:coinsgained:reason'] = 'El motivo del premio';
$string['privacy:metadata:log'] = 'Registro de monedas';
$string['privacy:metadata:log:actionhash'] = 'El hash de la acción';
$string['privacy:metadata:log:actionname'] = 'El nombre de acción';
$string['privacy:metadata:log:broadcasterror'] = 'El error para transmisiones fallidas';
$string['privacy:metadata:log:coins'] = 'El número de monedas';
$string['privacy:metadata:log:contextid'] = 'La identificación de contexto';
$string['privacy:metadata:log:timebroadcasted'] = 'El momento en que se transmitió el registro';
$string['privacy:metadata:log:timecreated'] = 'El momento en que se creó el registro';
$string['privacy:metadata:log:userid'] = 'El ID de usuario';
$string['privacy:metadata:playermap'] = 'Mapping de usuarios locales y jugadores de Motrain';
$string['privacy:metadata:playermap:accountid'] = 'La ID de cuenta de Motrain';
$string['privacy:metadata:playermap:blocked'] = 'Si este mapping está bloqueado';
$string['privacy:metadata:playermap:blockedreason'] = 'La razón por la que se bloquea';
$string['privacy:metadata:playermap:playerid'] = 'La identificación del jugador de Motrain';
$string['privacy:metadata:playermap:userid'] = 'El ID de usuario';
$string['privacy:metadata:remoteplayer'] = 'Información intercambiada al crear o identificar un jugador de Motrain.';
$string['privacy:metadata:remoteplayer:email'] = 'Correo electrónico';
$string['privacy:metadata:remoteplayer:firstname'] = 'Nombre';
$string['privacy:metadata:remoteplayer:lastname'] = 'Apellido';
$string['privacy:metadata:userspush'] = 'Una cola de usuarios para ser añadidos al tablero de Motrain.';
$string['privacy:metadata:userspush:userid'] = 'El ID de usuario';
$string['privacy:path:logs'] = 'Registros';
$string['privacy:path:mappings'] = 'Mappings';
$string['purchases'] = 'Mis compras';
$string['purgecache'] = 'Purgar caché';
$string['reallydeleteassociation'] = '¿De verdad quieres eliminar la asociación?';
$string['saverules'] = 'Guardar reglas';
$string['saving'] = 'Guardando...';
$string['setup'] = 'Configuración';
$string['store'] = 'Tienda';
$string['taskpushusers'] = 'Empujar a los usuarios al tablero';
$string['team'] = 'Equipo';
$string['team_help'] = 'El equipo de Motrain con el que se asociará la cohorte.';
$string['teamassociationcreated'] = 'Asociación de equipo creada.';
$string['teamassociations'] = 'Asociaciones de equipo';
$string['teams'] = 'Equipos';
$string['unknownactivityn'] = 'Actividad desconocida {$a}';
$string['unknowncoursen'] = 'Curso desconocido {$a}';
$string['usecohorts'] = 'Use cohortes';
$string['usecohorts_help'] = 'Cuando está habilitado, los usuarios pueden organizarse en diferentes equipos utilizando cohortes. Tenga en cuenta que cuando no se usan cohortes, todos los usuarios de Moodle se considerarán como jugadores potenciales.';
$string['userecommended'] = 'Use recomendado';
$string['usernotplayer'] = 'El usuario no cumple con los criterios para ser un jugador.';
$string['userteamnotfound'] = 'No se pudo encontrar el equipo del usuario actual, comuníquese con el administrador.';
