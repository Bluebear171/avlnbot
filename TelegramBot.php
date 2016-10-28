<?php
abstract class TelegramBotCore {
    protected $host;
    protected $port;
    protected $apiUrl;
    public    $botId;
    public    $botUsername;
    protected $botToken;

    protected $username;
    protected $username_len;

    protected $handle;
    protected $inited = false;
    protected $lpDelay = 1;
    protected $netDelay = 1;
    protected $updatesOffset = false;
    protected $updatesLimit = 100;
    protected $updatesTimeout = 9;
    protected $netTimeout = 10;
    protected $netConnectTimeout = 5;

    protected $checkPointTimeStamp;
    protected $currentTime;
    protected $isMaintenance;

    public $redis;

    public function __construct($token, $options = array()) {
        $options += array(
            'host' => 'api.telegram.org',
            'port' => 443,
        );
        $this->host = $host = $options['host'];
        $this->port = $port = $options['port'];
        $this->botToken = $token;
        $proto_part = ($port == 443 ? 'https' : 'http');
        $port_part = ($port == 443 || $port == 80) ? '' : ':'.$port;
        $this->apiUrl = "{$proto_part}://{$host}{$port_part}/bot{$token}";
    }
    public function init() {
        if ($this->inited) {
            echo "already been inited";
            return true;
        }

        $this->inited = true;

        // if want to change the status, request
        $envMaintenance = getenv('MAINTENANCE');
        if ($envMaintenance == 0) {
            $this->isMaintenance = false;
        }
        else {
            $this->isMaintenance = true;
        }

//        echo $this->apiUrl;

        // Init the CURL
        $this->handle = curl_init();

        // check GETME Request
        $response = $this->request('getMe');
        if (!$response['ok']) {
            throw new Exception("Can't connect to server");
        }
        $bot = $response['result'];

        $this->botId = $bot['id'];
        $this->botUsername = $bot['username'];
        $this->username = strtolower('@'.$this->botUsername);
        $this->username_len = strlen($this->username);

        $params = array(
            'limit' => $this->updatesLimit,
            'timeout' => $this->updatesTimeout,
        );
        if ($this->updatesOffset) {
            $params['offset'] = $this->updatesOffset;
        }
        $options = array(
            'timeout' => $this->netConnectTimeout + $this->updatesTimeout + 2,
        );
        $response = $this->request('getUpdates', $params, $options);
        if ($response['ok']) {
            $updates = $response['result'];
            if (is_array($updates) && count($updates) > 0) {
                $update = end($updates);
                $this->updatesOffset = $update['update_id'] + 1;
                $params['offset'] = $this->updatesOffset;
                $this->request('getUpdates', $params, $options);
            }
        }

        $this->dbInit();

        return true;
    }

    public function dbInit() {
        if (!$this->redis) {
            \Predis\Autoloader::register();
            $this->redis = new Predis\Client(getenv('REDIS_URL'));
        }
    }

    public function runLongpoll() {
        $this->init();

        // this function can run once in application
        $connExist = file_exists("connc.txt");
        if ($connExist) {
            // change to 0
            $connfile = fopen("connc.txt", "r") or die("Unable to open file!");
            $connFlag = fread($connfile,1); // read 1 byte
            fclose($connfile);
            if ($connFlag == 1) {
                echo "already running";
            }
            else {
                $connfile = fopen("connc.txt", "w") or die("Unable to open file!");
                fwrite($connfile,"1");
                fclose($connfile);

                // start longpoll add the timestamp
                if (Constant::$DEVELOPMENT) {
                    $file = 'devlongpolllog.txt';
                    $text = date("Y-m-d h:i:sa", strtotime("+5 hours"))."\n";
                }
                else {
                    $text = date("Y-m-d h:i:sa", strtotime("+9 hours"))."\n";
                    $file = 'longpolllog.txt';
                }
                // Write the contents to the file,
                // using the FILE_APPEND flag to append the content to the end of the file
                // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
                file_put_contents($file, $text, FILE_APPEND | LOCK_EX);

                $this->longpoll();
            }
        }
        else {
            echo "connc.txt does not exist.";
        }
    }
    public function setWebhook($url) {
        $this->init();
        $result = $this->request('setWebhook', array('url' => $url));
        return $result['ok'];
    }
    public function removeWebhook() {
        $this->init();
        $result = $this->request('setWebhook', array('url' => ''));
        return $result['ok'];
    }

