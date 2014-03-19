<?php

include('stations.inc');

$odataurl = 'http://traindata.dsb.dk/stationdeparture/opendataprotocol.svc/Queue()';

$uic = '8600' . $_POST['uic'];
$filter_elements = array(
 'StationUic' =>  $uic,
 'TrainType' => 'S-tog',
);

$elements = array();
foreach ($filter_elements as $key => $value) {
  $elements[] = '(' . $key . ' eq \'' . $value . '\')';
}

$filter_string = implode(' and ', $elements);

$odataurl .= '?$filter=' . urlencode($filter_string);

$request = curl_init($odataurl . '&$format=json');

curl_setopt($request, CURLOPT_HEADER, array());
curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

$output = curl_exec($request);

$data = json_decode($output);

$data = reset($data);

$direction_output = array();

$now = time();

foreach ($data as $departure) {
  preg_match('/[0-9]+/', $departure->Generated, $matches);
  $diff = ($now - ($matches[0]/1000 - 3600));
  $diffmin = $diff / 60;

  if ($diff > 30) {
    $minutestodepature = floor($departure->MinutesToDeparture - $diffmin);
  }
  else {
    $minutestodepature = $departure->MinutesToDeparture;
  }

  if ($minutestodepature > 0) {
    $direction_output[$departure->Direction][] = array(
      'line' => ucfirst(strtolower($departure->Line)),
      'destination' => $departure->DestinationName,
      'departure' => $minutestodepature,
    );
  }
}

$south = $direction_output['Syd'];
unset($direction_output['Syd']);
$direction_output['Syd'] = $south;

$output = array();

foreach ($direction_output as $direction => $data) {
  $output[] = array('head' => str_replace(' st', '', $stationlist[$uic]['station']) . ' m. ' . strtolower($direction));
  foreach ($data as $departure) {
    $output[] = $departure;
  }
}

print json_encode($output);
