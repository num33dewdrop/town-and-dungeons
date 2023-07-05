<?php
ini_set('log_errors','on');
ini_set('error_log','php.log');

include("class.php");
session_start();

$subjectCount = count(Subject::STRINGS);

//モンスター生成関数
function createMonster(){
  $monsters[] = new Monster('オーク', 'img/monster01.png', 1, 100, 100, 100, 100, 20, 20, 1, 1, 50, 20);
  $monsters[] = new Monster('イエティ', 'img/monster02.png', 1, 150, 150, 150, 150, 25, 25, 1, 1, 60, 30);
  $monsters[] = new Monster('グリフォン', 'img/monster03.png', 1, 160, 160, 180, 180, 30, 20, 1.5, 1.5, 70, 40);
  $monsters[] = new Monster('ドラゴン', 'img/monster04.png', 1, 200, 200, 150, 150, 50, 50, 2, 2, 150, 200);
  $monsters[] = new Monster('魔人', 'img/monster05.png', 1, 300, 300, 180, 180, 50, 60, 2, 2, 200, 250);
  $_SESSION['monster'] = $monsters[mt_rand(0,4)];
  for($i = 0;$i < $_SESSION['clearDungeonCount']; $i++){
    $_SESSION['monster']->levelUp();
  }
  //error_log(print_r($_SESSION['monster'],true));
  History::set($_SESSION['monster']->getName().'が現れた！');
}
function createBossMonster($count){
  $monsters[] = new Monster('番人', 'img/monster06.png', 5, 300, 300, 18, 18, 8, 7, 1, 2, 200, 250);
  $monsters[] = new Monster('魔人', 'img/monster05.png', 8, 200, 200, 20, 20, 10, 10, 2, 2, 200, 250);
  $monsters[] = new Monster('炎魔人', 'img/monster07.png', 16, 100, 100, 25, 25, 13, 13, 2, 2, 300, 300);
  $monsters[] = new Monster('堕天使', 'img/monster08.png', 24, 50, 50, 30, 30, 18, 18, 2.5, 2.5, 400, 400);
  $monsters[] = new Monster('魔王', 'img/monster09.png', 32, 40, 40, 40, 40, 24, 24, 3, 3, 500, 600);
  $monsters[] = new Monster('魔王', 'img/monster10.png', 40, 40, 40, 40, 40, 28, 28, 3, 3, 600, 1000);
  $_SESSION['monster'] = $monsters[$count];
  $_SESSION['monster']->startLevelUp();
  for($i = 0;$i < $_SESSION['clearDungeonCount']; $i++){
    $_SESSION['monster']->levelUp();
  }
  History::set($_SESSION['monster']->getName().'が現れた！');
}

//シリアライズ関数
function seriaMove(){
  //セッションのシリアライズ
  $_SESSION['adventurer'] = serialize($_SESSION['adventurer']);
  $_SESSION['History'] = serialize($_SESSION['History']);
  $_SESSION['clearDungeonCount'] = serialize($_SESSION['clearDungeonCount']);
}
//アンシリアライズ関数
function unSeriaMove(){
  $_SESSION['adventurer'] = unserialize($_SESSION['adventurer']);
  $_SESSION['History'] = unserialize($_SESSION['History']);
  $_SESSION['clearDungeonCount'] = unserialize($_SESSION['clearDungeonCount']);
}
//町へ移動関数
function removeTown(){
  //セッションのシリアライズ
  seriaMove();
  //町へフラグtrueセッションに代入
  $_SESSION['goTown'] = true;
  //町へ遷移
  header("Location:town.php");
  unset($_SESSION['questions']);
  exit();
}
function clear(){
  if(!empty($_SESSION['boss'])){
    unset($_SESSION['boss']);
    History::set('ゲームクリア！');
    $_SESSION['adventurer']->setGold($_SESSION['adventurer']->getGold() + 50000);
    $_SESSION['adventurer']->setExperience($_SESSION['adventurer']->getExperience() + 10000);
    History::set('50000G獲得した！');
    History::set('10000EXP獲得した！');
  }else{
    History::set('ダンジョンクリア！');
    $_SESSION['adventurer']->setGold($_SESSION['adventurer']->getGold() + 1000);
    $_SESSION['adventurer']->setExperience($_SESSION['adventurer']->getExperience() + 100);
    History::set('1000G獲得した！');
    History::set('100EXP獲得した！');
  }
  $_SESSION['adventurer']->levelUp();
  unset($_SESSION['knockDownMonster']);
  $_SESSION['clearDungeonCount'] = $_SESSION['clearDungeonCount']+1;
  removeTown();
}
//モンスターの攻撃関数
function monsterAttackPart(){
  $_SESSION['monster']->attack($_SESSION['adventurer']);
  if($_SESSION['adventurer']->getHp() <= 0){
    History::set($_SESSION['adventurer']->getName().'は目の前が真っ暗になった');
    $_SESSION['adventurer']->setGold(0);
    $_SESSION['adventurer']->setHp($_SESSION['adventurer']->getUpperHp());
    History::set('所持金を全て失った');
    unset($_SESSION['knockDownMonster']);
    removeTown();
  }
}
//プレイヤー攻撃関数
function playerAttackPart(){
  $_SESSION['adventurer']->attack($_SESSION['monster']);
  if($_SESSION['monster']->getHp() <=0){
    History::set($_SESSION['monster']->getName().'を倒した！');
    $_SESSION['adventurer']->setGold($_SESSION['adventurer']->getGold() + $_SESSION['monster']->getGold());
    $_SESSION['adventurer']->setExperience($_SESSION['adventurer']->getExperience() + $_SESSION['monster']->getExperience());
    History::set($_SESSION['monster']->getGold().'G獲得した！');
    History::set($_SESSION['monster']->getExperience().'EXP獲得した！');
    $_SESSION['adventurer']->levelUp();
    $_SESSION['knockDownMonster'] = $_SESSION['knockDownMonster']+1;
    if($_SESSION['knockDownMonster'] > 5){
      clear();
    }else{
      if(!empty($_SESSION['boss'])){
        createBossMonster($_SESSION['knockDownMonster']);
      }else{
        createMonster();
        for($i = 0; $i < $_SESSION['knockDownMonster']; $i++){
          $_SESSION['monster']->levelUp();
          error_log('$i：'.$i);
          error_log('モンスターレベルアップ時'.print_r($_SESSION['monster'],true));
        }
      }
    }
  }
}

