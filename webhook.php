
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('UfcIQQ/Knkg3E7DLS+0u0DL2+qZWSyfiObJkgLS09QbUx2kCc0GJHkffxAfHWItI39U4VwZV4VMfe49EycwgTa9Rg8xlNnk4rGh5jlkZdqgeYccRv1aKdju5l5RONh9Hvp28pLNRhfLUHsw2xL2bGQdB04t89/1O/w1cDnyilFU=');
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => 'dd6c195e52c72d80b7c32098843f9aba']);
$response = $bot->getProfile('<userId>');
if ($response->isSucceeded()) {
    $profile = $response->getJSONDecodedBody();
    echo $profile['displayName'];
    echo $profile['pictureUrl'];
    echo $profile['statusMessage'];
}