# tghp

This is full implemented library that handles telegram api and protocol in PHP environment for the first time.


### Authors:

- NIMIX3 (Nima Akhlaghi)



### Current versions:

- schema: Layer 57



### API, Protocol documentation

Documentation for Telegram API is available here: https://core.telegram.org/api

Documentation for MTproto protocol is available here: https://core.telegram.org/mtproto



### Usage & Deploy

Download GitHub Repository

     Web Server Part : 
        require Linux with LAMP(Apache,PHP 5.3 or newer) with cURL,mcrypt,mbstring installed (permission to use exec method in php needed!)
        Just install tg-cli (from: https://github.com/vysheng/tg) then make an agent with your phone number and create the socket of it, then go..
     
     Web Service Part :
        require PHP v.5.3 or newer with cURL,mcrypt,mbstring installed (no any permission needed)!
        just install and use the API or WebPanel to work with it.



### All available implemented methods and properties
	
#### Methods:

	tghp.SetStatusOnline
	tghp.SendTyping
	tghp.SendMessageMass
	tghp.SendMessage
	tghp.SendMessageWhatsApp
	tghp.SendMessageWhatsAppMass
	tghp.CreateGroupChat
	tghp.ChatInfo
	tghp.RenameChat
	tghp.ChatAddUser
	tghp.ChatDeleteUser
	tghp.SetProfileName
	tghp.BlockUser
	tghp.UnBlockUser
	tghp.MarkRead
	tghp.GetUserInfo
	tghp.GetDialogList
	tghp.GetHistory
	tghp.SetUsername
	tghp.SetProfilePhoto
	tghp.SendPhoto
	tghp.SendVideo
	tghp.SendAudio
	tghp.SendTex
	tghp.SendFile
	tghp.SendDocument
	tghp.SendPhotoMass
	tghp.SendVideoMass
	tghp.SendFileMass
	tghp.SendLocation
	tghp.SendContact
	tghp.APIGetLimits
	tghp.APIGetCredits
	tghp.APIGetIP
	tghp.APISetIP
	tghp.APIChangeSecret
	tghp.APIGetRobot
	tghp.APIGetActive
	tghp.APIGetUsername
	tghp.APIisSuper
	tghp.APIcanGet
	tghp.APIGetAds
	tghp.APIGetReport


### Term of use
- Please Accept all of these rules before using this source, or if you declined with these please close this repository.

1. Don't use this library to make dangerous applications and don't use for hacking or scamming purpose
2. Don't sell this library to other peoples, and if you want to bring this library you should send this repository link.
3. Don't use this library to face making though! and to inflate with pride!
4. Help peoples who search for such library as you like! and tell them this repository link.


### Contacts 

If you would like to ask a question, you can write to our telegram or to the github (or both). To contact us via telegram, use our usernames :  
- @NiMiX3


Be Happy  ;) 
