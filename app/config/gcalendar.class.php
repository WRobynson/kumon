<?php
//  from: https://www.apptha.com/blog/import-google-calendar-events-in-php/

function file_get_contents_curl( $url ) {
//	https://stackoverflow.com/questions/26148701/file-get-contents-ssl-operation-failed-with-code-1-failed-to-enable-crypto
	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );

	$data = curl_exec( $ch );
	curl_close( $ch );

	return $data;

}

class ics {
	
	function getIcsEventsAsStr($file) {
		// $icalString = file_get_contents($file);
		
		$icalString = file_get_contents_curl($file);
		
		return $icalString;
	}
    
	/* Function is to get all the contents from ics and explode all the datas according to the events and its sections */
	function getIcsEventsAsArray($file) {
		// $icalString = file_get_contents ( $file );
		$icalString = $file;
		$icsDates = array ();
		/* Explode the ICs Data to get datas as array according to string ‘BEGIN:’ */
		$icsData = explode ( "BEGIN:", $icalString );
		/* Iterating the icsData value to make all the start end dates as sub array */
		foreach ( $icsData as $key => $value ) {
			$icsDatesMeta [$key] = explode ( "<enter>", $value );
		}
		/* Itearting the Ics Meta Value */
		foreach ( $icsDatesMeta as $key => $value ) {
			foreach ( $value as $subKey => $subValue ) {
				/* to get ics events in proper order */
				$icsDates = $this->getICSDates ( $key, $subKey, $subValue, $icsDates );
			}
		}
		return $icsDates;
	}

    /* funcion is to avaid the elements wich is not having the proper start, end  and summary informations */
	function getICSDates($key, $subKey, $subValue, $icsDates) {
		if ($key != 0 && $subKey == 0) {
			$icsDates [$key] ["BEGIN"] = $subValue;
		} else {
			$subValueArr = explode ( ":", $subValue, 2 );
			if (isset ( $subValueArr [1] )) {
				$icsDates [$key] [$subValueArr [0]] = $subValueArr [1];
			}
		}
		return $icsDates;
	}
}

?>