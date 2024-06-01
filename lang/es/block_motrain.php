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
$string['accountidmismatch'] = 'Los ID de las cuentas no coinciden.';
$string['addactivityellipsis'] = 'Agregar actividad...';
$string['addcourseellipsis'] = 'Agregar curso...';
$string['addon'] = 'Complemento';
$string['addons'] = 'Complementos';
$string['addonstate'] = 'Estado';
$string['addonstate_desc'] = 'El estado del complemento. Algunos complementos requieren que se configuren algunas configuraciones antes de poderlos habilitar o que se habiliten solos.';
$string['addprogramellipsis'] = 'Agregar programa...';
$string['adminscanearn'] = 'Los administradores pueden ganar monedas';
$string['adminscanearn_desc'] = 'Cuando está habilitado, esto permite a los administradores ganar monedas.';
$string['apihost'] = 'Punto final de la API';
$string['apihost_desc'] = 'La URL en la que se encuentra la API.';
$string['apikey'] = 'Clave API';
$string['apikey_desc'] = 'La clave API para autenticar con la API.';
$string['areyousurerefreshallinteam'] = '¿Estás seguro de que quieres actualizar a todos los jugadores del equipo? Esto eliminará todas las asignaciones.';
$string['areyousurerefreshandpushallinteam'] = '¿Estás seguro de que quieres actualizar a todos los jugadores del equipo? Esto eliminará todas las asignaciones y enviará a todos los usuarios al panel de control.';
$string['autopush'] = 'Añadir automáticamente a los usuarios';
$string['autopush_help'] = 'Cuando está habilitado, los usuarios se añadiran automáticamente como jugadores en Motrain Dashboard, incluso antes de que comiencen a ganar monedas. Este proceso se realiza cuando pasa el cron, por lo que los jugadores pueden tardar varios minutos en aparecer en el tablero. Tenga en cuenta que esto no se aplica a las asociaciones existentes del equipo de cohorte, solo nuevos miembros y nuevas asociaciones.';
$string['blocked'] = 'Bloqueado';
$string['butpaused'] = 'Pero se detuvo';
$string['cachedef_coins'] = 'Monedas de usuario';
$string['cachedef_comprules'] = 'Reglas de finalización';
$string['cachedef_metadata'] = 'Metadatos';
$string['cachedef_programrules'] = 'Reglas del programa';
$string['cachedef_purchasemetadata'] = 'Metadatos de compras';
$string['cachepurged'] = 'Caché purgado';
$string['cohort'] = 'Cohorte';
$string['cohort_help'] = 'La cohorte para asociarse con el equipo, o "todos los usuarios" cuando no se requiere una cohorte específica.';
$string['coinrules'] = 'Reglas de moneda';
$string['coins'] = 'Monedas';
$string['coinsimage'] = 'Imagen de monedas';
$string['coinsimage_help'] = 'Use esta configuración para usar una imagen alternativa que represente las monedas que se muestran en el bloque.';
$string['completingacourse'] = 'Completando un curso';
$string['completingaprogram'] = 'Completando un programa';
$string['completingn'] = 'Completando {$a}';
$string['configaccentcolor'] = 'Color de acento';
$string['configaccentcolor_help'] = 'El color de acento que se utilizará en el bloque; acepta cualquier valor CSS.';
$string['configbgcolor'] = 'Color de fondo';
$string['configbgcolor_help'] = 'El color de fondo principal que se utilizará en el bloque; acepta cualquier valor CSS.';
$string['configfootercontent'] = 'Contenido de pie de página';
$string['configfootercontent_help'] = 'El contenido para mostrar en la parte inferior del bloque.';
$string['configtitle'] = 'Título';
$string['connect'] = 'Conectar';
$string['courseandactivitycompletion'] = 'Finalización del curso y de la actividad.';
$string['coursecompletion'] = 'Finalización del curso';
$string['createassociation'] = 'Crear asociación';
$string['dashboard'] = 'Dashboard';
$string['defaultparens'] = '(defecto)';
$string['defaulttitle'] = 'Motrain';
$string['disabled'] = 'Desactivado';
$string['disconnect'] = 'Desconectar';
$string['editassociation'] = 'Editar asociación';
$string['emailtemplate'] = 'Plantilla de correo electrónico';
$string['enableaddon'] = 'Habilitar complemento';
$string['enableaddon_help'] = 'Se debe habilitar un complemento para funcionar.';
$string['enabled'] = 'Activado';
$string['errorconnectingwebhookslocalnotificationsdisabled'] = 'Se ha producido un error al conectar los webhooks. Se ha desactivado el envío de notificaciones locales.';
$string['errorwhiledisconnectingwebhook'] = 'Se ha producido un error al desconectar el webhook: {$a}.';
$string['eventcoinsearned'] = 'Monedas ganadas';
$string['globalsettings'] = 'Configuración global';
$string['infopagetitle'] = 'Información';
$string['inspect'] = 'Inspeccionar';
$string['inspectuser'] = 'inspeccionar usuario';
$string['invalidcoinamount'] = 'Cantidad no válida de monedas';
$string['isenabled'] = 'Plugin habilitado';
$string['isenabled_desc'] = 'Para habilitar el complemento, complete la configuración a continuación con la información correcta. Mientras los detalles de la API sean correctos, el complemento se habilitará.';
$string['ispaused'] = 'Pausar el plugin';
$string['ispaused_help'] = 'Cuando se detenga, el plugin no enviará información al tablero de Motrain. No se otorgarán monedas, y los jugadores no serán creados. Además, los usuarios no pueden ver el bloque y no pueden acceder a la tienda. Es posible que desee marcar el plugin en pausa hasta que se haya configurado completamente (reglas, asignaciones) o durante una migración de cuenta.';
$string['lastwebhooktime'] = 'Último webhook recibido: {$a}';
$string['leaderboard'] = 'Tabla de clasificación';
$string['level'] = 'Nivel';
$string['leveln'] = 'Nivel {$a}';
$string['localteammgmt'] = 'Gestión de equipos locales';
$string['localteammgmt_help'] = 'Cuando se habilita, el equipo de un jugador será dictado por la configuración en Moodle y reflejado en el tablero de Motrain. Esto permite que los equipos se administren localmente. Tenga en cuenta que, en algunas circunstancias, cuando los jugadores migran a un equipo diferente, pueden perder su posición dentro del ranking de clasificación.';
$string['manageaddons'] = 'Administrar complementos';
$string['maximumlevel'] = 'Nivel máximo';
$string['messageprovider:notification'] = 'Notificaciones con relación a Motrain';
$string['messagetemplates'] = 'Plantillas de mensajes';
$string['messagetemplatesintro'] = 'Las siguientes plantillas se utilizan cuando se envían notificaciones a los jugadores desde este sitio. El sistema elegirá el idioma de la plantilla que mejor coincida con el idioma del destinatario o utilizará la plantilla alternativa cuando ninguna encaje.';
$string['metadatacache'] = 'Caché de metadatos';
$string['metadatacache_help'] = 'El plugin mantiene un caché de algunos metadatos de la API para mejorar el rendimiento. Almacena información, como qué tablas de clasificación están habilitadas, etc. Después de hacer algunos cambios en el tablero de Motrain, es posible que deba purgar manualmente este caché. Puede hacerlo haciendo clic en el enlace de arriba.';
$string['metadatasyncdisabled'] = 'La sincronización de metadatos está deshabilitada.';
$string['motrain:accessdashboard'] = 'Acceder al tablero Motrain';
$string['motrain:addinstance'] = 'Agregar un nuevo bloque de Motrain';
$string['motrain:awardcoins'] = 'Añadir monedas a otros usuarios';
$string['motrain:earncoins'] = 'Ganar monedas';
$string['motrain:manage'] = 'Gestionar aspectos de la integración de Motrain.';
$string['motrain:myaddinstance'] = 'Agregar el bloque Motrain en el tablero';
$string['motrain:view'] = 'Ver el contenido del bloque Motrain';
$string['motrainaddons'] = 'Complementos de Motrain';
$string['motrainemaillookup'] = 'Motrain (búsqueda de correo electrónico)';
$string['motrainidlookup'] = 'Motrain (búsqueda de ID)';
$string['multiteamsusers'] = 'Usuarios de equipos múltiples';
$string['nextlevelin'] = 'Siguiente nivel en';
$string['noaddoninstalled'] = 'Todavía no se han instalado complementos.';
$string['nocohortallusers'] = 'Todos los usuarios';
$string['nooptions'] = 'Sin opciones';
$string['noredemptionessagefound'] = '[No se encontró información]';
$string['noteamsyetcreatefirst'] = 'Todavía no existe una asociación de equipo, comience por crear una.';
$string['notenabled'] = 'El plugin no está habilitado, comuníquese con su administrador.';
$string['notfound'] = 'No se ha encontrado';
$string['pendingorders'] = 'Órdenes pendientes';
$string['placeholdercoins'] = 'El número de monedas asociadas.';
$string['placeholderitemname'] = 'El nombre del elemento asociado.';
$string['placeholderitemnameexample'] = 'Nombre del elemento ficticio';
$string['placeholderitems'] = 'Los nombres (y cantidad) de los elementos asociados.';
$string['placeholderitemsexample'] = '1x nombre de elemento ficticio, 2x otro nombre de elemento';
$string['placeholdermessage'] = 'Un mensaje asociado con este evento.';
$string['placeholdermessageexample'] = 'Un mensaje asociado con este evento.';
$string['placeholderoptionalmessagefromadmin'] = 'Un mensaje opcional del administrador.';
$string['placeholdervouchercode'] = 'El código de cupón que se ha reclamado.';
$string['placeholdervouchercodeexample'] = 'ABC123';
$string['playerid'] = 'ID del jugador';
$string['playeridnotfound'] = 'No se pudo encontrar el jugador asociado con el usuario actual, comuníquese con el administrador.';
$string['playermapping'] = 'Mapping de jugadores';
$string['playermappingintro'] = 'Un mapping de jugadores es la asociación entre un usuario local y un jugador en el tablero de Motrain. Puede encontrar la lista de asignaciones conocidas a continuación. Las asignaciones con un error no se volverán a completar, solucione el problema y restablezca la asignación.';
$string['playersmapping'] = 'Mapping de jugadores';
$string['pleasewait'] = 'Espere por favor...';
$string['pluginispaused'] = 'El plugin está en pausa actualmente.';
$string['pluginname'] = 'Motrain';
$string['pluginnotenabledseesettings'] = 'El plugin no está habilitado, consulte su configuración.';
$string['previewemail'] = 'Enviar correo electrónico de vista previa a';
$string['previewemail_help'] = 'El correo electrónico al que se envía la vista previa. Este valor no se ha guardado.';
$string['previewnotsent'] = 'No se ha podido enviar el correo electrónico de vista previa.';
$string['previewsent'] = 'El correo electrónico de vista previa se ha enviado con el contenido siguiente. Tenga en cuenta que la plantilla aún no se ha guardado.';
$string['primaryteam'] = 'Equipo primario';
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
$string['programcompletion'] = 'Finalización del programa';
$string['purchases'] = 'Mis compras';
$string['purgecache'] = 'Purgar caché';
$string['reallydeleteassociation'] = '¿De verdad quieres eliminar la asociación?';
$string['refresh'] = 'Refrescar';
$string['result'] = 'Resultado';
$string['resyncnow'] = 'Resincronizar ahora';
$string['saverules'] = 'Guardar reglas';
$string['saving'] = 'Guardando...';
$string['secondaryteam'] = 'Equipo secundario {$a}';
$string['sendlocalnotifications'] = 'Enviar notificaciones locales';
$string['sendlocalnotifications_help'] = 'Cuando se habilite, Moodle enviará mensajes a los jugadores en lugar de a Motrain. Para evitar que los mensajes se envíen dos veces, debes desactivar las "Comunicaciones salientes al jugador" desde el panel de Motrain. Los mensajes enviados a los jugadores se pueden personalizar en la página de configuración de \'Plantillas de mensajes\'. Se requieren webhooks para que esto funcione.';
$string['sendlocalnotificationsdisabledrequiresenabled'] = 'El envío de notificaciones locales se ha desactivado, no se puede activar antes de que se habilite la propia extensión.';
$string['sendlocalnotificationsdisabledwithwebhooks'] = 'El envío de notificaciones locales se ha deshabilitado; requiere webhooks para funcionar.';
$string['sendlocalnotificationsnotenabledseesettings'] = 'El envío de notificaciones locales no está habilitado; revise, por favor, la configuración.';
$string['sendpreview'] = 'Enviar vista previa';
$string['setup'] = 'Configuración';
$string['sourcex'] = 'Fuente: {$a}';
$string['spend'] = 'Gastar';
$string['store'] = 'Tienda';
$string['taskpushusers'] = 'Empujar a los usuarios al tablero';
$string['team'] = 'Equipo';
$string['team_help'] = 'El equipo de Motrain con el que se asociará la cohorte.';
$string['teamassociationcreated'] = 'Asociación de equipo creada.';
$string['teamassociations'] = 'Asociaciones de equipo';
$string['teams'] = 'Equipos';
$string['templatecontent'] = 'Contenido';
$string['templatecontent_help'] = 'El contenido del mensaje a enviar.
 
 Están disponibles los siguientes marcadores de posición:
 
 - `[monedas]`: El número de monedas recibidas, si las hay.
 - `[nombre del elemento]`: El nombre del elemento asociado, si lo hay.
 - `[mensaje]`: Un mensaje asociado opcional (por ejemplo, aprobación del pedido, pedido automático), si lo hay.
 - `[código de cupón]`: El código de cupón que se reclamó, si lo hay.
 - `[firstname]`: El nombre del jugador.
 - `[apellido]`: El apellido del jugador.
 - `[fullname]`: El nombre completo del jugador.';
