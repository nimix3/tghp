<?php
//namespace Nimix3\Telegram\Cli;
require('Telegram.class.php');
/**
 * php-client for telegram-cli.
 */
class Telegram extends TeleCli
{
    /**
     * Sets status as online.
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     */
    public function setStatusOnline()
    {
        return $this->exec('status_online');
    }
    /**
     * Sets status as offline.
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     */
    public function setStatusOffline()
    {
        return $this->exec('status_offline');
    }
    /**
     * Sends a typing notification to $peer.
     * Lasts a couple of seconds or till you send a message (whatever happens first).
     *
     * @param string $peer The peer, gets escaped with escapePeer()
     *
     * @return boolean true on success, false otherwise
     */
    public function sendTyping($peer)
    {
        return $this->exec('send_typing ' . $this->escapePeer($peer));
    }
    /**
     * Sends a text message to $peer.
     *
     * @param string $peer The peer, gets escaped with escapePeer()
     * @param string $msg The message to send, gets escaped with escapeStringArgument()
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     * @uses escapeStringArgument()
     */
    public function msg($peer, $msg)
    {
        $peer = $this->escapePeer($peer);
        $msg = $this->escapeStringArgument($msg);
        return $this->exec('msg ' . $peer . ' ' . $msg);
    }
    /**
     * Sends a text message to several users at once.
     *
     * @param array $userList List of users / contacts that shall receive the message,
     *                        gets formated with formatPeerList()
     * @param string $msg The message to send, gets escaped with escapeStringArgument()
     *
     * @return boolean true on success, false otherwise
     */
    public function broadcast(array $userList, $msg)
    {
        return $this->exec('broadcast ' . $this->formatPeerList($userList) . ' '
            . $this->escapeStringArgument($msg));
    }
    /**
     * Creates a new group chat with the users in $userList.
     *
     * @param string $chatTitle The title of the new chat
     * @param array $userList The users you want to add to the chat. Gets formatted with formatPeerList().
     *                        The current telgram-user (who creates the chat) will be added automatically.
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapeStringArgument()
     * @uses formatPeerList()
     */
    public function createGroupChat($chatTitle, $userList)
    {
        if (count($userList) <= 0) {
            return false;
        }
        return $this->exec('create_group_chat '. $this->escapeStringArgument($chatTitle).' '.
            $this->formatPeerList($userList));
    }
    /**
     * Returns an info-object about a chat (title, name, members, admin, etc.).
     *
     * @param string $chat The name of the chat (not the title). Gets escaped with escapePeer().
     *
     * @return object|boolean A chat-object; false if somethings goes wrong
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function chatInfo($chat)
    {
        return $this->exec('chat_info ', $this->escapePeer($chat));
    }
    /**
     * Renames a chat. Both, the chat title and the print-name will change.
     *
     * @param string $chat The name of the chat (not the title). Gets escaped with escapePeer().
     * @param string $chatTitle The new title of the chat.
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     * @uses escapeStringArgument()
     */
    public function renameChat($chat, $newChatTitle)
    {
        return $this->exec('rename_chat '. $this->escapePeer($chat).' '. $this->escapeStringArgument($newChatTitle));
    }
    /**
     * Adds a user to a chat.
     *
     * @param string $chat The chat you want the user to add to. Gets escaped with escapePeer().
     * @param string $user The user you want to add. Gets escaped with escapePeer().
     * @param int $numberOfMessagesToFoward The number of last messages of the chat, the new user should see.
     *                                      Default is 100.
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function chatAddUser($chat, $user, $numberOfMessagesToFoward = 100)
    {
        return $this->exec('chat_add_user '. $this->escapePeer($chat).' '. $this->escapePeer($user),
            (int) $numberOfMessagesToFoward);
    }
    /**
     * Deletes a user from a chat.
     *
     * @param string $chat The chat you want the user to delete from. Gets escaped with escapePeer().
     * @param string $user The user you want to delete. Gets escaped with escapePeer().
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function chatDeleteUser($chat, $user)
    {
        return $this->exec('chat_del_user '. $this->escapePeer($chat).' '. $this->escapePeer($user));
    }
    /**
     * Sets the profile name
     *
     * @param $firstName The first name
     * @param $lastName The last name
     *
     * @return object|boolean Your new user-info as an object; false if somethings goes wrong
     *
     * @uses exec()
     */
    public function setProfileName($firstName, $lastName)
    {
        return $this->exec('set_profile_name ' . $this->escapeStringArgument($firstName) . ' '
            . $this->escapeStringArgument($lastName));
    }
    /**
     * Adds a user to the contact list
     *
     * @param string $phoneNumber The phone-number of the new contact, needs to be a telegram-user.
     *                            Every char that is not a number gets deleted, so you don't need to care about spaces,
     *                            '+' and so on.
     * @param string $firstName The first name of the new contact
     * @param string $lastName The last name of the new contact
     *
     * @return object|boolean The new contact-info as an object; false if somethings goes wrong
     *
     * @uses exec()
     * @uses escapeStringArgument()
     */
    public function addContact($phoneNumber, $firstName, $lastName)
    {
        $phoneNumber = preg_replace('%[^0-9]%', '', (string) $phoneNumber);
        if (empty($phoneNumber)) {
            return false;
        }
        return $this->exec('add_contact ' . $phoneNumber . ' ' . $this->escapeStringArgument($firstName)
            . ' ' . $this->escapeStringArgument($lastName));
    }
    /**
     * Renames a user in the contact list
     *
     * @param string $contact The contact, gets escaped with escapePeer()
     * @param string $firstName The new first name for the contact
     * @param string $lastName The new last name for the contact
     *
     * @return object|boolean The new contact-info as an object; false if somethings goes wrong
     *
     * @uses exec()
     * @uses escapeStringArgument()
     */
    public function renameContact($contact, $firstName, $lastName)
    {
        return $this->exec('rename_contact ' . $this->escapePeer($contact)
            . ' ' . $this->escapeStringArgument($firstName) . ' ' . $this->escapeStringArgument($lastName));
    }
    /**
     * Deletes a contact.
     *
     * @param string $contact The contact, gets escaped with escapePeer()
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function deleteContact($contact)
    {
        return $this->exec('del_contact ' . $this->escapePeer($contact));
    }
    /**
     * Blocks a user .
     *
     * @param string $user The user, gets escaped with escapePeer()
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function blockUser($user)
    {
        return $this->exec('block_user ' . $this->escapePeer($user));
    }
    /**
     * Unblocks a user.
     *
     * @param string $user The user, gets escaped with escapePeer()
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function unblockUser($user)
    {
        return $this->exec('unblock_user ' . $this->escapePeer($user));
    }
    /**
     * Marks all messages with $peer as read.
     *
     * @param string $peer The peer, gets escaped with escapePeer()
     *
     * @return boolean true on success, false otherwise
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function markRead($peer)
    {
        return $this->exec('mark_read ' . $this->escapePeer($peer));
    }
    /**
     * Returns an array of all contacts. Every contact is an object like it gets returned from `getUserInfo()`.
     *
     * @return array|boolean An array with your contacts as objects; false if somethings goes wrong
     *
     * @uses exec()
     *
     * @see getUserInfo()
     */
    public function getContactList()
    {
        return $this->exec('contact_list');
    }
    /**
     * Returns the informations about the user as an object.
     *
     * @param string $user The user, gets escaped with escapePeer()
     *
     * @return object|boolean An object with informations about the user; false if somethings goes wrong
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function getUserInfo($user)
    {
        return $this->exec('user_info ' . $this->escapePeer($user));
    }
    /**
     * Returns an array of all your dialogs. Every dialog is an object with type "user" or "chat".
     *
     * @return array|boolean An array with your dialogs; false if somethings goes wrong
     *
     * @uses exec()
     *
     * @see getUserInfo()
     */
    public function getDialogList()
    {
        return $this->exec('dialog_list');
    }
    /**
     * Returns an array of your past message with that $peer. Every message is an object which provides informations
     * about it's type, sender, retriever and so one.
     * All messages will also be marked as read.
     *
     * @param string $peer The peer, gets escaped with escapePeer()
     * @param int $limit (optional) Limit answer to $limit messages. If not set, there is no limit.
     * @param int $offset (optional) Use this with the $limit parameter to go through older messages.
     *                    Can also be negative.
     *
     * @return array|boolean An array with your past messages with that $peer; false if somethings goes wrong
     *
     * @uses exec()
     * @uses escapePeer()
     */
    public function getHistory($peer, $limit = null, $offset = null)
    {
        if ($limit !== null) {
            $limit = (int) $limit;
            if ($limit < 1) { //if limit is lesser than 1, telegram-cli crashes
                $limit = 1;
            }
            $limit = ' ' . $limit;
        } else {
            $limit = '';
        }
        if ($offset !== null) {
            $offset = ' ' . (int) $offset;
        } else {
            $offset = '';
        }
        return $this->exec('history ' . $this->escapePeer($peer) . $limit . $offset);
    }
	public function sendPhoto($peer, $File)
	{
		if(file_exists($File))
        return $this->exec('send_photo ' . $this->escapePeer($peer).' '.$File);
	    else return false;
	}
	public function sendVideo($peer, $File)
	{
		if(file_exists($File))
        return $this->exec('send_video ' . $this->escapePeer($peer).' '.$File);
	    else return false;
	}
		public function sendAudio($peer, $File)
	{
		if(file_exists($File))
        return $this->exec('send_audio ' . $this->escapePeer($peer).' '.$File);
	    else return false;
	}
	public function sendText($peer, $File)
	{
		if(file_exists($File))
        return $this->exec('send_text ' . $this->escapePeer($peer).' '.$File);
	    else return false;
	}
	public function sendFile($peer, $File)
	{
		if(file_exists($File))
        return $this->exec('send_file ' . $this->escapePeer($peer).' '.$File);
	    else return false;
	}
	public function sendDocument($peer, $File)
	{
		if(file_exists($File))
        return $this->exec('send_document ' . $this->escapePeer($peer).' '.$File);
	    else return false;
	}
	public function sendLocation($peer, $latitude, $longitude)
	{
        return $this->exec('send_document ' . $this->escapePeer($peer).' '.$latitude.' '.$longitude);
	}
	public function sendContact($peer, $Phone, $First, $Last)
	{
        return $this->exec('send_contact ' . $this->escapePeer($peer).' '.$Phone.' '.$First.' '.$Last);
	}
	public function setProfilePhoto($File)
	{
		if(file_exists($File))
        return $this->exec('set_profile_photo ' . $File);
	    else return false;
	}
	public function setUsername($username)
	{
        return $this->exec('set_username ' .$username);
	}
	public function chatSetPhoto($chat, $file)
	{
        return $this->exec('chat_set_photo ' .$this->escapePeer($chat).' '.$file);
	}
	public function MetaGetAdmin($chat)
	{
        return $this->exec('channel_get_admins '.$this->escapePeer($chat).' 100');
	}
	public function MetaGetMember($chat)
	{
        return $this->exec('channel_get_members '.$this->escapePeer($chat).' 100');
	}
	public function MetaInfo($chat)
	{
        return $this->exec('channel_get_members '.$this->escapePeer($chat));
	}
	public function MetaInvite($chat,$peer)
	{
        return $this->exec('channel_invite '.$this->escapePeer($chat).' '.$this->escapePeer($peer));
	}
	public function MetaKick($chat,$peer)
	{
        return $this->exec('channel_kick '.$this->escapePeer($chat).' '.$this->escapePeer($peer));
	}
	public function MetaLeave($chat)
	{
        return $this->exec('channel_leave '.$this->escapePeer($chat));
	}
	public function MetaList($chat)
	{
        return $this->exec('channel_list '.$this->escapePeer($chat).' 100');
	}
	public function MetaJoin($chat)
	{
        return $this->exec('channel_join '.$this->escapePeer($chat));
	}
	public function MetaSetAbout($chat,$text)
	{
        return $this->exec('channel_set_about '.$this->escapePeer($chat).' '.$this->escapePeer($text));
	}
	public function MetaSetAdmin($chat,$peer,$type=2)
	{
        return $this->exec('channel_set_admin '.$this->escapePeer($chat).' '.$this->escapePeer($peer).' '.$type);
	}
	public function MetaSetUsername($chat,$text)
	{
        return $this->exec('channel_set_username '.$this->escapePeer($chat).' '.$this->escapePeer($text));
	}
	public function MetaSetPhoto($chat,$file)
	{
        return $this->exec('channel_set_photo '.$this->escapePeer($chat).' '.$file);
	}
	public function MetaCreate($chat,$text)
	{
        return $this->exec('create_channel '.$this->escapePeer($chat).' '.$this->escapePeer($text));
	}
	public function MetaUpgrade($chat)
	{
        return $this->exec('chat_upgrade '.$this->escapePeer($chat));
	}
	public function MetaLinkGen($chat)
	{
        return $this->exec('export_channel_link '.$this->escapePeer($chat));
	}
	public function ChatLinkGen($chat)
	{
        return $this->exec('export_chat_link '.$this->escapePeer($chat));
	}
	public function MetaRename($chat,$text)
	{
        return $this->exec('rename_channel '.$this->escapePeer($chat).' '.$this->escapePeer($text));
	}
	public function MetaLinkJoin($link)
	{
        return $this->exec('import_channel_link '.$this->escapePeer($link));
	}
	public function ChatLinkJoin($link)
	{
        return $this->exec('import_chat_link '.$this->escapePeer($link));
	}
	public function FindUsername($username)
	{
        return $this->exec('resolve_username '.$this->escapePeer($username));
	}
	public function MetaSendMessage($chat,$text)
	{
        return $this->exec('post '.$this->escapePeer($chat).' '.$text);
	}
	public function MetaSendPhoto($chat,$file,$caption="")
	{
        return $this->exec('post_photo '.$this->escapePeer($chat).' '.$file.' '.$caption);
	}
	public function MetaSendVideo($chat,$file,$caption="")
	{
        return $this->exec('post_video '.$this->escapePeer($chat).' '.$file.' '.$caption);
	}
	public function MetaSendFile($chat,$file)
	{
        return $this->exec('post_file '.$this->escapePeer($chat).' '.$file);
	}
	public function MetaSendDocument($chat,$file)
	{
        return $this->exec('post_document '.$this->escapePeer($chat).' '.$file);
	}
	public function MetaSendLocation($chat,$latitude,$longitude)
	{
        return $this->exec('post_location '.$this->escapePeer($chat).' '.$latitude.' '.$longitude);
	}
	public function DelMsg($msgid)
	{
        return $this->exec('delete_msg '.$msgid);
	}
}