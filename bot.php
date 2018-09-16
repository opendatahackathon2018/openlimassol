<?
	// t.me/keep_calm_bot
	// рега бота https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/setWebhook?url=https://www.topcyprus.net/calm/bot/bot.php
	// https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/getWebhookInfo

	include_once("/var/www/html/topcyprus.net/calm/bot/telegram/src/Telegram.php");	
	include_once("/var/www/html/topcyprus.net/calm/mysql.php");	
	
	$bot_token = '696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA';
	$telegram = new Telegram($bot_token);
	$text = $telegram->Text();
	$text = strtolower($text);
	$chat_id = $telegram->ChatID();
	$data = $telegram->getData();
	
	$user_id = $data['message']['from']['id'];
	$first_name = $data['message']['from']['first_name'];
	$reply = "";
	
	$output = json_decode(file_get_contents('php://input'), TRUE);
	$callback_query = $output['callback_query'];
	$callback_data = $callback_query['data'];
	$callback_query_json = json_encode($output);
	$message_id = $output['callback_query']['message']['message_id'];
	$callback_message = $data['message']['text'];
	$chat_id_in = $callback_query['message']['chat']['id'];
	$chat_user_id = $callback_query['from']['id'];
	$user_name = $callback_query['from']['username'];
	
	$location = $output['message']['location'];
	$location_chat_id = $output['message']['chat']['id'];
	$latitude = $location['latitude'];
	$longitude = $location['longitude'];
	
	// SaveLog("Your location is ".$latitude."/".$longitude.' '.$location_chat_id);

	if($latitude && $longitude && $location_chat_id){
		//$reply = "Your location is ".$latitude."/".$longitude;
		
		$url="http://open.mapquestapi.com/geocoding/v1/reverse?key=18Hrzfzlp0b1IIVo2yTulGqT5JAH98Zq&location=".$latitude.",".$longitude."&includeRoadMetadata=true&includeNearestIntersection=true";
		$result_json = file_get_contents($url);
		$result = json_decode($result_json, true);
		$loc = $result['results'][0]['locations'][0];
		$city = $loc['adminArea5'];
		$street = $loc['street'];
		$reply .= "Your location is ".$city;
		if($street)
			$reply .= " (".$street.")";
		
		$conn = GetConnection();
		SetUserLocation($conn, $location_chat_id, $latitude, $longitude, $city);
		mysqli_close($conn);
		
		$content = ['chat_id' => $chat_id, 'text' => $reply];
		$telegram->sendMessage($content);
	}



	if ($text == '/start') {
		$conn = GetConnection();
		//SaveLog("register"); SaveLog($user_id." ".$first_name." ".$chat_id);
		$res = RegisterBotUser($conn, $user_id, $first_name, $chat_id);
		mysqli_close($conn);
		
		//$reply .= 'Click /register for bot registration'.chr(10).chr(10).'/geo to share your geo location'.chr(10).chr(10).'/air for air condition'.chr(10).chr(10).'/report for create a report';
		$reply .= 'Click /geo to share your geo location'.chr(10).chr(10).'/air for air condition'.chr(10).chr(10).'/report for create a report';

		$content = ['chat_id' => $chat_id, 'text' => $reply];
		$telegram->sendMessage($content);
	}
	
	if ($text == '/register') {				
		$conn = GetConnection();
		if($conn){
			//SaveLog("register"); SaveLog($user_id." ".$first_name." ".$chat_id);
			$res = RegisterBotUser($conn, $user_id, $first_name, $chat_id);
			if($res)
				$reply .= 'Registration complete';
			else
				$reply .= 'Registration Error';
		}
		
		$content = ['chat_id' => $chat_id, 'text' => $reply];
		$telegram->sendMessage($content);
		mysqli_close($conn);
	}
	
	if ($text == '/report') {
		$replyMarkupReport =json_encode([
			'keyboard' =>[[[
				'text'=>'Air Pollutant',
				"callback_data"=>'/report_air'
			],
			[
				'text'=>'Fire',
				"callback_data"=>'/report_fire'
			],
			[
				'text'=>'Road Accident',
				"callback_data"=>'/report_accident'
			]
			]],
			'resize_keyboard'=>true,
			'one_time_keyboard'=>true,
		]);
		
		$reply = "Please select report type";

		$url = 'https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($reply) . '&reply_markup=' . $replyMarkupReport;
		
		$result = file_get_contents($url);
	}
	
	
	
	if($callback_message == "Air Pollutant"){
		$conn = GetConnection($conn);
		$res = GetUserCity($conn, $location_chat_id);
		if($res['city']){
			if (CreateReport($conn, $location_chat_id, $res['city'], $res['latitude'], $res['longitude'], "Air Pollutant"))
				$reply .= 'Thank you!';
			else
				$reply .= 'Please try again';
		}
		else
			$reply .= 'Please share your location first /geo';
			
		mysqli_close($conn);
		
		
		

		$content = ['chat_id' => $chat_id, 'text' => $reply];
		$telegram->sendMessage($content);
		
	}	
	if($callback_message == "Fire"){
		$conn = GetConnection($conn);
		$res = GetUserCity($conn, $location_chat_id);
		if($res['city']){
			if (CreateReport($conn, $location_chat_id, $res['city'], $res['latitude'], $res['longitude'], "Fire"))
				$reply .= 'Thank you!';
			else
				$reply .= 'Please try again';
		}
		else
			$reply .= 'Please share your location first /geo';
			
		mysqli_close($conn);

		$content = ['chat_id' => $chat_id, 'text' => $reply];
		$telegram->sendMessage($content);
		
	}	
	if($callback_message == "Road Accident"){
		$conn = GetConnection($conn);
		$res = GetUserCity($conn, $location_chat_id);
		if($res['city']){
			if (CreateReport($conn, $location_chat_id, $res['city'], $res['latitude'], $res['longitude'], "Road Accident"))
				$reply .= 'Thank you!';
			else
				$reply .= 'Please try again';
		}
		else
			$reply .= 'Please share your location first /geo';
			
		mysqli_close($conn);

		$content = ['chat_id' => $chat_id, 'text' => $reply];
		$telegram->sendMessage($content);
		
	}	
	
	if ($text == '/geo') {
		$reply .= 'Please share your location';

		$replyMarkupGEO =json_encode([
			'keyboard' =>[[[
				'text'=>'Share you GEO location',
				'request_location'=>true,
			]]],
			'resize_keyboard'=>true,
			'one_time_keyboard'=>true,
		]);

		$url = 'https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($reply) . '&reply_markup=' . $replyMarkupGEO;
		
		$result = file_get_contents($url);
		
	
		$res_array = json_decode($result, true);

		$message_id = intval($res_array['result']['message_id']);
		//var_dump ($message_id);

		//$content = ['chat_id' => $chat_id, 'text' => $reply];
		//$telegram->sendMessage($content);
	}
	
	if ($text == '/air') {		
		include_once("/var/www/html/topcyprus.net/calm/bot/data_func.php");	
		$conn = GetConnection($conn);
		$res = GetUserCity($conn, $location_chat_id);
		$city = $res['city'];
		mysqli_close($conn);
		
		if($city){
			$air_stats = GetAirStats($city);
			$co = intval($air_stats[6]);
			if($co){
				$reply .= chr(10)."Carbon Monoxide (CO): ".$co;
				if($co < 5000)
					$reply .= " (Good)";
				elseif($co < 10000)
					$reply .= " (Modrate)";
				elseif($co < 17000)
					$reply .= " (Unhealty)";
				elseif($co < 34000)
					$reply .= " (Very Unhealty)";
				else
					$reply .= " (Hazardous)";
			}
			
			$pm10 = intval($air_stats[25]);
			if($pm10){
				$reply .= chr(10)."Particulate Matter (PM10): ".$pm10;
				if($pm10 < 50)
					$reply .= " (Good)";
				elseif($pm10 < 150)
					$reply .= " (Modrate)";
				elseif($pm10 < 350)
					$reply .= " (Unhealty)";
				elseif($pm10 < 420)
					$reply .= " (Very Unhealty)";
				else
					$reply .= " (Hazardous)";
			}
			
			$pm25 = intval($air_stats[26]);
			if($pm25){
				$reply .= chr(10)."Particulate Matter (PM25): ".$pm25;
				if($pm25 < 12)
					$reply .= " (Good)";
				elseif($pm25 < 55)
					$reply .= " (Modrate)";
				elseif($pm25 < 150)
					$reply .= " (Unhealty)";
				elseif($pm25 < 250)
					$reply .= " (Very Unhealty)";
				else
					$reply .= " (Hazardous)";
			}		
			
			$so = intval($air_stats[4]);
			if($so){
				$reply .= chr(10)."Sulphur dioxide (SO2): ".$so;
				if($so < 80)
					$reply .= " (Good)";
				elseif($so < 365)
					$reply .= " (Modrate)";
				elseif($so < 800)
					$reply .= " (Unhealty)";
				elseif($so < 1600)
					$reply .= " (Very Unhealty)";
				else
					$reply .= " (Hazardous)";
			}
			
			$o3 = intval($air_stats[5]);
			if($o3){
				$reply .= chr(10)."Ozone (O3): ".$o3;
				if($o3 < 118)
					$reply .= " (Good)";
				elseif($o3 < 157)
					$reply .= " (Modrate)";
				elseif($o3 < 235)
					$reply .= " (Unhealty)";
				elseif($o3 < 785)
					$reply .= " (Very Unhealty)";
				else
					$reply .= " (Hazardous)";
			}
			
			$no2 = intval($air_stats[2]);
			if($no2){
				$reply .= chr(10)."Nitrogen dioxide (NO2): ".$no2;
				if($no2 < 1130)
					$reply .= " (Good)";
				elseif($no2 < 2260)
					$reply .= " (Very Unhealty)";
				else
					$reply .= " (Hazardous)";
			}
		}
		else
			$reply = "Please share your location first /geo";

		//SaveLog(json_encode($loc));
		
		$url = 'https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/sendMessage?chat_id=' . $location_chat_id . '&text=' . urlencode($reply);
		
		$result = file_get_contents($url);
		$res_array = json_decode($result, true);

		$message_id = intval($res_array['result']['message_id']);
	}
	
	function SaveLog($message){
		if(NeedToSaveLog()){
			$date = date("Y-m-d H:i:s");
			file_put_contents("/var/www/html/topcyprus.net/calm/bot/log_test.txt", 
			"DATE ".$date.": ".$message."\n\r", FILE_APPEND | LOCK_EX);
		}
	}	
	function NeedToSaveLog(){
		return true;
	}
?>