if(empty($_SESSION['adventurer'])) {
	$_SESSION = array();
    $_POST = array();
	header('Location:town.php');
    exit();
}

//町からダンジョンへ移動時
if(!empty($_SESSION['goDungeon']) || !empty($_SESSION['goBossDungeon'])){
  //セッションのアンシリアライズ
  unSeriaMove();
  //モンスター討伐数
  $_SESSION['knockDownMonster'] = 0;
  //ダンジョンへ移動フラグ解除
  if(!empty($_SESSION['goDungeon'])){
    History::set('ダンジョンへやってきた！');
    unset($_SESSION['goDungeon']);
    unset($_SESSION['boss']);
    //モンスター生成
    createMonster();
  }elseif(!empty($_SESSION['goBossDungeon'])){
    History::set('ボスダンジョンへやってきた！');
    unset($_SESSION['goBossDungeon']);
    $_SESSION['boss'] = true;
    //モンスター生成
    createBossMonster($_SESSION['knockDownMonster']);
  }
  //ページ移動時モンスター攻撃無しフラグ（POST送信無し時）
  $_SESSION['noAttack'] = true;
  error_log('セッション変数中身：'.print_r($_SESSION,true));
}

//お題生成
$questionsSubject = Subject::STRINGS[mt_rand(0,103)];
error_log('お題生成時：'.$questionsSubject);

if(empty($_SESSION['monster'])) {
	header('Location:town.php');
	exit();
}

//秒ごとにリロード
//POST送信があった場合
if(!empty($_POST)){
  error_log('post情報：'.print_r($_POST,true));
  //変数に値を代入
  $attack = !empty($_POST['attack'])? $_POST['attack']: '';
  $returnTown = !empty($_POST['returnTown']);
  error_log('変数アタック文字列：'.$attack);
  error_log('変数町へフラグ：'.$returnTown);
  //テキスト送信（プレイヤー攻撃）があった場合
  if(!empty($attack)){
    error_log('入力：'.$attack);
    error_log('お題比較session：'.$_SESSION['questions']);
    //お題と同じ場合
    if($attack == $_SESSION['questions']){
      //プレイヤーの攻撃
      playerAttackPart();
    }else{
      //お題と違う場合
      //モンスターの攻撃
      monsterAttackPart();
    }
    //攻撃用変数クリア
    $attack = '';
  }
  //町へを押した場合
  if($returnTown) removeTown();
  //攻撃パート終了後、リダイレクトし、POST情報クリア
  //リダイレクト時POSTがないとモンスター攻撃が発動するため、
  //ページ移動時モンスター攻撃無しフラグtrue（POST送信無し時）
  $_SESSION['noAttack'] = true;
  header("Location:dungeon.php");
  exit();
}else{
  //ページ移動時モンスター攻撃無しフラグtrue（POST送信無し時）
  if(!empty($_SESSION['noAttack'])){
    //フラグ解除
    unset($_SESSION['noAttack']);
  }else{
    //POST送信がなく、フラグもない場合（秒後毎のリロード時）
    //モンスターの攻撃
    monsterAttackPart();
  }
}

