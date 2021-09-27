<?php
function send_military_confirmation_request($test=false){
  
  $ch = curl_init();

  $send_out = [
    'ssn' => $_POST['ssn'], // this formats to xxx-xx-xxxx
    'gender' => $_POST['gender'],
    'first_name' => $_POST['first_name'],
    'middle_name' => $_POST['middle_name'],
    'last_name' => $_POST['last_name'],
    'birth_date' => date('Y-m-d',strtotime($_POST['birth_date']))
  ];

  $payload = json_encode($send_out);

  curl_setopt($ch, CURLOPT_URL,"https://sandbox-api.va.gov/services/veteran_confirmation/v0/status");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload),
    'apikey: xxx'
  ));

  $server_output = curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close ($ch);

  error_log(print_r($server_output,1) . "\r\n", 3, __DIR__ . '/va-full-log.log');
  
  if( $httpcode == 200 || $test) 
    return true;

  if( $httpcode == 403 || $httpcode == 401 )
    error_log(print_r(date('Y-m-d H:i:s').': No API Key',1) . "\r\n", 3, __DIR__ . '/va-api-errors.log');

  if( $httpcode == 503 )
    error_log(print_r(date('Y-m-d H:i:s').': VA Confirmation is down',1) . "\r\n", 3, __DIR__ . '/va-api-errors.log');

  if( $httpcode == 500 )
    error_log(print_r($httpcode.':'.$server_output,1) . "\r\n", 3, __DIR__ . '/va-api-errors.log');

  return false;
}