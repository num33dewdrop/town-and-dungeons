<?php
//お題定数配列
class Subject{
  const STRINGS = ['html','head','meta','charset','utf-8','title','link','rel','href','type','body','div','class','id','img','span','src','alt','form','action','method','post','get','input','text','submit','name','value','autofocus','script','const','define','true','false','function','css','width','height','getTime','setInterval','clearInterval','setTimeout','Math.floor','if','else','background','margin','padding','color','line-height','overflow','hidden','position','border','border-radius','text-align','center','z-index','relative','absolute','display','block','auto','font-size','none','box-sizing','float','left','right','solid','dashed','border-box','array','session','start','ini_set','log_errors','error_log','php','DOCTYPE','textarea','abstract','protected','public','private','return','mt_rand','extends','__construct','switch','case','break','parent','interface','static','implements','empty','unset','new','global','password','cursor','pointer','style'];
}

class Jobs{
  const KNIGHT = '騎士';
  const MAGICIAN = '魔法使い';
  const ASSASSIN = '暗殺者';
}

//抽象クラス
abstract class Creature{
  protected $name;
  protected $level;
  protected $hp;
  protected $upperHp;
  protected $mp;
  protected $upperMp;
  protected $attack;
  protected $defence;
  protected $hit;//命中率
  protected $remove;//回避率
  protected $gold;//所持金
  protected $experience;//経験値
  public function setName($str){
    $this->name = $str;
  }
  public function getName(){
    return $this->name;
  }
  public function setLevel($num){
    $this->level = $num;
  }
  public function getLevel(){
    return $this->level;
  }
  public function setHp($num){
    $this->hp = $num;
  }
  public function getHp(){
    return $this->hp;
  }
  public function setUpperHp($num){
    $this->upperHp = $num;
  }
  public function getUpperHp(){
    return $this->upperHp;
  }
  public function setMp($num){
    $this->mp = $num;
  }
  public function getMp(){
    return $this->mp;
  }
  public function setUpperMp($num){
    $this->upperMp = $num;
  }
  public function getUpperMp(){
    return $this->upperMp;
  }
  public function setAttack($num){
    $this->attack = $num;
  }
  public function getAttack(){
    return $this->attack;
  }
  public function setDefence($num){
    $this->defence = $num;
  }
  public function getDefence(){
    return $this->defence;
  }
  public function setHit($num){
    $this->hit = $num;
  }
  public function getHit(){
    return $this->hit;
  }
  public function setRemove($num){
    $this->remove = $num;
  }
  public function getRemove(){
    return $this->remove;
  }
  public function setGold($num){
    $this->gold = $num;
  }
  public function getGold(){
    return $this->gold;
  }
  public function setExperience($num){
    $this->experience = $num;
  }
  public function getExperience(){
    return $this->experience;
  }
  abstract public function levelUp();
  public function attack($targetObj){
    History::set($this->getName().'の攻撃！');
    $a = 127 * $this->getHit() / $targetObj->getRemove();
    $a = (int)$a;
    $randPoint = mt_rand(0,255);
    error_log('命中ポイント：'.$a);
    error_log('ランダムポイント：'.$randPoint);
    if($a > $randPoint){
      $attackPoint = floor(floor(floor($this->getLevel() * 2 / 5 + 2) * (100 * $this->getAttack() / $targetObj->getDefence())) / 50 + 2) * mt_rand(7,10);
      error_log('ダメージポイント：'.$attackPoint);
      if(!mt_rand(0,9)){
        $attackPoint = ceil($attackPoint * 1.5);
        error_log('クリティカルダメージポイント：'.$attackPoint);
        History::set('クリティカルヒット！');
      }
      $targetObj->setHp($targetObj->getHp() - $attackPoint);
      History::set($attackPoint.'のダメージ！');
    }else{
      History::set($targetObj->getName().'は回避した！');
    }
  }
}

