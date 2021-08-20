/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @author vitoalvaro
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Ajax Chat language Object:
var ajaxChatLang = {

	login: '%s acaba de entrar no Chat.',
	logout: '%s acaba de sair no Chat.',
	logoutTimeout: '%s desconectou (Tempo esgotado).',
	logoutIP: '%s desconectou (Endereço de IP inválido).',
	logoutKicked: '%s desconectou (Expulso do canal).',
	channelEnter: '%s entrou no canal.',
	channelLeave: '%s saiu do canal.',
	privmsg: '(sussurra)',
	privmsgto: '(Sussurrar para %s)',
	invite: '%s convida você para participar %s.',
	inviteto: 'O seu convite para se juntar ao %s do canal %s foi enviado.',
	uninvite: '%s desconvidou você do canal %s.',
	uninviteto: 'Seu desconvidamento %s do canal %s foi enviada.',	
	queryOpen: 'Canal privado aberto para %s.',
	queryClose: 'Canal privado para %s fechado.',
	ignoreAdded: 'Adicionar %s para a lista do igrnorado.',
	ignoreRemoved: 'Remover %s da lista do ignorado.',
	ignoreList: 'Usuários ignorados:',
	ignoreListEmpty: 'Nenhuma lista usuários ignorados.',
	who: 'Usuários Online:',
	whoChannel: 'Usuários online no canal %s:',
	whoEmpty: 'Nenhum usuários online na determinado canal.',
	list: 'Os canais disponíveis:',
	bans: 'Usuários banidos:',
	bansEmpty: 'Nenhuma lista usuários banidos.',
	unban: 'Usuário de banido %s revogado.',
	whois: 'Usuário %s - enderenço IP:',
	whereis: 'Usuário %s está no canal %s.',
	roll: '%s rolas %s e começa %s.',
	nick: '%s é agora conhecido como %s.',
	toggleUserMenu: 'Alternar usuário menu de %s',
	userMenuLogout: 'Deslogar',
	userMenuWho: 'Lista usuários online',
	userMenuList: 'Lista os canais disponíveis',
	userMenuAction: 'Descreva ação',
	userMenuRoll: 'Jogar dados',
	userMenuNick: 'Trocar usuário',
	userMenuEnterPrivateRoom: 'Entrar sala privada',
	userMenuSendPrivateMessage: 'Enviar mensagem privada',
	userMenuDescribe: 'Enviar ação privada',
	userMenuOpenPrivateChannel: 'Abrir canal privada',
	userMenuClosePrivateChannel: 'Fechar canal privada',
	userMenuInvite: 'Convidar',
	userMenuUninvite: 'Desconvidar',
	userMenuIgnore: 'Ignorar/Aceitar',
	userMenuIgnoreList: 'Lista usuários banidos',
	userMenuWhereis: 'Exibir canais',
	userMenuKick: 'Kickar/Banir',
	userMenuBans: 'Lista usuários banidos',
	userMenuWhois: 'Exibir IP',
	unbanUser: 'Revogar usuário de banido %s',
	joinChannel: 'Entrar canal %s',
	cite: '%s cita:',
	urlDialog: 'Digite o endereço (URL) da página:',
	deleteMessage: 'Excluir esta mensagem',
	deleteMessageConfirm: 'Realmente apagar a mensagem selecionada chat?',
	errorCookiesRequired: 'Os cookies são requeridas para este Chat.',
	errorUserNameNotFound: 'Erro: Usuário %s não encontrado.',
	errorMissingText: 'Erro: Faltando mensagem texto.',
	errorMissingUserName: 'Erro: Usuário faltando.',
	errorInvalidUserName: 'Erro: Usuário inválido.',
	errorUserNameInUse: 'Erro: Usuário já em uso.',
	errorMissingChannelName: 'Erro: Faltando nome do canal.',
	errorInvalidChannelName: 'Erro: Inválido nome do canal: %s',
	errorPrivateMessageNotAllowed: 'Erro: Mensagens privadas não são permitidos.',
	errorInviteNotAllowed: 'Erro: Você não tem permissão para convidar alguém para este canal.',
	errorUninviteNotAllowed: 'Erro: Você não está autorizado a desconvidar alguém deste canal.',
	errorNoOpenQuery: 'Erro: Nenhum canal privado aberto.',
	errorKickNotAllowed: 'Erro: Você não está autorizado a kickar %s.',
	errorCommandNotAllowed: 'Erro: Comando não permitido: %s',
	errorUnknownCommand: 'Erro: Comando desconhecido: %s',
	errorMaxMessageRate: 'Erro: Você excedeu o número máximo de mensagens por minuto.',
	errorConnectionTimeout: 'Erro: Intervalo de parada da conexão. Por favor, tente novamente..',
	errorConnectionStatus: 'Erro: Status da conexão: %s',
	errorSoundIO: 'Erro: Falha ao carregar som arquivo (Flash IO Error).',
	errorSocketIO: 'Erro: Conexão para socket servidor falhou (Flash IO Error).',
	errorSocketSecurity: 'Erro: Conexão para socket servidor falhou (Flash Security Error).',
	errorDOMSyntax: 'Erro: Inválido DOM Syntax (DOM ID: %s).'

}