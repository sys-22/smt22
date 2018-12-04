
define("CHANNEL_ACCESS_TOKEN", 'mREkOWlkv8FLyzekkvjSLa7peMd0ZXsQlRlTdfPGf83I1/L8agaoMUkKsmHOdree39U4VwZV4VMfe49EycwgTa9Rg8xlNnk4rGh5jlkZdqidra0OEmPSF0mmwtqHnoBklUUhXzczMEVVXLQeRYQ0IQdB04t89/1O/w1cDnyilFU=');
define("CHANNEL_SECRET", 'fb18e7478baee5c48f1cfad36786270f');

$contents = file_get_contents('php://input');
$json = json_decode($contents);
$event = $json->events[0];

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

// 個人トークの場合はグループに招待するようにメッセージ表示
if ($event->source->type != 'group') {
  $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('グループに招待してください！!');
}

else {
  try {
    // メッセージを書き込んだユーザ情報を取得
    $response = $bot->getProfile($event->source->userId);

    if ($response->isSucceeded()) {
      $profile = $response->getJSONDecodedBody();
      $user_display_name = $profile['displayName'];
      $user_picture_url = $profile['pictureUrl'];
      $user_status_message = $profile['statusMessage'];
      $fileUrl = "";

      // メッセージタイプが画像だった場合は画像を保存
      if ($event->message->type == 'image') {
        function uploadImageThenGetUrl ($rawBody) {
          $im = imagecreatefromstring($rawBody);

          if ($im !== false) {
            $filename = date("Ymd-His") . '-' . mt_rand() . '.jpg';
            imagejpeg($im, "images/uploads/" . $filename);
          }

          else {
            error_log("fail to create image.");
          }
        
          return $filename;
        }

        $res = $bot->getMessageContent($event->message->id);
        $fileUrl = uploadImageThenGetUrl($res->getRawBody());
      }
    }

    // ユーザ情報やメッセージをデータベースに保存
    $pdo = new PDO('mysql:host=localhost;dbname=suzukidb;charset=utf8','suzuki','Sysmet22',
    array(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC));
    $stmt = $pdo -> prepare("INSERT INTO talks (id, type, group_id, user_id, user_display_name, user_picture_url, user_status_message, talk_type, upload_image_name, time, reply_token, message_id, message_type, message_text, created_at) VALUES ('', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)");
    $stmt->execute(array($event->type, $event->source->groupId, $event->source->userId, $user_display_name, $user_picture_url, $user_status_message, $event->source->type, @$fileUrl, $event->timestamp, $event->replyToken, $event->message->id, $event->message->type, $event->message->text));
  }
  catch (PDOException $e) {
    exit('データベース接続失敗。'.$e->getMessage());
  }
}
