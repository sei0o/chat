<?php
	//アプリの名前
	namespace ChatApp;
	require __DIR__.'/../vendor/autoload.php';
	//こんなのつかいますよ (includeのclass版)
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	
	class Chat implements MessageComponentInterface{
		protected $clients;
		protected $clientinfo;
		
		public function __construct(){
			$this->clients = new \SplObjectStorage;
			$this->clientinfo = array();
		}
		public function onOpen(ConnectionInterface $conn){
			//接続をclientsにhokann
			$this->clients->attach($conn);
			$this->clientinfo[$conn->resourceId] = array("name" => "Guest","role" => "NORMAL");
			$newinfo = $this->clientinfo[$conn->resourceId];
			
			//id を　通知
			$conn->send(json_encode(array("type"=>"CLIENT","id"=>$conn->resourceId,"clients" => $this->clientinfo)));
			echo 'yaaaaaaaaaaaa';
			foreach($this->clients as $client){
				if($conn != $client){//newクライアント以外のものに投げる
					$data = array("type" => "READY","id"=>$conn->resourceId,"info" => $this->clientinfo[$conn->resourceId]);
					$client->send(json_encode($data));
				}
			}
			
			echo "New Connection! {$conn->resourceId}\n";
		}
		public function onClose(ConnectionInterface $conn){
			//接続を削除してメッセージを送れないようにする
			$this->clients->detach($conn);
			unset($this->clientinfo[$conn->resourceId]);
			
			foreach($this->clients as $client){
				if($conn != $client){//newクライアント以外のものに投げる
					$data = array("type" => "EXIT","id" => $conn->resourceId);
					$client->send(json_encode($data));
				}
			}
			
			echo "Connection {$conn->resourceId} has disconnected...\n";
		}
		public function onMessage(ConnectionInterface $from, $message){
			//送信通知 
			$tocount = count($this->clients) - 1;//送り主を除外
			echo sprintf("%d >>> %d Client  Message: %s"."\n", $from->resourceId, $tocount, $message);
			
			$data = json_decode($message,true);
			$frinfo = $this->clientinfo[$from->resourceId];
			if($data['type'] == "CHANGE"){
				if($data['target'] == "NAME"){
					$frinfo["name"] = $data['body'];
				}
			}else if($data['type'] == "MESSAGE"){
				//ユニキャストまたはブロードキャスト
			}
			
			$data['id'] = $from->resourceId;
			$this->clientinfo[$from->resourceId] = $frinfo;
			
			foreach($this->clients as $client){
				if($from != $client){
					$client->send(json_encode($data));
					echo 'ppppppppppppppppppppppp';
				}
			}
		}
		public function onError(ConnectionInterface $conn, \Exception $e){
			//errorが起こったら
			$conn->close();
			
			echo "ERROR! : {$e->getMessage()}\n";
			echo "ﾌﾟｷﾞｬｰ";
		}
	}
	
?>