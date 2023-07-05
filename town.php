<?php
ini_set('log_errors','on');
ini_set('error_log','php.log');

include("class.php");
session_start();

//gamestart関数
function init(){
  History::clear();
  History::set('ゲームスタート！');
  $_SESSION['clearDungeonCount'] = 0;
  $_SESSION['healCount'] = 1;
  $_SESSION['trainingCount'] = 1;
  $_SESSION['jobChangeCount'] = 1;
  error_log('ゲームスタート！');
}
//支払い関数
function pay($humanObj, $pushButton){
	$count = 1;
  switch($pushButton){
    case 'heal':
      $count = $_SESSION['healCount'];
    break;
    case 'training':
      $count = $_SESSION['trainingCount'];
    break;
    case 'jobChange':
      $count = $_SESSION['jobChangeCount'];
    break;
  }
  //お金が足りる場合
  if($humanObj->getGold() >= $count * 100){
    $humanObj->setGold($humanObj->getGold() - ($count * 100));
    History::set(($count * 100).'G支払った！');
    return true;
  }else{
    //お金が足りない場合
    History::set('お金が足りない！');
    return false;
  }
}
//humanOBJ初期生成関数
function createHuman($name, $jobSelect){
  switch($jobSelect){
    case "knight" :
      $selectedJob = Jobs::KNIGHT;
      $adventurer = new Knight($name, 1, $selectedJob, 500, 500, 100, 100, 20, 20, 1, 1, 1000, 0, 100);
      $_SESSION['adventurer'] = $adventurer;
      error_log(print_r($_SESSION,true));
    break;
    case "magician" :
      $selectedJob = Jobs::MAGICIAN;
      $adventurer = new Magician($name, 1, $selectedJob, 500, 500, 100, 100, 20, 20, 1, 1, 1000, 0, 100);
      $_SESSION['adventurer'] = $adventurer;
      error_log(print_r($_SESSION,true));
    break;
    case "assassin" :
      $selectedJob = Jobs::ASSASSIN;
      $adventurer = new Assassin($name, 1, $selectedJob, 500, 500, 100, 100, 20, 20, 1, 1, 1000, 0, 100);
      $_SESSION['adventurer'] = $adventurer;
      error_log(print_r($_SESSION,true));
    break;
  }
}
//転職関数
function changeJob($jobSelect, $humanObj){
  //支払い
  if(pay($humanObj, 'jobChange')){
    //変数に各定数を代入
    //オブジェクト再生成
    switch($jobSelect){
      case "knight" :
        $selectedJob = Jobs::KNIGHT;
        $adventurer = new Knight($humanObj->getName(), $humanObj->getLevel(), $selectedJob, $humanObj->getHp(), $humanObj->getUpperHp(), $humanObj->getMp(), $humanObj->getUpperMp(), $humanObj->getAttack(), $humanObj->getDefence(), $humanObj->getHit(), $humanObj->getRemove(), $humanObj->getGold(), $humanObj->getExperience(), $humanObj->getExperienceFull());
        $_SESSION['adventurer'] = $adventurer;
      break;
      case "magician" :
        $selectedJob = Jobs::MAGICIAN;
        $adventurer = new Magician($humanObj->getName(), $humanObj->getLevel(), $selectedJob, $humanObj->getHp(), $humanObj->getUpperHp(), $humanObj->getMp(), $humanObj->getUpperMp(), $humanObj->getAttack(), $humanObj->getDefence(), $humanObj->getHit(), $humanObj->getRemove(), $humanObj->getGold(), $humanObj->getExperience(), $humanObj->getExperienceFull());
        $_SESSION['adventurer'] = $adventurer;
      break;
      case "assassin" :
        $selectedJob = Jobs::ASSASSIN;
        $adventurer = new assassin($humanObj->getName(), $humanObj->getLevel(), $selectedJob, $humanObj->getHp(), $humanObj->getUpperHp(), $humanObj->getMp(), $humanObj->getUpperMp(), $humanObj->getAttack(), $humanObj->getDefence(), $humanObj->getHit(), $humanObj->getRemove(), $humanObj->getGold(), $humanObj->getExperience(), $humanObj->getExperienceFull());
        $_SESSION['adventurer'] = $adventurer;
      break;
    }
    History::set($_SESSION['adventurer']->getJob().'に転職した！');
    $_SESSION['jobChangeCount'] = $_SESSION['jobChangeCount'] + 1;
  }
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

//機能


if(!empty($_SESSION['goTown'])){
  unSeriaMove();
  unset($_SESSION['goTown']);
  History::set('町へ帰還した！');
}

if(!empty($_POST)){
  error_log('POST情報：'.print_r($_POST,true));
  error_log('SESSION情報：'.print_r($_SESSION,true));
  
  //変数にPOST情報を代入
  $name = !empty($_POST['name'])? $_POST['name']: 'no name';
  $jobSelect = !empty($_POST['job-select'])? $_POST['job-select'] : '';
  $jobChange = !empty($_POST['jobchange_x']);
  $heal = !empty($_POST['heal_x']);
  $training = !empty($_POST['training_x']);
  $goDungeon = !empty($_POST['goDungeon']);
  $goBossDungeon = !empty($_POST['goBossDungeon']);
  $start = !empty($_POST['start']);
  $restart = !empty($_POST['restart']);
  //職業選択した場合
  if(!empty($jobSelect)){
    //冒険者オブジェクトがない場合（初期ページ）
    if(empty($_SESSION) && $start){
      createHuman($name, $jobSelect);
      init();
    }
    //ジョブチェンジを押した場合
    if($jobChange){
      History::set('転職ギルドに来た！');
      changeJob($jobSelect, $_SESSION['adventurer']);
    }elseif($heal || $training){
    //選択したが、ジョブチェンジ送信をしていない（他のボタンを押した）
    History::set('ここでは転職できない！');
    History::set('追い払われてしまった！');
    }
  }elseif($heal){
    History::set('教会に来た！');
    //支払い
    if(pay($_SESSION['adventurer'], 'heal')){
      $_SESSION['adventurer']->setHp($_SESSION['adventurer']->getUpperHp());
      $_SESSION['adventurer']->setMp($_SESSION['adventurer']->getUpperMp());
      History::set('HPとMPが回復した！');
      $_SESSION['healCount'] = $_SESSION['healCount'] + 1;
    }
  }elseif($training){
    History::set('訓練場に来た！');
    //支払い
    if(pay($_SESSION['adventurer'], 'training')){
      $_SESSION['adventurer']->setExperience($_SESSION['adventurer']->getExperience() + 500);
      $_SESSION['adventurer']->levelUp();
      History::set('能力が向上した！');
      $_SESSION['trainingCount'] = $_SESSION['trainingCount'] + 1;
    }
  }
  if($goDungeon){
    seriaMove();
    $_SESSION['goDungeon'] = true;
    header("Location:dungeon.php");
    exit();
  }
  if($goBossDungeon){
    seriaMove();
    $_SESSION['goBossDungeon'] = true;
    header("Location:dungeon.php");
    exit();
  }
  if($restart){
    $_SESSION = array();
  }
  $_POST = array();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0">
    <title>町とダンジョン</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <div id="main" class="site-width">
    <?php if(empty($_SESSION)){ ?>
    <div class="initBox">
      <h1 class="title">町とダンジョン</h1>
      <form action="" method="post" class="init-form">
         <input type="text" name="name" id="name" placeholder="名前を入力してください">
         <select name="job-select" id="job-select">
          <option value>ジョブ選択</option>
          <option value="knight">騎士</option>
          <option value="magician">魔法使い</option>
          <option value="assassin">暗殺者</option>
        </select>
        <input type="submit" name="start" value="ゲームスタート！">
      </form>
    </div>
    <?php }else{ ?>
    <div class="town upper-side">
      <form action="" method="post" class="town-form go-dungeon">
        <?php if($_SESSION['clearDungeonCount'] < 5){?>
        <input type="submit" name="goDungeon" value="冒険する" >
        <?php }else{ ?>
        <div class="Boss-challenge" >
          <input type="submit" name="goDungeon" value="冒険する" >
          <input type="submit" name="goBossDungeon" style="float: right" value="ボス挑戦" >
        </div>
        <?php } ?>
        <input type="image" name="heal" src="img/heal.png" alt="" class="img-heal">
        <input type="image" name="training" src="img/training.jpg" alt="" class="img-training">
        <select name="job-select" id="" class="job-select">
          <option value="">ジョブ選択</option>
          <option value="knight" <?php if($_SESSION['adventurer']->getJob() == '騎士') echo 'disabled' ?> >騎士</option>
          <option value="magician" <?php if($_SESSION['adventurer']->getJob() == '魔法使い') echo 'disabled' ?>>魔法使い</option>
          <option value="assassin" <?php if($_SESSION['adventurer']->getJob() == '暗殺者') echo 'disabled' ?>>暗殺者</option>
        </select>
        <input type="image" name="jobchange" src="img/change.jpg" alt="" class="img-jobchange">
      </form>
    </div>
    <div class="middle-side">
      <b>職業:<?php echo $_SESSION['adventurer']->getJob(); ?></b>
      <p>攻撃力:<?php echo $_SESSION['adventurer']->getAttack(); ?>　防御力:<?php echo $_SESSION['adventurer']->getDefence(); ?>　命中：<?php echo $_SESSION['adventurer']->getHit(); ?>　回避:<?php echo $_SESSION['adventurer']->getRemove(); ?></p>
      <p>ダンジョン踏破数：<?php echo $_SESSION['clearDungeonCount']; ?></p>
      <p>必要経費：　教会 <?php echo ($_SESSION['healCount']*100).'G'; ?>　訓練場 <?php echo ($_SESSION['trainingCount']*100).'G'; ?>　転職ギルド <?php echo ($_SESSION['jobChangeCount']*100).'G'; ?></p>
    </div>
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
        <input type="submit" name="restart" value="ゲームリスタート！" style="margin-top: 15px;">
      </form>
    </div>
    <?php } ?>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.1.min.js"
  integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ="
  crossorigin="anonymous"></script>
  <script>
  $(function(){
    /*スクロール最下部*/
    var $jsLog = $('#js-log');
    if($jsLog.length) {
        $jsLog.animate({'scrollTop':$jsLog.get(0).scrollHeight},'fast');
    }
  });
  </script>
</body>
</html>