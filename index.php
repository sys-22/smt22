<?php
DEFINE("ACCESS_TOKEN","7xPGjrgg2r4z66m+w8CXEnisIdoqQnZe7ORIX/KPZFrnLl+lA1UWsv1cs5ufHFOn39U4VwZV4VMfe49EycwgTa9Rg8xlNnk4rGh5jlkZdqjHLtkU5oqF8jgOWPlZEEihlu+KaQ2zHNqhhhl8VThmtgdB04t89/1O/w1cDnyilFU=");
DEFINE("SECRET_TOKEN","fb18e7478baee5c48f1cfad36786270f");

use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\Constant\HTTPHeader;

//LINESDK�̓ǂݍ���
require_once(__DIR__."/vendor/autoload.php");

//LINE���瑗���Ă�����true�ɂȂ�
if(isset($_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE])){

//LINEBOT��POST�ő����Ă������f�[�^�̎擾
  $inputData = file_get_contents("php://input");

//LINEBOTSDK�̐ݒ�
  $httpClient = new CurlHTTPClient(ACCESS_TOKEN);
  $Bot = new LINEBot($HttpClient, ['channelSecret' => SECRET_TOKEN]);
  $signature = $_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE]; 
  $Events = $Bot->parseEventRequest($InputData, $Signature);

//��ʂɃ��b�Z�[�W��������ƕ������̃f�[�^�������ɑ����Ă��邽�߁Aforeach�����Ă���B
    foreach($Events as $event){
    $SendMessage = new MultiMessageBuilder();
    $TextMessageBuilder = new TextMessageBuilder("HELLO!�I");
    $SendMessage->add($TextMessageBuilder);
    $Bot->replyMessage($event->getReplyToken(), $SendMessage);
  }
}