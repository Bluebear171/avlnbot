<?php

require_once 'Constant.php';

class Script{
    static $inited;

    const PR_NEWGAME = 0; // private new game
    const PR_JOINGAME = 1; // private join game

    const PU_KINGDONE = 2; // public king already done the discussion
    const PU_APPROVENEW = 3; // player_xx approve. xx approve. xx reject.
    const PU_APPROVECHANGE = 4; // player_xx change to approve. xx approve. xx reject.
    const PU_REJECTNEW = 5; // // player_xx reject. xx approve. xx reject.
    const PU_REJECTCHANGE = 6; // // player_xx change to reject. xx approve. xx reject.
    const PU_REJECTCHANGEKING = 7; // because quest is rejected, go to next king
    const PU_KINGNEEDDISCUSS = 8; // before choose xx players, %s need %d seconds to discuss
    const PU_AFTERAPPROVE = 9; // approve is bigger, quest is executed.

    const PR_EXECQUEST = 10; // Quest no. xx, what do you want to choose

    const PR_SUCCESS = 11; // success
    const PR_FAIL = 12; // fail

    const PR_EXECQUESTGOOD = 13; // "Kamu orang baik. Kamu pun berusaha untuk menyelesaikan quest dengan sebaik-baiknya.";

    const PR_GOFORQUEST = 14; // xx pergi menyelesaikan quest, diberikan waktu xx.

    const PR_YOUAREMERLIN = 15; // you are merlin. bad guy are xxx
    const PR_YOUAREPERCIVAL = 16; // you are percival. merlin are xxx
    const PR_YOUAREGOODNORMAL = 17; // you are villager
    const PR_YOUAREMORDRED = 18; // you are mordred. Your bad team are xxx
    const PR_YOUAREASSASSIN = 19; // you are assassin. Your bad team are xxx
    const PR_YOUAREMORGANA = 20; // you are morgana. Your bad team are xxx
    const PR_YOUAREOBERON = 21; // you are oberon.
    const PR_YOUAREBADNORMAL = 22; // you are bad normal.

    const PU_KINGNEEDASSIGN = 23; // %s as king will assign %d players to do quest. time given %d
    const PR_SENDONEPLAYER = 24; // Choose person no. xx (from xx players) to do the quest.

    const PR_KILLMERLIN = 25; // Kill merlin
    const PR_LADYCHOOSE = 26; // Choose person to check
    const PU_APPRREJINST = 27; // %s telah menunjuk %s. time to approve reject

    const PU_REJECT5TIMES= 28; // Quest sudah direject 5 kali.
    const PU_FAILWITHXFAIL= 28; // Fail with xx fail

    const PU_BADGUYSWON= 29; // bad guys won
    const PU_GOODGUYSWON = 30; // good guys won

    const PU_OBERONFINALLY = 31; // oberon finally know friends they are %s
    const PU_LADYLAKEINST = 32; // %s as lady lake may choose 1 person

    const PU_KILLMERLIN = 33; // 3 quest success. Tim jahat adalah %s.

    const PU_QSUCCESSNOFAIL = 34; // Quest success
    const PU_QSUCCESSXXFAIL = 35; // but has xx fail

    const PR_ASSIGNONEQUEST = 36; // you have assign %s to quest
    const PU_ASSIGNONEQUEST = 37; // %s has assign %s to quest

    const PR_BADGUYSUCCESS = 38; //
    const PR_BADGUYFAIL = 39; //

    const PR_LADYNOTSEE = 40; //
    const PU_LADYNOTSEE = 41; //

    const PR_KILLMERLINSUCCESS = 42; //
    const PU_KILLMERLINSUCCESS = 43; //
    const PU_MERLIN = 44; //
    const PU_NOTMERLIN = 45; //

    const PR_LADYSEE = 42; //
    const PR_GOOD = 43; //
    const PR_BAD = 44; //
    const PU_LADYSEE = 42; //

    const PR_ASSIGNLATE = 43; //
    const PU_ASSIGNLATE = 44; //

    const PU_APPRREJLATE = 45; //
    const PU_APPRREJREMIND = 46; //

    const PR_BADGUYLATESUCCESS = 47; //
    const PR_BADGUYLATEFAIL = 48; //

    const PU_DISCUSSREMIND = 49; //

    const PR_LADYLATE = 50; //
    const PU_LADYLATE = 51; //

    const PR_KILLMERLINLATE = 52; //
    const PU_KILLMERLINLATE = 53; //
    const PU_KILLMERLINREMIND = 54; //

    const PU_NOHISTFOUND = 55; //
    const PU_HISTQEXECBY = 56; //
    const PU_HISTQREJECTBY = 57; //
    const PU_HISTQFAILREJ = 58; //

    const PU_CANNOTJOINFULL = 59; //
    const PU_CANNOTJOIN = 60; //
    const PU_STARTMEFIRST = 61; //

    const PU_CREATEFIRST = 62; //
    const PU_GAMESTART = 63; //
    const PU_GAMECANCEL = 64; //

    const PU_JOINREMIND = 65; //
    const PU_JOINSTART = 66; //
    const PU_JOINSUCCESS = 67; //

    const PU_CHCKPMTOCHGLANG = 100; // check PM untuk mengganti bahasa
    const PU_SECONDTODECIDE = 101; // you have %d to decide
    const PU_LANGGROUPNOTFOUND = 102; // "Bahasa tidak berhasil diganti. group tidak ditemukan.";
    const PR_LANGGROUPCHANGED = 103; // "Bahasa tidak berhasil diganti. group tidak ditemukan.";
    const PU_LANGCHANGED = 104; // "Bahasa berhasil diganti menjadi %s";
    const PR_SETLANGGROUPINST = 105;
    const PR_SETLANGINST = 106;
    const PR_GROUPONLY = 107;
    const PU_ADMINONLY = 108;
    const PU_MAINTENANCE = 109;

