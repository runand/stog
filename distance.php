<?php
/*

$lat1 = 55.66157579999999;
$lon1 = 12.4049744;
*/

$lat1 = $_POST['lat'];
$lon1 = $_POST['lon'];  

require('gPoint.php');

include('stations.inc');

$stations = array();

foreach($stationlist as $uic => $stationinfo) {
  $utm_parts = explode(';', $stationinfo['utm']);
  $station = new gPoint();
  $station->setUTM($utm_parts[0], $utm_parts[1], '32N');
  $station->convertTMtoLL();
 
  $stations[] = array('uic' => $uic - 8600000, 'name' => $stationinfo['station'], 'distance' => $station->distanceFrom($lon1, $lat1));
}

usort($stations, 'distancesort');

print(json_encode($stations));


function distancesort($a, $b) {
  if ($a['distance'] == $b['distance']) {
    return 0;
  }
  return ($a['distance'] < $b['distance']) ? -1 : 1;
}