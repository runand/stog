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

foreach ($data as $departure) {
  $direction_output[$departure->Direction][] = array(
    'line' => ucfirst(strtolower($departure->Line)),
    'destination' => $departure->DestinationName,
    'departure' => $departure->MinutesToDeparture,
  );
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
