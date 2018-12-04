
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('mREkOWlkv8FLyzekkvjSLa7peMd0ZXsQlRlTdfPGf83I1/L8agaoMUkKsmHOdree39U4VwZV4VMfe49EycwgTa9Rg8xlNnk4rGh5jlkZdqidra0OEmPSF0mmwtqHnoBklUUhXzczMEVVXLQeRYQ0IQdB04t89/1O/w1cDnyilFU=');
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => fb18e7478baee5c48f1cfad36786270f']);
$response = $bot->getProfile('<userId>');
if ($response->isSucceeded()) {
    $profile = $response->getJSONDecodedBody();
    echo $profile['displayName'];
    echo $profile['pictureUrl'];
    echo $profile['statusMessage'];
}