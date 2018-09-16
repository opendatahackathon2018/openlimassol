<?
	function GetAirStats($city){
		$apc_name = "calm_data_".$city;
		$res = ApcGet($apc_name, $apc_success);
		if($apc_success)
			return json_decode($res, true);
		
		$xml = simplexml_load_file("http://178.62.245.17/air/airquality.php");
		if($city == "Limassol")
			$station_code = "3";
		if($city == "Nicosia")
			$station_code = "1";
		if($city == "Larnaca")
			$station_code = "4";
		if($city == "Paphos")
			$station_code = "15";
		if($city == "Paralimni")
			$station_code = "16";
		
		$res = array();
		if($xml){
			foreach ($xml->stations->station as $item) { //echo "<li>"; var_dump($item); 
				if($item->station_code == $station_code){ 
					if($item->pollutant_code == "6"){// CO
						$res[6]=(float) $item->pollutant_value[0];						
					}
					
					if($item->pollutant_code == "25"){// PM10
						$res[25]=(float) $item->pollutant_value[0];
					}
					
					if($item->pollutant_code == "4"){// SO
						$res[4]=(float) $item->pollutant_value[0];
					}
					
					if($item->pollutant_code == "5"){// O3
						$res[5]=(float) $item->pollutant_value[0];
					}
					
					if($item->pollutant_code == "2"){// NO2
						$res[2]=(float) $item->pollutant_value[0];
					}
					
					if($item->pollutant_code == "26"){// PM25
						$res[26]=(float) $item->pollutant_value[0];
					}
				}
			}
		}
		
		var_dump($res);
		ApcSet($apc_name, json_encode($res), 60); // 30 минут 	
		
		return $res;
	}
	
	function ApcSet($name, $value, $minutes = 0){
		if($name && $value && $minutes)
			return apcu_add($name, $value, $minutes * 60);
	}

	function ApcGet($name, &$success){
		if($name)
			return apcu_fetch($name, $success);
	}

	function ApcDelete($name){
		if($name)
			return apcu_delete($name);
	}