$string['templatedeleted'] = 'La plantilla fue eliminada.';
$string['templateenabled'] = 'Habilitado';
$string['templateenabled_help'] = 'Habilite la plantilla cuando esté lista para enviarse a los jugadores.';
$string['templateforlangalreadyexists'] = 'Ya existe una plantilla para este idioma.';
$string['templatelanguage'] = 'Idioma de la plantilla';
$string['templatelanguage_help'] = 'El idioma de la plantilla coincidirá con el idioma del destinatario previsto.';
$string['templatelanguageanyfallback'] = 'Todos los idiomas (alternativa)';
$string['templatesaved'] = 'Se ha guardado la plantilla.';
$string['templatesubject'] = 'Asunto';
$string['templatesubject_help'] = 'El asunto del mensaje a enviar.
 
 Están disponibles los siguientes marcadores de posición:
 
 - `[nombre del elemento]`: El nombre del elemento asociado, si lo hay.
 - `[firstname]`: El nombre del jugador.
 - `[apellido]`: El apellido del jugador.
 - `[fullname]`: El nombre completo del jugador.';
$string['templatetypeauctionwon'] = 'Subasta ganada';
$string['templatetypemanualaward'] = 'Jugador premiado manualmente';
$string['templatetyperafflewon'] = 'Sorteo ganado';
$string['templatetyperedemptionrequestaccepted'] = 'Solicitud de pedido aprobada';
$string['templatetyperedemptionselfcompleted'] = 'Orden automática completada';
$string['templatetyperedemptionshippingordersubmitted'] = 'Orden de envío tramitada';
$string['templatetyperedemptionvoucherclaimed'] = 'Cupón reclamado';
$string['templatetypesweepstakeswon'] = 'Sorteo ganado';
$string['templatex'] = 'Plantilla: {$a}';
$string['therearexusersinmultipleteams'] = 'Hay {$a} usuarios en varios equipos.';
$string['tickets'] = 'Entradas';
$string['unknownactivityn'] = 'Actividad desconocida {$a}';
$string['unknowncoursen'] = 'Curso desconocido {$a}';
$string['unknownprogramn'] = 'Programa desconocido {$a}';
$string['unknowntemplatecode'] = 'Código de plantilla desconocido \'{$a}\'';
$string['usecohorts'] = 'Use cohortes';
$string['usecohorts_help'] = 'Cuando está habilitado, los usuarios pueden organizarse en diferentes equipos utilizando cohortes. Tenga en cuenta que cuando no se usan cohortes, todos los usuarios de Moodle se considerarán como jugadores potenciales.';
$string['userdoesnotexist'] = 'El usuario no existe.';
$string['userecommended'] = 'Use recomendado';
$string['useridoremail'] = 'ID de usuario o correo electrónico';
$string['usernotplayer'] = 'El usuario no cumple con los criterios para ser un jugador.';
$string['userteamnotfound'] = 'No se pudo encontrar el equipo del usuario actual, comuníquese con el administrador.';
$string['viewlist'] = 'Ver lista';
$string['webhooksconnected'] = 'Webhooks conectados';
$string['webhooksconnected_help'] = 'Los webhooks se utilizan para permitir que Motrain se comunique directamente con Moodle. Por ejemplo, se utilizan para enviar notificaciones locales a los jugadores. Los webhooks no se procesan cuando la extensión no está habilitada o está en pausa.';
$string['webhooksdisconnected'] = 'Webhooks desconectados';
