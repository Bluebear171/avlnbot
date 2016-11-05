<?php


class Constant{
    static $DEVELOPMENT;

    // $gameStatus
    const NOT_CREATED = 0;
    const CREATED = 1;
    const START_RANDOM_ROLES = 2;
    const DISCUSS_BEFORE_ASSIGNING_QUEST = 3;
    const ASSIGN_QUEST_PRIVATE = 4;
    const EXEC_APPROVE_REJECT_QUEST_GROUP = 5;
    const EXEC_QUEST_PRIVATE = 6;
    const EXEC_LADY_OF_LAKE_PRIVATE = 7;
    const EXEC_KILL_MERLIN_PRIVATE = 8;

    const MIN_PLAYER = 5;
    const MAX_PLAYER = 10;

    const MERLIN = 0;
    const PERCIVAL = 1;
    const GOOD_NORMAL = 2;
    const MORDRED = 3;
    const ASSASSIN = 4;
    const MORGANA = 5;
    const OBERON = 6;
    const BAD_NORMAL = 7;

    const MORGASSASSIN = 8;
    const GUARD = 9;


    const THRES_REMIND = 10; // $updatesTimeout+1

    static $_180; // start game
    static $_120; // start game
    static $_30;  // exec quest private, exec lady of the lake private
    static $_60;  // assignment_private, exec approve reject group,
    static $_90;  // discuss assignment group, exec kill merlin private

    static $_startGame;
    static $_startGame_r1;
    static $_startGame_r2;
    static $_discussAssignQuestGroup;
    static $_discussAssignQuestGroup_r1;
    static $_discussAssignQuestGroup_r2;
    static $_assignQuestPrivate;
    static $_execApproveRejectGroup;
    static $_execApproveRejectGroup_r1;
    static $_execQuestPrivate;
    static $_execQuestPrivate_r1;
    static $_execLadyOfTheLakePrivate;
    static $_execKillMerlin;
    static $_execKillMerlin_r1;
    static $_execKillMerlin_r2;

    // index for $players
    const ROLE = "role";
    const INDEX = "role_index";
    const LAST_MESSAGE_ID = "lmsgid";

    // index for $questAssigneeIDsHistory
    const FAIL_COUNT = "fail_count";
    const ASSIGNEEIDS = "assigneeIDs";
    const REJECTIDS = "rejectIDs";
    const KINGID = "king_id";

    // value for mode
    const MODE_NORMAL = 0;
    const MODE_CHAOS = 1;

    const EMO_KING = 0x1F451;
    const EMO_LADY = 0x1F469;
    const EMO_SUCCESS = 0x2714;
    const EMO_FAIL = 0x274C;
    const EMO_RED_CIRCLE = 0x1F534;
    const EMO_EVIL = 0x1F608;
    const EMO_SMILE = 0x1F642;

    const idString = "Bahasa Indonesia";
    const enString = "English";

    private static $inited = false;
    public static $quest;
    public static $two_fails_required;
    public static $bad_guys_numbers;
    public static $good_guys_numbers;
    public static $players;

    static function isGNBPlayer($role){
        switch ($role) {
            case Constant::MERLIN:
            case Constant::PERCIVAL:
            case Constant::GOOD_NORMAL:
            case Constant::GUARD:
                return 1;
            default:
                return -1;
        }
    }

    static function isAppearGoodPlayer($role){
        switch ($role) {
            case Constant::MERLIN:
            case Constant::PERCIVAL:
            case Constant::GOOD_NORMAL:
            case Constant::GUARD:
            case Constant::MORDRED:
                return 1;
            default:
                return -1;
        }
    }

    static function getLanguageString($langID){
        switch ($langID) {
            case "id": return Constant::idString;
            case "en": return Constant::enString;
        }
    }

    static function getNameByRole($role){
        switch ($role) {
            case Constant::MERLIN:
                return "Merlin";
            case Constant::PERCIVAL:
                return "Percival";
            case Constant::GOOD_NORMAL:
                return "Villager";
            case Constant::MORDRED:
                return "Mordred";
            case Constant::ASSASSIN:
                return "Assassin";
            case Constant::MORGANA:
                return "Morgana";
            case Constant::OBERON:
                return "Oberon";
            case Constant::BAD_NORMAL:
                return "Thief";
            case Constant::MORGASSASSIN:
                return "Morgassassin";
            case Constant::GUARD:
                return "Guard";
        }
    }