    // function to get response by requesting to telegram API
    public function request($method, $params = array(), $options = array()) {
        $options += array(
            'http_method' => 'GET',
            'timeout' => $this->netTimeout,
        );
        $params_arr = array();
        foreach ($params as $key => &$val) {
            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
            $params_arr[] = urlencode($key).'='.urlencode($val);
        }
        $query_string = implode('&', $params_arr);
        $url = $this->apiUrl.'/'.$method;
        if (Constant::$DEVELOPMENT) {
            echo "<br /> <br />" . $url . ($query_string ? '?' . $query_string : '');
        }
        if ($options['http_method'] === 'POST') {
            curl_setopt($this->handle, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($this->handle, CURLOPT_POST, true);
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, $query_string);
        } else {
            $url .= ($query_string ? '?'.$query_string : '');
            curl_setopt($this->handle, CURLOPT_HTTPGET, true);
        }
        $connect_timeout = $this->netConnectTimeout;
        $timeout = $options['timeout'] ?: $this->netTimeout;
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_VERBOSE, true);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, $timeout);
        $response_str = curl_exec($this->handle);
        $errno = curl_errno($this->handle);
        $http_code = intval(curl_getinfo($this->handle, CURLINFO_HTTP_CODE));

        if ($http_code == 401) {
            throw new Exception('Invalid access token provided');
        } else if ($http_code >= 500 || $errno) {
            sleep($this->netDelay);
            if ($this->netDelay < 30) {
                $this->netDelay *= 2;
            }
        };
        if (Constant::$DEVELOPMENT) {
            echo "<br/> " . $response_str . "<br/> ";
        }
        $response = json_decode($response_str, true);
        return $response;
    }
    protected function longpoll() {
        $this->currentTime = time();
        // to clean up
        $this->onCheckInterval();

        $params = array(
            'limit' => $this->updatesLimit,
            'timeout' => $this->updatesTimeout,
        );
        if ($this->updatesOffset) {
            $params['offset'] = $this->updatesOffset;
        }
        $options = array(
            'timeout' => $this->netConnectTimeout + $this->updatesTimeout + 2,
        );
        $response = $this->request('getUpdates', $params, $options);
        if ($response['ok']) {
            $updates = $response['result'];
            if (is_array($updates)) {
                foreach ($updates as $update) {
                    $this->updatesOffset = $update['update_id'] + 1;
                    $this->onUpdateReceived($update);
                }
            }
        }

        $this->onCheckTimer();
        $this->sendBulkMessage();

        // condition to stop the service
        $myfile = fopen("f.txt", "r") or die("Unable to open file!");
        $stopFlag = fread($myfile,1); // read 1 byte
        fclose($myfile);
        if ($stopFlag > 0) {
            // change to 0
            $connfile = fopen("connc.txt", "w") or die("Unable to open file!");
            fwrite($connfile, "0");
            fclose($connfile);
            die ("<br /><br />  service terminated by file f changed");
        }
        $this->longpoll();
    }

    public function onCheckInterval(){
        if ($this->checkPointTimeStamp == 0) {
            $this->checkPointTimeStamp = $this->currentTime;
        }
        else {
            // if already has value,
            $timeDiff = $this->currentTime - $this->checkPointTimeStamp;
            if ($timeDiff >= 900) { // 5 minute
                $this->checkPointTimeStamp = $this->currentTime;
                $this->cleanUpChatInstances();
                // garbage unneeded chat instances
            }
            // else do nothing
        }
    }

    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    abstract public function onUpdateReceived($update);
    abstract public function onCheckTimer();
    abstract public function sendBulkMessage();
    abstract public function cleanUpChatInstances();
}


class TelegramBot extends TelegramBotCore {
    protected $chatClass;
    protected $chatInstances = array();
    public function __construct($token, $chat_class, $options = array()) {
        parent::__construct($token, $options);
        $instance = new $chat_class($this, 0);
        if (!($instance instanceof TelegramBotChat)) {
            throw new Exception('ChatClass must be extends TelegramBotChat');
        }
        $this->chatClass = $chat_class;
    }
    public function onUpdateReceived($update) {
        if (Constant::$DEVELOPMENT) {
            echo "<br/> Update received <br/> ";
            print_r($update);
        }
        // if there is message
        if (isset($update['message'])) { // message is the main body
            $message = $update['message'];
            $chat_id = $message['chat']['id'];
            if ($chat_id) {
                $chat = $this->getChatInstance($chat_id);
                if (isset($message['group_chat_created'])) {
                    $chat->bot_added_to_chat($message);
                } else if (isset($message['new_chat_participant'])) {
                    if ($message['new_chat_participant']['id'] == $this->botId) {
                        $chat->bot_added_to_chat($message);
                    }
                } else if (isset($message['left_chat_participant'])) {
                    if ($message['left_chat_participant']['id'] == $this->botId) {
                        // do nothing
                        $chat->bot_kicked_from_chat($message);
                    }
                } else {
                    $text = trim($message['text']);
                    if (strtolower(substr($text, 0, $this->username_len)) == $this->username) {
                        $text = trim(substr($text, $this->username_len));
                    }
                    if (preg_match('/^(?:\/([a-z0-9_]+)(@[a-z0-9_]+)?(?:\s+(.*))?)$/is', $text, $matches)) {
                        $command = $matches[1];
                        $command_owner = false;
                        if (isset($matches[2])) {
                            $command_owner = strtolower($matches[2]);
                        }
                        $command_params = false;
                        if (isset($matches[3])) {
                            $command_params = strtolower($matches[3]);
                        }

                        if (!$command_owner || $command_owner == $this->username) {
                            if ($this->isMaintenance ) {
                                $chat->sendMaintenance($command_params, $message);
                            }
                            else {
                                $method = 'command_'.$command;
                                if (method_exists($chat, $method)) {
                                    $chat->$method($command_params, $message);
                                }
                            }

                            // other command is unsupported
                            //else {
                            //    $chat->some_command($command, $command_params, $message);
                            //}
                        }
                        else{
                            if (Constant::$DEVELOPMENT) {
                                echo "have command owner but owner is different";
                            }
                        }
                    } else {
//                        $chat->message($text, $message);
                        if (Constant::$DEVELOPMENT) {
                            echo "not command";
                        }
                    }
                }
            }
        }
        elseif (isset($update['callback_query'])) { //callback_query is the body
            $callbackQuery = $update['callback_query'];
            $callbackData = $callbackQuery['data'];
            list($chat_id, $data) = explode(":", $callbackData);
            if ($chat_id && $data) {
                $chat = $this->getChatInstance((float)$chat_id);
                $messageID = $callbackQuery['message']['message_id'];
                $from = $callbackQuery["from"];
                $chat->callback($messageID, $from, $data);
            }
        }
    }