//セッションにお題を代入
$_SESSION['questions'] = $questionsSubject;
error_log('お題セッション：'.$_SESSION['questions']);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0">
  <title>街とダンジョン</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <div id="main" class="site-width">
    <div class="dungeon upper-side">
      <div class="progBar">
        <p class="seconds js-remainTime"></p>
        <p class="bar js-remainBar"></p>
      </div>
      <div class="monster-container">
        <div class="img-container">
          <img src="<?php echo $_SESSION['monster']->getImg(); ?>" alt="">
        </div>
        <div class="monster-info">
          <p>名前:<?php echo $_SESSION['monster']->getName(); ?></p>
          <p>LEVEL:<?php echo $_SESSION['monster']->getLevel(); ?></p>
          <p>HP:<?php echo $_SESSION['monster']->getHp(); ?>/<?php echo $_SESSION['monster']->getUpperHp(); ?></p>
          <p>MP:<?php echo $_SESSION['monster']->getMp(); ?>/<?php echo $_SESSION['monster']->getUpperMp(); ?></p>
          <p>モンスター討伐数:<?php echo $_SESSION['knockDownMonster']; ?></p>
          <p>ダンジョン踏破数:<?php echo $_SESSION['clearDungeonCount']; ?></p>
        </div>
      </div>
      <p class="subject"><?php echo $_SESSION['questions']; ?></p>
    </div>
    <form action="" method="post" class="dungeon-form">
      <input type="text" name="attack" autofocus autocomplete="off">
    </form>
    <div class="lower-side">
      <div class="human-info">
        <p>名前:<?php echo $_SESSION['adventurer']->getName(); ?></p>
        <p>LEVEL:<?php echo $_SESSION['adventurer']->getLevel(); ?></p>
        <p>HP:<?php echo $_SESSION['adventurer']->getHp(); ?>/<?php echo $_SESSION['adventurer']->getUpperHp(); ?></p>
        <p>MP:<?php echo $_SESSION['adventurer']->getMp(); ?>/<?php echo $_SESSION['adventurer']->getUpperMp(); ?></p>
        <p>所持金:<?php echo $_SESSION['adventurer']->getGold(); ?><span>G</span></p>
        <p>経験値:<?php echo $_SESSION['adventurer']->getExperience(); ?>/<?php echo $_SESSION['adventurer']->getExperienceFull(); ?></p>
      </div>
      <div class="log" id="js-log">
        <p><?php echo (!empty($_SESSION['History']))? $_SESSION['History']: '';?></p>
      </div>
      <form action="" method="post">
        <input type="submit" name="returnTown" value="町へ" style="margin-top: 15px;">
      </form>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.1.min.js"
  integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ="
  crossorigin="anonymous"></script>
  <script>

      $(function(){
        /*プログレスバー*/
          var TimerCD = (function() {
              /*========================================
              PRIVATE PROPERTY
              ========================================*/
              var timeMili = 10000;//10000ミリ秒
              var timeSec = timeMili / 1000;//10秒
              var remainTime = timeMili;
              var $time = $('.js-remainTime');
              var $bar = $('.js-remainBar');
              /*========================================
              PRIVATE METHOD
              ========================================*/
              //表示用String変換
              var conversionStr = function() {
                  var strSec = String(Math.floor(remainTime/1000) % 60);
                  var strMili = String(Math.floor(remainTime/10) % 100).padStart(2, '0');
                  return strSec+':'+strMili;
              };
              //初期化
              var init = function() {
                  remainTime = timeMili;
                  $time.text(conversionStr());
                  $bar.removeAttr('style');
              };

              return {
                  /*========================================
                  PUBLIC METHOD
                  ========================================*/
                  //ゲッター
                  getTimeMili : function() {
                      return timeMili;
                  },
                  getRemainTime : function() {
                      return remainTime;
                  },
                  runInit : function() {
                      init();
                  },
                  //カウントダウン開始メソッド
                  countDown : function() {
                      var date = new Date();
                      var now = date.getTime();
                      var targetTime = now + timeMili;

                      var intervalId = setInterval(function() {
                          date = new Date();
                          now = date.getTime();
                          remainTime = targetTime - now;
                          $time.text(conversionStr());
                          if(remainTime <= 0) {
                              init();
                              clearInterval(intervalId);
                          }
                      },20);
                      //プログレスバーにstyle付与
                      $bar.css({'transition':timeSec+'s linear','width':'0%'});
                  }
              };
          })();

          //初期化
          TimerCD.runInit();
          TimerCD.countDown();

          /*スクロール最下部*/
          var $jsLog = $('#js-log');
          $jsLog.animate({'scrollTop':$jsLog.get(0).scrollHeight},'fast');

          setTimeout(function () {
              location.reload();
          }, 10000);
      });
  </script>
</body>
</html>