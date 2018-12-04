
define("CHANNEL_ACCESS_TOKEN", 'i/wwsOJ/KkkxnpPi+m5TQZP779JbDvwC2bHwXqQhTVOFDLCuGj4uTMrINt52QM9239U4VwZV4VMfe49EycwgTa9Rg8xlNnk4rGh5jlkZdqhRZaf442A/HuOiezFl0S2VRxr2o/UQFo6I2fl2yxQBxAdB04t89/1O/w1cDnyilFU=');
define("CHANNEL_SECRET", '40a52cd250f92c4f564217b593766ee6');

$contents = file_get_contents('php://input');
$json = json_decode($contents);
$event = $json->events[0];

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

// �l�g�[�N�̏ꍇ�̓O���[�v�ɏ��҂���悤�Ƀ��b�Z�[�W�\��
if ($event->source->type != 'group') {
  $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('�O���[�v�ɏ��҂��Ă��������I!');
}

else {
  try {
    // ���b�Z�[�W���������񂾃��[�U�����擾
    $response = $bot->getProfile($event->source->userId);

    if ($response->isSucceeded()) {
      $profile = $response->getJSONDecodedBody();
      $user_display_name = $profile['displayName'];
      $user_picture_url = $profile['pictureUrl'];
      $user_status_message = $profile['statusMessage'];
      $fileUrl = "";

      // ���b�Z�[�W�^�C�v���摜�������ꍇ�͉摜��ۑ�
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

    // ���[�U���⃁�b�Z�[�W���f�[�^�x�[�X�ɕۑ�
    $pdo = new PDO('mysql:host=localhost;dbname=suzukidb;charset=utf8','suzuki.talks','sysmet22',
    array(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC));
    $stmt = $pdo -> prepare("INSERT INTO talks (id, type, group_id, user_id, user_display_name, user_picture_url, user_status_message, talk_type, upload_image_name, time, reply_token, message_id, message_type, message_text, created_at) VALUES ('', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)");
    $stmt->execute(array($event->type, $event->source->groupId, $event->source->userId, $user_display_name, $user_picture_url, $user_status_message, $event->source->type, @$fileUrl, $event->timestamp, $event->replyToken, $event->message->id, $event->message->type, $event->message->text));
  }
  catch (PDOException $e) {
    exit('�f�[�^�x�[�X�ڑ����s�B'.$e->getMessage());
  }
}