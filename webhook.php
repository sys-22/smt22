
 package bot

import (
	"fmt"
	"net/http"
	"os"

	"golang.org/x/net/context"

	"github.com/line/line-bot-sdk-go/linebot"
	"google.golang.org/appengine"
	"google.golang.org/appengine/log"
	"google.golang.org/appengine/urlfetch"
)

/**
 * LINE Bot�N���C�A���g�C���X�^���X�𐶐�
 */
func createBotClient(c context.Context, client *http.Client) (bot *linebot.Client, err error) {
	var (
		channelSecret = os.Getenv("dd6c195e52c72d80b7c32098843f9aba")
		channelToken  = os.Getenv("9DaHPUurWQB3oZVvk9iVSWatRaTSR/qMBpGMs3HwFzfOkAGXiLOpp1cZs6F2SydS39U4VwZV4VMfe49EycwgTa9Rg8xlNnk4rGh5jlkZdqijpiKGsrCmH/JFY1OXKvgC3WtMfBB+fIF9G0osw3tuLAdB04t89/1O/w1cDnyilFU=")
	)

	bot, err = linebot.New(channelSecret, channelToken, linebot.WithHTTPClient(client)) //Appengine��urlfetch���g�p����
	if err != nil {
		log.Errorf(c, "Error occurred at create linebot client: %v", err)
		return bot, err
	}
	return bot, nil
}

/**
 * Get event sender's id
 */
func getSenderID(c context.Context, event *linebot.Event) string {
	switch event.Source.Type {
	case linebot.EventSourceTypeGroup:
		return event.Source.GroupID
	case linebot.EventSourceTypeRoom:
		return event.Source.RoomID
	case linebot.EventSourceTypeUser:
		return event.Source.UserID
	}
	log.Warningf(c, "Can not get sender id. type: %v", event.Source.Type)
	return ""
}

/**
 * ���M�҂̕\�������擾����
 *
 * ���[�U�����擾�ł��Ȃ��̂ŁA���[������уO���[�v�ł�id�����̂܂ܕԂ�
 * �O���[�v�����o�[��UserID�̏ꍇ�A���̃��[�U������Bot�ƗF�����o�^���Ă��Ȃ���Ύ擾�ł��Ȃ�
 */
func getSenderName(c context.Context, bot *linebot.Client, from string) string {
	if len(from) == 0 {
		log.Warningf(c, "Parameter `mid` was not specified.")
		return from
	}
	if from[0:1] == "U" {
		senderProfile, err := bot.GetProfile(from).Do()
		if err != nil {
			log.Warningf(c, "Error occurred at get sender profile. from: %v, err: %v", from, err)
			return from
		}
		return senderProfile.DisplayName
	}
	return from
}

/**
 * LINE Messaging API����̃R�[���o�b�N���n���h�����O
 */
func lineBotCallback(w http.ResponseWriter, r *http.Request) {

	c := appengine.NewContext(r)
	bot, err := createBotClient(c, urlfetch.Client(c))
	if err != nil {
		return
	}

	events, err := bot.ParseRequest(r)
	if err != nil {
		if err == linebot.ErrInvalidSignature {
			log.Warningf(c, "Linebot request status: 400")
			w.WriteHeader(400)
		} else {
			log.Warningf(c, "linebot request status: 500\n\terror: %v", err)
			w.WriteHeader(500)
		}
		return
	}

	for _, event := range events {
		switch event.Type {
		case linebot.EventTypeFollow, linebot.EventTypeJoin:
			sender := getSenderName(c, bot, getSenderID(c, event))
			message := sender + " ����A�F�����o�^���肪�Ƃ��������܂��I"
			if _, err = bot.ReplyMessage(event.ReplyToken, linebot.NewTextMessage(message)).Do(); err != nil {
				log.Errorf(c, "Error occurred at reply-message for follow/join. err: %v", err)
			}

		case linebot.EventTypeUnfollow, linebot.EventTypeLeave:
			sender := getSenderName(c, bot, getSenderID(c, event))
			message := sender + " ����A���悤�Ȃ�"
			if _, err = bot.ReplyMessage(event.ReplyToken, linebot.NewTextMessage(message)).Do(); err != nil {
				log.Errorf(c, "Error occurred at reply-message for unfollow/leave. err: %v", err)
			}

		case linebot.EventTypeMessage:
			switch message := event.Message.(type) {
			case *linebot.TextMessage:
				var replyMessage string
				if message.Text == "/version" {
					//�o�[�W����
					replyMessage = "version: " + version

				} else if message.Text == "/mention" || message.Text == "/mention1" {
					//ID�Ń����V����
					replyMessage = "@" + event.Source.UserID + " �����V�����ɂȂ��Ă��܂����H"

				} else if message.Text == "/mention2" {
					//���O�Ń����V����
					sender := getSenderName(c, bot, event.Source.UserID)
					replyMessage = "@" + sender + " �����V�����ɂȂ��Ă��܂����H"

				} else if message.Text == "/profile" {
					//���[�U�̃v���t�@�C�����擾�i��API�j
					senderProfile, err2 := bot.GetProfile(event.Source.UserID).Do()
					if err2 != nil {
						replyMessage = fmt.Sprintf("GetProfile()�Ńv���t�@�C�����擾�ł��܂���ł���\nerr: %v", err2)
					} else {
						replyMessage = fmt.Sprintf("UserID: %v\nDisplayName: %v\nPictureURL: %v\nStatusMessage: %v",
							senderProfile.UserID,
							senderProfile.DisplayName,
							senderProfile.PictureURL,
							senderProfile.StatusMessage,
						)
					}

				} else if message.Text == "/profile2" {
					//���[�U�̃v���t�@�C�����擾�i�VAPI�j
					switch event.Source.Type {
					case linebot.EventSourceTypeGroup:
						senderProfile, err2 := bot.GetGroupMemberProfile(event.Source.GroupID, event.Source.UserID).Do()
						if err2 != nil {
							replyMessage = fmt.Sprintf("GetGroupMemberProfile()�Ńv���t�@�C�����擾�ł��܂���ł���\nerr: %v", err2)
						} else {
							replyMessage = fmt.Sprintf("UserID: %v\nDisplayName: %v\nPictureURL: %v\nStatusMessage: %v",
								senderProfile.UserID,
								senderProfile.DisplayName,
								senderProfile.PictureURL,
								senderProfile.StatusMessage,
							)
						}
					case linebot.EventSourceTypeRoom:
						senderProfile, err2 := bot.GetRoomMemberProfile(event.Source.RoomID, event.Source.UserID).Do()
						if err2 != nil {
							replyMessage = fmt.Sprintf("GetRoomMemberProfile()�Ńv���t�@�C�����擾�ł��܂���ł���\nerr: %v", err2)
						} else {
							replyMessage = fmt.Sprintf("UserID: %v\nDisplayName: %v\nPictureURL: %v\nStatusMessage: %v",
								senderProfile.UserID,
								senderProfile.DisplayName,
								senderProfile.PictureURL,
								senderProfile.StatusMessage,
							)
						}
					}

				} else {
					//�I�E���Ԃ�
					sender := getSenderName(c, bot, event.Source.UserID)
					replyMessage = sender + "����̔���:\n" + message.Text
				}
				if _, err = bot.ReplyMessage(event.ReplyToken, linebot.NewTextMessage(replyMessage)).Do(); err != nil {
					log.Errorf(c, "Error occurred at reply-message. err: %v", err)
				}
			}