//冒険者クラス
class AdventurerOrigin extends Creature{
  protected $job;
  protected $experienceFull;//必要経験値
  public function __construct($name, $level, $job, $hp, $upperHp, $mp, $upperMp, $attack, $defence, $hit, $remove, $gold, $experience, $experienceFull){
    $this->name = $name;
    $this->level = $level;
    $this->job = $job;
    $this->hp = $hp;
    $this->upperHp = $upperHp;
    $this->mp = $mp;
    $this->upperMp = $upperMp;
    $this->attack = $attack;
    $this->defence = $defence;
    $this->hit = $hit;
    $this->remove = $remove;
    $this->gold = $gold;
    $this->experience = $experience;
    $this->experienceFull = $experienceFull;
  }
  public function getJob(){
    return $this->job;
  }
  public function setExperienceFull($num){
    $this->experienceFull = $num;
  }
  public function getExperienceFull(){
    return $this->experienceFull;
  }
  public function levelUp(){
    $beforeLevel = $this->getLevel();
    while($this->getExperience() >= $this->getExperienceFull()){
      error_log('現在経験値：'.$this->getExperience());
      error_log('必要経験値：'.$this->getExperienceFull());
      $exDiff = $this->getExperience() - $this->getExperienceFull();
      $this->setLevel($this->getLevel() + 1);
      $this->setExperience($exDiff);
      $this->setExperienceFull(ceil($this->getExperienceFull() * 1.2));
      History::set('レベルアップ！');
      error_log(print_r($_SESSION['adventurer'],true));
    }
    $afterLevel = $this->getLevel();
    error_log(print_r($_SESSION['adventurer'],true));
  }
}

class Knight extends AdventurerOrigin{
  public function attack($targetObj){
    if(empty($_SESSION['power'])){
      if(!mt_rand(0,4)){
        History::set($this->getName().'は力を溜めた！');
        if($this->getMp() >= 10){
          $this->setMp($this->getMp() - 10);
          History::set($this->getName().'はMPを10消費した！');
          $_SESSION['power'] = 1;
        }else{
          History::set('MPが足りない！');
          parent::attack($targetObj);
        }
      }else{
        parent::attack($targetObj);
      }
    }else{
      History::set($this->getName().'の溜め攻撃！');
      $a = 127 * $this->getHit() / $targetObj->getRemove();
      $a = (int)$a;
      $randPoint = mt_rand(0,255);
      error_log('命中ポイント：'.$a);
      error_log('ランダムポイント：'.$randPoint);
      if($a > $randPoint){
        $attackPoint = floor(floor(floor($this->getLevel() * 2 / 5 + 2) * (100 * $this->getAttack() / ($targetObj->getDefence() / 2))) / 50 + 2) * mt_rand(7,10);
        $attackPoint = ceil($attackPoint * 2);
        error_log('ダメージポイント：'.$attackPoint);
        $targetObj->setHp($targetObj->getHp() - $attackPoint);
        History::set('防御を崩した！');
        History::set($attackPoint.'のダメージ！');
      }else{
        History::set($targetObj->getName().'は回避した！');
      }
      $_SESSION['power'] = 0;
    }
  }
  public function levelUp(){
    error_log('レベルアップ前Level：'.$this->getLevel());
    error_log('レベルアップ前Status：'.print_r($_SESSION['adventurer'],true));
    $beforeLevel = $this->getLevel();
    while($this->getExperience() >= $this->getExperienceFull()){
      error_log('現在経験値：'.$this->getExperience());
      error_log('必要経験値：'.$this->getExperienceFull());
      $exDiff = $this->getExperience() - $this->getExperienceFull();
      $this->setLevel($this->getLevel() + 1);
      $this->setExperience($exDiff);
      $this->setExperienceFull(ceil($this->getExperienceFull() * 1.2));
      History::set('レベルアップ！');
      error_log(print_r($_SESSION['adventurer'],true));
    }
    $afterLevel = $this->getLevel();
    error_log(print_r($_SESSION['adventurer'],true));
    if($afterLevel > $beforeLevel){
      $levelDiff = $afterLevel - $beforeLevel;
      for($i=1; $i <= $levelDiff; $i++){//12point 0.2point
        $this->setUpperHp(ceil($this->getUpperHp() * 1.4));
        $this->setUpperMp(ceil($this->getUpperMp() * 1.1));
        $this->setAttack(ceil($this->getAttack() * 1.3));
        $this->setDefence(ceil($this->getDefence() * 1.4));
        $this->setHit($this->getHit() + 0.1);
        $this->setRemove($this->getRemove() + 0.1);
        error_log($i);
      }
      error_log('レベルアップ後Level：'.$this->getLevel());
      error_log('レベルアップ後Status：'.print_r($_SESSION['adventurer'],true));
    }
  }
}