    const PU_CONTACT = 110;
    const PU_HELP = 111;
    const PU_HOWTOPLAY = 112;
    const PU_RATEME = 113;

    const PU_MERLININFO = 120;
    const PU_PERCIVALINFO = 121;
    const PU_SERVANTINFO = 122;
    const PU_MORDREDINFO = 123;
    const PU_MORGANAINFO = 124;
    const PU_ASSASSININFO = 125;
    const PU_OBERONINFO = 126;
    const PU_THIEFINFO = 127;

    static $script;
    static function init(){
        // if have not inited
        if (!Script::$inited) {
            Script::$inited = true;
            Script::$script["en"][Script::PR_NEWGAME]
                = "You have created the new game - %s mode in group %s.";
            Script::$script["id"][Script::PR_NEWGAME]
                = "Kamu telah membuat permainan baru - %s di grup %s.";

            Script::$script["en"][Script::PR_JOINGAME]
                = "You have join the Avalon game in Group %s.";
            Script::$script["id"][Script::PR_JOINGAME]
                = "Kamu telah bergabung Avalon di group %s.";

            Script::$script["en"][Script::PU_KINGDONE]
                = "The king have already found the answer and then finally decided to finish this never-ending discussion.";
            Script::$script["id"][Script::PU_KINGDONE]
                = "Raja sudah mendapatkan pencerahan dan akhirnya memutuskan untuk mengakhiri diskusi tiada akhir ini.";

            Script::$script["en"][Script::PU_APPROVENEW]
                = "%s Approve. Approve <b>%d</b>. Reject <b>%d</b>.";
            Script::$script["id"][Script::PU_APPROVENEW]
                = "%s setuju. Setuju <b>%d</b>. Menolak <b>%d</b>.";

            Script::$script["en"][Script::PU_APPROVECHANGE]
                = "%s changed answer to Approve. Approve <b>%d</b>. Reject <b>%d</b>.";
            Script::$script["id"][Script::PU_APPROVECHANGE]
                = "%s mengganti jawaban menjadi setuju. Setuju <b>%d</b>. Menolak <b>%d</b>.";

            Script::$script["en"][Script::PU_REJECTNEW]
                = "%s Reject. Approve <b>%d</b>. Reject <b>%d</b>.";
            Script::$script["id"][Script::PU_REJECTNEW]
                = "%s menolak. Setuju <b>%d</b>. Menolak <b>%d</b>.";

            Script::$script["en"][Script::PU_REJECTCHANGE]
                = "%s changed answer to Reject. Approve <b>%d</b>. Reject <b>%d</b>.";
            Script::$script["id"][Script::PU_REJECTCHANGE]
                = "%s mengganti jawaban menjadi menolak. Setuju <b>%d</b>. Menolak <b>%d</b>.";

            Script::$script["en"][Script::PU_REJECTCHANGEKING]
                = "The quest lead by %s have already been rejected. The Quest was cancelled and %s is now the King.";
            Script::$script["id"][Script::PU_REJECTCHANGEKING]
                = "Quest yang dipimpin oleh %s telah ditolak. quest dibatalkan dan king berpindah ke %s.";

            Script::$script["en"][Script::PU_KINGNEEDDISCUSS]
                = "Before assign <b>%d persons</b>, %s as the king has <b>%d seconds</b> to discuss with the team. King may type /done to finish the discussion. Click /questhistory to see the history.";
            Script::$script["id"][Script::PU_KINGNEEDDISCUSS]
                = "Sebelum menunjuk <b>%d orang</b>, %s sebagai raja mempunyai <b>%d detik</b> untuk berdiskusi dengan tim. Raja boleh mengetik /done untuk mengakhiri diskusi. Klik /questhistory untuk melihat history.";

            Script::$script["en"][Script::PU_AFTERAPPROVE]
                = "Most of the team members approved. The quest will be executed!";
            Script::$script["id"][Script::PU_AFTERAPPROVE]
                = "Sebagian besar tim meng-approve, quest pun akan dijalankan!";

            Script::$script["en"][Script::PR_EXECQUEST]
                = "Quest no.%d. What do you want to choose?";
            Script::$script["id"][Script::PR_EXECQUEST]
                = "Quest ke-%d. Apa yang ingin kamu pilih?";

            Script::$script["en"][Script::PR_SUCCESS]
                = "SUCCESS";
            Script::$script["id"][Script::PR_SUCCESS]
                = "SUKSES";

            Script::$script["en"][Script::PR_FAIL]
                = "FAIL";
            Script::$script["id"][Script::PR_FAIL]
                = "GAGAL";

            Script::$script["en"][Script::PR_EXECQUESTGOOD]
                = "You are a good guy. You put a lot effort to finish the quest as well as possible.";
            Script::$script["id"][Script::PR_EXECQUESTGOOD]
                = "Kamu orang baik. Kamu pun berusaha untuk menyelesaikan quest dengan sebaik-baiknya.";

            Script::$script["en"][Script::PR_GOFORQUEST]
                = "%s go to execute the quest. <b>%d seconds</b> are given to finish the quest.";
            Script::$script["id"][Script::PR_GOFORQUEST]
                = "%s pergi untuk menyelesaikan quest. Diberikan waktu <b>%d detik</b> untuk menyelesaikan quest.";

            Script::$script["en"][Script::PR_YOUAREMERLIN]
                = "You are Merlin. Bad aura is radiated from %s. Guide your team without getting caught by the bad guys!";
            Script::$script["id"][Script::PR_YOUAREMERLIN]
                = "Kamu adalah Merlin. Aura jahat terpancar kuat dari %s. Pandu timmu dalam quest tanpa ketahuan tim jahat!";

            Script::$script["en"][Script::PR_YOUAREPERCIVAL]
                = "You are Percival. You see %s as Merlin, but it seems that only one of them is the true Merlin.";
            Script::$script["id"][Script::PR_YOUAREPERCIVAL]
                = "Kamu adalah Percival. Kamu melihat %s sebagai Merlin, namun hanya satu dari mereka Merlin yang asli.";

            Script::$script["en"][Script::PR_YOUAREGOODNORMAL]
                = "You are the good villager. You don't know anything good or bad, but it is important to follow the right king.";
            Script::$script["id"][Script::PR_YOUAREGOODNORMAL]
                = "Kamu adalah Rakyat jelata yang baik. Kamu tidak tahu menahu, yang penting ikut menyukseskan quest dan mengikuti perintah raja.";

            Script::$script["en"][Script::PR_YOUAREMORDRED]
                = "You are Mordred. You team are %s. Merlin does not know you are in the bad side.";
            Script::$script["id"][Script::PR_YOUAREMORDRED]
                = "Kamu adalah Mordred. Tim jahatmu adalah %s. Merlin tidak tahu bahwa kamu orang jahat.";

            Script::$script["en"][Script::PR_YOUAREASSASSIN]
                = "You are Assassin. Your team are %s. At the end of the game, you can kill Merlin to win.";
            Script::$script["id"][Script::PR_YOUAREASSASSIN]
                = "Kamu adalah Assassin. Tim jahatmu adalah %s. Di akhir permainan, kamu bisa membunuh Merlin untuk menang.";

            Script::$script["en"][Script::PR_YOUAREMORGANA]
                = "You are Morgana. Your evil team are %s. On Percival's eyes, you were Merlin.";
            Script::$script["id"][Script::PR_YOUAREMORGANA]
                = "Kamu adalah Morgana. Tim jahatmu adalah %s. Di mata Percival, kamu adalah Merlin.";

            Script::$script["en"][Script::PR_YOUAREOBERON]
                = "You are Oberon. Currently you know nothing about your team and the team also doesn't know you.";
            Script::$script["id"][Script::PR_YOUAREOBERON]
                = "Kamu adalah Oberon. Sekarang, kamu belum tahu tim jahatmu siapa dan mereka juga belum tahu kamu.. :'( ";

            Script::$script["en"][Script::PR_YOUAREBADNORMAL]
                = "You are the bad guy. Your evil team are %s.";
            Script::$script["id"][Script::PR_YOUAREBADNORMAL]
                = "Kamu adalah Pejahat kacangan. Tim jahatmu adalah %s.";


            Script::$script["en"][Script::PU_KINGNEEDASSIGN]
                = "%s as king will assign <b>%d persons</b> to execute the quest. The king is given time <b>%d seconds</b>";
            Script::$script["id"][Script::PU_KINGNEEDASSIGN]
                = "%s sebagai raja akan menunjuk <b>%d orang</b> untuk menyelesaikan quest. Raja diberikan waktu sebanyak <b>%d detik</b>";


            Script::$script["en"][Script::PR_SENDONEPLAYER]
                = "Choose person no.%d (from %d persons) to execute the quest";
            Script::$script["id"][Script::PR_SENDONEPLAYER]
                = "Pilih orang ke-%d (dari %d orang) untuk menyelesaikan quest";

            Script::$script["en"][Script::PR_KILLMERLIN]
                = "You evil team has lost the missions. But, you still have the last weapon. Kill Merlin!";
            Script::$script["id"][Script::PR_KILLMERLIN]
                = "Tim jahatmu sudah kalah dalam misi. Namun, kamu masih punya senjata terakhir. Bunuh Merlin!";

            Script::$script["en"][Script::PR_LADYCHOOSE]
                = "Choose one person you want to see its true identity.";
            Script::$script["id"][Script::PR_LADYCHOOSE]
                = "Pilih orang untuk diterawang.";

            Script::$script["en"][Script::PU_APPRREJINST]
                = "%s has assigned %s to do the quest.\n\nYou have <b>%d seconds</b> to have a discussion. If you approve it, type /approve. Otherwise, type /reject.";
            Script::$script["id"][Script::PU_APPRREJINST]
                = "%s telah menunjuk %s untuk menyelesaikan quest.\n\nDiberikan waktu <b>%d detik</b> untuk berdiskusi. Jika setuju, ketik /approve. Jika menolak, ketik /reject.";

            Script::$script["en"][Script::PU_REJECT5TIMES]
                = "Quest was rejected 5 times, so it's failed.";
            Script::$script["id"][Script::PU_REJECT5TIMES]
                = "Quest sudah di-reject 5 kali, sehingga dianggap gagal.\n";

            Script::$script["en"][Script::PU_FAILWITHXFAIL]
                = "In quest, we found <b>%d FAIL</b>! Quest is failed.";
            Script::$script["id"][Script::PU_FAILWITHXFAIL]
                = "Dalam menyelesaikan quest ditemukan <b>%d FAIL</b>! Quest dianggap gagal.";

            Script::$script["en"][Script::PU_BADGUYSWON]
                = "The evil team won! Indeed, they have the experience more than 10 decades.";
            Script::$script["id"][Script::PU_BADGUYSWON]
                = "Para penjahat menang! Mereka memang sudah berpengalaman lebih dari 10 dekade..";

            Script::$script["en"][Script::PU_GOODGUYSWON]
                = "Congratulations! The good team won! The good team are really solid and very clever deceiving evil team.";
            Script::$script["id"][Script::PU_GOODGUYSWON]
                = "Selamat! Tim baik menang! Tim baik memang kompak dan pintar menipu orang jahat..";

            Script::$script["en"][Script::PU_OBERONFINALLY]
                = "Finally you know your teammates. They are %s.";
            Script::$script["id"][Script::PU_OBERONFINALLY]
                = "Akhirnya kamu tahu juga teman jahat seperjuanganmu.. Mereka adalah %s.";

            Script::$script["en"][Script::PU_LADYLAKEINST]
                = "%s as Lady of the Lake may use the power to see the true identity of one player. The other team members may give the advice.. <b>%d seconds</b> are given to do the action.";
            Script::$script["id"][Script::PU_LADYLAKEINST]
                = "%s sebagai Lady of the Lake dapat menggunakan kekuatannya untuk menerawang salah seorang anggota tim. Anggota tim lain boleh memberikan petunjuk... Diberikan waktu <b>%d detik</b>.";

            Script::$script["en"][Script::PU_KILLMERLIN]
                = "3 Quest has been successfully done by the team. However, evil team still has the last weapon. The evil team are %s. If the assassin can guess the Merlin, the evil team will win! Evil team has <b>%d seconds</b> to decide.";
            Script::$script["id"][Script::PU_KILLMERLIN]
                = "3 Quest berhasil disukseskan oleh tim. Namun, tim jahat masih mempunyai senjata terakhir. Tim jahat membuka kedok mereka %s.. Jika assassin berhasil menebak merlin, maka tim jahatlah yang menang! Diberikan waktu <b>%d detik</b>.";

            Script::$script["en"][Script::PU_QSUCCESSNOFAIL]
                = "Quest has been successfully done! ";
            Script::$script["id"][Script::PU_QSUCCESSNOFAIL]
                = "Quest berhasil diselesaikan dengan baik sekali. ";

            Script::$script["en"][Script::PU_QSUCCESSXXFAIL]
                = "However, team found there is <b>%d FAIL</b> in this quest..";
            Script::$script["id"][Script::PU_QSUCCESSXXFAIL]
                = "Namun, tim menemukan <b>%d FAIL </b> dalam quest ini..";

            Script::$script["en"][Script::PR_ASSIGNONEQUEST]
                = "You have successfully assigned %s in quest.";
            Script::$script["id"][Script::PR_ASSIGNONEQUEST]
                = "Kamu berhasil memilih %s dalam quest.";

            Script::$script["en"][Script::PR_BADGUYSUCCESS]
                = "Although you are evil, you have successfully made a good impression.";
            Script::$script["id"][Script::PR_BADGUYSUCCESS]
                = "Meskipun kamu jahat, kamu berhasil membuat pencitraan yang baik.";

            Script::$script["en"][Script::PR_BADGUYFAIL]
                = "You have successfully failed the quest.";
            Script::$script["id"][Script::PR_BADGUYFAIL]
                = "Kamu berhasil menggagalkan quest.";

            Script::$script["en"][Script::PR_LADYNOTSEE]
                = "You decided to not use your power to see.";
            Script::$script["id"][Script::PR_LADYNOTSEE]
                = "Kamu memilih untuk tidak menerawang..";

            Script::$script["en"][Script::PU_LADYNOTSEE]
                = "%s decided to not use the power to see.";
            Script::$script["id"][Script::PU_LADYNOTSEE]
                = "%s memilih untuk tidak menerawang.";

            Script::$script["en"][Script::PR_LADYSEE]
                = "You have seen %s.. He/she is ";
            Script::$script["id"][Script::PR_LADYSEE]
                = "Kamu berhasil menerawang %s.. Dia adalah orang ";

            Script::$script["en"][Script::PR_GOOD]
                = "good.";
            Script::$script["id"][Script::PR_GOOD]
                = "baik.";

            Script::$script["en"][Script::PR_BAD]
                = "evil.";
            Script::$script["id"][Script::PR_BAD]
                = "jahat.";

            Script::$script["en"][Script::PU_LADYSEE]
                = "%s use its power to see %s.";
            Script::$script["id"][Script::PU_LADYSEE]
                = "%s menerawang %s.";

            Script::$script["en"][Script::PR_KILLMERLINSUCCESS]
                = "You have successfully killed %s.";
            Script::$script["id"][Script::PR_KILLMERLINSUCCESS]
                = "Kamu berhasil membunuh %s.";

            Script::$script["en"][Script::PU_KILLMERLINSUCCESS]
                = "%s have successfully killed %s dan in fact %s ";
            Script::$script["id"][Script::PU_KILLMERLINSUCCESS]
                = "%s berhasil membunuh %s dan ternyata %s ";

            Script::$script["en"][Script::PU_MERLIN]
                = "is <b>MERLIN</b>!";
            Script::$script["id"][Script::PU_MERLIN]
                = "adalah <b>MERLIN</b>!";

            Script::$script["en"][Script::PU_NOTMERLIN]
                = "is <b>not MERLIN</b>!";
            Script::$script["id"][Script::PU_NOTMERLIN]
                = "<b>bukan MERLIN</b>!";

            Script::$script["en"][Script::PR_ASSIGNLATE]
                = "The time's up. The rest of players is assigned randomly. ";
            Script::$script["id"][Script::PR_ASSIGNLATE]
                = "Jawabanmu terlambat, sisa player dipilih secara random. ";

            Script::$script["en"][Script::PU_ASSIGNLATE]
                = "Because the time's up, The rest of players is assigned randomly: %s.";
            Script::$script["id"][Script::PU_ASSIGNLATE]
                = "Karena waktu habis, sisa pemain dipilih secara random: %s.";

            Script::$script["en"][Script::PU_APPRREJLATE]
                = "Because the time's up, the other members are assumed to choose approve..";
            Script::$script["id"][Script::PU_APPRREJLATE]
                = "Karena waktu habis, pemain lain dianggap memilih approve..";

            Script::$script["en"][Script::PU_APPRREJREMIND]
                = "The assignees in this quest are %s\n\n<b>%d seconds</b> left to choose /approve or /reject. If there is minimum <b>%d members</b> fail the quest, the quest will be failed.";
            Script::$script["id"][Script::PU_APPRREJREMIND]
                = "Pejuang di quest ini %s\n\n<b>%d detik</b>lagi pilih /approve atau /reject. Jika ada minimal <b>%d anggota</b> menggagalkan quest, maka quest akan dianggap gagal!";

            Script::$script["en"][Script::PR_BADGUYLATESUCCESS]
                = "You answered late. Boss forced you to give the good impression this time.";
            Script::$script["id"][Script::PR_BADGUYLATESUCCESS]
                = "Jawabanmu terlambat. Boss memaksamu untuk memberikan pencitraan yang baik.";

            Script::$script["en"][Script::PR_BADGUYLATEFAIL]
                = "You answered late. Boss forced you to fail the quest.";
            Script::$script["id"][Script::PR_BADGUYLATEFAIL]
                = "Jawabanmu terlambat. Kamu dipaksa menggagalkan quest dari boss.";

            Script::$script["en"][Script::PU_DISCUSSREMIND]
                = "<b>%d seconds</b> left to discuss... %s may type /done if you are ready to give the assignment.";
            Script::$script["id"][Script::PU_DISCUSSREMIND]
                = "<b>%d detik</b> lagi untuk berdiskusi... %s boleh mengetik /done jika sudah mendapat pencerahan.";

            Script::$script["en"][Script::PR_LADYLATE]
                = "You answered late to see the person's identity.";
            Script::$script["id"][Script::PR_LADYLATE]
                = "Kamu terlambat memilih untuk menerawang..";

            Script::$script["en"][Script::PU_LADYLATE]
                = "%s answered late so the power to see is not used.";
            Script::$script["id"][Script::PU_LADYLATE]
                = "%s terlambat memilih sehingga tidak bisa menerawang.";

            Script::$script["en"][Script::PR_KILLMERLINLATE]
                = "You answered late to kill Merlin";
            Script::$script["id"][Script::PR_KILLMERLINLATE]
                = "Kamu terlambat memilih untuk membunuh Merlin..";

            Script::$script["en"][Script::PU_KILLMERLINLATE]
                = "%s answered late. It seems Merlin is saved this time.";
            Script::$script["id"][Script::PU_KILLMERLINLATE]
                = "%s terlambat memilih.. Sepertinya Merlin selamat kali ini..";

            Script::$script["en"][Script::PU_KILLMERLINREMIND]
                = "%s seconds</b> left for assassin to guess and kill Merlin...";
            Script::$script["id"][Script::PU_KILLMERLINREMIND]
                = "%s detik</b> lagi waktu yang dibutuhkan assassin untuk membunuh Merlin...";

            Script::$script["en"][Script::PU_NOHISTFOUND]
                = "No History found for the current game.";
            Script::$script["id"][Script::PU_NOHISTFOUND]
                = "Tidak ditemukan history untuk game yang sedang berlangsung.";

            Script::$script["en"][Script::PU_HISTQEXECBY]
                = "Quest no.%d %s lead by %s %s, executed by %s";
            Script::$script["id"][Script::PU_HISTQEXECBY]
                = "Quest ke-%d %s dipimpin oleh %s %s, dieksekusi oleh %s";

            Script::$script["en"][Script::PU_HISTQREJECTBY]
                = ", rejected by %s\n\n";
            Script::$script["id"][Script::PU_HISTQREJECTBY]
                = ", ditolak oleh %s\n\n";

            Script::$script["en"][Script::PU_HISTQFAILREJ]
                = "Quest no.%d [%s 5x REJECT]\n\n";
            Script::$script["id"][Script::PU_HISTQFAILREJ]
                = "Quest ke-%d [%s 5x GAGAL]\n\n";

            Script::$script["en"][Script::PU_CANNOTJOINFULL]
                = " cannot join. Already %d players.";
            Script::$script["id"][Script::PU_CANNOTJOINFULL]
                = " tidak bisa bergabung. Sudah %d pemain.";

            Script::$script["en"][Script::PU_CANNOTJOIN]
                = " cannot join.";
            Script::$script["id"][Script::PU_CANNOTJOIN]
                = " tidak bisa bergabung.";

            Script::$script["en"][Script::PU_STARTMEFIRST]
                = " <a href=\"http://telegram.me/%s\">Start Me</a> first.";
            Script::$script["id"][Script::PU_STARTMEFIRST]
                = " <a href=\"http://telegram.me/%s\">Start Me</a> terlebih dahulu.";


            Script::$script["en"][Script::PU_CREATEFIRST]
                = "Game has not started yet. Type /start to start Avalon.";
            Script::$script["id"][Script::PU_CREATEFIRST]
                = "Game belum distart. Ketik /start untuk memulai Avalon.";

            Script::$script["en"][Script::PU_GAMESTART]
                = "Game has been started. Please anyone to check the private chat to know your role.";
            Script::$script["id"][Script::PU_GAMESTART]
                = "Game sudah dimulai. Silakan cek PM masing-masing untuk melihat peran.";

            Script::$script["en"][Script::PU_GAMECANCEL]
                = "Game is canceled because there is not enough players. Invite your friends to join.";
            Script::$script["id"][Script::PU_GAMECANCEL]
                = "Game dibatalkan karena tidak cukup pemain. Ayo ajak teman-temanmu untuk join";


            Script::$script["en"][Script::PU_JOINREMIND]
                = "<b>%d seconds</b> left. Invite your friends to /join.";
            Script::$script["id"][Script::PU_JOINREMIND]
                = "<b>%d detik</b> lagi. Ayo ajak teman-temanmu untuk /join.";


            Script::$script["en"][Script::PU_JOINSTART]
                = "%s has started the Avalon - %s. Type /join to join the game. <b>".Constant::$_startGame." seconds</b> left.";
            Script::$script["id"][Script::PU_JOINSTART]
                = "%s telah memulai Avalon - %s. Ketik /join untuk bergabung. <b>".Constant::$_startGame." detik</b> lagi.";

            Script::$script["en"][Script::PU_JOINSUCCESS]
                = "%s joined. <b>%d</b> players. min <b>%d</b>. max <b>%d</b>.";
            Script::$script["id"][Script::PU_JOINSUCCESS]
                = "%s bergabung. <b>%d</b> pemain. min <b>%d</b>. max <b>%d</b>.";






            Script::$script["en"][Script::PU_CHCKPMTOCHGLANG]
                = "Please check private chat to change the language.";
            Script::$script["id"][Script::PU_CHCKPMTOCHGLANG]
                = "Silakan mengecek PM untuk memilih bahasa.";

            Script::$script["en"][Script::PU_SECONDTODECIDE]
                = "\n\nYou have <b>%d seconds</b> to decide.\n";
            Script::$script["id"][Script::PU_SECONDTODECIDE]
                = "\n\nWaktu yang diberikan adalah <b>%d</b> detik.\n";

            Script::$script["en"][Script::PU_LANGGROUPNOTFOUND]
                = "Language cannot be changed. We cannot find the group.";
            Script::$script["id"][Script::PU_LANGGROUPNOTFOUND]
                = "Bahasa tidak berhasil diganti. group tidak ditemukan.";

            Script::$script["en"][Script::PR_LANGGROUPCHANGED]
                = "Language in %s has been set to %s.";
            Script::$script["id"][Script::PR_LANGGROUPCHANGED]
                = "Bahasa di %s berhasil diganti menjadi %s.";

            Script::$script["en"][Script::PU_LANGCHANGED]
                = "Language has been set to %s.";
            Script::$script["id"][Script::PU_LANGCHANGED]
                = "Bahasa berhasil diganti menjadi %s.";

            Script::$script["en"][Script::PR_SETLANGGROUPINST]
                = "for %s.";
            Script::$script["id"][Script::PR_SETLANGGROUPINST]
                = "untuk %s.";

            Script::$script["en"][Script::PR_SETLANGINST]
                = "Choose the language ";
            Script::$script["id"][Script::PR_SETLANGINST]
                = "Pilih bahasa yang ingin digunakan ";

            Script::$script["en"][Script::PR_GROUPONLY]
                = "This command can only be executed from group.";
            Script::$script["id"][Script::PR_GROUPONLY]
                = "Kamu harus berada di grup untuk dapat menggunakan perintah ini.";

            Script::$script["en"][Script::PU_ADMINONLY]
                = "This command can only be executed by admin.";
            Script::$script["id"][Script::PU_ADMINONLY]
                = "Hanya admin yang dapat menggunakan perintah ini.";

            Script::$script["en"][Script::PU_MAINTENANCE]
                = "Currently there is a maintenance for avalon bot.\nPlease try again later.";
            Script::$script["id"][Script::PU_MAINTENANCE]
                = "Saat ini, bot sedang dalam maintenance. Silakan coba beberapa saat lagi.";


            Script::$script["en"][Script::PU_CONTACT]
                = "Telegram code by <b>Hendry Setiadi</b>.\n\n"
                ."Contact to email: hendry.setiadi.89@gmail.com to give support or feedback.\n\n"
                ."Rate me by clicking the link: <a href=\"http://telegram.me/storebot?start=%s\">Rate me</a>.\n\n"
                ."Thank you.";
            Script::$script["id"][Script::PU_CONTACT]
                = "Kode Telegram oleh <b>Hendry Setiadi</b>.\n\n"
                ."Hubungi email: hendry.setiadi.89@gmail.com untuk memberikan support atau feedback.\n\n"
                ."Berikan rating dengan meng-klik link berikut: <a href=\"http://telegram.me/storebot?start=%s\">Rate me</a>.\n\n"
                ."Terima kasih.";

            Script::$script["en"][Script::PU_HELP]
                = "Avalon bot for telegram.\n"
                . "Based on the <a href=\"https://boardgamegeek.com/boardgame/128882/resistance-avalon\">The Resistance:Avalon BoardGame</a>\n\n"
                . "To start playing, invite this bot to your group then type /start to start the game.\n\n"
                . "Type /howtoplay if you are new to avalon and want to know more\n"
                . "Type /contact if you want to contact the developer\n";
            Script::$script["id"][Script::PU_HELP]
                = "Avalon bot untuk Telegram.\n"
                . "Berdasarkan game <a href=\"https://boardgamegeek.com/boardgame/128882/resistance-avalon\">The Resistance:Avalon BoardGame</a>\n\n"
                . "Untuk bermain, undang bot ini ke dalam grup kemudian ketik /start untuk memulai permainan.\n\n"
                . "Ketik /howtoplay untuk tahu cara bermain avalon\n"
                . "Ketik /contact untuk menghubungi developer\n";



            Script::$script["en"][Script::PU_HOWTOPLAY]
            = " <b>The avalon game is a game about deduction and bluffing</b>\n\n"
            . "This game tells the story about a group that is in a journey to control the civilization of Arthur. "
            . "There are always <b>5 quests</b> in total. The players will play the first quest first, then sequentially go to the next quest."
            . " If at least 3 quests succeed, then good forces <i>might</i> win. If there are 3 quests fail, evil force win.\n\n"

            . "At the start of the game, each player will be randomly assigned a role secretly.\n"
            . "Click to see the detail of the role:\n"
                .self::unichr(Constant::EMO_SMILE)."/merlin\n"
                .self::unichr(Constant::EMO_SMILE)."/percival\n"
                .self::unichr(Constant::EMO_SMILE)."/servant\n"
                .self::unichr(Constant::EMO_EVIL)."/assassin\n"
                .self::unichr(Constant::EMO_EVIL)."/morgana\n"
                .self::unichr(Constant::EMO_EVIL)."/mordred\n"
                .self::unichr(Constant::EMO_EVIL)."/oberon\n\n"
            . "At the start of the game, King token".self::unichr(Constant::EMO_KING). " will be randomly assigned to a player and the King may choose who can complete the current quest.\n"
            . "After the king has done the assignment, any player may vote <b>approve</b> or <b>reject</b> to the assignment. Then, the approve and reject will be counted.\n\n"

            . "If the <b>reject</b> count is half or more the count of the players, then the quest is rejected, and the king token"
                .self::unichr(Constant::EMO_KING)." will be given to the next player (next to the King)\n"
            . "If the <b>approve</b> count is more than half players' count, then the quest is executed by the assignees.\n\n"

            . "When executing a quest, evil players may choose to fail the quest and this will be done secretly. "
            . "In general, if at least 1 player give the FAIL to that quest, it means that quest will FAIL.\n\n"

            . "And to prevent each quest being rejected over and over, each quest has maximum reject of 5. If the quest is rejected 5 times, it will automatically FAIL\n\n"

            . "In a game 8 players or more, <b>lady of the lake</b>".self::unichr(Constant::EMO_LADY)." can be used. Lady of The Lake will give a large benefit for a good forces.. "
            . "A player who hold the lady token may choose a player to know his/her true identity (good or evil) but only the lady holder knows the truth.\n\n"

            . "That's all. Practice is the faster way to learn.. Type /start to start the game.\n\n";




            Script::$script["id"][Script::PU_HOWTOPLAY]
            = " <b>Game Avalon adalah game tentang deduksi dan berbohong</b>\n\n"
            . "Game ini bercerita tentang perjalanan suatu grup untuk menemukan peradaban Arthur. "
            . "Ada <b>5 quests</b> yang harus diselesaikan. Pemain harus memainkan quest pertama terlebih dahulu, baru kemudian lanjut ke quest berikutnya secara berurutan."
            . " Jika terdapat minimal 3 quest berhasil, maka tim baik <i>mungkin</i> menang. Jika terdapat 3 quest gagal, maka tim jahatlah yang menang.\n\n"

            . "Di awal permainan, Tiap orang akan diberikan peran secara rahasia.\n"
            . "Klik untuk melihat detil peran :\n"
                .self::unichr(Constant::EMO_SMILE)."/merlin\n"
                .self::unichr(Constant::EMO_SMILE)."/percival\n"
                .self::unichr(Constant::EMO_SMILE)."/servant\n"
                .self::unichr(Constant::EMO_EVIL)."/assassin\n"
                .self::unichr(Constant::EMO_EVIL)."/morgana\n"
                .self::unichr(Constant::EMO_EVIL)."/mordred\n"
                .self::unichr(Constant::EMO_EVIL)."/oberon\n\n"
            . "Di awal permainan, King token ".self::unichr(Constant::EMO_KING). " akan diberikan secara random ke salah seorang pemain dan raja boleh memilih orang yang akan berangkat untuk quest yang berlangsung.\n"
            . "Setelah raja memberikan penugasan, tiap orang boleh <b>setuju</b> or <b>menolak</b> terhadap penugasan itu. Kemudian, jumlah setuju dan jumlah menolak akan dihitung.\n\n"

            . "Jika jumlah <b>menolak</b> lebih besar atau sama dengan jumlah pemain, maka quest akan ditolak dan King token"
            .self::unichr(Constant::EMO_KING)." akan berpindah ke pemain berikutnya (sesudah raja)\n"
            . "Jika jumlah <b>setuju</b> lebih besar dari jumlah pemain, maka quest akan dijalankan oleh orang yang ditunjuk oleh raja.\n\n"

            . "Ketika mengerjakan quest, Pemain jahat boleh menggagalkan quest dan ini dilakukan secara rahasia."
            . "Umumnya, Jika minimal terdapat 1 GAGAL dalam quest, maka quest tersebut akan dianggap GAGAL.\n\n"

            . "Untuk mencegah suatu quest ditolak terus menerus, setiap quest memiliki penolakan maksimum 5 kali. "
            . " Jika quest tersebut sudah direject 5 kali, maka quest itu dianggap GAGAL.\n\n"

            . "Dalam game dengan 8 pemain atau lebih, <b>lady of the lake</b>".self::unichr(Constant::EMO_LADY)." dapat digunakan. Lady of the Lake dapat memberikan keuntungan yang besar bagi tim baik. "
            . "Pemain yang memegang the lady token boleh menerawang sesorang untuk mengetahui identitas sebenarnya (baik atau jahat) namun hanya pemegang lady lah yang mengetahuinya.\n\n"

            . "Sekian. Latihan adalah cara yang epat untuk belajar. Ketik /start untuk memulai game.\n\n";


            Script::$script["en"][Script::PU_RATEME]
                = "If you like this bot, please rate by clicking below link. ". self::unichr(Constant::EMO_SMILE) . "\n\n"
                 ."<a href=\"http://telegram.me/storebot?start=%s\">Rate me</a>";
            Script::$script["id"][Script::PU_RATEME]
                = "Jika kamu menyukai bot ini, silakan berikan rating dengan meng-klik link di bawah. ". self::unichr(Constant::EMO_SMILE) . "\n\n"
                 ."<a href=\"http://telegram.me/storebot?start=%s\">Rate me</a>";

            Script::$script["en"][Script::PU_MERLININFO]
                = "<b>Merlin</b>".self::unichr(Constant::EMO_SMILE)
                . " knows all evil players except /mordred. His job is to give clues to the good team, "
                . "so it will prevent the evil players having a chance failing the quests.\n\n"
                . "Note that if Merlin is too obvious, even though 3 quests have succeed, the /assassin can "
                . "guess the Merlin at the end of the game. If Assassin's guess is correct, the good side will lose although 3 quests has been success.";
            Script::$script["id"][Script::PU_MERLININFO]
                = "<b>Merlin</b>".self::unichr(Constant::EMO_SMILE)
                . " tahu semua pemain jahat kecuali Mordred. Merlin harus memberikan petunjuk-petunjuk ke tim baik, "
                . "sehingga mencegah pemain jahat untuk mengerjakan quest.\n\n"
                . "Penting juga bagi Merlin agar dia tidak ketahuan tim jahat karena /assassin dapat "
                . "menebak Merlin di akhir game. Jika assassin berhasil menebak Merlin, tim baik akan kalah meskipun 3 quest sudah berhasil.";

            Script::$script["en"][Script::PU_PERCIVALINFO]
                = "<b>Percival</b>".self::unichr(Constant::EMO_SMILE)
                . " knows the Merlin and Morgana at the start of the game. However, Percival does not know which is Merlin or Morgana\n\n"
                . "Percival's job is to guess the Merlin correctly between the 2 and then follow the Merlin's order. ".
                "Besides that , Percival needs to act as a Merlin to deceive /assassin.";
            Script::$script["id"][Script::PU_PERCIVALINFO]
                = "<b>Percival</b>".self::unichr(Constant::EMO_SMILE)
                . " tahu Merlin dan Morgana di awal permainan. Namun, Percival tidak tahu siapa yang Merlin atau Morgana\n\n"
                . "Tugas Percival adalah menebak dengan benar siapa Merlin dan mengikuti perintah Merlin. ".
                " Selain itu, Percival perlu berpura-pura menjadi Merlin untuk mengelabui /assassin.";

            Script::$script["en"][Script::PU_SERVANTINFO]
                = "<b>Servant</b>".self::unichr(Constant::EMO_SMILE)
                . " is in a good side but do not know anything at the start of the game.\n\n"
                ."Servant's job is to succeed the quest and to try guess the Merlin correctly (mainly based on the deduction). ".
                "Servant might also need to act as a Merlin to deceive the evil force.";
            Script::$script["id"][Script::PU_SERVANTINFO]
                = "<b>Servant</b>".self::unichr(Constant::EMO_SMILE)
                . " (Pelayan/rakyat) adalah pemain baik yang tidak tahu apa-apa.\n\n"
                ."Tugas servant adalah menyukseskan quest dan mencoba menebak Merlin (berdasarkan deduksi). ".
                "Servant mungkin juga perlu berpura-pura menjadi Merlin untuk mengelabui tim jahat.";

            Script::$script["en"][Script::PU_MORDREDINFO]
                = "<b>Mordred</b>".self::unichr(Constant::EMO_EVIL)
                . " as an evil player knows the other evil players at the start of the game (except Oberon) and have to cooperate together to fail the quests.\n\n"
                . "Merlin cannot see Mordred as an evil player so Mordred may act as a good player without being known.";
            Script::$script["id"][Script::PU_MORDREDINFO]
                = "<b>Mordred</b>".self::unichr(Constant::EMO_EVIL)
                . " adalah pemain jahat yang tahu teman-teman jahatnya di awal permainan (kecuali Oberon) dan harus bekerja sama dengan tim jahat untuk mengagalkan quest.\n\n"
                . "Merlin tidak dapat melihat Mordred sehingga Mordred dapat berpura-pura menjadi pemain yang baik tanpa diketahui.\n\n";

            Script::$script["en"][Script::PU_MORGANAINFO]
                = "<b>Morgana</b>".self::unichr(Constant::EMO_EVIL)
                . " as an evil player knows the other evil players at the start of the game and have to cooperate together to fail the quests.\n\n"
                . "Because Percival can see Merlin and Morgana, Morgana's primary job is to gain trust from Percival by acting as a Merlin. If Percival can be deceived, Merlin will be in trouble.\n\n";
            Script::$script["id"][Script::PU_MORGANAINFO]
                = "<b>Morgana</b>".self::unichr(Constant::EMO_EVIL)
                . " adalah pemain jahat yang tahu teman-teman jahatnya di awal permainan (kecuali Oberon) dan harus bekerja sama dengan tim jahat untuk mengagalkan quest.\n\n"
                . "Karena Percival dapat melihat Merlin dan Morgana, tugas utama Morgana adalah mendapatkan kepercayaan dari Percival dengan berpura-pura menjadi Merlin. Jika Percival dapat ditipu, maka Merlin akan berada dalam bahaya.\n\n";

            Script::$script["en"][Script::PU_ASSASSININFO]
                = "<b>Assassin</b>".self::unichr(Constant::EMO_EVIL)
                . " as an evil player knows the other evil players at the start of the game and have to cooperate together to fail the quests.\n\n"
                . "Assassin can guess Merlin at the end of the game (if 3 quests already been succeed). If the guess is correct, whatever the result in the quests, Evil force will win.";
            Script::$script["id"][Script::PU_ASSASSININFO]
                = "<b>Assassin</b>".self::unichr(Constant::EMO_EVIL)
                . " adalah pemain jahat yang tahu teman-teman jahatnya di awal permainan (kecuali Oberon) dan harus bekerja sama dengan tim jahat untuk mengagalkan quest.\n\n"
                . "Assassin dapat menebak Merlin di akhir game (jika 3 quest sudah sukses). Jika Merlin berhasil dibunuh, apapun hasil di quest, tim jahat akan menang.";

            Script::$script["en"][Script::PU_OBERONINFO]
                = "<b>Oberon</b>".self::unichr(Constant::EMO_EVIL)
                . " is an evil player but all other evil players do not know the oberon's identity. Merlin can still see Oberon though."
                . " In this telegram, Oberon will know the evil teammate until 2nd quest is finished.";
            Script::$script["id"][Script::PU_OBERONINFO]
                = "<b>Oberon</b>".self::unichr(Constant::EMO_EVIL)
                . " adalah pemain jahat namun pemain jahat lain tidak tahu identitas Oberon. Sayangnya, Merlin masih dapat melihat Oberon."
                . " Di telegram, Oberon akan tahu teman jahatnya setelah quest kedua selesai.";

            Script::$script["en"][Script::PU_THIEFINFO]
                = "<b>Thief</b>".self::unichr(Constant::EMO_EVIL)
                . " adalah pemain jahat yang tahu teman-teman jahatnya di awal permainan (kecuali Oberon) dan harus bekerja sama dengan tim jahat untuk mengagalkan quest.\n\n";
            Script::$script["id"][Script::PU_THIEFINFO]
                = "<b>Thief</b>".self::unichr(Constant::EMO_EVIL)
                . " adalah pemain jahat yang tahu teman-teman jahatnya di awal permainan (kecuali Oberon) dan harus bekerja sama dengan tim jahat untuk mengagalkan quest.\n\n";


        }
    }

    static function unichr($i) {
        return iconv('UCS-4LE', 'UTF-8', pack('V', $i));
    }
}