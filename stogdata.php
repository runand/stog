<?php
$uic = $_POST['uic'];

$url = 'http://wap.dsb.dk/servlet/WapServlet?stationsid='.$uic;

$file = file_get_contents($url);


$seperated = str_replace('<br/>', ';', $file);
$stripped = strip_tags($seperated);
$list = array_filter(explode(';', $stripped));

$output = array();

$count = 0;

foreach ($list as $entry){
	$count++;

	if($count != count($list)){
		if (!preg_match('/[\d\x{00BD}]+/', $entry)){
      if(strpos($entry, 'Tilbage') === FALSE) {
        $output[] = array('head' => utf8_encode($entry));
    
      }
		
		}
		else {
			if (preg_match('/^([\w]+) (.*) ([\d\x{00BD}]+)$/', $entry, $matches)) {
        $station_info = array_splice($matches, 1);
        $output[] = array(
          'line' => $station_info[0],
          'destination' => utf8_encode($station_info[1]),
          'departure' => utf8_encode($station_info[2]),
        );
			}
		}
	}
}

print json_encode($output);

?>
