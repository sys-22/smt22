
new Bot;

class Bot{
  private $channel_id = "U062b8e88bed9a2b49e723c7396eb7b6e";
  private $channel_secret = "214a3ce5066c7517d355b43cd69fd25a";
  private $mid = "jNa8s93IlwXzTcujdJn2sSgtGc3T7X0O+bm6yZi2rrfqMAYN7GPKQLug2fU8Hj297FgeX9wPEl0KLOaYMwErgLDfHcT0pYxk3S5e2tGHYd9x+6K70AziRpv9SbiNbav0R2Zr8RuMuk42VgBXeCLKVAdB04t89/1O/w1cDnyilFU=";
  private $header;

  private $from;
  private $text;
  private $content_type;

  private $name;

  public function __construct(){

    $json_string = file_get_contents('php://input');
    $receive = json_decode($json_string);
    $this->from = $receive->result{0}->content->from;
    $this->text = $receive->result{0}->content->text;
    $this->content_type = $receive->result[0]->content->contentType;

    $this->header = array(
      "Content-Type: application/json; charser=UTF-8",
      "X-Line-ChannelID:" . $this->channel_id,
      "X-Line-ChannelSecret:" . $this->channel_secret,
      "X-Line-Trusted-User-With-ACL:" . $this->mid,
    );

    $this->getting_user_profile_rinformation($this->from);
    $this->sending_messages($this->name);

  }

  private function sending_messages($message){
    $url = "https://trialbot-api.line.me/v1/events";

    $data = array("to" => array($this->from), "toChannel" => 1383378250, "eventType" => "138311608800106203", "content" => array("contentType" => 1, "toType" => 1, "text" => $message));
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
    $result = curl_exec($ch);
    curl_close($ch);
  }

  private function getting_user_profile_rinformation($mid) {
    $url = "https://trialbot-api.line.me/v1/profiles?mids={$mid}";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curl);
    $receive = json_decode($output);
    error_log($output);

    $this->name = $receive->contacts[0]->displayName; //displayName=>名前 pictureUrl=>プロフィール画像 statusMessage=>自己紹介文
    $this->name .= "さんこんにちは！";

    file_put_contents("json.php", $output);
  }
}