    static function init(){
        // if have not inited
        if (!Constant::$inited) {
            if (getenv('DEVELOPMENT') >= 1) {
                Constant::$DEVELOPMENT = true;
            }
            else {
                Constant::$DEVELOPMENT = false;
            }
            if (Constant::$DEVELOPMENT) {
//                Constant::$_120 = 20;
//                Constant::$_30 = 5;
//                Constant::$_60 = 10;
//                Constant::$_90 = 15;
//
//                Constant::$_startGame = 15;
//                Constant::$_startGame_r1 = 5;
//                Constant::$_startGame_r2 = 10;
                Constant::$_120 = 8;
                Constant::$_30 = 2;
                Constant::$_60 = 4;
                Constant::$_90 = 6;

                Constant::$_startGame = 6;
                Constant::$_startGame_r1 = 2;
                Constant::$_startGame_r2 = 4;
            }
            else{
                Constant::$_120 = 120;
                Constant::$_30 = 30;
                Constant::$_60 = 60;
                Constant::$_90 = 90;

                Constant::$_startGame = Constant::$_120 + Constant::$_30;
                Constant::$_startGame_r1 = Constant::$_60 + Constant::$_30;
                Constant::$_startGame_r2 = Constant::$_90 + Constant::$_30;
            }

            Constant::$_discussAssignQuestGroup = Constant::$_120;
            Constant::$_discussAssignQuestGroup_r1 = Constant::$_60;
            Constant::$_discussAssignQuestGroup_r2 = Constant::$_90;

            Constant::$_assignQuestPrivate = Constant::$_60;

            Constant::$_execApproveRejectGroup = Constant::$_90;
            Constant::$_execApproveRejectGroup_r1 = Constant::$_60;

            Constant::$_execQuestPrivate = Constant::$_30;
            Constant::$_execQuestPrivate_r1 = Constant::$_30/2;

            Constant::$_execLadyOfTheLakePrivate = Constant::$_60;

            Constant::$_execKillMerlin = Constant::$_120 + Constant::$_60;
            Constant::$_execKillMerlin_r1 = Constant::$_60 + Constant::$_60;
            Constant::$_execKillMerlin_r2 = Constant::$_90 + Constant::$_60;

            Constant::$inited = true;
            // $quest [number of player][quest no]
            Constant::$quest = array(
                5 => array(2,3,2,3,3),
                6 => array(2,3,4,3,4),
                7 => array(2,3,3,4,4),
                8 => array(3,4,4,5,5),
                9 => array(3,4,4,5,5),
                10 => array(3,4,4,5,5)
            );
            Constant::$two_fails_required = array(
                5 => array(1,1,1,1,1),
                6 => array(1,1,1,1,1),
                7 => array(1,1,1,2,1),
                8 => array(1,1,1,2,1),
                9 => array(1,1,1,2,1),
                10 => array(1,1,1,2,1),
            );

            Constant::$players = array(
                5 => array(Constant::MERLIN,
                            Constant::PERCIVAL,
                            Constant::GUARD,
                            Constant::MORGASSASSIN,
                            Constant::MORDRED),
                6 => array(Constant::MERLIN,
                    Constant::PERCIVAL,
                    Constant::GUARD,
                    Constant::GOOD_NORMAL,
                    Constant::MORDRED,
                    Constant::MORGASSASSIN),
                7 => array(Constant::MERLIN,
                    Constant::PERCIVAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::MORDRED,
                    Constant::MORGANA,
                    Constant::ASSASSIN),
                8 => array(Constant::MERLIN,
                    Constant::PERCIVAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::MORDRED,
                    Constant::MORGANA,
                    Constant::ASSASSIN),
                9 => array(Constant::MERLIN,
                    Constant::PERCIVAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::MORDRED,
                    Constant::MORGANA,
                    Constant::ASSASSIN),
                10 => array(Constant::MERLIN,
                    Constant::PERCIVAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::GOOD_NORMAL,
                    Constant::MORDRED,
                    Constant::MORGANA,
                    Constant::ASSASSIN,
                    Constant::OBERON),
            );
        }
    }

    static function getMinPlayer(){
        return Constant::MIN_PLAYER;
    }

    static function getMaxPlayer(){
        return Constant::MAX_PLAYER;
    }

    static function getMode($mode){
        switch ($mode) {
            case Constant::MODE_CHAOS:
                return "Chaos";
            default:
                return "Normal";
        }
    }

    static function generateRandomRoleArray($size){
        if (!Constant::$inited) {
            Constant::init();
        }

        // generate [0,1,2,3,...,size]
        $defaultInt = array();
        for ($i = 0; $i<$size; $i++){
            $defaultInt[$i] = Constant::$players[$size][$i];
        }
        $randomizedArr = array();
        for ($i=0; $i<$size;$i++){
            $pick = rand(0, $size-$i-1);
            $randomizedArr[$i] = $defaultInt[$pick];
            $defaultInt[$pick] = $defaultInt[$size-$i-1];
        }
        return $randomizedArr;
    }
}