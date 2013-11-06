<?php
	require __DIR__.'/../vendor/autoload.php';
	use Ratchet\Server\IoServer;
	use Ratchet\WebSocket\WsServer;
	
	require 'chat.php';
	use ChatApp\Chat;
	
	$apserver = IoServer::factory(new WsServer(new Chat()),3939);
	$apserver->run();
?>