class Magician extends AdventurerOrigin{
  public function attack($targetObj){
    if(!mt_rand(0,2)){
      History::set($this->getName().'の魔法攻撃！');
      if($this->getMp() >= 10){
        $this->setMp($this->getMp() - 10);
        History::set($this->getName().'はMPを10消費した！');
        $a = 127 * $this->getHit() / $targetObj->getRemove();
        $a = (int)$a;
        $randPoint = mt_rand(0,255);
        error_log('命中ポイント：'.$a);
        error_log('ランダムポイント：'.$randPoint);
        if($a > $randPoint){
          $attackPoint = floor(floor(floor($this->getLevel() * 2 / 5 + 2) * (100 * $this->getAttack() / $targetObj->getDefence() )) / 50 + 2) * 10;
          $attackPoint = ceil($attackPoint * 1.5);
          error_log('ダメージポイント：'.$attackPoint);
          $targetObj->setHp($targetObj->getHp() - $attackPoint);
          History::set($attackPoint.'のダメージ！');
          if($this->getHp() < $this->getUpperHp()){
            $healPoint = floor($attackPoint/5);
            $this->setHp($this->getHp() + $healPoint);
            if($this->getHp() >= $this->getUpperHp()){
              $this->setHp($this->getUpperHp());
            }
            History::set($this->getName().'はHPを少し吸収した！');
          }
        }else{
          History::set($targetObj->getName().'は回避した！');
        }
      }else{
        History::set('MPが足りない！');
        parent::attack($targetObj);
      }
    }else{
      parent::attack($targetObj);
    }
  }
  public function levelUp(){
    error_log('レベルアップ前Level：'.$this->getLevel());
    error_log('レベルアップ前Status：'.print_r($_SESSION['adventurer'],true));
    $beforeLevel = $this->getLevel();
    while($this->getExperience() >= $this->getExperienceFull()){
      error_log('現在経験値：'.$this->getExperience());
      error_log('必要経験値：'.$this->getExperienceFull());
      $exDiff = $this->getExperience() - $this->getExperienceFull();
      $this->setLevel($this->getLevel() + 1);
      $this->setExperience($exDiff);
      $this->setExperienceFull(ceil($this->getExperienceFull() * 1.2));
      History::set('レベルアップ！');
      error_log(print_r($_SESSION['adventurer'],true));
    }
    $afterLevel = $this->getLevel();
    error_log(print_r($_SESSION['adventurer'],true));
    if($afterLevel > $beforeLevel){
      $levelDiff = $afterLevel - $beforeLevel;
      for($i=0; $i < $levelDiff; $i++){//12point 0.2point
        $this->setUpperHp(ceil($this->getUpperHp() * 1.2));
        $this->setUpperMp(ceil($this->getUpperMp() * 1.5));
        $this->setAttack(ceil($this->getAttack() * 1.3));
        $this->setDefence(ceil($this->getDefence() * 1.2));
        $this->setHit($this->getHit() + 0.12);
        $this->setRemove($this->getRemove() + 0.08);
      }
      error_log('レベルアップ後Level：'.$this->getLevel());
      error_log('レベルアップ後Status：'.print_r($_SESSION['adventurer'],true));
    }
  }
}

