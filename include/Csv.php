<?php

/**
 * Csv.
 * @autor sonar
 * 1行目をヘッダー行として使用するcsv parser.
 * 全ての行が同じカラム数でなければエラー.
 * 1列目の値が重複していればエラー.
 * 1行目のheader行の値名が重複すればエラー.
 */
class Csv {

	var $file;
	var $separator;

	/**
	 * コンストラクタ.
	 *
	 * @param string $file CSVファイルのパス
	 * @param string $separator CSVファイルの区切り文字
	 */
	public function Csv($file , $separator = ',')
	{
		$this->file = $file;
		$this->separator = $separator;
		$this->error = $this->checkCsv();
	}
	
	/**
	 * checkCsv.
	 * csvファイル全体を読み込みチェックを行う.
	 *
	 * @param string $file CSVファイルのパス
	 */
	public function checkCsv()
	{
		if ( !file_exists($this->file) ) {
			throw new Exception($this->file . " : ファイルが見つかりません.");
		}
		if ( !is_writable($this->file) ) {
			throw new Exception($this->file . " : ファイルに書き込み権限がありません.");
		}
		$csvlist = $this->getCsvList($this->file);
		$header = $this->parseString($csvlist[0]);
		if (count(array_unique($header)) !== count($header)) {
			throw new Exception($this->file . ": ヘッダーの要素が重複しています.");
		}
		foreach( $csvlist as $v ){
			$params = $this->parseString($v);
			if (count($params) !== count($header)) {
				throw new Exception($this-> file .":カラム数に差異があります. ");
			}
			$firstColArray[] = $params[0];
		}
		if (count(array_unique($firstColArray)) !== count($csvlist)) {
			throw new Exception($this->file . ": 1列目の値が重複します.");
		}
	}

 	/**
	 * checkUnique.
	 * csvの1カラム目が全体でユニークにならない更新はエラーとする.
	 *
	 * @param array $firstCol CSVファイルの1行
	 * @return boolean
	 */
	public function checkUnique($string)
	{
		$csvlist = $this->getCsvList($this->file);
		foreach( $csvlist as $v ){
			$params = $this->parseString($v);
			$firstColArray[] = $params[0];
		}
		if (array_search($string, $firstColArray)) {
			return false;
		}
		return true;
	}

  	/**
	 * is_hash.
	 * 値が連想配列かどうか調べる
	 *
	 * @param array $array
	 * @return boolean trueであれば連想配列
	 */
	public function is_hash(&$array) {
		$i = 0;
		foreach($array as $k => $dummy) {
			if ( $k !== $i++ ) return true;
		}
		return false;
	}

	/**
	 * getCsvList.
	 * ファイル全体を読み込み配列に格納する.
	 *
	 * @param string $file CSVファイルのパス
	 * @return array ファイル全体を読み込んで配列に格納した値
	 */
	public function getCsvList()
	{
		$csvlist = file($this->file);
		return $csvlist;
	}

	/**
	 * getCsvHeader.
	 * ファイルの1行目(header行)を取得して配列として返す
	 *
	 * @param string $flug 値を入れると配列ではなく文字列で返す
	 * @return array header行の配列
	 */
	public function getCsvHeader($flug = null)
	{
		$csvlist = $this->getCsvList();
		if(isset($flug)){
			$header = $csvlist[0];
		}else{
			$header = $this->parseString($csvlist[0]);
		}
		return $header;
	}


	/**
	 * parseString.
	 * csvのラインを配列にする.
	 *
	 * @param string $string CSVファイルの1行
	 * @return array
	 */
	public function parseString($string)
	{
		$values = array();
		$string = trim($string);
		$values = explode($this->separator,$string);
		return $values;
	}

	/**
	 * getCsv.
	 * csvファイルを連想配列で取得する.
	 * 1行目をヘッダー行として取得し、配列のkeyとしてセットします.
	 *
	 * @return array
	 */
	public function getCsv()
	{
		$csvlist = $this->getCsvList();
		//1行目をヘッダーとして取得する
		$header = $this->parseString($csvlist[0]);
		foreach($csvlist as $k => $v){
			$v = trim($v);
			//1行目を無視
			if ($k === 0 ) { continue; }
			$value = $this->parseString($v);
			$tmp = array_combine ( $header , $value );
			if(!$tmp){
				throw new Exception("number of commas is different.");
			}
			$list[] = $tmp;
		}
		return $list;
	}

