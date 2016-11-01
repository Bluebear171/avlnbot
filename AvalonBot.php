<?php
require_once 'TelegramBot.php';
require_once 'Constant.php';
require_once 'Script.php';

require 'vendor/predis/predis/autoload.php';

class AvalonBot extends TelegramBot {

    public function init() {
        parent::init();
        Constant::init();
        Script::init();
    }
}

class AvalonBotChat extends TelegramBotChat {

    protected $gameStatus = Constant::NOT_CREATED;
    // count(playerIDs) = number of players
    protected $playerIDs = array();
    // the array index is the player ID
    protected $players = array();
    // startTimeStamp, game status will determined its type
    protected $startTimeStamp = 0; // http://php.net/manual/en/function.time.php
    protected $flagRemind1; // xr1 sec has passed
    protected $flagRemind2; // xr2 sec has passed
    protected $playerCount;
    protected $randomizedRole;
    protected $kingTokenIndex;
    protected $ladyLakeTokenIndex;
    protected $rejectCountInQuest; // if already 5, fail the quest

    protected $mode;

    protected $all_bad_guys_id = array();

    // 0,1,2,3,4
    protected $currentQuestNumberStart0;

    // 0,1,2,3,4 -1:fail 0:not done 1:success
    protected $questStatus = array();

    // reset when doing assign person
    // 0... numassignmentQuest-1 value:assigneeID
    protected $questAssigneeIDs = array();
    protected $questAssigneeIDsHistory = array();

    // reset when doing exec quest
    // 0... numBacguysassignee
    // [ID] value: 0:abstain, -1= reject, 1:approve
    protected $badGuyAssigneeChoices = array();
    protected $fail_count_by_badguy;
    protected $success_count_by_badguy;

    protected $lady_of_the_lake_holderIDs = array();

    protected $assassinID;
    protected $merlinID;
    protected $oberonID;

    // reset when approve reject
    // [playerID] 0,1,..,numplayers-1 value:0:abstain 1:approve -1:reject
    protected $currentApproveReject = array();
    protected $rejectAssignCount;
    protected $approveAssigncount;

    protected $lang;

    protected $redis;
    protected $langScript;

    public function __construct($core, $chat_id) {
        parent::__construct($core, $chat_id);
        $this->redis = $this->core->redis;
    }

    public function init() {
        if ($this->redis instanceof Predis\Client) {
            $langKey = $this->chatId."_lang";
            $retry = 0;
            while ($retry < 2) {
                try {
                    $isLangExist = $this->redis->exists($this->chatId . "_lang");
                    if ($isLangExist) {
                        $this->lang = $this->redis->get($langKey);
                        $this->langScript = Script::$script[$this->lang];
                    } else {
                        $this->lang = "en"; // default is english
                        $this->langScript = Script::$script["en"];
                    }
                    break;
                } catch (Exception $e) {
                    $this->core->redis = false;
                    $this->core->dbInit();
                    $this->redis = $this->core->redis;
                }
                $retry++;
            };
        }
    }

    /**************************************************************************************
     * START
     * GAME COMMAND
     * *************************************************************************************
     */

    public function command_start($params, $message) {
        $this->startGameWithMode(Constant::MODE_NORMAL, $params, $message);
    }

    public function command_startchaos($params, $message) {
        $this->startGameWithMode(Constant::MODE_CHAOS, $params, $message);
    }

    public function startGameWithMode($mode, $params, $message) {
        if (!$this->isGroup) {
            $this->sendWarningOnlyGroup();
        } else {
            if ($this->gameStatus == Constant::NOT_CREATED){
                // SCRIPT
                // "Kamu telah membuat permainan baru - %s di grup %s.";
                $text = sprintf($this->langScript[Script::PR_NEWGAME],
                    Constant::getMode($mode),
                    $message["chat"]["title"]);

                $response = $this->apiSendMessageToTarget( $text ,
                    $message["from"]["id"]);
                if (!$response['ok']) {
                    // not ok --> cannot start the game
                    $this->sendStartMeFirstToGroup ($message["from"]);
                    return;
                }
                $this->mode = $mode;
                $this->gameStatus = Constant::CREATED;
                $this->playerIDs = array();
                $this->players = array();

                $this->startTimeStamp = $this->core->getCurrentTime();
                $this->clearFlagRemind();

                // check if player already exist,if not exist, add to array
                $sender_id = $message["from"]["id"];
                $this->addNewPlayer($message["from"]);
                $this->sendCreateSuccessToGroup($sender_id);

                if (Constant::$DEVELOPMENT) {
                    $message2["from"]["id"] = "215067238";
                    $message2["from"]["first_name"] = "testLiman";
//                    $message2["from"]["last_name"] = "ululu";
//                    $message2["from"]["username"] = "GdzAntoniusLiman";
                    $this->addNewPlayer($message2["from"]);
                    $this->sendJoinSuccessToGroup($message2["from"]["id"]);

                    $message3["from"]["id"] = "205647650";
                    $message3["from"]["first_name"] = "testRendy";
//                    $message3["from"]["last_name"] = "ululu";
//                    $message3["from"]["username"] = "LoneDreamer";
                    $this->addNewPlayer($message3["from"]);
                    $this->sendJoinSuccessToGroup($message3["from"]["id"]);

                    $message4["from"]["id"] = "254488963";
                    $message4["from"]["first_name"] = "testAmbros";
//                    $message4["from"]["last_name"] = "ululu";
//                    $message4["from"]["username"] = "Ambrosius_Hp";
                    $this->addNewPlayer($message4["from"]);
                    $this->sendJoinSuccessToGroup($message4["from"]["id"]);

                    $message5["from"]["id"] = "74264638";
                    $message5["from"]["first_name"] = "testAlfian";
//                    $message5["from"]["last_name"] = "ululu";
//                    $message5["from"]["username"] = "fiantinangon";
                    $this->addNewPlayer($message5["from"]);
                    $this->sendJoinSuccessToGroup($message5["from"]["id"]);

//                    $message6["from"]["id"] = "295076115";
//                    $message6["from"]["first_name"] = "testPaulana";
////                    $message6["from"]["last_name"] = "ululu";
////                    $message6["from"]["username"] = "Paulanakho";
//                    $this->addNewPlayer($message6["from"]);
//                    $this->sendJoinSuccessToGroup($message6["from"]["id"]);
//
//                    $message7["from"]["id"] = "291655534";
//                    $message7["from"]["first_name"] = "testHerman";
//////                    $message7["from"]["last_name"] = "ululu";
//////                    $message7["from"]["username"] = "chrono06";
//                    $this->addNewPlayer($message7["from"]);
//                    $this->sendJoinSuccessToGroup($message7["from"]["id"]);

//
//                    $message8["from"]["id"] = "248185104";
//                    $message8["from"]["first_name"] = "testLucy";
////                    $message8["from"]["last_name"] = "ululu";
////                    $message8["from"]["username"] = "arclaire";
//                    $this->addNewPlayer($message8["from"]);
//                    $this->sendJoinSuccessToGroup($message8["from"]["id"]);
//
//                    $message9["from"]["id"] = "1";
//                    $message9["from"]["first_name"] = "test9";
//                    $this->addNewPlayer($message9["from"]);
//                    $this->sendJoinSuccessToGroup($message9["from"]["id"]);
//
//                    $message10["from"]["id"] = "2";
//                    $message10["from"]["first_name"] = "test10";
//                    $this->addNewPlayer($message10["from"]);
//                    $this->sendJoinSuccessToGroup($message10["from"]["id"]);
                }
            }
        }
    }

    public function command_join($params, $message) {
        if (!$this->isGroup) {
            $this->sendWarningOnlyGroup();
        } else {
            if ($this->gameStatus == Constant::NOT_CREATED) {
                $this->sendCreateFirstToGroup();
            }
            if ($this->gameStatus == Constant::CREATED) {
                $sender_id = $message["from"]["id"];

                //check if already join
                if (in_array($sender_id, $this->playerIDs)) {
                    // already join, do nothing
                }
                else {
                    // check if already 10 players
                    $player_count = count($this->playerIDs);
                    // if less, then can join
                    if ($player_count < Constant::getMaxPlayer()) {
                        // check if player already exist,if not exist, add to array

                        // SCRIPT
                        // "Kamu telah bergabung Avalon di group %s.";
                        $text = sprintf($this->langScript[Script::PR_JOINGAME],
                            $message["chat"]["title"]);

                        $response = $this->apiSendMessageToTarget(
                            $text,
                            $message["from"]["id"]);
                        if (!$response['ok']) {
                            // not ok --> cannot start the game
                            $this->sendStartMeFirstToGroup ($message["from"]);
                            return;
                        }
                        $this->addNewPlayer($message["from"]);
                        $this->sendJoinSuccessToGroup($sender_id);
                    } else { // already full, cannot join anymore
                        $this->sendJoinFullToGroup($message["from"]);
                    }
                }

            }
        }
    }

    public function command_done($params, $message) {
        if ($this->isGroup) {
            if ($this->gameStatus == Constant::DISCUSS_BEFORE_ASSIGNING_QUEST) {
                $sender_id = $message["from"]["id"];

                // if the sender is king, then done the game status
                if (Constant::$DEVELOPMENT) {
                    $isCorrectSender = (
                        $sender_id == $this->playerIDs[$this->kingTokenIndex]
                        ||
                        $sender_id == "286457946");
                } else {
                    $isCorrectSender =
                        $sender_id == $this->playerIDs[$this->kingTokenIndex];
                }
                if ($isCorrectSender) {
                    $text = $this->langScript[Script::PU_KINGDONE];
                    $this->apiSendMessage($text);

                    $this->assignQuestPrivate();
                }
            }
        }
    }

    public function command_approve($params, $message) {
        if ($this->isGroup) {
            if ($this->gameStatus == Constant::EXEC_APPROVE_REJECT_QUEST_GROUP) {
                $sender_id = $message["from"]["id"];

                if (!in_array($sender_id, $this->playerIDs)) {
                    return;
                }
                // check if previously abstain,
                //      change to approve
                //      approve++;
                //      send message
                if (!isset($this->currentApproveReject[$sender_id])){
                    $this->currentApproveReject[$sender_id] = 1;
                    $this->approveAssigncount++;
                    // SCRIPT
                    // "%s setuju. Setuju <b>%d</b>. Menolak <b>%d</b>."
                    $text = sprintf($this->langScript[Script::PU_APPROVENEW],
                        $this->getPlayerIDFullNameString($sender_id),
                        $this->approveAssigncount,
                        $this->rejectAssignCount);
                    $this->apiSendMessage($text);
                }
                // else if previously reject
                //      change to approve
                //      reject --;
                //      approve++;
                //      send message
                else if ($this->currentApproveReject[$sender_id] == -1){
                    $this->currentApproveReject[$sender_id] = 1;
                    $this->approveAssigncount++;
                    $this->rejectAssignCount--;
                    // "%s mengganti jawaban menjadi setuju. Setuju <b>%d</b>. Menolak <b>%d</b>."
                    $text = sprintf($this->langScript[Script::PU_APPROVECHANGE],
                        $this->getPlayerIDFullNameString($sender_id),
                        $this->approveAssigncount,
                        $this->rejectAssignCount);
                    $this->apiSendMessage($text);
                }

                // count reject and approve
                if ($this->rejectAssignCount >= ($this->playerCount/2)){
                    $this->rejectCurrentQuest();
                }
                elseif($this->approveAssigncount > ($this->playerCount/2)){
                    $this->approveCurrentQuest();
                }
            }
        }
    }