    public function cleanUpChatInstances(){
        foreach ($this->chatInstances as $key=>$value) {
            if ($this->chatInstances[$key] instanceof AvalonBotChat) {
                if ($this->chatInstances[$key]->getGameStatus() == Constant::NOT_CREATED) {
                    unset($this->chatInstances[$key]);
                }
            }
        }
    }
    public function onCheckTimer() {
        foreach ($this->chatInstances as $chatInstance) {
            $chatInstance->checkTimer();
        }
    }

    public function sendBulkMessage() {
        foreach ($this->chatInstances as $chatInstance) {
            $chatInstance->sendBulkMessage();
        }
    }

    protected function getChatInstance($chat_id) {
        if (!isset($this->chatInstances[$chat_id])) {
            $instance = new $this->chatClass($this, $chat_id);
            $this->chatInstances[$chat_id] = $instance;
            $instance->init();
        }
        return $this->chatInstances[$chat_id];
    }

}

abstract class TelegramBotChat {
    protected $core;
    protected $chatId;
    protected $isGroup;
    private $texts;
    public function __construct($core, $chat_id) {
        if (!($core instanceof TelegramBot)) {
            throw new Exception('$core must be TelegramBot instance');
        }
        $this->core = $core;
        $this->chatId = $chat_id;
        $this->isGroup = $this->startsWith($chat_id, "-");
        $this->texts = array();
    }

    // http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
    private function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    private function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
    public function init() {}
    public function bot_added_to_chat($message) {}
    public function bot_kicked_from_chat($message) {}
//public function command_commandname($params, $message) {}
    public function some_command($command, $params, $message) {}
    public function message($text, $message) {}

    public function sendBulkMessage(){
        if (count($this ->texts) == 0) {
            return null;
        }
        $bulkText = "";
        foreach ($this->texts as $text) {
            $bulkText .= $text . "\n\n";
        }
        $params = array(
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'chat_id' => $this->chatId,
            'text' => $bulkText,
        );
        $response = $this->core->request('sendMessage', $params);
        $this->texts = array();
        return $response;
    }

    protected function apiSendMessage($text, $params = array()) {
        array_push($this->texts,$text);
//        $params += array(
//            'parse_mode' => 'HTML',
//            'disable_web_page_preview' => true,
//            'chat_id' => $this->chatId,
//            'text' => $text,
//        );
//        return $this->core->request('sendMessage', $params);
    }

    protected function apiSendMessageDirect($text, $params = array()) {
        $params += array(
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'chat_id' => $this->chatId,
            'text' => $text,
        );
        return $this->core->request('sendMessage', $params);
    }

    protected function apiSendMessageToTarget($text, $target_id, $params = array()) {
        $params += array(
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'chat_id' => $target_id,
            'text' => $text,
        );
        return $this->core->request('sendMessage', $params);
    }
    protected function apiHideInlineKeyboard($messageID, $target_id, $params = array()) {
        $params += array(
            'chat_id' => $target_id,
            'message_id' => $messageID,
        );
        return $this->core->request('editMessageReplyMarkup', $params);
    }

    protected function getStatusMember($target_id) {
        $params = array(
            'chat_id' => $this->chatId,
            'user_id' => $target_id,
        );
        $response = $this->core->request('getChatMember', $params);
        if ($response["ok"]){
            return $response["result"]["status"];
        }
        else return null;
    }

    protected function getChatTitle() {
        $params = array(
            'chat_id' => $this->chatId,
        );
        $response = $this->core->request('getChat', $params);
        if ($response["ok"]){
            return $response["result"]["title"];
        }
        else return null;
    }

    protected function apiEditMessageText($text, $messageID, $target_id, $params = array()) {
        $params += array(
            'text' => $text,
            'chat_id' => $target_id,
            'message_id' => $messageID,
        );
        if ($this instanceof AvalonBotChat) {
            unset( $this->players[$target_id][Constant::LAST_MESSAGE_ID] );
        }
        return $this->core->request('editMessageText', $params);
    }
}