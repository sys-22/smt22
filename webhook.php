
<?php

require_once('./vendor/autoload.php');

new LineMessage;

class LineMessage{

  private $token = '[s2HtA7+7R5R6LBD7KlyP0lSEJUCiS2UgffJU/QyF0kz6PjEcw/yWaQKU4VroIXo039U4VwZV4VMfe49EycwgTa9Rg8xlNnk4rGh5jlkZdqilTuNrC5Dyk/0O51k1jfGquHExFtwLcwESii+ze0g9aAdB04t89/1O/w1cDnyilFU=]';
  private $secret = '[fb18e7478baee5c48f1cfad36786270f]';
  private $profile_array = array(); //プロフィールを格納する配列 displayName:表示名 userId:ユーザ識別子 pictureUrl:画像URL statusMessage:ステータスメッセージ

  private $replyToken;
  private $userId;
  private $httpClient;
  private $bot;

  function __construct(){

    $json_string = file_get_contents('php://input');
    $jsonObj = json_decode($json_string);
    $this->userId = $jsonObj->{"events"}[0]->{"source"}->{"userId"};
    $this->replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

    $this->httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($this->token);
    $this->bot = new \LINE\LINEBot($this->httpClient, ['channelSecret' => $this->secret]);

    $this->get_profile();

  }

  function get_profile(){

    $response = $this->bot->getProfile($this->userId);

    if ($response->isSucceeded()) {

      $profile = $response->getJSONDecodedBody();
      $displayName = $profile['displayName'];
      $userId = $profile['userId'];
      $pictureUrl = $profile['pictureUrl'];
      $statusMessage = $profile['statusMessage'];
      $this->profile_array = array("displayName"=>$displayName,"userId"=>$userId,"pictureUrl"=>$pictureUrl,"statusMessage"=>$statusMessage);
      $this->reply_message();
    }
  }

  function reply_message(){

    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($this->profile_array["displayName"]."さんこんにちは！");
    $response = $this->bot->replyMessage($this->replyToken, $textMessageBuilder);   
  }

}

?>