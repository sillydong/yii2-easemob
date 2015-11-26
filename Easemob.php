<?php
/**
 * Project: basic
 * User: chenzhidong
 * Date: 15/11/26
 * Time: 08:54
 */
namespace sillydong\easemob;

use sillydong\http\Client;
use sillydong\http\Request;
use Yii;
use yii\base\Component;
use yii\base\Exception;

class Easemob extends Component {
    protected $client_id;
    protected $client_secret;
    protected $org_name;
    protected $app_name;
    protected $url;
    protected $cachekey;

    const TYPE_USERS = "users";
    const TYPE_GROUPS = "chatgroups";
    const TYPE_ROOTS = "chatrooms";

    function __construct($client_id, $client_secret, $org_name, $app_name, $config = []) {
        parent::__construct($config);

        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->org_name = $org_name;
        $this->app_name = $app_name;

        $this->url = 'https://a1.easemob.com/' . $this->org_name . '/' . $this->app_name . '/';
        $this->cachekey = 'easemob_token_' . $this->client_id;
    }

    public function refreshSign() {
        Yii::$app->cache->delete($this->cachekey);
    }

    public function openRegist($username, $password) {
        $url = $this->url . 'users';
        $post = [
            'username' => $username,
            'password' => $password,
        ];
        $request = new Request('POST', $url, $this->commonHeader(), json_encode($post));

        return $this->request($request, 'easemob_openregist');
    }

    public function authorizedRegist($username, $password) {
        $url = $this->url . 'users';
        $post = [
            'username' => $username,
            'password' => $password,
        ];
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_authorizedregist');
    }

    public function batchRegist($users) {
        $url = $this->url . 'users';
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($users));

