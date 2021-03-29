<?php

/**

Mr. Id - v1.4
Author: Sleiman A.
Desc: A simple ticket-id assignment automation telegram bot.
-- Made for productivity & fun!

**/

	define ('url', T_CALLBACK);
	define ('ext', P_ENDPOINT);

	$update = json_decode(file_get_contents('php://input') ,true);

	// REPRELIBILITA FUNCTION (Excel Macro)
	
	if ($update['op'] == 99){
		$rep = $update['message'];
		$chat_id = $update['cid'];
		file_get_contents(url."sendmessage?chat_id=".$chat_id."&text=".$rep);
		logger($update);
		exit();
	}


	// ticket-id assignment

	$chat_id = $update['message']['chat']['id'];
	$name = $update['message']['from']['first_name'];
	$msg_rcvd = $name = $update['message']['text'];


	$msg = explode(" ", $msg_rcvd);
	$last_id = getLastID($chat_id);
	$last_id = str_pad($last_id, 5,'0',STR_PAD_LEFT);
	$free_id = $last_id + 1;
	$free_id= str_pad($free_id, 5,'0',STR_PAD_LEFT);


	logger($update);



	if($msg[0] == '/last'  or $msg[0]=='/last@IDPLZ_BOT'){
		
		$rep = "*L'ultime 10 prenotazioni*:".PHP_EOL.PHP_EOL;
		$rep = $rep.tailHistory(10, $chat_id);
		send($rep, $chat_id);
		
	}

	if($msg[0] == '/rep'  or $msg[0]=='/rep@IDPLZ_BOT'){
		
		file_get_contents(ext."rep?cid=".$chat_id);
		
	}

	if($msg[0] == '/set'  or $msg[0]=='/set@IDPLZ_BOT'){
		
		$id = $msg[1];
		$emoPublic = "\xF0\x9F\x93\xA2";
		storeID($id, $chat_id);
		$rep = $emoPublic.$update['message']['from']['first_name']." has reset last used id to ".$id;
		send($rep, $chat_id);
		$free = $id+1;
		$rep = "L'id *".$free."* è disponibile. Premi /get per prendere";
		send($rep, $chat_id);
		
	}

	if($msg[0] == '/get' or $msg[0]=='/get@IDPLZ_BOT'){

		$emoCheck = "\xE2\x9C\x85";
		$emoSonoIo = "\xE2\x98\x9D";
		
		$rep = $update['message']['from']['first_name']." ha preso l'id *".giveID($update['message']['from']['first_name'], $chat_id)."* ".$emoCheck;
		send($rep, $chat_id);
	}

	if ($msg[0] == '/id'  or $msg[0]=='/id@IDPLZ_BOT'){
		
			$k = array(
			"text" => "Take It",
			"callback_data" => "TK 0x0F1E7C"
		);
			$keyboard = array(
			"inline_keyboard" => array($k)
		);
		
		$rep = "L'id *".$free_id."* è disponibile. Premi /get per prendere";
		send($rep, $chat_id);

	}

	function send($txt, $chat_id){
		
		$txt = urlencode($txt);
		file_get_contents(url."sendmessage?chat_id=".$chat_id."&text=".$txt."&parse_mode=Markdown");
		
	}

	function storeID($txt, $chat_id) {
		
		$chat_id= str_replace(["-", "–"], '', $chat_id);

		$myfile = fopen($chat_id.".txt", "w") or die('Unable to open file!');
		fwrite($myfile, "\n". $txt);
		fclose($myfile);
		
	}

	function getLastID($chat_id){
		
		$chat_id= str_replace(["-", "–"], '', $chat_id);
		$file = ($chat_id.".txt" );
		$last_line = `tail -n 1 $file`;
		return  $last_line;
		
	}

	function giveID($user, $chat_id){
		
		$last_id = getLastID($chat_id);
		$free_id = $last_id + 1;
		$free_id = str_pad($free_id, 5,'0',STR_PAD_LEFT);
		storeID($free_id, $chat_id);
		appendHistory($user, $free_id, $chat_id);
		return $free_id;
		
	}

	function appendHistory($user, $tid, $chat_id){
		
		date_default_timezone_set('Europe/Rome');
		$chat_id= str_replace(["-", "–"], '', $chat_id);
		$fp = fopen($chat_id.'_history.txt', 'a');
		fwrite($fp, date("d/M H:i:s").' - '.$tid.' - '.$user.PHP_EOL);
		fclose($fp);
		
	}

	function tailHistory($num, $chat_id){
		
		$chat_id= str_replace(["-", "–"], '', $chat_id);
		$file = ($chat_id.'_history.txt' );
		$last_lines = `tail -n $num $file`;
		return $last_lines;
		
	}

	function logger($update){

		$myFile = "log.txt";
		$updateArray = print_r($update,TRUE);
		$fh = fopen($myFile, 'a') or die("can't open file");
		fwrite($fh, date("Y-m-d H:i:s").' - '.PHP_EOL.$updateArray.PHP_EOL.PHP_EOL);
		fclose($fh);
		
	}

?>
