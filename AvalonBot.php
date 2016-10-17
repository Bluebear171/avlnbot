<?php
require_once 'TelegramBot.php';
require_once 'Constant.php';

class AvalonBot extends TelegramBot {
    public function init() {
        parent::init();
        Constant::init();
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

    public function __construct($core, $chat_id) {
        parent::__construct($core, $chat_id);
    }

    //public function init() {
        //$this->curPoll = $this->dbGetPoll();
    //}

    public function command_start($params, $message) {
        $this->startGameWithMode(Constant::MODE_NORMAL, $params, $message);
    }

    public function command_startlotl($params, $message) {
        $this->startGameWithMode(Constant::MODE_LADY_OF_LAKE, $params, $message);
    }

    public function startGameWithMode($mode, $params, $message) {
        if (!$this->isGroup) {
            $this->sendWarningOnlyGroup();
        } else {
            if ($this->gameStatus == Constant::NOT_CREATED){
                $response = $this->apiSendMessageToTarget(
                    "Kamu telah membuat permainan baru - ".
                        Constant::getMode($mode)." di group ".$message["chat"]["title"] ,
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

                    $message6["from"]["id"] = "295076115";
                    $message6["from"]["first_name"] = "testPaulana";
//                    $message6["from"]["last_name"] = "ululu";
//                    $message6["from"]["username"] = "Paulanakho";
                    $this->addNewPlayer($message6["from"]);
                    $this->sendJoinSuccessToGroup($message6["from"]["id"]);

                    $message7["from"]["id"] = "291655534";
                    $message7["from"]["first_name"] = "testHerman";
////                    $message7["from"]["last_name"] = "ululu";
////                    $message7["from"]["username"] = "chrono06";
                    $this->addNewPlayer($message7["from"]);
                    $this->sendJoinSuccessToGroup($message7["from"]["id"]);
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

                        $response = $this->apiSendMessageToTarget(
                            "Kamu telah bergabung Avalon di group ".$message["chat"]["title"] ,
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
                    $text = "Raja sudah mendapatkan pencerahan dan akhirnya memutuskan untuk mengakhiri diskusi tiada akhir ini";
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
                    $text = $this->getPlayerIDFullNameString($sender_id) . " meng-approve quest.";
                    $text .= " Approve <b>". $this->approveAssigncount. "</b>. Reject <b>". $this->rejectAssignCount. "</b>.";
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
                    $text = $this->getPlayerIDFullNameString($sender_id) . " mengganti jawaban menjadi approve.";
                    $text .= " Approve <b>". $this->approveAssigncount. "</b>. Reject <b>". $this->rejectAssignCount. "</b>.";
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
                    $text = $this->getPlayerIDFullNameString($sender_id) . " me-reject quest.";
                    $text .= " Approve <b>". $this->approveAssigncount. "</b>. Reject <b>"
                        . $this->rejectAssignCount. "</b>.";
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
                    $text = $this->getPlayerIDFullNameString($sender_id) . " mengganti jawaban menjadi reject.";
                    $text .= " Approve <b>". $this->approveAssigncount. "</b>. Reject <b>"
                        . $this->rejectAssignCount. "</b>.";
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

    // already count reject, reject is more or half the playercount
    public function rejectCurrentQuest(){
        $this->rejectCountInQuest++;
        if ($this->rejectCountInQuest == 5) {
            $this->failCurrentQuest();
        }
        else { // reject still less than 5, can continue this quest
            //change king to next
            $this->nextKing();

            $text = "Karena Quest di-reject oleh sebagian besar tim, quest dibatalkan dan king berpindah ke ".
                $this->getPlayerIDString($this->playerIDs[$this->kingTokenIndex]) . ".";
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
        $text .= "Sebelum menunjuk <b>".
            $personNeedToCurrentQuest . " orang</b>, ". $this->getPlayerIDString($kingPlayerID).
            " sebagai raja boleh berdiskusi dengan team.\n";
        $text .= "Waktu untuk berdiskusi adalah <b>"
            .Constant::$_discussAssignQuestGroup
            ." detik</b>. Raja boleh mengetik /done untuk mengakhiri diskusi. Klik /questhistory untuk melihat history.";
        $this->apiSendMessage($text);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
    }

    // approve is more
    public function approveCurrentQuest(){
        $text = "Sebagian besar tim meng-approve, quest pun akan dijalankan!";
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

                $text = "Quest ke-".($this->currentQuestNumberStart0+ 1).". Apa yang ingin kamu pilih?";
                if (Constant::$DEVELOPMENT) {
                    $text .= " " . $this->getPlayerIDString($questAssigneeID);
                }
                $params = array(
                    'reply_markup'=> array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    "text"=>"SUKSES",
                                    "callback_data"=> $this->chatId.":1",
                                )
                            ),
                            array(
                                array(
                                    "text"=>"GAGAL",
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
                $text = "Kamu orang baik. Kamu pun berusaha untuk menyelesaikan quest dengan sebaik-baiknya.";
                if (Constant::$DEVELOPMENT) {
                    $text .= " " . $this->getPlayerIDString($questAssigneeID);
                }
                $this->sendDEVMessageToPrivate($text,$questAssigneeID);
            }
        }

        $text = $this->playersToString($this->questAssigneeIDs) . " pergi untuk menyelesaikan quest. ";
        $text .= "Waktu yang diberikan <b>".Constant::$_execQuestPrivate. "</b> detik";
        $this->apiSendMessage($text);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
    }

    public function checkTimer(){
        // created and waiting for players
        switch ($this->gameStatus) {
            case Constant::CREATED :
                // if player count already enough, the change status to START RANDOM ROLES
                $playercount = count($this->playerIDs);
                if ($playercount == Constant::getMaxPlayer()){
                    $this->playerCount = $playercount;
                    $this->gameStatus = Constant::START_RANDOM_ROLES;
                    $this->sendGameStartedToGroup();
                    $this->assigningRandomRoles();
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
                        if ($playercount >= Constant::getMinPlayer($this->mode)){
                            $this->playerCount = $playercount;
                            $this->gameStatus = Constant::START_RANDOM_ROLES;
                            $this->sendGameStartedToGroup();
                            $this->assigningRandomRoles();
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
                            $textPrivate = "Jawabanmu terlambat, sisa player dipilih secara random.";
                            if (Constant::$DEVELOPMENT) {
                                $this->apiEditMessageText($textPrivate, $messageID, "286457946");
                            } else {
                                $this->apiEditMessageText($textPrivate, $messageID, $kingID);
                            }
                        }

                        $text = "Karena waktu habis, sisa pemain dipilih secara random: ".
                            $this->playersToFullNameString($pickIDs);
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
                        $text = "Karena waktu habis, pemain lain dianggap memilih approve..";
                        $this->apiSendMessage($text);
                        $this->approveCurrentQuest();
                    }
                    else if (! $this->flagRemind1
                        && $difftime >= Constant::$_execApproveRejectGroup_r1
                        && $difftime <= (Constant::$_execApproveRejectGroup_r1 + Constant::THRES_REMIND)){
                        $text = "Pejuang di quest ini ". $this->playersToString($this->questAssigneeIDs) ."\n";
                        $text .= "\n\n<b>".(Constant::$_execApproveRejectGroup - Constant::$_execApproveRejectGroup_r1)
                            ." detik</b>  lagi untuk /approve atau /reject. Jika ada minimal <b>".
                            Constant::$two_fails_required[$this->playerCount][$this->currentQuestNumberStart0]
                            . " pemain</b> menggagalkan quest, maka quest akan dianggap gagal!";
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
                            // 2 quest pertama paksa berhasil
                            if ($this->currentQuestNumberStart0 < 2) {
                                $this->badGuyAssigneeChoices[$key] = 1;
                                $this->success_count_by_badguy++;
                                if (isset($this->players[$key][Constant::LAST_MESSAGE_ID])) {
                                    $messageID = $this->players[$key][Constant::LAST_MESSAGE_ID];
                                    $textPrivate = "Jawabanmu terlambat. Boss memaksamu untuk memberikan pencitraan yang baik.";
                                    if (Constant::$DEVELOPMENT) {
                                        $textPrivate .= $this->getPlayerIDFullNameString($key);
                                        $this->apiEditMessageText($textPrivate, $messageID, "286457946");
                                    } else {
                                        $this->apiEditMessageText($textPrivate, $messageID, $key);
                                    }
                                }
                            }
                            else { // quest index ke 2,3,4, always fail
                                $this->badGuyAssigneeChoices[$key] = -1;
                                $this->fail_count_by_badguy++;
                                if (isset($this->players[$key][Constant::LAST_MESSAGE_ID])) {
                                    $messageID = $this->players[$key][Constant::LAST_MESSAGE_ID];
                                    $textPrivate = "Jawabanmu terlambat. Kamu dipaksa menggagalkan quest dari boss.";
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
                    $text = "<b>". (Constant::$_discussAssignQuestGroup - Constant::$_discussAssignQuestGroup_r1)
                        ." detik</b> lagi untuk berdiskusi... "
                        .$this->getPlayerIDFullNameString($this->playerIDs[$this->kingTokenIndex])
                        ." boleh mengetik /done jika sudah mendapat pencerahan.";
                    $this->apiSendMessage($text);
                    $this->flagRemind1 = true;
                }
                else if (!$this->flagRemind2
                    && $difftime >= Constant::$_discussAssignQuestGroup_r2
                    && $difftime <= (Constant::$_discussAssignQuestGroup_r2 + Constant::THRES_REMIND)){
                    $text = "<b>".(Constant::$_discussAssignQuestGroup - Constant::$_discussAssignQuestGroup_r2)
                        ." detik</b> lagi untuk berdiskusi... "
                        .$this->getPlayerIDFullNameString($this->playerIDs[$this->kingTokenIndex])
                        ." boleh mengetik /done jika sudah mendapat pencerahan.";
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
                        $text = "Kamu terlambat memilih untuk menerawang..";
                        if (Constant::$DEVELOPMENT) {
                            $this->apiEditMessageText($text, $messageID, "286457946");
                        } else {
                            $this->apiEditMessageText($text, $messageID, $ladyID);
                        }
                    }

                    $text = $this->getPlayerIDFullNameString($ladyID)
                        . " terlambat memilih sehingga tidak bisa menerawang.";
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
                        $text = "Kamu terlambat memilih untuk membunuh Merlin..";
                        if (Constant::$DEVELOPMENT) {
                            $this->apiEditMessageText($text, $messageID, "286457946");
                        } else {
                            $this->apiEditMessageText($text, $messageID, $this->assassinID);
                        }
                    }

                    $text = $this->getPlayerIDFullNameString($this->assassinID)
                        . " terlambat memilih.. Sepertinya Merlin selamat kali ini..";
                    $this->apiSendMessage($text);

                    $this->goodGuysWinTheGame();
                }
                else if (! $this->flagRemind1
                    && $difftime >= Constant::$_execKillMerlin_r1
                    && $difftime <= (Constant::$_execKillMerlin_r1 + Constant::THRES_REMIND)){
                    $text = "<b>".(Constant::$_execKillMerlin - Constant::$_execKillMerlin_r1)
                        ." detik</b> lagi waktu yang dibutuhkan assassin untuk membunuh Merlin...";
                    $this->apiSendMessage($text);
                    $this->flagRemind1 = true;
                }
                else if (!$this->flagRemind2
                    && $difftime >= Constant::$_execKillMerlin_r2
                    && $difftime <= (Constant::$_execKillMerlin_r2 + Constant::THRES_REMIND)){
                    $text = "<b>".(Constant::$_execKillMerlin - Constant::$_execKillMerlin_r2)
                        ." detik</b> lagi waktu yang dibutuhkan assassin untuk membunuh Merlin...";
                    $this->apiSendMessage($text);
                    $this->flagRemind2 = true;
                }
            }
            break;
            // END case Constant::EXEC_LADY_OF_LAKE_PRIVATE
        }
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
            if (! Constant::isGoodPlayer($role)) {
                array_push($this->all_bad_guys_id, $playerID);
            }
            if ($role == Constant::MERLIN || $role == Constant::MORGANA) {
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
                    $text = "Kamu adalah Merlin. Aura jahat terpancar kuat dari ". $this->playersToString($all_bad_guys_no_mordred_id);
                    $text .= ". Pandu timmu dalam quest tanpa ketahuan tim jahat!";
                    $this->merlinID = $playerID;
                    break;
                case Constant::PERCIVAL:
                    $text = "Kamu adalah Percival. Kamu melihat ". $this->playersToString($morgana_and_merlin_ids);
                    $text .= " sebagai Merlin, namun salah satu dari mereka mungkin fake.";
                    break;
                case Constant::GOOD_NORMAL:
                    $text = "Kamu adalah Rakyat jelata yang baik. Kamu tidak tahu menahu,";
                    $text .= " yang penting ikut menyukseskan quest dan mengikuti perintah raja. (T_T)";
                    break;
                case Constant::MORDRED:
                    $text = "Kamu adalah Mordred. Tim jahatmu adalah ". $this->playersToString($all_bad_guys_no_oberon_id);
                    $text .= ". Merlin tidak tahu bahwa kamu orang jahat. ULULULULU..";
                    break;
                case Constant::ASSASSIN:
                    $text = "Kamu adalah Assassin. Tim jahatmu adalah ". $this->playersToString($all_bad_guys_no_oberon_id);
                    $text .= ". Di akhir permainan, kamu bisa membunuh Merlin untuk menang.";
                    $this->assassinID = $playerID;
                    break;
                case Constant::MORGANA:
                    $text = "Kamu adalah Morgana. Tim jahatmu adalah ". $this->playersToString($all_bad_guys_no_oberon_id);
                    $text .= ". Di mata Percival, kamu adalah Merlin.";
                    break;
                case Constant::OBERON:
                    $text = "Kamu adalah Oberon. Kamu tidak tahu tim jahatmu siapa ";
                    $text .= "dan mereka juga tidak tahu kamu.. :'( ";
                    $this->oberonID = $playerID;
                    break;
                case Constant::BAD_NORMAL:
                    $text = "Kamu adalah Pejahat kacangan. ";
                    $text .= "Tim jahatmu adalah ". $this->playersToString($all_bad_guys_no_oberon_id);
                    $text .= ".";
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

//        echo "<br /> Bad Guys: <br />";
//        print_r($all_bad_guys_id);

        $this->currentQuestNumberStart0 = 0;
        $this->questStatus = array(0,0,0,0,0);
        $this->kingTokenIndex = rand(0, $this->playerCount - 1);
        if ($this->mode == Constant::MODE_LADY_OF_LAKE) {
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
        $text .= $this->getPlayerIDString($kingPlayerID).
            " sebagai raja akan menunjuk <b>".
            $personNeedToCurrentQuest . " orang</b> untuk menyelesaikan quest.\n";
        $text .= "Waktu untuk memberikan penugasan adalah <b>"
            .Constant::$_assignQuestPrivate ." detik</b>.\n";
        $this->apiSendMessage($text);

        $this->startTimeStamp = $this->core->getCurrentTime();
        $this->clearFlagRemind();
        // send to private
        $this->sendAssignOnePlayerToPrivate($kingPlayerID);
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
        $text .= $this->getPlayerIDString($kingPlayerID).
            " telah menunjuk ".$this->playersToString($this->questAssigneeIDs)." untuk menyelesaikan quest.\n";
        $text .= "Saatnya berdiskusi.. Jika setuju, ketik /approve. Jika tidak setuju, ketik /reject.\n";
        $text .= "Waktu yang diberikan adalah <b>"
            .Constant::$_execApproveRejectGroup ." detik</b>.\n";
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
        if ($this->rejectAssignCount == 5) {
            $text = "Quest sudah di-reject 5 kali, sehingga dianggap gagal.\n";
        }
        else { // fail because fail_count is bigger than requirement
            $failCount = $this->fail_count_by_badguy;
            $text = "Dalam menyelesaikan quest ditemukan <b>"
                .$failCount." FAIL</b>! Quest dianggap gagal.";

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
        $text = "Para penjahat menang! Mereka memang sudah berpengalaman lebih dari 10 dekade..";
        $this->apiSendMessage($text);

        $this->revealAllRoles();
        $this->gameStatus = Constant::NOT_CREATED;
    }

    public function goodGuysWinTheGame(){
        $text = "Selamat! Kalian tim baik memang kompak dan pintar menipu orang jahat..";
        $this->apiSendMessage($text);

        $this->revealAllRoles();
        $this->gameStatus = Constant::NOT_CREATED;
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
            $text = "Akhirnya kamu tahu juga teman jahat seperjuanganmu.. Mereka adalah ";
            $text .= $this->playersToFullNameString($bad_guys_no_mordred_and_oberon_id) .".";
            $this->sendDEVMessageToPrivate($text,$this->oberonID);
        }

        if ($this->currentQuestNumberStart0 >= 2 && $this->ladyLakeTokenIndex > -1) {
            // do lady of the lake
            $this->gameStatus = Constant::EXEC_LADY_OF_LAKE_PRIVATE;

            $ladyToken = $this->ladyLakeTokenIndex;
            $ladyPlayerID = $this->playerIDs[$ladyToken];

            $text = $this->getPlayerIDString($ladyPlayerID).
                " sebagai Lady of the Lake dapat menggunakan kekuatannya untuk menerawang salah seorang anggota tim.";
            $text .= "Diberikan waktu <b>". Constant::$_execLadyOfTheLakePrivate
                ." detik</b>. Anggota tim lain boleh memberikan petunjuk...";
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

        $text = "3 Quest berhasil disukseskan oleh tim. Namun, tim jahat masih mempunyai senjata terakhir. ";
        $text .= "Tim jahat membuka kedok mereka ". $this->playersToString($this->all_bad_guys_id);
        $text .= ".. Jika assassin berhasil menebak merlin, maka tim jahatlah yang menang! ";
        $text .= "Diberikan waktu <b>". Constant::$_execKillMerlin ." detik</b>.";

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

            $text = "Quest berhasil diselesaikan dengan baik sekali. ";
            if ($this->fail_count_by_badguy > 1) {
                $text .= " Namun, pengawal menemukan <b>".$this->fail_count_by_badguy." FAIL </b> dalam quest ini.. ";
            }
            $this->apiSendMessage($text);

            $this->currentQuestNumberStart0++;
            $this->nextKing();
            $this->execLadyOfTheLakePrivate();
        }
    }

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
            if (Constant::isGoodPlayer($role) ){
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
                    $failCountText = " " .$failCount . " FAIL";
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






    public function command_help($params, $message) {
        $this->sendHelp();
    }
    public function bot_added_to_chat($message) {
        $this->sendHelp();
    }

    public function sendMaintenance($params, $message) {
        $text = "Currently there is a maintenance for avalon bot.
                 \nPlease try to connect 5 minutes later.";
        $this->apiSendMessage($text);
    }

    public function sendBlankHistory (){
        $text = "Tidak ditemukan history untuk game yang sedang berlangsung.";
        $this->apiSendMessage($text);
    }

    public function sendHistory (){
        $text = "";
        for ($i = 0; $i<5;$i++) {
            // if current quest, stop.
            if ($i == $this->currentQuestNumberStart0) {
                break;
            }
            $text .= "Quest ke-".($i+1)." ". $this->getQuestStatusWithCountString($i) . " dipimpin oleh ".
                    $this->getPlayerIDFirstNameString($this->questAssigneeIDsHistory[$i][Constant::KINGID]).
                    $this->unichr(Constant::EMO_KING).", dieksekusi oleh ".
                    $this->playersToFirstNameString(
                        $this->questAssigneeIDsHistory[$i][Constant::ASSIGNEEIDS]);
            if (count($this->questAssigneeIDsHistory[$i][Constant::REJECTIDS])>0) {
                $text .= ", di-reject oleh ".
                $this->playersToFirstNameString(
                    $this->questAssigneeIDsHistory[$i][Constant::REJECTIDS])."\n\n";
            }
            else {
                $text .= "\n\n";
            }
        }
        $this->apiSendMessage($text);
    }




    public function callback($messageID, $from, $dataString){
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
                    $text = "Kamu berhasil memilih " .
                        $this->getPlayerIDFullNameString($assignedPlayerID) . " dalam quest.";
                    $this->apiEditMessageText($text, $messageID, $from["id"]);

                    $text = $this->getPlayerIDFullNameString($from["id"]) . " memilih " .
                        $this->getPlayerIDFullNameString($assignedPlayerID) . " dalam quest.";
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
                        $text = "Meskipun kamu jahat, kamu berhasil membuat pencitraan yang baik.";
                    }
                    else {
                        $this->fail_count_by_badguy++;
                        $this->badGuyAssigneeChoices[$from["id"]] = -1;
                        $text = "Kamu berhasil menggagalkan quest.";
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
                        $text = "Kamu memilih untuk tidak menerawang..";
                        $this->apiEditMessageText($text, $messageID, $from["id"]);

                        $text = $this->getPlayerIDFullNameString($from["id"])
                            . " memilih untuk tidak menerawang.";
                        $this->apiSendMessage($text);

                        $this->discussBeforeAssigningQuest();
                    } else {
                        $chosenPlayerID = $dataString;
                        if (!in_array($chosenPlayerID, $this->lady_of_the_lake_holderIDs)
                            &&
                            in_array($chosenPlayerID, $this->playerIDs)
                            ) {
                            $isGoodGuy = Constant::isGoodPlayer($this->players[$chosenPlayerID][Constant::ROLE]);
                            $text = "Kamu berhasil menerawang " .
                                $this->getPlayerIDFullNameString($chosenPlayerID) . ".. Dia adalah orang ";
                            $text .= $isGoodGuy? "baik." : "jahat.";
                            $this->apiEditMessageText($text, $messageID, $from["id"]);

                            $text = $this->getPlayerIDFullNameString($from["id"]) . " menerawang " .
                                $this->getPlayerIDFullNameString($chosenPlayerID) . ".";
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

                    $text = "Kamu berhasil membunuh " .
                        $this->getPlayerIDFullNameString($merlinIDToKill) . ".";
                    $this->apiEditMessageText($text, $messageID, $from["id"]);

                    $text = $this->getPlayerIDString($this->assassinID) ." berhasil membunuh " .
                        $this->getPlayerIDString($merlinIDToKill) ." dan ternyata " .
                        $this->getPlayerIDString($merlinIDToKill) ;
                    if ($isMerlin) {
                        $text .= " adalah <b>MERLIN</b>!";
                    }
                    else {
                        $text .= " <b>bukan MERLIN!</b>";
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


//    public function some_command($command, $params, $message) {
//
//    }







    protected function sendHelp() {
        if ($this->isGroup) {
            $text = "Avalon for Telegram. Play avalon while chatting with your friends on Telegram! type /start if you are ready!";
        } else {
            $text = "Avalon for Telegram. Invite to your group and type /start to play Avalon";
        }
        $this->apiSendMessage($text);
    }

    protected function sendCreateSuccessToGroup($sender_id) {
        $text = $this->getPlayerIDString($sender_id) . " telah memulai Avalon - "
                .Constant::getMode($this->mode).". Ketik /join untuk bergabung.";
        $text.= " <b>".Constant::$_startGame." detik</b> lagi.";
        $this->apiSendMessage($text);
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
        $text = "Pilih orang ke-". ($countcurrassignee+1).
            " (dari ".Constant::$quest[$this->playerCount][$this->currentQuestNumberStart0] .
            " orang) untuk menyelesaikan quest";
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

        $text = "Bunuh Merlin.";
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

        $text = "Pilih orang untuk diterawang.";
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

    protected function sendDEVMessageToPrivate($text, $targetID, $params=array()) {
        if (Constant::$DEVELOPMENT) {
            return $this->apiSendMessageToTarget($text,"286457946", $params);
        }
        else {
            return $this->apiSendMessageToTarget($text, $targetID, $params);
        }
    }

    protected function sendJoinSuccessToGroup($sender_id) {
        $text = $this->getPlayerIDString($sender_id) ." bergabung. ";
        $text.= "<b>".count($this->playerIDs). "</b> pemain. min <b>".
            Constant::getMinPlayer($this->mode)."</b>. max <b>".Constant::getMaxPlayer()."</b>.";
        $this->apiSendMessage($text);
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
                .$full_name."</a> tidak bisa bergabung. Sudah ".Constant::getMaxPlayer()." pemain. ";
        }
        else {
            $text = "<b>".$full_name."</b> tidak bisa bergabung. Sudah ".Constant::getMaxPlayer()." pemain.";
        }
        $this->apiSendMessage($text);
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
                .$full_name."</a> tidak bisa bergabung.";
        }
        else {
            $text = "<b>".$full_name."</b> tidak bisa bergabung.";
        }
        $text .= " <a href=\"http://telegram.me/".$this->core->botUsername."\">Start Me</a> terlebih dahulu";
        $this->apiSendMessage($text);
    }

    protected function sendCreateFirstToGroup (){
        $this->apiSendMessage("Game belum distart. Ketik /start atau /startlotl untuk memulai Avalon");
    }

    protected function sendGameStartedToGroup() {
        $this->apiSendMessage("Game sudah dimulai. Silakan check PM masing-masing untuk melihat peran kalian.");
    }

    protected function sendGameCanceledToGroup() {
        $this->apiSendMessage("Game dibatalkan karena tidak cukup pemain. Ayo ajak teman-temanmu untuk join");
    }

    public function sendCreate60SECToGroup(){
        $text = "<b>".(Constant::$_startGame - Constant::$_startGame_r1)." detik</b> lagi. Ayo ajak teman-temanmu untuk /join";
        $this->apiSendMessage($text);
    }

    public function sendCreate90SECToGroup(){
        $text = "<b>".(Constant::$_startGame - Constant::$_startGame_r2)
            ." detik</b>  lagi. Ayo ajak teman-temanmu untuk /join";
        $this->apiSendMessage($text);
    }


    protected function sendWarningOnlyGroup() {
        $text = "This command can only be executed from group.";
        $this->apiSendMessage($text);
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

}