        return $this->request($request, 'easemob_batchregist');
    }

    public function getUser($username) {
        $url = $this->url . 'users/' . urlencode($username);
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getuser');
    }

    public function getUsers($cursor = "", $limit = 10) {
        $url = $this->url . 'users';
        $params = [];
        if ($cursor)
            $params['cursor'] = $cursor;
        if ($limit)
            $params['limit'] = intval($limit);
        $request = new Request('GET', $url . '?' . http_build_query($params), $this->authorizedHeader());

        return $this->request($request, 'easemob_getusers');
    }

    public function deleteUser($username) {
        $url = $this->url . 'users/' . urlencode($username);
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deleteuser');
    }

    public function deleteUsers($limit) {
        $url = $this->url . 'users?limit=' . intval($limit);
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deleteusers');
    }

    public function resetPassword($username, $password) {
        $url = $this->url . 'users/' . urlencode($username) . '/password';
        $request = new Request('PUT', $url, $this->authorizedHeader(), json_encode(['newpassword' => $password]));

        return $this->request($request, 'easemob_resetpassword');
    }

    public function resetNickname($username, $nickname) {
        $url = $this->url . 'users/' . urlencode($username);
        $request = new Request('PUT', $url, $this->authorizedHeader(), json_encode(['nickname' => $nickname]));

        return $this->request($request, 'easemob_resetnickname');
    }

    public function addFriend($owner, $friend) {
        $url = $this->url . 'users/' . urlencode($owner) . '/contacts/users/' . urlencode($friend);
        $request = new Request('POST', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_addfriend');
    }

    public function deleteFriend($owner, $friend) {
        $url = $this->url . 'users/' . urlencode($owner) . '/contacts/users/' . urlencode($friend);
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletefriend');
    }

    public function getFriends($owner) {
        $url = $this->url . 'users/' . urlencode($owner) . '/contacts/users';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getfriends');
    }

    public function getBlacklist($owner) {
        $url = $this->url . 'users/' . urlencode($owner) . '/blocks/users';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getblacklist');
    }

    public function addBlacklistUser($owner, $users) {
        $url = $this->url . 'users/' . urlencode($owner) . '/blocks/users';
        $post = ['usernames' => $users];
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_addblacklist');
    }

    public function deleteBlacklistUser($owner, $username) {
        $url = $this->url . '/users/' . urlencode($owner) . '/blocks/users/' . urlencode($username);
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deleteblacklist');
    }

    public function userStatus($username) {
        $url = $this->url . 'users/' . $username . '/status';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_userstatus');
    }

    public function getOfflineMsgCount($owner) {
        $url = $this->url . 'users/' . $owner . '/offline_msg_count';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getofflinemsgcount');
    }

    public function getOfflineMsgStatus($owner, $msgid) {
        $url = $this->url . 'users/' . $owner . '/offline_msg_status/' . $msgid;
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getofflinemsgstatus');
    }

    public function deactivateUser($username) {
        $url = $this->url . 'users/' . $username . '/deactivate';
        $request = new Request('POST', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deactivateuser');
    }

    public function activateUser($username) {
        $url = $this->url . 'users/' . $username . '/activate';
        $request = new Request('POST', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_activateuser');
    }

    public function disconnectUser($username) {
        $url = $this->url . 'users/' . $username . '/disconnect';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_disconnectuser');
    }

    public function chatMessages($ltgt, $timestamp, $cursor = "", $limit = 10) {
        $url = $this->url . 'chatmessages';
        $params = [];
        if ($cursor)
            $params['cursor'] = $cursor;
        if ($limit)
            $params['limit'] = $limit;
        $params['ql'] = str_replace(' ', '+', "select * where timestamp " . $ltgt . " " . intval($timestamp));
        $request = new Request('GET', $url . '?' . http_build_query($params), $this->authorizedHeader());

        return $this->request($request, 'easemob_chatmessages');
    }

    public function upload($filepath) {
        if (!file_exists($filepath))
            throw new Exception('file(' . $filepath . ') not found');
        $url = $this->url . 'chatfiles';
        $post = ['file' => '@' . $filepath];
        $header = ["Authorization" => 'Bearer ' . $this->sign(false), "restrict-access" => true, 'Content-Type' => 'multipart/form-data'];
        $request = new Request('POST', $url, $header, $post);

        return $this->request($request, 'easemob_upload');
    }

    public function download($uuid, $sharesecret) {
        $url = $this->url . 'chatfiles/' . $uuid;
        $header = ["Authorization" => 'Bearer ' . $this->sign(false), "share-secret" => $sharesecret, 'Accept' => 'application/octet-stream'];
        $request = new Request('GET', $url, $header);

        return $this->request($request, 'easemob_download');
    }

    public function thumb($uuid, $sharesecret) {
        $url = $this->url . 'chatfiles/' . $uuid;
        $header = ["Authorization" => 'Bearer ' . $this->sign(false), "share-secret" => $sharesecret, 'Accept' => 'application/octet-stream', 'thumbnail' => true];
        $request = new Request('GET', $url, $header);

        return $this->request($request, 'easemob_download');
    }

    public function sendTxtMsg($type, $from, $targets, $msg, $ext) {
        $content = [
            'type' => 'txt',
            'msg' => $msg
        ];

        return $this->sendMsg($type, $from, $targets, $content, $ext);
    }

    public function sendImgMsg($type, $from, $targets, $url, $filename, $secret, $width, $height, $ext) {
        $content = [
            'type' => 'img',
            'url' => $url,
            'filename' => $filename,
            'secret' => $secret,
            'size' => ['width' => $width, 'height' => $height]
        ];

        return $this->sendMsg($type, $from, $targets, $content, $ext);
    }

    public function sendAudioMsg($type, $from, $targets, $url, $filename, $secret, $length, $ext) {
        $content = [
            'type' => 'audio',
            'url' => $url,
            'filename' => $filename,
            'secret' => $secret,
            'length' => $length
        ];

        return $this->sendMsg($type, $from, $targets, $content, $ext);
    }

    public function sendVideoMsg($type, $from, $targets, $url, $secret, $filename, $length, $file_length, $thumb, $thumb_secret, $ext) {
        $content = [
            'type' => 'video',
            'filename' => $filename,
            'length' => $length,
            'file_length' => $file_length,
            'url' => $url,
            'secret' => $secret,
            'thumb' => $thumb,
            'thumb_secret' => $thumb_secret
        ];

        return $this->sendMsg($type, $from, $targets, $content, $ext);
    }

    public function sendCmdMsg($type, $from, $targets, $action, $ext) {
        $content = [
            'type' => 'cmd',
            'action' => $action
        ];

        return $this->sendMsg($type, $from, $targets, $content, $ext);
    }

    protected function sendMsg($type, $from, $targets, $msg, $ext) {
        if (!is_array($targets))
            $targets = [$targets];
        $url = $this->url . 'messages';
        $post = [
            'target_type' => $type,
            'target' => $targets,
            'msg' => $msg,
            'from' => $from,
        ];
        if ($ext)
            $post['ext'] = $ext;
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_sendmsg_' . $msg['type']);
    }

    public function getGroups($cursor = "", $limit = 10) {
        $url = $this->url . 'chatgroups';
        $params = [];
        if ($cursor)
            $params['cursor'] = $cursor;
        if ($limit)
            $params['limit'] = $limit;
        $request = new Request('GET', $url . '?' . http_build_query($params), $this->authorizedHeader());

        return $this->request($request, 'easemob_getgroups');
    }

    public function getGroupsDetail($groups) {
        if (is_array($groups))
            $groups = implode(',', $groups);
        $url = $this->url . 'chatgroups/' . $groups;
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getgroupsdetail');
    }

    public function addGroup($groupname, $desc, $public, $maxusers, $approval, $owner, $members) {
        $url = $this->url . 'chatgroups';
        $post = [
            'groupname' => $groupname,
            'desc' => $desc,
            'public' => $public,
            'maxusers' => $maxusers,
            'approval' => $approval,
            'owner' => $owner,
            'members' => is_array($members) ? $members : [$members]
        ];
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_addgroup');
    }

    public function setGroup($groupname, $desc, $maxusers) {
        $url = $this->url . 'chatgroups';
        $post = [
            'groupname' => $groupname,
            'desc' => $desc,
            'maxusers' => $maxusers,
        ];
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_setgroup');
    }

    public function deleteGroup($groupid) {
        $url = $this->url . 'chatgroups/' . $groupid;
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletegroup');
    }

    public function getGroupUsers($groupid) {
        $url = $this->url . 'chatgroups/' . $groupid . '/users';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getgroupusers');
    }

    public function addGroupUser($groupid, $username) {
        $url = $this->url . 'chatgroups/' . $groupid . '/users/' . $username;
        $request = new Request('POST', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_addgroupuser');
    }

    public function addGroupUsers($groupid, $usernames) {
        $url = $this->url . 'chatgroups/' . $groupid . '/users';
        $post = [
            'usernames' => $usernames
        ];
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_addgroupusers');
    }

    public function deleteGroupUser($groupid, $username) {
        $url = $this->url . 'chatgroups/' . $groupid . '/users/' . $username;
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletegroupuser');
    }

    public function deleteGroupUsers($groupid, $usernames) {
        $url = $this->url . 'chatgroups/' . $groupid . '/users';
        $post = [
            'usernames' => $usernames
        ];
        $request = new Request('DELETE', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_deletegroupusers');
    }

    public function getUserGroups($username) {
        $url = $this->url . 'users/' . $username . '/joined_chatgroups';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getusergroups');
    }

    public function changeGroupOwner($groupid, $owner) {
        $url = $this->url . 'chatgroups/' . $groupid;
        $post = [
            'newowner' => $owner
        ];
        $request = new Request('PUT', $url, $this->authorizedHeader(), $post);

        return $this->request($request, 'easemob_changegroupowner');
    }

    public function getGroupBlacklist($groupid) {
        $url = $this->url . 'chatgroups/' . $groupid . '/blocks/users';

        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getgroupblacklist');
    }

    public function addGroupBlacklistUser($groupid, $username) {
        $url = $this->url . 'chatgroups/' . $groupid . '/blocks/users/' . $username;
        $request = new Request('POST', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_addgroupblacklist');
    }

    public function addGroupBlacklistUsers($groupid, $usernames) {
        $url = $this->url . 'chatgroups/' . $groupid . '/blocks/users';
        $post = [
            'usernames' => $usernames
        ];
        $request = new Request('POST', $url, $this->authorizedHeader(), $post);

        return $this->request($request, 'easemob_addgroupblacklistusers');
    }

    public function deleteGroupBlacklistUser($groupid, $username) {
        $url = $this->url . 'chatgroups/' . $groupid . '/blocks/users/' . $username;
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletegroupblacklistuser');
    }

    public function deleteGroupBlacklistUsers($groupid, $usernames) {
        if (!is_array($usernames))
            $usernames = [$usernames];
        $url = $this->url . 'chatgroups/' . $groupid . '/blocks/users/' . implode(',', $usernames);
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletegroupblacklistusers');
    }

    public function addChatroom($name, $description, $maxusers, $owner, $members) {
        $url = $this->url . 'chatrooms';
        if (!is_array($members))
            $members = [$members];
        $post = [
            'name' => $name,
            'description' => $description,
            'maxusers' => $maxusers,
            'owner' => $owner,
            'members' => $members
        ];

        $request = new Request('POST', $url, $this->authorizedHeader(), $post);

        return $this->request($request, 'easemob_addchatroom');
    }

    public function setChatroom($chatroomid, $name, $description, $maxusers) {
        $url = $this->url . 'chatrooms/' . $chatroomid;
        $post = [
            'name' => $name,
            'description' => $description,
            'maxusers' => $maxusers
        ];

        $request = new Request('PUT', $url, $this->authorizedHeader(), $post);

        return $this->request($request, 'easemob_setchatroom');
    }

    public function deleteChatroom($chatroomid) {
        $url = $this->url . 'chatrooms/' . $chatroomid;

        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletechatroom');
    }

    public function getChatrooms() {
        $url = $this->url . 'chatrooms';

        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getchatrooms');
    }

    public function getChatroomDetail($chatroomid) {
        $url = $this->url . 'chatrooms/' . $chatroomid;

        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getchatroomdetail');
    }

    public function getUserChatrooms($username) {
        $url = $this->url . 'users/' . $username . '/joined_chatrooms';
        $request = new Request('GET', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_getuserchatrooms');
    }

    public function addChatroomUser($chatroomid, $username) {
        $url = $this->url . 'chatrooms/' . $chatroomid . '/users/' . $username;
        $request = new Request('POST', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_addchatroomuser');
    }

    public function addChatroomUsers($chatroomid, $usernames) {
        $url = $this->url . 'chatrooms/' . $chatroomid . '/users/';
        $post = [
            'usernames' => $usernames
        ];
        $request = new Request('POST', $url, $this->authorizedHeader(), json_encode($post));

        return $this->request($request, 'easemob_addchatroomusers');
    }

    public function deleteChatroomUser($chatroomid, $username) {
        $url = $this->url . 'chatrooms/' . $chatroomid . '/users/' . $username;
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletechatroomuser');
    }

    public function deleteChatroomUsers($chatroomid, $usernames) {
        if (!is_array($usernames))
            $usernames = [$usernames];
        $url = $this->url . 'chatrooms/' . $chatroomid . '/users/' . implode(',', $usernames);
        $request = new Request('DELETE', $url, $this->authorizedHeader());

        return $this->request($request, 'easemob_deletechatroomuser');
    }

    protected function request($request, $category) {
        $response = Client::sendRequest($request);
        if ($response->ok()) {
            $result = json_decode($response->get_body(), true);
            if ($result) {
                return $result;
            }
            else {
                Yii::error($response->get_body(), $category);
            }
        }
        else {
            Yii::error($response->get_error(), $category);
        }

        return null;
    }

    protected function commonHeader() {
        return ["Content-Type" => 'application/json'];
    }

    protected function authorizedHeader($refresh = false) {
        return ["Content-Type" => 'application/json', "Authorization" => 'Bearer ' . $this->sign($refresh)];
    }

    protected function sign($refresh = false) {
        if (!Yii::$app->cache->exists($this->cachekey) || $refresh) {
            $url = $this->url . 'token';
            $post = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            ];
            $response = Client::post($url, json_encode($post), $this->commonHeader());
            if ($response->ok()) {
                $result = json_decode($response->get_body(), true);
                if ($result && isset($result['access_token'])) {
                    Yii::$app->cache->set($this->cachekey, $result['access_token'], $result['expires_in'] - 100);

                    return $result['access_token'];
                }
                else {
                    Yii::error($response->get_body(), 'easemob_token');
                }
            }
            else {
                Yii::error($response->get_error(), 'easemob_token');
            }

            return "";
        }
        else {
            return Yii::$app->cache->get($this->cachekey);
        }
    }
}
