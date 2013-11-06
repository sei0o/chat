<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>CHAT</title>
		<link rel="stylesheet" type="text/css" href="index.css">
		<link href='http://fonts.googleapis.com/css?family=Ubuntu:400,700|Oswald:400,700' rel='stylesheet' type='text/css'>
		<script type="text/javascript">
			conn = new WebSocket('ws://sei0o.dip.jp:3939');
			name = "Guest";
			myid = 0;
			clients = {};
			
			window.onload = function(){
				chatdiv = document.getElementById('mes');
				clients = {};
				name = "Guest";
			}
			//コネクションがオープン(通信を開始したら)
			conn.onopen = function(e){
				console.log(e);
			}
			//コネクションが閉じたら
			conn.onclose = function(e){
				console.log("落ちたよー");
				location.reload();
			}
			//メッセージが届いたら
			conn.onmessage = function(mes){
				var data = JSON.parse(mes.data);
				console.log(data);
				console.log(clients);
				if (data['type'] == "CHANGE") {
					if (data['target'] == "NAME") {
						//クライアント表を改変
						var from = clients[data['id']];
						chatdiv.innerHTML = 'CHANGE_NAME:' + from['name'] + ">>>" + data['body'] + "<br>\n" + chatdiv.innerHTML;
						from['name'] = data['body'];
						clients[data['id']] = from;
						namereload();
					}
				}else if (data['type'] == "MESSAGE"){//メッセージを受信
					var from = clients[data['id']];
					var mesu = '<article><header class="name">' + from['name'] + '</header>' + nl2br(data['body']) + '</article>';
					chatdiv.innerHTML = mesu + chatdiv.innerHTML;
				}else if (data['type'] == "READY") {//newclientがきたとき
					//クライアント表を改変
					clients[data['id']] = data['info'];
					namereload();
				}else if (data['type'] == "CLIENT") {//open時に送られる
					//クライアント表を作成(自分が新しいクライアント)
					clients = data['clients'];
					myid = data['id'];
					//デバッグとして 自分のinfoを生で表示
					console.log("GUUUUUUUUUUUUUUUUUUUUUUU\t"+clients[myid]);
					namereload();
				}else if (data['type'] == "EXIT") {//誰かが抜けたら
					//クライアンと表を改変
					delete clients[data['id']];
					namereload();
				}
				
				//chatdiv.innerHTML = mes.data + "<br>\n" +chatdiv.innerHTML;
			}
			//エラー処理
			conn.onerror = function(e){
				console.log("エラーだよ、全員解散！");
				conn.close();
				location.reload();
			}
			//送る
			function send(text) {
				if(typeof text === 'undefined') text = document.getElementById('body').value;
				var data = {};
				data["type"] = "MESSAGE";
				data["target"] = "ALL";
				data["body"] = nl2br(text);
				
				conn.send(JSON.stringify(data));//JSONを送信
				chatdiv.innerHTML = '<article><header class="name">' + name + '</header>' + nl2br(text) + '</article>' + chatdiv.innerHTML;
			}
			function namechange(){
				var data = {};
				data["type"] = "CHANGE";
				data["target"] = "NAME";
				data["body"] = document.getElementById('name').value;
				
				conn.send(JSON.stringify(data));//JSONを送
				chatdiv.innerHTML = 'CHANGE_NAME:' + name + ">>>" + document.getElementById('name').value + "<br>\n" + chatdiv.innerHTML;
				name = document.getElementById('name').value;
				
				//クライアント表を改変
				//var from = clients[myid];
				//from['name'] = document.getElementById('name').value;
				clients[myid].name = document.getElementById('name').value;
				namereload();
			}
			
			function namereload() {
				var namediv = document.getElementById('client');
				var names = "";
				namediv.innerHTML = "";
				for (id in clients) {//idを全部取得するまで直す
					//名前を順次表示
					var cname = clients[id];
					if (myid == id) {
						names += "<span class='me'>" + cname['name'] + "</span><br>\n";	
					}else{
						names += cname['name'] + "</span><br>\n";
					}
				}
				namediv.innerHTML = names;
			}
			function nl2br(str) {
				return str.replace(/[\n\r]/g, "<br>");
			}
		</script>
	</head>
	<body>
		<header>
			<h1 style="font-weight: bold;">CHAT</h1>
		</header>
		<div id="cont">
			<div id="chat">
				<div id="sendform">
					<textarea id="body"></textarea>
					<button onclick="send();">SEND</button>
				</div>
				<div id="mes"> </div>
			</div>
			<div id="clients">
				<button onclick=""></button>
				<div id="joinform">
					<input type="text" id="name">
					<button onclick="namechange();">CHANGE</button>
				</div>
				<div id="client"> </div>
			</div>
		</div>
	</body>
</html>