class Assassin extends AdventurerOrigin{
  public function attack($targetObj){
    if($targetObj->getHp() == $targetObj->getUpperHp() && !mt_rand(0,19)){
      History::set($this->getName().'の暗殺');
      if($this->getMp() >= 100){
        $this->setMp($this->getMp() - 100);
        $targetObj->setHp(0);
      }else{
        History::set('MPが足りない！');
        parent::attack($targetObj);
      }
    }else{
      parent::attack($targetObj);
    }
  }
  public function levelUp(){
    error_log('レベルアップ前Level：'.$this->getLevel());
    error_log('レベルアップ前Status：'.print_r($_SESSION['adventurer'],true));
    $beforeLevel = $this->getLevel();
    while($this->getExperience() >= $this->getExperienceFull()){
      error_log('現在経験値：'.$this->getExperience());
      error_log('必要経験値：'.$this->getExperienceFull());
      $exDiff = $this->getExperience() - $this->getExperienceFull();
      $this->setLevel($this->getLevel() + 1);
      $this->setExperience($exDiff);
      $this->setExperienceFull(ceil($this->getExperienceFull() * 1.2));
      History::set('レベルアップ！');
      error_log(print_r($_SESSION['adventurer'],true));
    }
    $afterLevel = $this->getLevel();
    error_log(print_r($_SESSION['adventurer'],true));
    if($afterLevel > $beforeLevel){
      $levelDiff = $afterLevel - $beforeLevel;
      for($i=0; $i < $levelDiff; $i++){//12point 0.2point
        $this->setUpperHp(ceil($this->getUpperHp() * 1.2));
        $this->setUpperMp(ceil($this->getUpperMp() * 1.2));
        $this->setAttack(ceil($this->getAttack() * 1.5));
        $this->setDefence(ceil($this->getDefence() * 1.2));
        $this->setHit($this->getHit() + 0.15);
        $this->setRemove($this->getRemove() + 0.15);
      }
      error_log('レベルアップ後Level：'.$this->getLevel());
      error_log('レベルアップ後Status：'.print_r($_SESSION['adventurer'],true));
    }
  }
}

//モンスタークラス
class Monster extends Creature{
  protected $img;
  public function __construct($name, $img, $level, $hp, $upperHp, $mp, $upperMp, $attack, $defence, $hit, $remove, $gold, $experience){
    $this->name = $name;
    $this->img = $img;
    $this->level = $level;
    $this->hp = $hp;
    $this->upperHp = $upperHp;
    $this->mp = $mp;
    $this->upperMp = $upperMp;
    $this->attack = $attack;
    $this->defence = $defence;
    $this->hit = $hit;
    $this->remove = $remove;
    $this->gold = $gold;
    $this->experience = $experience;
  }
  public function getImg(){
    return $this->img;
  }
  public function levelUp(){
    $beforeLevel = $this->getLevel();
    error_log('レベルアップ前Level：'.$this->getLevel());
    error_log('レベルアップ前Status：'.print_r($_SESSION['monster'],true));
    $this->setLevel($this->getLevel() + 1);
    $afterLevel = $this->getLevel();
    $diffLevel = $afterLevel - $beforeLevel;
    for($i = 0; $i < $diffLevel; $i++){
      $this->setHp(ceil($this->getHp() * 1.2));
      $this->setUpperHp(ceil($this->getUpperHp() * 1.2));
      $this->setMp(ceil($this->getMp() * 1.2));
      $this->setUpperMp(ceil($this->getUpperMp() * 1.2));
      $this->setAttack(ceil($this->getAttack() * 1.2));
      $this->setDefence(ceil($this->getDefence() * 1.2));
      $this->setHit($this->getHit() + 0.05);
      $this->setRemove($this->getRemove() + 0.05);
      $this->setGold(ceil($this->getGold() * 1.1));
      $this->setExperience(ceil($this->getExperience() * 1.1));
    }
    error_log('レベルアップ後Level：'.$this->getLevel());
    error_log('レベルアップ後Status：'.print_r($_SESSION['monster'],true));
  }
  public function startLevelUp(){
    error_log('レベルアップ前Status：'.print_r($_SESSION['monster'],true));
    for($i = 1; $i < $this->getLevel(); $i++){
      $this->setHp(ceil($this->getHp() * 1.2));
      $this->setUpperHp(ceil($this->getUpperHp() * 1.2));
      $this->setMp(ceil($this->getMp() * 1.2));
      $this->setUpperMp(ceil($this->getUpperMp() * 1.2));
      $this->setAttack(ceil($this->getAttack() * 1.2));
      $this->setDefence(ceil($this->getDefence() * 1.2));
      $this->setHit($this->getHit() + 0.05);
      $this->setRemove($this->getRemove() + 0.05);
      $this->setGold(ceil($this->getGold() * 1.1));
      $this->setExperience(ceil($this->getExperience() * 1.1));
    }
    error_log('レベルアップ後Status：'.print_r($_SESSION['monster'],true));
  }
}

class History{
  public static function set($str){
    if(empty($_SESSION['History'])) $_SESSION['History'] = '';
    $_SESSION['History'] .= $str.'<br>';
  }
  public static function clear(){
    unset($_SESSION['History']);
  }
}
?>