	/**
	 * getCsvline.
	 * 1列目の値をキーとしてcsvファイルの特定行を連想配列で取得する.
	 * @param string $key
	 * @return array
	 */
	public function getCsvLine($key)
	{
		$data = $this->getCsv();
		$keys = array_keys($data[0]);
		foreach($data as $k => $v) {
				if ($v[$keys[0]] === $key) {
					$value = $v;
				}
		}
		return($value);
	}

	/**
	 * getCsvColumn.
	 * csvファイルの特定ヘッダ列を連想配列で取得する.
	 * 連想配列のkey配列は1番目のカラムとなる.
	 * 取得してきた連想配列のvalue値が空であれば削除する.
	 *
	 * @param string $key
	 * @return array
	 */
	public function getCsvColumn($key)
	{
		$data = $this->getCsv();
		$keys = array_keys($data[0]);
		foreach($data as $k => $v) {
				$value[$v[$keys[0]]] = $v[$key];
		}
		$value = array_filter($value, 'strlen');
		return($value);
	}

	/**
	 * editCsv.
	 * csvファイルの1番目のカラムの値をキーとして特定要素を書き換える.
	 *
	 * @param string $string 書き換える値
	 * @param string $key 検索する1番目のカラムの値
	 * @param string $col 書き換える値を入力するカラム名
	 * @return boolean 編集が実行されればtrue
	 */
	public function editCsv($string, $key, $col)
	{
		$data = $this->getCsv();
		$keys = array_keys($data[0]);
		$header = $this->getCsvHeader();
		$k = 0;
		foreach($data as $k => $value) {
			if($col === $header[0] && $value[$col] === $string ) {
				throw new Exception("if set this value , first column will not unique.");
			}
			if ($value[$keys[0]] === $key ) {
				if( array_key_exists($col, $value) ) {
					$value[$col] = $string;
					$exists = 1;
				}
			}
			$value = implode($this->separator,$value);
			$str[] = $value . "\n";
		}
		$lastvalue = array_pop($str);
		//改行を削除
		$lastvalue = str_replace(array("\n"), '', $lastvalue);
		array_push($str, $lastvalue);

		if (!$exists) {
			return false;
		}
		$keys_str = implode($this->separator,$keys) . "\n";
		array_unshift($str,$keys_str);
		file_put_contents($this->file, $str);
		return true;
	}

	/**
	 * delCsv.
	 * csvファイルの1番目のカラムの値をキーとして特定行を削除する.
	 *
	 * @param string $key 検索する1番目のカラムの値。
	 * @return boolean 削除が実行されればtrue
	 */
	public function delCsv($key)
	{
		$data = $this->getCsv();
		$keys = array_keys($data[0]);
		foreach($data as $k => $value) {
			if ($value[$keys[0]] === $key ) {
				$exists = 1;
				continue;
			}
			$value = implode($this->separator,$value);
			$str[] = "\n" . $value;
		}
		if (!$exists) {
			return false;
		}
		$keys_str = implode($this->separator,$keys);
		if(!isset($keys_str)){
			$keys_str = getCsvHeader(1);
		}
		array_unshift($str,$keys_str);
		if(!isset($str)){
			$str = $keys_str;
		}
		file_put_contents($this->file, $str);
		return true;
	}

	/**
	 * addCsv.
	 * csvファイルの最終行に値を追加.
	 * paramsのarray要素が少ない場合はheaderと同じ要素数までコンマで埋める.
	 *
	 * @param array $params CSVファイルの要素。
	 * @return void
	 */
	public function addCsv($params)
	{
		// ファイルロックをかけておく
		$file_dat=fopen($this->file,"a+");
		flock($file_dat, LOCK_EX);
		
		if(is_array($params)) {
			if($this->is_hash($params)) {
				throw new Exception("argument is associative array. the argument must array. ");
			}
		}else{
			throw new Exception("argument is not array.");
		}
		$csvlist = $this->getCsvList($this->file);
		$header = $this->parseString($csvlist[0]);
		if (count($params) > count($header)) {
			throw new Exception("array number is longer than header.");
		}elseif (count($params) < count($header)) {
			//挿入しようとしている配列がヘッダーよりも少ない場合は空要素で埋める
			$num = count($header) - count($params);
			for ($i = 0; $i < $num; $i++) {
				$params[] = '';
			}
		}
		if(!$this->checkUnique($params[0])){ throw new Exception("if set this value , first column will not unique."); }
		$str = implode($this->separator,$params);
		$str = "\n" . $str;
		fputs($file_dat, $str);
		flock($file_dat, LOCK_UN);
		chmod($this->file,0666);
	}

}


