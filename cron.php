<?
	include_once("/var/www/html/topcyprus.net/calm/mysql.php");	
	include_once("/var/www/html/topcyprus.net/calm/bot/data_func.php");	
	
	
	$reply = "";
	$conn = GetConnection();
	$users = getUsers($conn);
	
	$user_reports = GetReports($conn);
	
	foreach ($user_reports as $report){
		$report_id = $report['id'];
		$report_type = $report['report_type'];
		$city = $report['city'];
		$latitude = $report['latitude'];
		$longitude = $report['longitude'];
		$report_user_id = $report['user_id'];
		//echo "<li>".$report['report_type'];

		
		$custom_reply = "Alarm: ".$report_type." (".$city.")";
		
		if($custom_reply){
			foreach($users as $user){
				//var_dump($user);
				$chat_id = $user['chat_id'];
				$city = $user['city'];
				
				if($chat_id == $report_user_id)
					continue;
				
				$url = 'https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($custom_reply);
				//echo $url;
				$result = file_get_contents($url);
			
				$res_array = json_decode($result, true);

				$message_id = intval($res_array['result']['message_id']);
				var_dump ($message_id);
				
				$url = 'https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/sendlocation?chat_id='.$chat_id.'&latitude='.$latitude.'&longitude='.$longitude;
				
				$result = file_get_contents($url);
			
				$res_array = json_decode($result, true);

				$message_id = intval($res_array['result']['message_id']);
				var_dump ($message_id);
			}
		}
		
		UpdateReportStatus($conn, $report_id, 0);
		
	}

	
	foreach($users as $user){
		//var_dump($user);
		$chat_id = $user['chat_id'];
		$city = $user['city'];
		
		
		
		
		$air_stats = GetAirStats($city);
				
		$co = intval($air_stats[6]);
		if($co >= 5000){
			$reply .= chr(10)."Carbon Monoxide (CO): ".$co;

			if($co < 10000)
				$reply .= " (Modrate)";
			elseif($co < 17000)
				$reply .= " (Unhealty)";
			elseif($co < 34000)
				$reply .= " (Very Unhealty)";
			else
				$reply .= " (Hazardous)";
		}
		
		$pm10 = intval($air_stats[25]);
		if($pm10 >= 50){
			$reply .= chr(10)."Particulate Matter (PM10): ".$pm10;

			if($pm10 < 150)
				$reply .= " (Modrate)";
			elseif($pm10 < 350)
				$reply .= " (Unhealty)";
			elseif($pm10 < 420)
				$reply .= " (Very Unhealty)";
			else
				$reply .= " (Hazardous)";
		}
		
		$pm25 = intval($air_stats[26]);
		if($pm25 >= 12){
			$reply .= chr(10)."Particulate Matter (PM25): ".$pm25;

			if($pm25 < 55)
				$reply .= " (Modrate)";
			elseif($pm25 < 150)
				$reply .= " (Unhealty)";
			elseif($pm25 < 250)
				$reply .= " (Very Unhealty)";
			else
				$reply .= " (Hazardous)";
		}		
		
		$so = intval($air_stats[4]);
		if($so >= 80){
			$reply .= chr(10)."Sulphur dioxide (SO2): ".$so;
			if($so < 365)
				$reply .= " (Modrate)";
			elseif($so < 800)
				$reply .= " (Unhealty)";
			elseif($so < 1600)
				$reply .= " (Very Unhealty)";
			else
				$reply .= " (Hazardous)";
		}
		
		$o3 = intval($air_stats[5]);
		if($o3 >= 118){
			$reply .= chr(10)."Ozone (O3): ".$o3;
			if($o3 < 157)
				$reply .= " (Modrate)";
			elseif($o3 < 235)
				$reply .= " (Unhealty)";
			elseif($o3 < 785)
				$reply .= " (Very Unhealty)";
			else
				$reply .= " (Hazardous)";
		}
		
		$no2 = intval($air_stats[2]);
		if($no2 >= 1130){
			$reply .= chr(10)."Nitrogen dioxide (NO2): ".$no2;
			if($no2 < 2260)
				$reply .= " (Very Unhealty)";
			else
				$reply .= " (Hazardous)";
		}

		if($reply){
			$message = "Air Pollutant ALARM! ".chr(10).$reply;
		}		
	
		if($message){			
			$url = 'https://api.telegram.org/bot696457331:AAFhcTuLuB1HQgFKC-MtkWBkne6xkq8DoKA/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message);
			echo $url;
			$result = file_get_contents($url);
		
			$res_array = json_decode($result, true);

			$message_id = intval($res_array['result']['message_id']);
			var_dump ($message_id);
		}
				
	}
	
	mysqli_close($conn);
	
	
	
?>