    public function command_reject($params, $message) {
        if ($this->isGroup) {
            if ($this->gameStatus == Constant::EXEC_APPROVE_REJECT_QUEST_GROUP) {
                $sender_id = $message["from"]["id"];

                // check if previously abstain,
                //      change to reject
                //      reject++;
                //      send message
                if (!in_array($sender_id, $this->playerIDs)) {
                    return;
                }
                if (!isset($this->currentApproveReject[$sender_id])){
                    $this->currentApproveReject[$sender_id] = -1;
                    $this->rejectAssignCount++;
                    // "%s menolak. Setuju <b>%d</b>. Menolak <b>%d</b>."
                    $text = sprintf($this->langScript[Script::PU_REJECTNEW],
                        $this->getPlayerIDFullNameString($sender_id),
                        $this->approveAssigncount,
                        $this->rejectAssignCount);
                    $this->apiSendMessage($text);
                }
                // else if previously approve
                //      change to reject
                //      reject ++;
                //      approve--;
                //      send message
                else if ($this->currentApproveReject[$sender_id] == 1){
                    $this->currentApproveReject[$sender_id] = -1;
                    $this->approveAssigncount--;
                    $this->rejectAssignCount++;
                    // "%s mengganti jawaban menjadi menolak. Setuju <b>%d</b>. Menolak <b>%d</b>."
                    $text = sprintf($this->langScript[Script::PU_REJECTCHANGE],
                        $this->getPlayerIDFullNameString($sender_id),
                        $this->approveAssigncount,
                        $this->rejectAssignCount);
                    $this->apiSendMessage($text);
                }

                // count reject and approve
                if ($this->rejectAssignCount >= ($this->playerCount/2)){
                    $this->rejectCurrentQuest();
                }
                elseif($this->approveAssigncount > ($this->playerCount/2)){
                    $this->approveCurrentQuest();
                }
            }
        }
    }

    public function command_questhistory($params, $message) {
        if (!$this->isGroup) {
            $this->sendWarningOnlyGroup();
        } else {
            if ($this->gameStatus == Constant::NOT_CREATED) {
                $this->sendCreateFirstToGroup();
            }
            else {
                if (!isset($this->questStatus)) {
                    $this->sendBlankHistory();
                    return;
                }
                $failOrSuccessQuestCount = 0;
                for ($i=0; $i<5;$i++){
                    if ($this->questStatus[$i] != 0) {
                        $failOrSuccessQuestCount++;
                    }
                }
                if ($failOrSuccessQuestCount == 0){
                    $this->sendBlankHistory();
                }
                else {
                    $this->sendHistory();
                }
            }
        }
    }

    public function command_setlang($params, $message) {
        if (!$this->isGroup) {
            $this->sendSetLangToPrivate($message["from"]["id"]);
        } else {
            // check if all is admin
            if (isset ($message["chat"]["all_members_are_administrators"])
                && $message["chat"]["all_members_are_administrators"]){
                $isAdmin = true;
            }
            else {
                $status = $this->getStatusMember($message["from"]["id"]);
                if ($status == "creator" || $status == "administrator") {
                    $isAdmin = true;
                }
                else {
                    $isAdmin = false;
                }
            }
            if ($isAdmin) {
                $text = $this->langScript[Script::PU_CHCKPMTOCHGLANG];
                $this->apiSendMessage($text);
                $this->sendSetLangToPrivate($message["from"]["id"]);
            }
            else {
                $this->sendOnlyAdmin();
            }
        }
    }

    // view my stats
    public function command_stats($params, $message) {
        $this->sendPrivateStats($message["from"]);
    }

    // view global stats
    public function command_statsglobal($params, $message) {
        $this->sendGlobalStats();
    }

    // view global stats
    public function command_statsgroup($params, $message) {
        if ($this->isGroup){
            $this->sendGroupStats($message);
        }
        else {
            $this->sendWarningOnlyGroup();
        }
    }

    public function command_howtoplay($params, $message) {
        $this->sendHowToPlay();
    }
    public function command_merlin($params, $message) {
        $this->sendMerlin();
    }
    public function command_percival($params, $message) {
        $this->sendPercival();
    }
    public function command_servant($params, $message) {
        $this->sendServant();
    }
    public function command_mordred($params, $message) {
        $this->sendMordred();
    }
    public function command_morgana($params, $message) {
        $this->sendMorgana();
    }
    public function command_assassin($params, $message) {
        $this->sendAssassin();
    }
    public function command_oberon($params, $message) {
        $this->sendOberon();
    }
    public function command_morgassassin($params, $message) {
        $this->sendMorgassassin();
    }

    public function command_contact($params, $message) {
        $this->sendContact();
    }
    public function command_help($params, $message) {
        $this->sendHelp();
    }

    public function command_rateme($params, $message) {
        $this->rateMe();
    }

    public function bot_added_to_chat($message) {
        $this->sendHelp();
    }


    /***************************************************************************************
     * END
     * GAME COMMAND
     * *************************************************************************************
     */





    /***************************************************************************************
     * START INGAME FUNCTIONS
     * *************************************************************************************
     */

    // already count reject, reject is more or half the playercount
    public function rejectCurrentQuest(){
        $this->rejectCountInQuest++;
        if ($this->rejectCountInQuest == 5) {
            $this->failCurrentQuest();
        }
        else { // reject still less than 5, can continue this quest
            $prevKingID = $this->playerIDs[$this->kingTokenIndex];
            //change king to next
            $this->nextKing();

            // "Quest yang dipimpin oleh %s telah ditolak. quest dibatalkan dan king berpindah ke %s."
            $text = sprintf($this->langScript[Script::PU_REJECTCHANGEKING],
                $this->getPlayerIDString($prevKingID),
                $this->getPlayerIDString($this->playerIDs[$this->kingTokenIndex]));
            $this->apiSendMessage($text);
            // still in the same current quest
            $this->discussBeforeAssigningQuest();
        }
    }

    public function nextKing(){
        $this->kingTokenIndex++;
        if ($this->kingTokenIndex == $this->playerCount) {
            $this->kingTokenIndex = 0;
        }
    }

    public function discussBeforeAssigningQuest(){
        $this->gameStatus = Constant::DISCUSS_BEFORE_ASSIGNING_QUEST;

        //send Message to Group
        $text = $this->getBoardGameText();
        $kingPlayerID = $this->playerIDs[$this->kingTokenIndex];
        $personNeedToCurrentQuest = Constant::$quest[$this->playerCount][$this->currentQuestNumberStart0];
        // SCRIPT
        // "Sebelum menunjuk <b>%d orang</b>, %s sebagai raja boleh berdiskusi
        // dengan team.\nWaktu untuk berdiskusi adalah <b>%d detik</b>.
        // Raja boleh mengetik /done untuk mengakhiri diskusi.
        // Klik /questhistory untuk melihat history.";
        $text .= sprintf($this->langScript[Script::PU_KINGNEEDDISCUSS],
            $personNeedToCurrentQuest,
            $this->getPlayerIDString($kingPlayerID),
            Constant::$_discussAssignQuestGroup);
        $this->apiSendMessage($text);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
    }

    // approve is more
    public function approveCurrentQuest(){
        // SCRIPT
        // "Sebagian besar tim meng-approve, quest pun akan dijalankan!"
        $text = $this->langScript[Script::PU_AFTERAPPROVE];
        $this->apiSendMessage($text);
        $this->execQuestPrivate();
    }

