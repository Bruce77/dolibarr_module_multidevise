<?php

class TCurrency extends TObjetStd {
	
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'currency');
		parent::add_champs('code,name','type=chaine;index;');

		parent::start();
		parent::_init_vars();

		$this->setChild('TCurrencyRate', 'id_currency');
		
	}
	
	function loadByCode(&$db, $code) {
		
		return $this->loadBy($db, $code, 'code');
		
	}
	
	function addRate($rate, $id_entity, $date='') {
		if(empty($date))$date = date('Y-m-d h:i:s');
		foreach($this->TCurrencyRate as &$row) {
			if($row->get_date('dt_sync','Y-m-d') == $date && $row->id_entity == $id_entity) {
				$row->rate = $rate;
				return true;
			}
		}
		
		$k = $this->addChild($db, 'TCurrencyRate');
		
		$this->TCurrencyRate[$k]->rate = $rate;
		$this->TCurrencyRate[$k]->dt_sync = strtotime($date);
		$this->TCurrencyRate[$k]->id_entity = $id_entity;
		return $k;
	}
	
	static function getRate(&$db, $code, $date='') {
		
		if(empty($date))$date = date('Y-m-d');
		
		$db->Execute("SELECT rate 
		FROM ".MAIN_DB_PREFIX."currency_rate cr INNER JOIN ".MAIN_DB_PREFIX."currency c ON (cr.id_currency=c.rowid)
		WHERE c.code = '".$code."' AND cr.dt_sync>='".$date."'
		ORDER BY cr.dt_sync DESC
		LIMIT 1
		");
		
		$db->Get_line();
		
		return (double)$db->Get_field('rate');
		
	} 
	
	static function getPrice(&$db, $code, $price, $date='') {
		
		if(empty($date))$date = date('Y-m-d');
		
		$rate = TCurrency::getRate($db, $code, $date);
		
		return $price * $rate;
		
	}
	
	static function getChangeLost(&$db, $code, $price, $dateFrom, $date='') {
		
		if(empty($date))$date = date('Y-m-d');
		
		$rateFrom = TCurrency::getRate($db, $code, $dateFrom);
		$rate = TCurrency::getRate($db, $code, $date);
		
		return $price * ( $rateFrom - $rate );
		
	}
	
}

class TCurrencyRate extends TObjetStd {
	
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'currency_rate');
		
		parent::add_champs('rate','type=float;');
		parent::add_champs('id_currency, id_entity','type=entier;index;');
		parent::add_champs('dt_sync','type=date;index;');

		parent::start();
		parent::_init_vars();
		
	}
	
}
