<?php
//  require_once('Net/UserAgent/Mobile.php');
//  require_once('../libs/EmojiTable.class.php');
  
  /*
   * Smarty plugin
   * -------------------------------------------------------------
   * File:     function.emoji.php
   * Type:     function
   * Name:     emoji
   * Purpose:  ケータイ用絵文字を表示する
   * -------------------------------------------------------------
   */
  function smarty_function_emoji($params, &$smarty)
  {
      // 現在のキャリアを取得
      $agent = $_SERVER["HTTP_USER_AGENT"];
      if (preg_match("/^DoCoMo\/[12]\.0/i", $agent)) {
          $carrier = 'docomo';
      } else if (preg_match("/^KDDI\-/i", $agent) || preg_match("/UP\.Browser/i", $agent)) {
          $carrier = 'au';
      } else if (preg_match("/^(J\-PHONE|Vodafone|MOT\-[CV]980|SoftBank)\//i", $agent)) {
          $carrier = 'softbank';
	} else if (preg_match("/^L\-mode/i", $agent)) {
          $carrier = 'docomo';

      }
      else{
          $carrier = null;
      }
  
      $emoji_tbl = EmojiTable::getInstance();
  
      // キャリア指定のパラメータがあるか?
      if($carrier && isset($params[$carrier])){
          $emoji = $emoji_tbl->get($params[$carrier], $carrier);
      }
  
      // 絵文字コードの指定があるか?
      elseif(isset($params['code'])){
          $emoji = $emoji_tbl->get($params['code'], $carrier);
      }
  
      return $emoji ? $emoji : EmojiTable::UNKNOWN_CHAR;
  } 
  


  class EmojiTable
  {
      const UNKNOWN_CHAR = '〓';
  
      public static function getInstance()
      {
          static $instance = null;
          if(is_null($instance)){
              $instance = new EmojiTable();
          }
          return $instance;
      }
      
      public function get($code, $carrier = null)
      {     
          if($carrier && isset(self::$_table[$code][$carrier])){
              return self::$_table[$code][$carrier];
          }   
          elseif(isset(self::$_table[$code]['caption'])){
              return '[' . self::$_table[$code]['caption'] . ']';
          }
          else{
              return null;
          }
      }  
  
      protected static $_table = array(
          'hare' => array(
              'caption'  => '晴れ',
              'docomo'   => '&#xE63E;',
              'au'       => '&#xE488;',
              'softbank' => '&#xE04A;'
          ),
          'kumori' => array(
              'caption'  => '曇り',
              'docomo'   => '&#xE63F;',
              'au'       => '&#xE48D;',
              'softbank' => '&#xE049;'
          ),
          'ame' => array(
              'caption'  => '雨',
              'docomo'   => '&#xE640;',
              'au'       => '&#xE48C;',
              'softbank' => '&#xE04B;'
          ),
          'yuki' => array(
              'caption'  => '雪',
              'docomo'   => '&#xE641;',
              'au'       => '&#xE485;',
              'softbank' => '&#xE048;'
          ),
          'kaminari' => array(
              'caption'  => '雷',
              'docomo'   => '&#xE642;',
              'au'       => '&#xE487;',
              'softbank' => '&#xE13D;'
          ),
          'denwa' => array(
              'caption'  => '電話',
              'docomo'   => '&#xE687;',
              'au'       => '&#xE596;',
              'softbank' => '&#xE009;'
          ),
          'keitai_denwa' => array(
              'caption'  => '携帯電話',
              'docomo'   => '&#xE688;',
              'au'       => '&#xE588;',
              'softbank' => '&#xE00A;'
          ),
          'hottosita_kao' => array(
              'caption'  => 'ほっとした顔',
              'docomo'   => '&#xE721;',
              'au'       => '&#xEAC5;',
              'softbank' => '&#xE40A;'
          ),
          'new' => array(
              'caption'  => 'NEW',
              'docomo'   => '&#xE6DD;',
              'au'       => '&#xE5B5;',
              'softbank' => '&#xE212;'
          ),
          'oukan' => array(
              'caption'  => '王冠',
              'docomo'   => '&#xE71A;',
              'au'       => '&#xE5C9;',
              'softbank' => '&#xE10E;'
          ),
          'ticket' => array(
              'caption'  => 'チケット',
              'docomo'   => '&#xE67E;',
              'au'       => '&#xE49E;',
              'softbank' => '&#xE125;'
          ),
          'hirameki' => array(
              'caption'  => 'ひらめき',
              'docomo'   => '&#xE6FB;',
              'au'       => '&#xE476;',
              'softbank' => '&#xE10F;'
          ),
          'hidariue' => array(
              'caption'  => '左斜め上',
              'docomo'   => '&#xE697;',
              'au'       => '&#xE54C;',
              'softbank' => '&#xE237;'
          ),
          'wa-i' => array(
              'caption'  => 'わーい（うれしい顔）',
              'docomo'   => '&#xE6F0;',
              'au'       => '&#xE471;',
              'softbank' => '&#xE057;'
          ),
          'kirakira' => array(
              'caption'  => 'きらきら',
              'docomo'   => '&#xE6FA;',
              'au'       => '&#xEAAB;',
              'softbank' => '&#xE32E;'
          ),
          'movie' => array(
              'caption'  => '映画',
              'docomo'   => '&#xE677;',
              'au'       => '&#xE517;',
              'softbank' => '&#xE03D;'
          ),
          'enpitu' => array(
              'caption'  => '鉛筆',
              'docomo'   => '&#xE719;',
              'au'       => '&#xE4A1;',
              'softbank' => '&#xE301;'
          ),
          'return' => array(
              'caption'  => '次項有',
              'docomo'   => '&#xE6DA;',
              'au'       => '&#xE55D;',
              'softbank' => '&#xE23B;'
          ),
          'tokei' => array(
              'caption'  => '時計',
              'docomo'   => '&#xE6BA;',
              'au'       => '&#xE594;',
              'softbank' => '&#xE024;'
          ),
          'memo' => array(
              'caption'  => 'メモ',
              'docomo'   => '&#xE689;',
              'au'       => '&#xEA92;',
              'softbank' => '&#xE301;'
          ),
          'present' => array(
              'caption'  => 'プレゼント',
              'docomo'   => '&#xF8E6;',
              'au'       => '&#xE4CF;',
              'softbank' => '&#xE112;'
          ),
          'present' => array(
              'caption'  => 'プレゼント',
              'docomo'   => '&#xF8E6;',
              'au'       => '&#xE4CF;',
              'softbank' => '&#xE112;'
          ),
          'kutu' => array(
              'caption'  => 'ブティック',
              'docomo'   => '&#xE674;',
              'au'       => '&#xE51A;',
              'softbank' => '&#xE13E;'
          ),
          'biru' => array(
              'caption'  => 'ビル',
              'docomo'   => '&#xE664;',
              'au'       => '&#xE4AD;',
              'softbank' => '&#xE038;'
          ),
          'asi' => array(
              'caption'  => '足',
              'docomo'   => '&#xE698;',
              'au'       => '&#xEB2A;',
              'softbank' => '&#xE536;'
          ),
          'car' => array(
              'caption'  => '車',
              'docomo'   => '&#xE65E;',
              'au'       => '&#xE4B1;',
              'softbank' => '&#xE01B;'
          ),
          'hart' => array(
              'caption'  => 'ハート',
              'docomo'   => '&#xE6EC;',
              'au'       => '&#xE595;',
              'softbank' => '&#xE022;'
          ),
          'game' => array(
              'caption'  => 'ゲーム',
              'docomo'   => '&#xE68B;',
              'au'       => '&#xE4C6;',
              'softbank' => '&#xE12A;'
          ),
          'one' => array(
              'caption'  => '1',
              'docomo'   => '&#xE6E2;',
              'au'       => '&#xE522;',
              'softbank' => '&#xE21C;'
          ),
          'two' => array(
              'caption'  => '2',
              'docomo'   => '&#xE6E3;',
              'au'       => '&#xE523;',
              'softbank' => '&#xE21D;'
          ),
          'three' => array(
              'caption'  => '3',
              'docomo'   => '&#xE6E4;',
              'au'       => '&#xE524;',
              'softbank' => '&#xE21E;'
          ),
          'four' => array(
              'caption'  => '4',
              'docomo'   => '&#xE6E5;',
              'au'       => '&#xE525;',
              'softbank' => '&#xE21F;'
          ),
          'five' => array(
              'caption'  => '5',
              'docomo'   => '&#xE6E6;',
              'au'       => '&#xE526;',
              'softbank' => '&#xE220;'
          ),
          'six' => array(
              'caption'  => '6',
              'docomo'   => '&#xE6E7;',
              'au'       => '&#xE527;',
              'softbank' => '&#xE221;'
          ),
          'seven' => array(
              'caption'  => '7',
              'docomo'   => '&#xE6E8;',
              'au'       => '&#xE528;',
              'softbank' => '&#xE222;'
          ),
          'eight' => array(
              'caption'  => '8',
              'docomo'   => '&#xE6E9;',
              'au'       => '&#xE529;',
              'softbank' => '&#xE223;'
          ),
          'nine' => array(
              'caption'  => '9',
              'docomo'   => '&#xE6EA;',
              'au'       => '&#xE52A;',
              'softbank' => '&#xE224;'
          ),
          'zero' => array(
              'caption'  => '0',
              'docomo'   => '&#xE6EB;',
              'au'       => '&#xE5AC;',
              'softbank' => '&#xE225;'
          ),
          'home' => array(
              'caption'  => '家',
              'docomo'   => '&#xE663;',
              'au'       => '&#xE4AB;',
              'softbank' => '&#xE036;'
          ),
          'camera' => array(
              'caption'  => 'カメラ',
              'docomo'   => '&#xE681;',
              'au'       => '&#xE515;',
              'softbank' => '&#xE008;'
          ),
          'sharp' => array(
              'caption'  => 'シャープダイヤル',
              'docomo'   => '&#xE6E0;',
              'au'       => '&#xEB84;',
              'softbank' => '&#xE210;'
          ),
      );
  }
  ?>