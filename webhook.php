
<?php

$postData = array(
  'grant_type'    => 'authorization_code',
  'code'          => $_GET['code'],
  'redirect_uri'  => 'smt22.herokuapp.com/webhook.php',
  'client_id'     => '1627227117',
  'client_secret' => 'fb18e7478baee5c48f1cfad36786270f'
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/oauth2/v2.1/token');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response);
$accessToken = $json->access_token;

$ch = curl_init();

curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/profile');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response);

echo $response;
?>