    public function execQuestPrivate(){
        $this->badGuyAssigneeChoices = array();
        $this->fail_count_by_badguy = 0;
        $this->success_count_by_badguy = 0;
        $this->gameStatus = Constant::EXEC_QUEST_PRIVATE;

        foreach ($this->questAssigneeIDs as $questAssigneeID) {
            // bad guy quest assignee
            if ( in_array( $questAssigneeID , $this->all_bad_guys_id)){
                $this->badGuyAssigneeChoices[$questAssigneeID] = 0; // abstain

                // SCRIPT
                // "Quest ke-%d. Apa yang ingin kamu pilih?";
                $text = sprintf($this->langScript[Script::PR_EXECQUEST],
                    ($this->currentQuestNumberStart0+ 1) );
                if (Constant::$DEVELOPMENT) {
                    $text .= " " . $this->getPlayerIDString($questAssigneeID);
                }
                $params = array(
                    'reply_markup'=> array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    "text"=>$this->langScript[Script::PR_SUCCESS],
                                    "callback_data"=> $this->chatId.":1",
                                )
                            ),
                            array(
                                array(
                                    "text"=>$this->langScript[Script::PR_FAIL],
                                    "callback_data"=> $this->chatId.":-1",
                                )
                            )
                        )
                    ),
                );
                $response = $this->sendDEVMessageToPrivate($text, $questAssigneeID, $params);
                if ($response['ok']) {
                    // store messageID to be hidden later
                    $this->players[$questAssigneeID][Constant::LAST_MESSAGE_ID] =
                        $response["result"]["message_id"];
                }
            }
            // good guy quest assignee
            else {
                $text = $this->langScript[Script::PR_EXECQUESTGOOD];
                if (Constant::$DEVELOPMENT) {
                    $text .= " " . $this->getPlayerIDString($questAssigneeID);
                }
                $this->sendDEVMessageToPrivate($text,$questAssigneeID);
            }
        }

        $text = sprintf($this->langScript[Script::PR_GOFORQUEST],
            $this->playersToString($this->questAssigneeIDs),
            Constant::$_execQuestPrivate);
        $this->apiSendMessage($text);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
    }


    public function addNewPlayer($message_from){
        $sender_id = $message_from["id"];
        if (! in_array($sender_id, $this->playerIDs)) {
            array_push($this->playerIDs, $sender_id );
            $this->players[$sender_id]["first_name"] = $message_from["first_name"];
            if (isset($message_from["last_name"])) {
                $this->players[$sender_id]["last_name"] = $message_from["last_name"];
                $this->players[$sender_id]["full_name"] = $message_from["first_name"] . " " . $message_from["last_name"];
            }
            else {
                $this->players[$sender_id]["last_name"] = "";
                $this->players[$sender_id]["full_name"] = $message_from["first_name"];
            }
            if (isset($message_from["username"])) {
                $this->players[$sender_id]["username"] = $message_from["username"];
            }
        }
    }

    public function startGame(){
        $this->gameStatus = Constant::START_RANDOM_ROLES;
        // TODO will be random
//        $this->theme = 0;
//        $this->langScriptTheme = $this->langScript[$this->theme];
        $this->sendGameStartedToGroup();
        $this->assigningRandomRoles();
    }

    public function assigningRandomRoles(){
        $this->randomizedRole = Constant::generateRandomRoleArray($this->playerCount);

        // all bad guys see your eyes! (this is just to collect all bad guys)
        $this->all_bad_guys_id = array();
        $morgana_and_merlin_ids= array();
        for ($i=0 ; $i < $this->playerCount; $i++) {
            $playerID = $this->playerIDs[$i];
            $role = $this->randomizedRole[$i];
            // this is to link id with its role
            $this->players[$playerID][Constant::ROLE] = $role;
            $this->players[$playerID][Constant::INDEX] = $i;
            if (Constant::isGNBPlayer($role) == -1) {
                array_push($this->all_bad_guys_id, $playerID);
            }
            if ($role == Constant::MERLIN
                || $role == Constant::MORGANA
                || $role == Constant::MORGASSASSIN) {
                array_push($morgana_and_merlin_ids, $playerID);
            }
        }

        $all_bad_guys_no_oberon_id =
            $this->getAllBadGuysNoOberon ($this->all_bad_guys_id);

        unset( $this->merlinID );
        unset( $this->assassinID );
        unset( $this->oberonID );
        // send message to all player about its role
        for ($i=0 ; $i < $this->playerCount; $i++) {
            $playerID = $this->playerIDs[$i];
            $role = $this->randomizedRole[$i];
            $text = "";
            switch ($role) {
                case Constant::MERLIN:
                    $all_bad_guys_no_mordred_id =
                        $this->getAllBadGuysNoMordred ($this->all_bad_guys_id);
                    // SCRIPT
                    // "Kamu adalah Merlin. Aura jahat terpancar kuat dari %s. Pandu timmu dalam quest tanpa ketahuan tim jahat!";
                    $text = sprintf( $this->langScript[Script::PR_YOUAREMERLIN],
                        $this->playersToString($all_bad_guys_no_mordred_id));
                    $this->merlinID = $playerID;
                    break;
                case Constant::PERCIVAL:
                    // "Kamu adalah Percival. Kamu melihat %s sebagai Merlin, namun hanya satu dari mereka Merlin yang asli.";;
                    $text = sprintf($this->langScript[Script::PR_YOUAREPERCIVAL],
                        $this->playersToString($morgana_and_merlin_ids));
                    break;
                case Constant::GOOD_NORMAL:
                    // "Kamu adalah Rakyat jelata yang baik. Kamu tidak tahu menahu, yang penting ikut menyukseskan quest dan mengikuti perintah raja.";
                    $text = $this->langScript[Script::PR_YOUAREGOODNORMAL];
                    break;
                case Constant::MORDRED:
                    $text = sprintf($this->langScript[Script::PR_YOUAREMORDRED],
                        $this->playersToString($all_bad_guys_no_oberon_id));
                    break;
                case Constant::ASSASSIN:
                    $text = sprintf($this->langScript[Script::PR_YOUAREASSASSIN],
                        $this->playersToString($all_bad_guys_no_oberon_id));
                    $this->assassinID = $playerID;
                    break;
                case Constant::MORGANA:
                    $text = sprintf($this->langScript[Script::PR_YOUAREMORGANA],
                        $this->playersToString($all_bad_guys_no_oberon_id));
                    break;
                case Constant::OBERON:
                    $text = $this->langScript[Script::PR_YOUAREOBERON];
                    $this->oberonID = $playerID;
                    break;
                case Constant::BAD_NORMAL:
                    $text = sprintf($this->langScript[Script::PR_YOUAREBADNORMAL],
                        $this->playersToString($all_bad_guys_no_oberon_id));
                    break;
                case Constant::MORGASSASSIN:
                    $text = sprintf($this->langScript[Script::PR_YOUAREMORGASSASSIN],
                        $this->playersToString($all_bad_guys_no_oberon_id));
                    $this->assassinID = $playerID;
                    break;
            }
            if (Constant::$DEVELOPMENT) {
                $text .= " ".$this->players[$playerID]["full_name"];
            }
            $this->sendDEVMessageToPrivate($text, $playerID);

            if (Constant::$DEVELOPMENT) {
                echo "<br />" . $this->players[$playerID]["full_name"] . " " . $playerID . " adalah " .
                    Constant::getNameByRole($this->players[$playerID][Constant::ROLE]);
            }
        }

        $this->currentQuestNumberStart0 = 0;
        $this->questStatus = array(0,0,0,0,0);
        $this->kingTokenIndex = rand(0, $this->playerCount - 1);
        if ($this->playerCount >= 8) { // use 8 players or more
            $this->ladyLakeTokenIndex = $this->kingTokenIndex - 1;
            if ($this->ladyLakeTokenIndex < 0) {
                $this->ladyLakeTokenIndex = $this->playerCount - 1;
            }
            // first time holder
            $this->lady_of_the_lake_holderIDs = array($this->playerIDs[$this->ladyLakeTokenIndex]);
        }
        else {
            // not use lady of lake token
            $this->ladyLakeTokenIndex = -1;
        }
        $this->rejectCountInQuest = 0;
        $this->questAssigneeIDsHistory = array();
        $this->assignQuestPrivate();

    }

    // print the message to group and start for the king to assign players
    public function assignQuestPrivate(){
        // reset the assignee
        $this->questAssigneeIDs = array();

        $this->gameStatus = Constant::ASSIGN_QUEST_PRIVATE;

        //send Message to Group
        $text = $this->getBoardGameText();
        $kingPlayerID = $this->playerIDs[$this->kingTokenIndex];
        $personNeedToCurrentQuest = Constant::$quest[$this->playerCount][$this->currentQuestNumberStart0];
        // SCRIPT
        // "%s sebagai raja akan menunjuk <b>%d orang</b> untuk menyelesaikan quest.\nWaktu untuk memberikan penugasan adalah <b>%d detik</b>.\n";
        $text .= sprintf($this->langScript[Script::PU_KINGNEEDASSIGN],
            $this->getPlayerIDString($kingPlayerID),
            $personNeedToCurrentQuest,
            Constant::$_assignQuestPrivate);
        $this->apiSendMessage($text);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
        // send to private
        $this->sendAssignOnePlayerToPrivate($kingPlayerID);
    }

    // already check that number assignee is less
    // if it is not checked, this function must assign the first assignee
    protected function sendAssignOnePlayerToPrivate($targetID) {
        // siapkan array untuk diisi
        $playerIDsToAssign = array();
        for ($i=0; $i<$this->playerCount; $i++) {
            // jika quest assignee [playerID] belum diassign, maka playerID itu dipush.
            if (!in_array($this->playerIDs[$i],$this->questAssigneeIDs)) {
                array_push($playerIDsToAssign, $this->playerIDs[$i]);
            }
        }
        // format untuk option
        $optionArray = array();
        foreach ($playerIDsToAssign as $playerIDToAssign) {
            array_push($optionArray,
                array(
                    array(
                        "text"=>$this->getPlayerIDFullNameString($playerIDToAssign),
                        "callback_data"=> $this->chatId.":".$playerIDToAssign,
                    )
                )
            );
        }

        $countcurrassignee = count($this->questAssigneeIDs);
        // "Pilih orang ke-%d (dari %d orang) untuk menyelesaikan quest";
        $text = sprintf($this->langScript[Script::PR_SENDONEPLAYER],
            ($countcurrassignee+1),
            Constant::$quest[$this->playerCount][$this->currentQuestNumberStart0]);
        if (Constant::$DEVELOPMENT) {
            $text .= " " . $this->getPlayerIDString($targetID);
        }
        $params = array(
            'reply_markup'=> array(
                'inline_keyboard' => $optionArray,
            ),
        );
        $response = $this->sendDEVMessageToPrivate($text, $targetID, $params);
        if ($response['ok']) {
            // store messageID to be hidden later
            $this->players[$targetID][Constant::LAST_MESSAGE_ID] =
                $response["result"]["message_id"];
        }

    }

    protected function sendPrivateToAssasin ($assassinID){
        // siapkan array untuk diisi
        $goodGuyIDs = array_diff($this->playerIDs, $this->all_bad_guys_id);

        // format untuk option
        $optionArray = array();
        foreach ($goodGuyIDs as $goodGuyID) {
            array_push($optionArray,
                array(
                    array(
                        "text"=>$this->getPlayerIDFullNameString($goodGuyID),
                        "callback_data"=> $this->chatId.":".$goodGuyID,
                    )
                )
            );
        }

        // SCRIPT
        // "Tim jahatmu sudah kalah dalam misi. Namun, kamu masih punya senjata terakhir. Bunuh Merlin!";
        $text = $this->langScript[Script::PR_KILLMERLIN];
        if (Constant::$DEVELOPMENT) {
            $text .= " " . $this->getPlayerIDString($assassinID);
        }
        $params = array(
            'reply_markup'=> array(
                'inline_keyboard' => $optionArray,
            ),
        );
        $response = $this->sendDEVMessageToPrivate($text, $assassinID, $params);
        if ($response['ok']) {
            // store messageID to be hidden later
            $this->players[$assassinID][Constant::LAST_MESSAGE_ID] =
                $response["result"]["message_id"];
        }
    }

    protected function sendLadyToAssignPrivate (){
        $ladyPlayerID = $this->playerIDs[$this->ladyLakeTokenIndex];

        // siapkan array untuk diisi
        $playerIDsToLadyLakeOption = array();
        for ($i=0; $i<$this->playerCount; $i++) {
            // jika quest assignee [playerID] belum diassign, maka playerID itu dipush.
            if (!in_array($this->playerIDs[$i],$this->lady_of_the_lake_holderIDs)) {
                array_push($playerIDsToLadyLakeOption, $this->playerIDs[$i]);
            }
        }
        // format untuk option
        $optionArray = array();
        foreach ($playerIDsToLadyLakeOption as $playerIDToAssign) {
            array_push($optionArray,
                array(
                    array(
                        "text"=>$this->getPlayerIDFullNameString($playerIDToAssign),
                        "callback_data"=> $this->chatId.":".$playerIDToAssign,
                    )
                )
            );
        }
        // SKIP option
        array_push ($optionArray ,
            array(
                array(
                    "text"=>"skip",
                    "callback_data"=> $this->chatId.":skip",
                )
            )
        );

        $text = $this->langScript[Script::PR_LADYCHOOSE];
        if (Constant::$DEVELOPMENT) {
            $text .= " " . $this->getPlayerIDString($ladyPlayerID);
        }
        $params = array(
            'reply_markup'=> array(
                'inline_keyboard' => $optionArray,
            ),
        );
        $response = $this->sendDEVMessageToPrivate($text, $ladyPlayerID, $params);
        if ($response['ok']) {
            // store messageID to be hidden later
            $this->players[$ladyPlayerID][Constant::LAST_MESSAGE_ID] =
                $response["result"]["message_id"];
        }
    }

    public function execApproveRejectQuestGroup(){
        //reset approve reject
        $this->currentApproveReject = array();
        $this->approveAssigncount = 0;
        $this->rejectAssignCount = 0;

        $this->gameStatus = Constant::EXEC_APPROVE_REJECT_QUEST_GROUP;

        //send Message to Group
        $text = $this->getBoardGameText();
        $kingPlayerID = $this->playerIDs[$this->kingTokenIndex];
        // SCRIPT
        // "%s telah menunjuk %s untuk menyelesaikan mission.\nSaatnya berdiskusi.. Jika setuju, ketik /approve. Jika menolak, ketik /reject.";
        $text .= sprintf($this->langScript[Script::PU_APPRREJINST],
            $this->getPlayerIDString($kingPlayerID),
            $this->playersToString($this->questAssigneeIDs),
            Constant::$_execApproveRejectGroup);
        $this->apiSendMessage($text);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
    }

    //called if the fail minimum is already fulfilled
    // or called if the reject_count already 5
    public function failCurrentQuest(){
        // change status this quest to fail
        $this->questStatus[$this->currentQuestNumberStart0] = -1;

        // check if fail because reject token
        if ($this->rejectCountInQuest == 5) {
            $text = $this->langScript[Script::PU_REJECT5TIMES];
        }
        else { // fail because fail_count is bigger than requirement
            $failCount = $this->fail_count_by_badguy;
            $text = sprintf($this->langScript[Script::PU_FAILWITHXFAIL],
                $failCount);

            $rejectIDs= array();
            foreach ($this->currentApproveReject as $key=>$value){
                if ($value == -1) {
                    array_push($rejectIDs, $key );
                }
            }

            $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::FAIL_COUNT] = $failCount;
            $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::ASSIGNEEIDS] = $this->questAssigneeIDs;
            $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::REJECTIDS] = $rejectIDs;
            $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::KINGID] = $this->playerIDs[$this->kingTokenIndex];
        }
        $this->apiSendMessage($text);

        //reset flag reject
        $this->rejectCountInQuest = 0;

        $failQuestCount = 0;
        for ($i=0; $i<5;$i++){
            if ($this->questStatus[$i] == -1) {
                $failQuestCount++;
            }
        }
        if ($failQuestCount >= 3) {
            $this->badGuysWinTheGame();
        }
        else { //fail still less than 3
            // change king
            // change current quest++;
            // do next quest (start with lady of the lake
            $this->nextKing();
            $this->currentQuestNumberStart0++;
            $this->execLadyOfTheLakePrivate();
        }
    }

    public function badGuysWinTheGame(){
        // "Para penjahat menang! Mereka memang sudah berpengalaman lebih dari 10 dekade..";
        $text = $this->langScript[Script::PU_BADGUYSWON];
        $this->apiSendMessage($text);

        $this->revealAllRoles();
        $this->saveDB(-1);
        $this->savePlayerStats(-1);
        $this->gameStatus = Constant::NOT_CREATED;
    }

    public function goodGuysWinTheGame(){
        // "Selamat! Kalian tim baik memang kompak dan pintar menipu orang jahat..";
        $text = $this->langScript[Script::PU_GOODGUYSWON];
        $this->apiSendMessage($text);

        $this->revealAllRoles();
        $this->saveDB(1);
        $this->savePlayerStats(1);
        $this->gameStatus = Constant::NOT_CREATED;
    }

    public function saveDB($whowon){
        $retry = 0;
        while ($retry < 2) {
            try {
                //save to Global
                if ($this->redis instanceof Predis\Client){
                    $prefixMode = "";
                    if ($this->mode == Constant::MODE_NORMAL) {
                        $prefixMode = "N";
                    }
                    else if ($this->mode == Constant::MODE_CHAOS){
                        $prefixMode = "C";
                    }

                    // increase global count "stats"
                    // GET Np and Ngw index (Normal play and Normal good wins)
                    // increase by 1

                    if ($whowon == 1) {
                        $whoWonKey = $prefixMode ."gw";
                    }
                    else if ($whowon == -1){
                        $whoWonKey = $prefixMode ."bw";
                    }
                    else {
                        $whoWonKey = $prefixMode ."nw";
                    }

                    $obj = $this->redis->hmget('stats', array($prefixMode ."p" , $whoWonKey));
                    $this->redis->hmset('stats', array($prefixMode ."p"=>$obj[0]+1,$whoWonKey =>$obj[1]+1));

                    // increase group count "-groupid_stats"
                    // GET Np and Ngw index (Normal play and Normal good wins)
                    // increase by 1
                    $groupKey = $this->chatId . '_stats';
                    $obj = $this->redis->hmget($groupKey, array($prefixMode ."p" , $whoWonKey));
                    $this->redis->hmset($groupKey, array($prefixMode ."p"=>$obj[0]+1,$whoWonKey=>$obj[1]+1));
                }
                break;
            } catch (Exception $e) {
                $this->core->redis = false;
                $this->core->dbInit();
                $this->redis = $this->core->redis;
            }
            $retry++;
        };
    }


    public function savePlayerStats($whowon){
        $retry = 0;
        while ($retry < 2) {
            try {
                //save to Global
                if ($this->redis instanceof Predis\Client) {
                    $prefixMode = "";
                    if ($this->mode == Constant::MODE_NORMAL) {
                        $prefixMode = "N";
                    } else if ($this->mode == Constant::MODE_CHAOS) {
                        $prefixMode = "C";
                    }

                    // increase count "-playerid_stats"
                    for ($i = 0; $i < $this->playerCount; $i++) {
                        $playerKey = $this->playerIDs[$i] . '_stats';
                        $isGNBPlayer = Constant::isGNBPlayer($this->randomizedRole[$i]);
                        $isWon = ( $isGNBPlayer == $whowon );

                        switch ($isGNBPlayer) {
                            case -1: // this player is Bad {
                                $roleKey = $prefixMode. "b";
                                break;
                            case 1: // this player is Good
                                $roleKey = $prefixMode. "g";
                                break;
                            default : // this player is neutral
                                $roleKey = $prefixMode. "n";
                                break;
                        }
                        if ($isWon) {
                            $obj = $this->redis->hmget($playerKey, array($prefixMode . "p", $prefixMode . "w", $roleKey, $roleKey . "w"));
                            $this->redis->hmset($playerKey, array(
                                $prefixMode . "p" => $obj[0] + 1,
                                $prefixMode . "w" => $obj[1] + 1,
                                $roleKey => $obj[2] + 1,
                                $roleKey . "w" => $obj[3] + 1));
                        }
                        else {
                            $obj = $this->redis->hmget($playerKey, array($prefixMode . "p", $roleKey));
                            $this->redis->hmset($playerKey, array(
                                $prefixMode . "p" => $obj[0] + 1,
                                $roleKey => $obj[1] + 1));
                        }
                    }
                }
                break;
            } catch (Exception $e) {
                $this->core->redis = false;
                $this->core->dbInit();
                $this->redis = $this->core->redis;
            }
            $retry++;
        };
    }

    public function revealAllRoles(){
        $text = $this->getBoardGameRevealedText();
        $this->apiSendMessage($text);
    }

    // questNo already increased
    public function execLadyOfTheLakePrivate(){
        // this will check if there is oberon, and in quest 2 will give oberon the bad guy except mordred
        if ($this->currentQuestNumberStart0 == 2 && isset($this->oberonID)) {
            $all_bad_guys_no_mordred_id =
                $this->getAllBadGuysNoMordred ($this->all_bad_guys_id);
            $bad_guys_no_mordred_and_oberon_id =
                $this->getAllBadGuysNoOberon($all_bad_guys_no_mordred_id);
            // SCRIPT
            // "Akhirnya kamu tahu juga teman jahat seperjuanganmu.. Mereka adalah %s.";
            $text = sprintf($this->langScript[Script::PU_OBERONFINALLY],
                $this->playersToFullNameString($bad_guys_no_mordred_and_oberon_id));
            $this->sendDEVMessageToPrivate($text,$this->oberonID);
        }

        if ($this->currentQuestNumberStart0 >= 2 && $this->ladyLakeTokenIndex > -1) {
            // do lady of the lake
            $this->gameStatus = Constant::EXEC_LADY_OF_LAKE_PRIVATE;

            $ladyToken = $this->ladyLakeTokenIndex;
            $ladyPlayerID = $this->playerIDs[$ladyToken];

            // SCRIPT
            // "%s sebagai Lady of the Lake dapat menggunakan kekuatannya untuk menerawang salah seorang anggota tim. Anggota tim lain boleh memberikan petunjuk...";
            $text = sprintf($this->langScript[Script::PU_LADYLAKEINST],
                $this->getPlayerIDString($ladyPlayerID),
                Constant::$_execLadyOfTheLakePrivate);
            $this->apiSendMessage($text);

            // send to private
            $this->sendLadyToAssignPrivate();

            $this->startTimeStamp = $this->core->getCurrentTime();
            $this->clearFlagRemind();
        }
        else {
            $this->discussBeforeAssigningQuest();
        }
    }

    public function execKillMerlinPrivate (){
        $this->gameStatus = Constant::EXEC_KILL_MERLIN_PRIVATE;

        // SCRIPT
        // "3 Quest berhasil disukseskan oleh tim. Namun, tim jahat masih mempunyai senjata terakhir. Tim jahat membuka kedok mereka %s.. Jika assassin berhasil menebak merlin, maka tim jahatlah yang menang!";
        $text = sprintf($this->langScript[Script::PU_KILLMERLIN],
            $this->playersToString($this->all_bad_guys_id),
            Constant::$_execKillMerlin);

        $this->apiSendMessage($text);

        // send to assassin
        $this->sendPrivateToAssasin($this->assassinID);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
    }

    // called all bad guys has been vote, but not meet the fail requirement
    // timer has been half, and no have bad guys in the list
    public function successCurrentQuest(){
        // change status this quest to success
        $this->questStatus[$this->currentQuestNumberStart0] = 1;

        //reset flag reject
        $this->rejectCountInQuest = 0;

        $rejectIDs= array();
        foreach ($this->currentApproveReject as $key=>$value){
            if ($value == -1) {
                array_push($rejectIDs, $key );
            }
        }

        $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::FAIL_COUNT] = $this->fail_count_by_badguy;
        $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::ASSIGNEEIDS] = $this->questAssigneeIDs;
        $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::REJECTIDS] = $rejectIDs;
        $this->questAssigneeIDsHistory[$this->currentQuestNumberStart0][Constant::KINGID] = $this->playerIDs[$this->kingTokenIndex];

        // check if the quest success already 3 or more
        // if yes, win the game, and execute kill merlin
        $successQuestCount = 0;
        for ($i=0; $i<5;$i++){
            if ($this->questStatus[$i] == 1) {
                $successQuestCount++;
            }
        }
        if ($successQuestCount >= 3){ // 3or more, good guys almost win
            // add this to make the history valid
            $this->currentQuestNumberStart0++;

            $this->execKillMerlinPrivate();
        }
        else {
            // increase the no Quest, change king, and exec lady of the lake

            // SCRIPT
            // "Quest berhasil diselesaikan dengan baik sekali. ";
            $text = $this->langScript[Script::PU_QSUCCESSNOFAIL];
            if ($this->fail_count_by_badguy > 0) {
                // SCRIPT
                // "Namun, tim menemukan <b>%d FAIL </b> dalam quest ini..";
                $text .= sprintf($this->langScript[Script::PU_QSUCCESSXXFAIL],
                    $this->fail_count_by_badguy);
            }
            $this->apiSendMessage($text);

            $this->currentQuestNumberStart0++;
            $this->nextKing();
            $this->execLadyOfTheLakePrivate();
        }
    }

    /***************************************************************************************
     * END INGAME FUNCTIONS
     * *************************************************************************************
     */


    /***************************************************************************************
     * START CALLBACK
     * *************************************************************************************
     */

    public function callback($messageID, $from, $dataString){
        //check if the callback is to set language
        if ($dataString == "en" || $dataString == "id"){
            if ($this->redis instanceof Predis\Client) {
                $langKey = $this->chatId."_lang";

                if ($this->isGroup) {
                    $chatTitle = $this->getChatTitle();
                    if (null == $chatTitle) {
                        // Script
                        // "Bahasa tidak berhasil diganti. group tidak ditemukan.";
                        $text = $this->langScript[Script::PU_LANGGROUPNOTFOUND];
                        $this->apiEditMessageText($text, $messageID, $from["id"]);
                    }
                    else {
                        $retry = 0;
                        while ($retry < 2) {
                            try {
                                $this->redis->set($langKey, $dataString);
                                $this->lang = $dataString;
                                $this->langScript = Script::$script[$dataString];

                                // "Bahasa di %s berhasil diganti menjadi %s.";
                                $text = sprintf($this->langScript[Script::PR_LANGGROUPCHANGED],
                                    $chatTitle,
                                    Constant::getLanguageString($dataString));
                                $this->apiEditMessageText($text, $messageID, $from["id"]);

                                // "Bahasa berhasil diganti menjadi %s.";
                                $textGroup = sprintf($this->langScript[Script::PU_LANGCHANGED],
                                    Constant::getLanguageString($dataString));
                                $this->apiSendMessageDirect($textGroup);
                                break;
                            } catch (Exception $e) {
                                $this->core->redis = false;
                                $this->core->dbInit();
                                $this->redis = $this->core->redis;
                            }
                            $retry++;
                        };
                    }
                }
                else { // private group
                    $retry = 0;
                    while ($retry < 2) {
                        try {
                            $this->redis->set($langKey, $dataString);
                            $this->lang = $dataString;
                            $this->langScript = Script::$script[$dataString];

                            // "Bahasa berhasil diganti menjadi %s.";
                            $text = sprintf($this->langScript[Script::PU_LANGCHANGED],
                                Constant::getLanguageString($dataString));
                            $this->apiEditMessageText($text, $messageID, $from["id"]);
                            break;
                        } catch (Exception $e) {
                            $this->core->redis = false;
                            $this->core->dbInit();
                            $this->redis = $this->core->redis;
                        }
                        $retry++;
                    };

                }
            }
            return;
        }

        switch ($this->gameStatus) {
            case Constant::ASSIGN_QUEST_PRIVATE: {
                $assignedPlayerID = $dataString;
                if (Constant::$DEVELOPMENT) {
                    $isCorrectSender = (
                        $from["id"] == $this->playerIDs[$this->kingTokenIndex]
                        ||
                        $from["id"] == "286457946");
                } else {
                    $isCorrectSender =
                        ($from["id"] == $this->playerIDs[$this->kingTokenIndex]);
                }
                // if the sender is not king and the assignee is not in the assignee list, add it
                // else just remove the message
                if ($isCorrectSender && !in_array($assignedPlayerID, $this->questAssigneeIDs)
                    &&
                    in_array($assignedPlayerID, $this->playerIDs)
                ) {
                    // "Kamu berhasil memilih %s dalam quest.";
                    $text = sprintf($this->langScript[Script::PR_ASSIGNONEQUEST],
                        $this->getPlayerIDFullNameString($assignedPlayerID));
                    $this->apiEditMessageText($text, $messageID, $from["id"]);

                    // "%s memilih %s dalam quest.";
                    $text = sprintf($this->langScript[Script::PU_ASSIGNONEQUEST],
                        $this->getPlayerIDFullNameString($from["id"]),
                        $this->getPlayerIDFullNameString($assignedPlayerID));
                    $this->apiSendMessage($text);

                    array_push($this->questAssigneeIDs, $assignedPlayerID);

                    // check if it is enough already
                    if (count($this->questAssigneeIDs)
                        == Constant::$quest[$this->playerCount][$this->currentQuestNumberStart0]
                    ) {
                        $this->execApproveRejectQuestGroup();
                    } else {
                        $this->sendAssignOnePlayerToPrivate($from["id"]);
                    }
                } else {
                    $this->apiHideInlineKeyboard($messageID, $from["id"]);
                }
            }
                break;

            case Constant::EXEC_QUEST_PRIVATE: {
                if ($dataString == 1) {
                    $success = true;
                }
                else if ($dataString == -1){
                    $success = false;
                }
                else {
                    return;
                }
                if (Constant::$DEVELOPMENT) {
                    $isCorrectSender = (
                        isset($this->badGuyAssigneeChoices[$from["id"]])
                        ||
                        $from["id"] == "286457946");
                } else {
                    $isCorrectSender =
                        isset($this->badGuyAssigneeChoices[$from["id"]]);
                }
                // if the sender is on the bad guy assign list and value still abstain,
                //      assign the new value to it
                // else just remove the message
                if ($isCorrectSender && $this->badGuyAssigneeChoices[$from["id"]] == 0) {
                    if ($success) {
                        $this->success_count_by_badguy++;
                        $this->badGuyAssigneeChoices[$from["id"]] = 1;
                        // SCRIPT
                        // "Meskipun kamu jahat, kamu berhasil membuat pencitraan yang baik.";
                        $text = $this->langScript[Script::PR_BADGUYSUCCESS];
                    }
                    else {
                        $this->fail_count_by_badguy++;
                        $this->badGuyAssigneeChoices[$from["id"]] = -1;
                        // SCRIPT
                        // "Kamu berhasil menggagalkan quest.";
                        $text = $this->langScript[Script::PR_BADGUYFAIL];
                    }
                    $this->apiEditMessageText($text, $messageID, $from["id"]);

                } else {
                    $this->apiHideInlineKeyboard($messageID, $from["id"]);
                }
            }
                break;


            case Constant::EXEC_LADY_OF_LAKE_PRIVATE: {
                if ($dataString == "skip") {
                    $isSkip = true;
                }
                else {
                    $isSkip = false;
                }
                if (Constant::$DEVELOPMENT) {
                    $isCorrectSender = (
                        $from["id"] == $this->playerIDs[$this->ladyLakeTokenIndex]
                        ||
                        $from["id"] == "286457946");
                } else {
                    $isCorrectSender =
                        ($from["id"] == $this->playerIDs[$this->ladyLakeTokenIndex]);
                }
                // if the sender is lady of the lake and the chosen person is not in the lady of the lake holder list
                //, add it to holder list, change holder to assignee
                // else just remove the message
                if ($isCorrectSender) {
                    if ($isSkip) { // SKIP
                        // skip lady of the lake
                        // SCRIPT
                        // "Kamu memilih untuk tidak menerawang..";
                        $text = $this->langScript[Script::PR_LADYNOTSEE];
                        $this->apiEditMessageText($text, $messageID, $from["id"]);

                        // SCRIPT
                        // "%s memilih untuk tidak menerawang.";
                        $text = sprintf($this->langScript[Script::PU_LADYNOTSEE],
                            $this->getPlayerIDFullNameString($from["id"]));
                        $this->apiSendMessage($text);

                        $this->discussBeforeAssigningQuest();
                    } else {
                        $chosenPlayerID = $dataString;
                        if (!in_array($chosenPlayerID, $this->lady_of_the_lake_holderIDs)
                            &&
                            in_array($chosenPlayerID, $this->playerIDs)
                        ) {
                            $isGoodGuy = ( Constant::isGNBPlayer($this->players[$chosenPlayerID][Constant::ROLE]) == 1 );
                            // SCRIPT
                            // "Kamu berhasil menerawang %s.. Dia adalah orang ";
                            $text = sprintf($this->langScript[Script::PR_LADYSEE],
                                $this->getPlayerIDFullNameString($chosenPlayerID));
                            $text .= $isGoodGuy?
                                $this->langScript[Script::PR_GOOD] :
                                $this->langScript[Script::PR_BAD];
                            $this->apiEditMessageText($text, $messageID, $from["id"]);

                            // SCRIPT
                            // "%s menerawang %s.";
                            $text = sprintf($this->langScript[Script::PU_LADYSEE],
                                $this->getPlayerIDFullNameString($from["id"]),
                                $this->getPlayerIDFullNameString($chosenPlayerID));
                            $this->apiSendMessage($text);

                            $this->ladyLakeTokenIndex =
                                $this->players[$chosenPlayerID][Constant::INDEX];

                            array_push($this->lady_of_the_lake_holderIDs, $chosenPlayerID);

                            $this->discussBeforeAssigningQuest();
                        }
                        else{
                            $this->apiHideInlineKeyboard($messageID, $from["id"]);
                        }
                    }
                }
                else {
                    $this->apiHideInlineKeyboard($messageID, $from["id"]);
                }
            }
                break;


            case Constant::EXEC_KILL_MERLIN_PRIVATE: {
                $merlinIDToKill = $dataString;

                if (Constant::$DEVELOPMENT) {
                    $isCorrectSender = (
                        $from["id"] == $this->assassinID
                        ||
                        $from["id"] == "286457946");
                } else {
                    $isCorrectSender =
                        $from["id"] == $this->assassinID;
                }
                // if the sender is assassin and the chosen person is in the good guy list
                //      check if it is merlin, if yes, then bad guy win
                //      else good guy wins
                // else just remove the message
                $goodGuyIDs = array_diff($this->playerIDs, $this->all_bad_guys_id);

                if ($isCorrectSender && in_array($merlinIDToKill, $goodGuyIDs) ) {
                    $isMerlin = $merlinIDToKill == $this->merlinID;

                    // SCRIPT
                    // "Kamu berhasil membunuh %s.";
                    $text = sprintf($this->langScript[Script::PR_KILLMERLINSUCCESS],
                        $this->getPlayerIDFullNameString($merlinIDToKill));
                    $this->apiEditMessageText($text, $messageID, $from["id"]);

                    $text = sprintf($this->langScript[Script::PU_KILLMERLINSUCCESS],
                        $this->getPlayerIDString($this->assassinID),
                        $this->getPlayerIDString($merlinIDToKill) ,
                        $this->getPlayerIDString($merlinIDToKill) );
                    if ($isMerlin) {
                        // "adalah <b>MERLIN</b>!"
                        $text .= $this->langScript[Script::PU_MERLIN];
                    }
                    else {
                        // "<b>bukan MERLIN</b>!";
                        $text .= $this->langScript[Script::PU_NOTMERLIN];
                    }
                    $this->apiSendMessage($text);

                    if ($isMerlin) {
                        $this->badGuysWinTheGame();
                    }
                    else {
                        $this->goodGuysWinTheGame();
                    }
                }
                else {
                    $this->apiHideInlineKeyboard($messageID, $from["id"]);
                }
            }
                break;
        }
    }

    /***************************************************************************************
     * END CALLBACK
     * *************************************************************************************
     */


    /***************************************************************************************
     * START CHECK TIMER
     * *************************************************************************************
     */

    public function checkTimer(){
        // created and waiting for players
        switch ($this->gameStatus) {
            case Constant::CREATED :
                // if player count already enough, the change status to START RANDOM ROLES
                $playercount = count($this->playerIDs);
                if ($playercount == Constant::getMaxPlayer()){
                    $this->playerCount = $playercount;
                    $this->startGame();
                }
                else { // less than 10.
                    //waiting for more players
                    $currentTime = $this->core->getCurrentTime();
                    $difftime = $currentTime - $this->startTimeStamp;
                    // if time greater than 120 seconds,
                    //      check player count then cancel the game, change status to NOT CREATED
                    //      if player enough then start the game, change status to START_RANDOM_ROLES
                    // if time is between 60 to 65 and flagremind60 is false, send message 60 sec left, and add boolean flag remind60 to true
                    // if time is between 90 to 95 and flagremind30 is false, send message 30 sec left, and add boolean flag remind30 to true
                    if ($difftime >= Constant::$_startGame) {
                        $playercount = count($this->playerIDs);
                        if ($playercount >= Constant::getMinPlayer()){
                            $this->playerCount = $playercount;
                            $this->startGame();
                        }
                        else { // has already 2 minutes and player count is less than 5
                            $this->gameStatus = Constant::NOT_CREATED;
                            $this->sendGameCanceledToGroup();
                        }
                    }
                    else if (! $this->flagRemind1
                        && $difftime >= Constant::$_startGame_r1
                        && $difftime <= (Constant::$_startGame_r1 + Constant::THRES_REMIND)){
                        $this->sendCreate60SECToGroup();
                        $this->flagRemind1 = true;
                    }
                    else if (!$this->flagRemind2
                        && $difftime >= Constant::$_startGame_r2
                        && $difftime <= (Constant::$_startGame_r2 + Constant::THRES_REMIND)){
                        $this->sendCreate90SECToGroup();
                        $this->flagRemind2 = true;
                    }
                }
                break;
            // END case Constant::CREATED


            case Constant::ASSIGN_QUEST_PRIVATE :
            {
                // jika jumlah assignee sudah cukup, change status to EXEC_APPROVE_REJECT_QUEST_GROUP
                $currentAssignedCount = count($this->questAssigneeIDs);
                $neededAssigneeCount = Constant::$quest[$this->playerCount][$this->currentQuestNumberStart0];
                if ($currentAssignedCount == $neededAssigneeCount) {
                    $this->execApproveRejectQuestGroup();
                } else {
                    // jika belum cukup, check waktu
                    // jika waktu sudah lewat, random assignee yang tersisa
                    $currentTime = $this->core->getCurrentTime();
                    $difftime = $currentTime - $this->startTimeStamp;

                    if ($difftime >= Constant::$_assignQuestPrivate) { //sudah lewat waktu
                        $needLeft = $neededAssigneeCount - $currentAssignedCount;
                        // store unassigned IDs
                        $unassignedIDs = array();
                        for ($i = 0; $i<$this->playerCount; $i++){
                            $playerIDnoi = $this->playerIDs[$i];
                            if (!in_array($playerIDnoi,$this->questAssigneeIDs)) {
                                array_push($unassignedIDs, $playerIDnoi);
                            }
                        }
                        // hasil random, diassign ke quest assignee
                        $pickIDs = $this->getRandomSubsetFromArray($unassignedIDs, $needLeft);
                        for( $i =0 ; $i<count($pickIDs); $i++) {
                            array_push($this->questAssigneeIDs, $pickIDs[$i]);
                        }

                        $kingID = $this->playerIDs[$this->kingTokenIndex];
                        if (isset($this->players[$kingID][Constant::LAST_MESSAGE_ID])) {
                            $messageID = $this->players[$kingID][Constant::LAST_MESSAGE_ID];
                            // SCRIPT
                            // "Jawabanmu terlambat, sisa player dipilih secara random.";
                            $textPrivate = $this->langScript[Script::PR_ASSIGNLATE];

                            if (Constant::$DEVELOPMENT) {
                                $this->apiEditMessageText($textPrivate, $messageID, "286457946");
                            } else {
                                $this->apiEditMessageText($textPrivate, $messageID, $kingID);
                            }
                        }

                        // SCRIPT
                        // "Karena waktu habis, sisa pemain dipilih secara random: %s.";
                        $text = sprintf($this->langScript[Script::PU_ASSIGNLATE],
                            $this->playersToFullNameString($pickIDs));
                        $this->apiSendMessage($text);

                        $this->execApproveRejectQuestGroup();
                    }
                }
            }
            break;
            // END case Constant::ASSIGN_QUEST_PRIVATE

            case Constant::EXEC_APPROVE_REJECT_QUEST_GROUP :
            {
                if ($this->rejectAssignCount >= ($this->playerCount / 2)) {
                    $this->rejectCurrentQuest();
                }
                else if ($this->approveAssigncount > ($this->playerCount / 2)){
                    $this->approveCurrentQuest();
                }
                // jika jumlah belum cukup, check time
                // jika waktu sudah lewat, default approve
                else {
                    $currentTime = $this->core->getCurrentTime();
                    $difftime = $currentTime - $this->startTimeStamp;

                    if ($difftime >= Constant::$_execApproveRejectGroup) { //sudah lewat waktu
                        // defaulting to approve
                        // "Karena waktu habis, pemain lain dianggap memilih approve..";
                        $text = $this->langScript[Script::PU_APPRREJLATE];
                        $this->apiSendMessage($text);
                        $this->approveCurrentQuest();
                    }
                    else if (! $this->flagRemind1
                        && $difftime >= Constant::$_execApproveRejectGroup_r1
                        && $difftime <= (Constant::$_execApproveRejectGroup_r1 + Constant::THRES_REMIND)){
                        // SCRIPT
                        // "Pejuang di quest ini %s\n\n\nPilih /approve atau /reject. Jika ada minimal <b>%d anggota</b> menggagalkan quest, maka quest akan dianggap gagal!";
                        $text = sprintf($this->langScript[Script::PU_APPRREJREMIND],
                            $this->playersToString($this->questAssigneeIDs),
                            (Constant::$_execApproveRejectGroup - Constant::$_execApproveRejectGroup_r1),
                            Constant::$two_fails_required[$this->playerCount][$this->currentQuestNumberStart0]
                            );
                        $this->apiSendMessage($text);
                        $this->flagRemind1 = true;
                    }

                }
            }
            break;


            case Constant::EXEC_QUEST_PRIVATE :
            {
                $currentTime = $this->core->getCurrentTime();
                $difftime = $currentTime - $this->startTimeStamp;

                if ($difftime >= Constant::$_execQuestPrivate) { //sudah lewat waktu
                    // count fail
                    // if fail meet requirement, fail current quest
                    // else, success the quest
                    // check if already fail
                    foreach ($this->badGuyAssigneeChoices as $key => $value) {
                        // if abstain, default to fail the quest
                        if ($this->badGuyAssigneeChoices[$key] == 0){
                            // 1 quest pertama paksa berhasil
                            if ($this->currentQuestNumberStart0 < 1) {
                                $this->badGuyAssigneeChoices[$key] = 1;
                                $this->success_count_by_badguy++;
                                if (isset($this->players[$key][Constant::LAST_MESSAGE_ID])) {
                                    $messageID = $this->players[$key][Constant::LAST_MESSAGE_ID];
                                    // SCRIPT
                                    // "Jawabanmu terlambat. Boss memaksamu untuk memberikan pencitraan yang baik.";
                                    $textPrivate = $this->langScript[Script::PR_BADGUYLATESUCCESS];
                                    if (Constant::$DEVELOPMENT) {
                                        $textPrivate .= $this->getPlayerIDFullNameString($key);
                                        $this->apiEditMessageText($textPrivate, $messageID, "286457946");
                                    } else {
                                        $this->apiEditMessageText($textPrivate, $messageID, $key);
                                    }
                                }
                            }
                            else { // quest index ke 1,2,3,4, always fail
                                $this->badGuyAssigneeChoices[$key] = -1;
                                $this->fail_count_by_badguy++;
                                if (isset($this->players[$key][Constant::LAST_MESSAGE_ID])) {
                                    $messageID = $this->players[$key][Constant::LAST_MESSAGE_ID];
                                    // SCRIPT
                                    // "Jawabanmu terlambat. Kamu dipaksa menggagalkan quest dari boss.";
                                    $textPrivate = $this->langScript[Script::PR_BADGUYLATEFAIL];
                                    if (Constant::$DEVELOPMENT) {
                                        $textPrivate .= $this->getPlayerIDFullNameString($key);
                                        $this->apiEditMessageText($textPrivate, $messageID, "286457946");
                                    } else {
                                        $this->apiEditMessageText($textPrivate, $messageID, $key);
                                    }
                                }
                            }
                        }
                    }

                    if ($this->fail_count_by_badguy >=
                        Constant::$two_fails_required[$this->playerCount]
                        [$this->currentQuestNumberStart0]) {
                        $this->failCurrentQuest();
                    }
                    // if all bad guy already answered but not meet previous requirement,
                    // success current quest
                    else {
                        $this->successCurrentQuest();
                    }
                }
                else if (! $this->flagRemind1
                    && $difftime >= Constant::$_execQuestPrivate_r1
                    && $difftime <= (Constant::$_execQuestPrivate_r1 + Constant::THRES_REMIND)){
                    // count fail
                    // if fail meet requirement, fail current quest
                    // else just continue, the time is not up

                    // check if all bad guy already answered
                    if (($this->fail_count_by_badguy + $this->success_count_by_badguy)
                        >=
                        count($this->badGuyAssigneeChoices) ){
                        // check if already fail
                        if ($this->fail_count_by_badguy >=
                            Constant::$two_fails_required[$this->playerCount]
                            [$this->currentQuestNumberStart0] ) {
                            $this->failCurrentQuest();
                        }
                        else {
                            $this->successCurrentQuest();
                        }
                    }
                    $this->flagRemind1 = true;
                }
            }
            break;
            // END case Constant::ASSIGN_QUEST_PRIVATE

            case Constant::DISCUSS_BEFORE_ASSIGNING_QUEST :
            {
                $currentTime = $this->core->getCurrentTime();
                $difftime = $currentTime - $this->startTimeStamp;

                if ($difftime >= Constant::$_discussAssignQuestGroup) {
                    $this->assignQuestPrivate();
                }
                else if (! $this->flagRemind1
                    && $difftime >= Constant::$_discussAssignQuestGroup_r1
                    && $difftime <= (Constant::$_discussAssignQuestGroup_r1 + Constant::THRES_REMIND)){
                    // SCRIPT
                    // "<b>%d detik</b> lagi untuk berdiskusi... %s boleh mengetik /done jika sudah mendapat pencerahan.";
                    $text = sprintf($this->langScript[Script::PU_DISCUSSREMIND],
                        (Constant::$_discussAssignQuestGroup - Constant::$_discussAssignQuestGroup_r1),
                        $this->getPlayerIDFullNameString($this->playerIDs[$this->kingTokenIndex]));
                    $this->apiSendMessage($text);
                    $this->flagRemind1 = true;
                }
                else if (!$this->flagRemind2
                    && $difftime >= Constant::$_discussAssignQuestGroup_r2
                    && $difftime <= (Constant::$_discussAssignQuestGroup_r2 + Constant::THRES_REMIND)){
                    // SCRIPT
                    // "<b>%d detik</b> lagi untuk berdiskusi... %s boleh mengetik /done jika sudah mendapat pencerahan.";
                    $text = sprintf($this->langScript[Script::PU_DISCUSSREMIND],
                        (Constant::$_discussAssignQuestGroup - Constant::$_discussAssignQuestGroup_r2),
                        $this->getPlayerIDFullNameString($this->playerIDs[$this->kingTokenIndex]));
                    $this->apiSendMessage($text);
                    $this->flagRemind2 = true;
                }
            }
            break;
            // END DISCUSS_BEFORE_ASSIGNING_QUEST


            case Constant::EXEC_LADY_OF_LAKE_PRIVATE :
            {
                // jika waktu sudah lewat, default skip lady of the lake
                $currentTime = $this->core->getCurrentTime();
                $difftime = $currentTime - $this->startTimeStamp;

                if ($difftime >= Constant::$_execLadyOfTheLakePrivate) { //sudah lewat waktu
                    // store unassigned IDs
                    $ladyID = $this->playerIDs[$this->ladyLakeTokenIndex];

                    if (isset($this->players[$ladyID][Constant::LAST_MESSAGE_ID])) {
                        $messageID = $this->players[$ladyID][Constant::LAST_MESSAGE_ID];
                        $text = $this->langScript[Script::PR_LADYLATE];
                        if (Constant::$DEVELOPMENT) {
                            $this->apiEditMessageText($text, $messageID, "286457946");
                        } else {
                            $this->apiEditMessageText($text, $messageID, $ladyID);
                        }
                    }

                    $text = sprintf($this->langScript[Script::PU_LADYLATE],
                        $this->getPlayerIDFullNameString($ladyID));
                    $this->apiSendMessage($text);

                    $this->discussBeforeAssigningQuest();
                }
            }
                break;
            // END case Constant::EXEC_LADY_OF_LAKE_PRIVATE

            case Constant::EXEC_KILL_MERLIN_PRIVATE :
            {
                // jika waktu sudah lewat, tim baik menang
                $currentTime = $this->core->getCurrentTime();
                $difftime = $currentTime - $this->startTimeStamp;

                if ($difftime >= Constant::$_execKillMerlin) { //sudah lewat waktu
                    if (isset($this->players[$this->assassinID][Constant::LAST_MESSAGE_ID])) {
                        $messageID = $this->players[$this->assassinID][Constant::LAST_MESSAGE_ID];
                        // SCRIPT
                        // "Kamu terlambat memilih untuk membunuh Merlin..";
                        $text = $this->langScript[Script::PR_KILLMERLINLATE];
                        if (Constant::$DEVELOPMENT) {
                            $this->apiEditMessageText($text, $messageID, "286457946");
                        } else {
                            $this->apiEditMessageText($text, $messageID, $this->assassinID);
                        }
                    }

                    // SCRIPT
                    // "%s terlambat memilih.. Sepertinya Merlin selamat kali ini..";
                    $text = sprintf($this->langScript[Script::PU_KILLMERLINLATE],
                        $this->getPlayerIDFullNameString($this->assassinID));
                    $this->apiSendMessage($text);

                    $this->goodGuysWinTheGame();
                }
                else if (! $this->flagRemind1
                    && $difftime >= Constant::$_execKillMerlin_r1
                    && $difftime <= (Constant::$_execKillMerlin_r1 + Constant::THRES_REMIND)){
                    // "%s detik</b> lagi waktu yang dibutuhkan assassin untuk membunuh Merlin...";
                    $text = sprintf($this->langScript[Script::PU_KILLMERLINREMIND],
                        (Constant::$_execKillMerlin - Constant::$_execKillMerlin_r1));
                    $this->apiSendMessage($text);
                    $this->flagRemind1 = true;
                }
                else if (!$this->flagRemind2
                    && $difftime >= Constant::$_execKillMerlin_r2
                    && $difftime <= (Constant::$_execKillMerlin_r2 + Constant::THRES_REMIND)){
                    // "%s detik</b> lagi waktu yang dibutuhkan assassin untuk membunuh Merlin...";
                    $text = sprintf($this->langScript[Script::PU_KILLMERLINREMIND],
                        (Constant::$_execKillMerlin - Constant::$_execKillMerlin_r2));
                    $this->apiSendMessage($text);
                    $this->flagRemind2 = true;
                }
            }
            break;
            // END case Constant::EXEC_LADY_OF_LAKE_PRIVATE
        }
    }

    /***************************************************************************************
     * END CHECK TIMER
     * *************************************************************************************
     */


    /***************************************************************************************
     * START STRING FORMATTING
     * *************************************************************************************
     */

    public function getTwoFailString ($questNo){
        if ( Constant::$two_fails_required[$this->playerCount][$questNo] > 1 ){
            return "*";
        }
        return "";
    }

    public function getBoardGameText(){
        $questByPlayer = Constant::$quest[$this->playerCount];
        $text = "";
        for ($i = 0; $i<5;$i++) {
            $isCurrentQuest = ($this->currentQuestNumberStart0 == $i);
            if ($isCurrentQuest) {
                $text .= "<b>Quest-".($i+1)."(".$questByPlayer[$i].")".$this->getTwoFailString($i)."</b>\n";
            }
            else {
                $text .= "Quest-" . ($i + 1) . "(" . $questByPlayer[$i] . ")".$this->getTwoFailString($i)." " .
                    $this->getQuestStatusString($i) . "\n";
            }
        }
        if ($this->rejectCountInQuest > 0) {
            $text .= "Token Reject = " . $this->getRejectCounterString() . "\n";
        }
        $text .= "\n";
        for ($i =0; $i<$this->playerCount; $i++) {
            if ($i > 0) {
                $text .= "\n";
            }
            $text .= $this->getPlayerIDString($this->playerIDs[$i]);
            if ($this->kingTokenIndex == $i) {
                $text .= $this->unichr(Constant::EMO_KING);
            }
            if ($this->ladyLakeTokenIndex == $i) {
                $text .= $this->unichr(Constant::EMO_LADY);
            }
        }
        $text .= "\n\n\n";
        return $text;
    }


    public function getBoardGameRevealedText(){
        $questByPlayer = Constant::$quest[$this->playerCount];
        $text = "";
        for ($i = 0; $i<5;$i++) {
            $text .= "Quest-" . ($i + 1) . "(" . $questByPlayer[$i] . ") " . $this->getQuestStatusString($i) . "\n";
        }
        if ($this->rejectCountInQuest > 0) {
            $text .= "Token Reject = " . $this->getRejectCounterString() . "\n";
        }
        for ($i =0; $i<$this->playerCount; $i++) {
            if ($i > 0) {
                $text .= "\n";
            }
            $role = $this->randomizedRole[$i];
            if (Constant::isGNBPlayer($role) == 1){
                $emoText = $this->unichr(Constant::EMO_SMILE);
            }
            else {
                $emoText = $this->unichr(Constant::EMO_EVIL);
            }

            $text .= $emoText . " " . $this->getPlayerIDString($this->playerIDs[$i])
                ." - ". Constant::getNameByRole($role) ;
            if ($this->kingTokenIndex == $i) {
                $text .= $this->unichr(Constant::EMO_KING);
            }
            if ($this->ladyLakeTokenIndex == $i) {
                $text .= $this->unichr(Constant::EMO_LADY);
            }
        }
        $text .= "\n";
        return $text;
    }

    public function unichr($i) {
        return iconv('UCS-4LE', 'UTF-8', pack('V', $i));
    }

    public function getQuestStatusString($questNo){
        switch ($this->questStatus[$questNo]) {
            case -1: return "[".$this->unichr(Constant::EMO_FAIL)."]";
            case 1: return "[".$this->unichr(Constant::EMO_SUCCESS)."]";
            default: return "[  ]";
        }
    }

    public function getQuestStatusWithCountString($questNo){
        switch ($this->questStatus[$questNo]) {
            case -1: {
                $failCount = $this->questAssigneeIDsHistory[$questNo][Constant::FAIL_COUNT];
                $failCountText = "";
                if ($failCount > 0) {
                    $failCountText = " " .$failCount . " ". $this->langScript[Script::PR_FAIL];
                }
                return "[".$this->unichr(Constant::EMO_FAIL).$failCountText." ]";
            }
            case 1: return "[".$this->unichr(Constant::EMO_SUCCESS)."]";
            default: return "[  ]";
        }
    }

    public function getRejectCounterString(){
        $text = "";
        for ($i = 0; $i<$this->rejectCountInQuest ; $i++) {
            $text.= $this->unichr(Constant::EMO_RED_CIRCLE)." ";
        }
        return $text;
    }

    /***************************************************************************************
     * END STRING FORMATTING
     * *************************************************************************************
     */


    /***************************************************************************************
     * START OTHER INGAME HELPER FUNCTIONS
     * *************************************************************************************
     */

    public function clearFlagRemind(){
        $this->flagRemind1 = false;
        $this->flagRemind2 = false;
    }

    public function getAllBadGuysNoMordred($all_bad_guys_id){
        $all_bad_guys_no_mordred_id = array();
        foreach ($all_bad_guys_id as $bad_guy_id) {
            if ($this->players[$bad_guy_id][Constant::ROLE] != Constant::MORDRED) {
                array_push($all_bad_guys_no_mordred_id, $bad_guy_id);
            }
        }
        return $all_bad_guys_no_mordred_id;
    }

    public function getAllBadGuysNoOberon($all_bad_guys_id){
        $all_bad_guys_no_oberon_id = array();
        foreach ($all_bad_guys_id as $bad_guy_id) {
            if ($this->players[$bad_guy_id][Constant::ROLE] != Constant::OBERON) {
                array_push($all_bad_guys_no_oberon_id, $bad_guy_id);
            }
        }
        return $all_bad_guys_no_oberon_id;
    }

    public function getGameStatus()
    {
        return $this->gameStatus;
    }

    public function getRandomSubsetFromArray($arrayToPick, $howManyToPick){
        $arrayToPickCopy = $arrayToPick;

        $size = count($arrayToPick);

        $randomizedArr = array();
        for ($i=0; $i<$howManyToPick;$i++){
            $pick = rand(0, $size-$i-1);
            $randomizedArr[$i] = $arrayToPickCopy[$pick];
            $arrayToPickCopy[$pick] = $arrayToPickCopy[$size-$i-1];
        }
        return $randomizedArr;
    }

    /***************************************************************************************
     * END OTHER INGAME HELPER FUNCTIONS
     * *************************************************************************************
     */


    /***************************************************************************************
     * START OTHER PLAYER HELPER FUNCTIONS
     * *************************************************************************************
     */

    public function playersToString($playersID){
        $text = "";
        $in = false;
        foreach ($playersID as $playerId) {
            if ($in) { // add comma for the next player
                $text .= ", ";
            }
            $text .= $this->getPlayerIDString($playerId);
            $in = true;
        }
        return $text;
    }

    public function playersToFullNameString($playersID){
        $text = "";
        $in = false;
        foreach ($playersID as $playerId) {
            if ($in) { // add comma for the next player
                $text .= ", ";
            }
            $text .= $this->getPlayerIDFullNameString($playerId);
            $in = true;
        }
        return $text;
    }

    public function playersToFirstNameString($playersID){
        $text = "";
        $in = false;
        foreach ($playersID as $playerId) {
            if ($in) { // add comma for the next player
                $text .= ", ";
            }
            $text .= $this->getPlayerIDFirstNameString($playerId);
            $in = true;
        }
        return $text;
    }

    public function getPlayerIDString($playerId){
        $text = "";
        $full_name = $this->players[$playerId]["full_name"];
        if (isset($this->players[$playerId]["username"])) { //user has username
            $username = $this->players[$playerId]["username"];
            $text .= "<a href=\"http://telegram.me/" . $username . "\">"
                . $full_name . "</a>";
        } else {
            $text .= "<b>". $full_name . "</b>";
        }
        return $text;
    }

    public function getPlayerIDFullNameString($playerId){
        return $this->players[$playerId]["full_name"];
    }

    public function getPlayerIDFirstNameString($playerId){
        return $this->players[$playerId]["first_name"];
    }


    /***************************************************************************************
     * START SEND OTHER MESSAGE
     * *************************************************************************************
     */

    public function sendMerlin(){
        // SCRIPT
        $text = $this->langScript[Script::PU_MERLININFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendPercival(){
        $text = $this->langScript[Script::PU_PERCIVALINFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendServant(){
        $text = $this->langScript[Script::PU_SERVANTINFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendAssassin(){
        $text = $this->langScript[Script::PU_ASSASSININFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendMorgana(){
        $text = $this->langScript[Script::PU_MORGANAINFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendMorgassassin(){
        $text = $this->langScript[Script::PU_MORGASSASSININFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendMordred(){
        $text = $this->langScript[Script::PU_MORDREDINFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendOberon(){
        $text = $this->langScript[Script::PU_OBERONINFO];
        $this->apiSendMessageDirect($text);
    }

    public function sendMaintenance($params, $message) {
        // SCRIPT
        // "Saat ini, bot sedang dalam maintenance. Silakan coba beberapa saat lagi.";
        $text = $this->langScript[Script::PU_MAINTENANCE];
        $this->apiSendMessage($text);
    }

    public function sendSetLangToPrivate($targetID){
        $text = $this->langScript[Script::PR_SETLANGINST];
        if ($this->isGroup) {
            $chatTitle = $this->getChatTitle();
            if (null!= $chatTitle) {
                $text .= sprintf($this->langScript[Script::PR_SETLANGGROUPINST],
                    $chatTitle);
            }
        }
        $params = array(
            'reply_markup'=> array(
                'inline_keyboard' => array(
                    array(
                        array(
                            "text"=>"ENGLISH",
                            "callback_data"=> $this->chatId.":en",
                        )
                    ),
                    array(
                        array(
                            "text"=>"BAHASA INDONESIA",
                            "callback_data"=> $this->chatId.":id",
                        )
                    )
                )
            ),
        );
        $this->sendDEVMessageToPrivate($text, $targetID, $params);
        // response need not to be stored.
        // message id will be retrieved from callback only
//        if ($response['ok']) {
//            // store messageID to be hidden later
//            $this->players[$targetID][Constant::LAST_LANGMSG_ID] =
//                $response["result"]["message_id"];
//        }
    }

    public function sendBlankHistory (){
        // SCRIPT
        // "Tidak ditemukan history untuk game yang sedang berlangsung.";
        $text = $this->langScript[Script::PU_NOHISTFOUND];
        $this->apiSendMessageDirect($text);
    }

    public function sendHistory (){
        $text = "";
        for ($i = 0; $i<5;$i++) {
            // if current quest, stop.
            if ($i == $this->currentQuestNumberStart0) {
                break;
            }
            if (isset($this->questAssigneeIDsHistory[$i])) {

                // "Quest ke-%d %s dipimpin oleh %s %s, dieksekusi oleh %s";
                $text .= sprintf($this->langScript[Script::PU_HISTQEXECBY],
                    ($i + 1),
                    $this->getQuestStatusWithCountString($i),
                    $this->getPlayerIDFirstNameString($this->questAssigneeIDsHistory[$i][Constant::KINGID]),
                    $this->unichr(Constant::EMO_KING),
                    $this->playersToFirstNameString(
                        $this->questAssigneeIDsHistory[$i][Constant::ASSIGNEEIDS]));
                if (count($this->questAssigneeIDsHistory[$i][Constant::REJECTIDS]) > 0) {
                    // ", ditolak oleh %s\n\n";
                    $text .= sprintf($this->langScript[Script::PU_HISTQREJECTBY],
                        $this->playersToFirstNameString(
                            $this->questAssigneeIDsHistory[$i][Constant::REJECTIDS]));
                } else {
                    $text .= "\n\n";
                }
            }
            else { // this is not rset because 5 times reject
                // "Quest ke-%d [%s 5x REJECT]\n\n";
                $text .= sprintf($this->langScript[Script::PU_HISTQFAILREJ],
                    ($i + 1),
                    $this->unichr(Constant::EMO_FAIL));
            }
        }
        $this->apiSendMessageDirect($text);
    }


//    public function some_command($command, $params, $message) {
//
//    }



    protected function sendHelp() {
        // SCRIPT
        $text = $this->langScript[Script::PU_HELP];
        $this->apiSendMessageDirect($text);
    }

    protected function rateMe() {
        // SCRIPT
        $text = sprintf($this->langScript[Script::PU_RATEME],
            $this->core->botUsername);
        $this->apiSendMessageDirect($text);
    }

    protected function sendContact() {
        // SCRIPT
        $text = sprintf($this->langScript[Script::PU_CONTACT],
            $this->core->botUsername);
        $this->apiSendMessageDirect($text);
    }

    protected function sendHowToPlay() {
        // SCRIPT
        $text = $this->langScript[Script::PU_HOWTOPLAY];
        $this->apiSendMessageDirect($text);
    }

    protected function sendCreateSuccessToGroup($sender_id) {
        $text = sprintf($this->langScript[Script::PU_JOINSTART],
            $this->getPlayerIDString($sender_id),
            Constant::getMode($this->mode),
            Constant::$_startGame);
        $this->apiSendMessageDirect($text);
    }

    protected function sendDEVMessageToPrivate($text, $targetID, $params=array()) {
        if (Constant::$DEVELOPMENT) {
            return $this->apiSendMessageToTarget($text,"286457946", $params);
        }
        else {
            return $this->apiSendMessageToTarget($text, $targetID, $params);
        }
    }

    protected function sendJoinSuccessToGroup($sender_id) {
        // SCRIPT
        // "%s bergabung. <b>%d</b> pemain. min <b>%d</b>. max <b>%d</b>.";
        $text = sprintf($this->langScript[Script::PU_JOINSUCCESS],
            $this->getPlayerIDString($sender_id),
            count($this->playerIDs),
            Constant::getMinPlayer(),
            Constant::getMaxPlayer());
        $this->apiSendMessage($text);
    }

    protected function sendPrivateStats($message_from){
        // Script
        // Player Name
        // Normal Mode - 100x play,
        // EMOGOOD 70x winrate 50%,
        // EMOBAD  30x winrate 80%
        // Chaos Mode - 100x play,
        // EMOGOOD     70x winrate 50%,
        // EMONEUTRAL  30x winrate 80%
        // EMOBAD      30x winrate 80%

        if ($this->redis instanceof Predis\Client) {
            $retry = 0;
            while ($retry < 2) {
                try {
                    $playerKey = $message_from["id"] . "_stats";
                    $redisGet = $this->redis->hgetall($playerKey);

                    $first_name = $message_from["first_name"];
                    $last_name = "";
                    if (isset($message_from["last_name"])) {
                        $last_name = $message_from["last_name"];
                    }
                    $full_name = trim($first_name . " " . $last_name);
                    $text = "<b>" . $full_name . "</b>\n";

                    $hasStat = false;
                    if ( isset($redisGet["Np"] )) { // has player normal
                        $hasStat = true;
                        $normalPlay = $redisGet["Np"];
                        $text .= "NORMAL MODE - played ".$normalPlay . " times. ";
                        if (isset($redisGet["Nw"])) { // has normal win value
                            $winrate = round (100* $redisGet["Nw"] / $normalPlay);
                            $text .= " Winrate: ". $winrate . "%\n";
                        }
                        if (isset($redisGet["Ng"])) { // has normal good value
                            $text .= $this->unichr(Constant::EMO_SMILE)." ". $redisGet["Ng"] ." times. ";
                            if (isset($redisGet["Ngw"])) { // has normal good win value
                                $winrate = round (100* $redisGet["Ngw"] / $redisGet["Ng"]);
                                $text .= " Winrate: ". $winrate . "%\n";
                            }
                        }
                        if (isset($redisGet["Nb"])) { // has normal bad value
                            $text .= $this->unichr(Constant::EMO_EVIL)." ". $redisGet["Nb"] ." times. ";
                            if (isset($redisGet["Nbw"])) { // has normal bad win value
                                $winrate = round (100* $redisGet["Nbw"] / $redisGet["Nb"]);
                                $text .= " Winrate: ". $winrate . "%\n";
                            }
                        }
                    }
                    if (isset($redisGet["Cp"] )){ // has played chaos
                        $hasStat = true;
                        $chaosPlay = $redisGet["Cp"];
                        $text .= "CHAOS MODE - played ".$chaosPlay . " times. ";
                        if (isset($redisGet["Cw"])) { // has chaos win value
                            $winrate = round (100* $redisGet["Cw"] / $chaosPlay);
                            $text .= " Winrate: ". $winrate . "%\n";
                        }
                        if (isset($redisGet["Cg"])) { // has normal good value
                            $text .= $this->unichr(Constant::EMO_SMILE). " ". $redisGet["Cg"] ." times. ";
                            if (isset($redisGet["Cgw"])) { // has normal good win value
                                $winrate = round (100* $redisGet["Cgw"] / $redisGet["Cg"]);
                                $text .= " Winrate: ". $winrate . "%\n";
                            }
                        }
                        if (isset($redisGet["Cb"])) { // has chaos bad value
                            $text .= $this->unichr(Constant::EMO_EVIL). " ". $redisGet["Cb"] ." times. ";
                            if (isset($redisGet["Cbw"])) { // has chaos bad win value
                                $winrate = round (100* $redisGet["Cbw"] / $redisGet["Cb"]);
                                $text .= " Winrate: ". $winrate . "%\n";
                            }
                        }
                    }
                    if (!$hasStat) {
                        $text .= "You have to play a game to have the statistics\n";
                    }

                    $this->apiSendMessageDirect($text);
                    break;
                } catch (Exception $e) {
                    $this->core->redis = false;
                    $this->core->dbInit();
                    $this->redis = $this->core->redis;
                }
                $retry++;
            };
        }
    }

    protected function sendGlobalStats(){
        // Script
        // Avalon
        // Normal Mode - 100x play,
        // EMOGOOD win 50%, EMOBAD win 80%, EMONEUTRAL winrate 80%

        if ($this->redis instanceof Predis\Client) {
            $retry = 0;
            while ($retry < 2) {
                try {
                    $redisGet = $this->redis->hgetall("stats");
                    $text = "<b>Avalon Bot for Telegram</b>\n";

                    $hasStat = false;
                    if ( isset($redisGet["Np"] )) { // has player normal
                        $hasStat = true;
                        $normalPlay = $redisGet["Np"];
                        $text .= "NORMAL MODE - played ".$normalPlay . " times.\n";
                        if (isset($redisGet["Ngw"])) { // has normal good win value
                            $winrate = round (100* $redisGet["Ngw"] / $normalPlay);
                            $text .= $this->unichr(Constant::EMO_SMILE)." Good Team Won ". $winrate . "%\n";
                        }
                        if (isset($redisGet["Nbw"])) { // has normal bad win value
                            $winrate = round (100* $redisGet["Nbw"] / $normalPlay);
                            $text .= $this->unichr(Constant::EMO_EVIL)." Evil Team Won ". $winrate . "%\n";
                        }
                    }
                    if (!$hasStat) {
                        $text .= "This bot has no statistics yet.\n";
                    }

                    $this->apiSendMessageDirect($text);
                    break;
                } catch (Exception $e) {
                    $this->core->redis = false;
                    $this->core->dbInit();
                    $this->redis = $this->core->redis;
                }
                $retry++;
            };
        }
    }

    protected function sendGroupStats($message){
        // Script
        // Avalon
        // Normal Mode - 100x play,
        // EMOGOOD win 50%, EMOBAD win 80%, EMONEUTRAL winrate 80%

        if ($this->redis instanceof Predis\Client) {
            $retry = 0;
            while ($retry < 2) {
                try {
                    $key = $this->chatId . "_stats";
                    $redisGet = $this->redis->hgetall($key);
                    $text = "<b>".$message["chat"]["title"]."</b>\n";

                    $hasStat = false;
                    if ( isset($redisGet["Np"] )) { // has player normal
                        $hasStat = true;
                        $normalPlay = $redisGet["Np"];
                        $text .= "NORMAL MODE - played ".$normalPlay . " times.\n";
                        if (isset($redisGet["Ngw"])) { // has normal good win value
                            $winrate = round (100* $redisGet["Ngw"] / $normalPlay);
                            $text .= $this->unichr(Constant::EMO_SMILE)." Good Team Won ". $winrate . "%\n";
                        }
                        if (isset($redisGet["Nbw"])) { // has normal bad win value
                            $winrate = round (100* $redisGet["Nbw"] / $normalPlay);
                            $text .= $this->unichr(Constant::EMO_EVIL)." Evil Team Won ". $winrate . "%\n";
                        }
                    }
                    if (!$hasStat) {
                        $text .= "This group has no statistics yet.\n";
                    }

                    $this->apiSendMessageDirect($text);
                    break;
                } catch (Exception $e) {
                    $this->core->redis = false;
                    $this->core->dbInit();
                    $this->redis = $this->core->redis;
                }
                $retry++;
            };
        }
    }

    protected function sendJoinFullToGroup($message_from) {
        $first_name = $message_from["first_name"];
        $last_name = "";
        if (isset($message_from["last_name"])) {
            $last_name = $message_from["last_name"];
        }
        $full_name = trim ($first_name. " ". $last_name );
        $username = $message_from["username"];

        if (isset($username)) { //user has username
            $text = "<a href=\"http://telegram.me/".$username."\">"
                .$full_name."</a>";
        }
        else {
            $text = "<b>".$full_name."</b>";
        }
        // "tidak bisa bergabung. Sudah %d pemain.";
        $text .= sprintf($this->langScript[Script::PU_CANNOTJOINFULL],
            Constant::getMaxPlayer());
        $this->apiSendMessageDirect($text);
    }

    protected function sendStartMeFirstToGroup($message_from) {
        $first_name = "";
        if (isset($message_from["first_name"])){
            $first_name = $message_from["first_name"];
        }
        if (isset($message_from["last_name"])) {
            $last_name = $message_from["last_name"];
            $full_name = trim ($first_name. " ". $last_name );
        }
        else {
            $full_name = $first_name;
        }

        if (isset($message_from["username"])) { //user has username
            $text = "<a href=\"http://telegram.me/".$message_from["username"]."\">"
                .$full_name."</a>";
        }
        else {
            $text = "<b>".$full_name."</b>";
        }
        // SCRIPT
        // " tidak bisa bergabung.";
        $text .= $this->langScript[Script::PU_CANNOTJOIN];

        //SCRIPT
        // " <a href=\"http://telegram.me/%s\">Start Me</a> terlebih dahulu.";
        $text .= sprintf($this->langScript[Script::PU_STARTMEFIRST],
            $this->core->botUsername);
        $this->apiSendMessage($text);


    }


    protected function sendCreateFirstToGroup (){
        // SCRIPT
        // "Game belum distart. Ketik /start untuk memulai Avalon.";
        $this->apiSendMessage($this->langScript[Script::PU_CREATEFIRST]);
    }

    protected function sendGameStartedToGroup() {
        // SCRIPT
        // "Game sudah dimulai. Silakan cek PM masing-masing untuk melihat peran.";
        $this->apiSendMessage($this->langScript[Script::PU_GAMESTART]);
    }

    protected function sendGameCanceledToGroup() {
        // SCRIPT
        // "Game dibatalkan karena tidak cukup pemain. Ayo ajak teman-temanmu untuk join";
        $this->apiSendMessageDirect($this->langScript[Script::PU_GAMECANCEL]);
    }

    public function sendCreate60SECToGroup(){
        // "<b>%d detik</b> lagi. Ayo ajak teman-temanmu untuk /join.";
        $text = sprintf($this->langScript[Script::PU_JOINREMIND],
            (Constant::$_startGame - Constant::$_startGame_r1));
        $this->apiSendMessage($text);
    }

    public function sendCreate90SECToGroup(){
        // "<b>%d detik</b> lagi. Ayo ajak teman-temanmu untuk /join.";
        $text = sprintf($this->langScript[Script::PU_JOINREMIND],
            (Constant::$_startGame - Constant::$_startGame_r2));
        $this->apiSendMessage($text);
    }


    protected function sendWarningOnlyGroup() {
        // "Kamu harus berada di grup untuk dapat menggunakan perintah ini.";
        $text = $this->langScript[Script::PR_GROUPONLY];
        $this->apiSendMessageDirect($text);
    }

    protected function sendOnlyAdmin() {
        // "Hanya admin yang dapat menggunakan perintah ini.";
        $text = $this->langScript[Script::PU_ADMINONLY];
        $this->apiSendMessageDirect($text);
    }

    /***************************************************************************************
     * END SEND OTHER MESSAGE
     * *************************************************************************************
     */


}