<?php

/**
* Created By : Jalvin Vohra
* Date : 13-08-2015
* Company : E2Logy
**/
	
namespace yii\data;

use yii\db\Query;
use yii\web\HttpException;

/**
* SpDataProvider extends SqlDataProvider and its basic methods 
* 
* This data provider is used for processing data with stored procedures.
* 
* $spDataProvider = new SpDataProvider([
*			'spName' => 'sp_get_posts',
*			//'totalCount'=>5,
*			'params'=>$this->attributes,
*			 'sort'=> [
*				'attributes'=>[
*					'id'=>[
*						'asc'=>['id' => SORT_ASC],
*						'desc'=>['id' =>SORT_DESC],
*						'default'=>SORT_DESC,
*						'label'=>'ID'
*					],
*					'title'=>[
*						'asc'=>['title' => SORT_ASC],
*						'desc'=>['title' =>SORT_DESC],
*						'default'=>SORT_DESC,
*						'label'=>'Title'
*					],			
*				],
*				'defaultOrder'=>[
*					'id' => SORT_DESC
*				]
*			 ],  
*			 //'sort' => $sort,
*			'pagination' => [
*				  'pageSize' => 2,
*			 ],
*			 
*		]);
**/

class SpDataProvider extends SqlDataProvider 
{
	/*
	*	@var string procedure name to call
	*/
	public $spName; //Name of the stored procedure to call
	
	/*
	*	@var integer total records 
	*/
	public $total = 0; //Extra parameters to be added to called procedure
	
	/*
	* @var string action
	*/
	public $action = 'search';
	
	/*
	* @inheritdoc
	*/
	public function init() {		
		$this->sql = $this->spName;
		parent::init();		

		if($this->action != 'search') {
			$this->storeData();
		}
	}
	
	/*
	* Method is used to create sql statement
	*/
	private function createSql() {	
		
		$limit = 0;
		$offset = 0;
		$total = false;
		
		$sql = 'CALL ' . $this->spName . "(";
		
		// Get list of parameters for perticular stored procedure which is called.
		$query = new Query;
		$query->select('PARAMETER_NAME AS Name, DATA_TYPE AS Type, CHARACTER_MAXIMUM_LENGTH AS Length');
		$query->from('information_schema.parameters');
		$query->where("SPECIFIC_NAME = '{$this->spName}'");
		
		$result = $query->all();			
		
		if(count($result) > 0 && (count($this->params) < 0)) {
			throw new HttpException(400, 'Required Parameters are missing', '420');			
		}
		
		if(!empty($this->params)) {			
			// Params to skip for sort, pageOffset, pageIndex which will be added latter
			$arrSkip = ['sort','pageOffset', 'pageIndex'];
			
			foreach($result AS $key => $value) {
				
				if(in_array($value['Name'], $arrSkip)) continue;
				
				if(!array_key_exists($value['Name'], $this->params)) {
					throw new HttpException(400, 'Required parameter missing ' . $value['Name'], '420');
					break;
				}				
				else if(array_key_exists($value['Name'], $this->params)) {
					if(!empty($value['Length']) && $value['Length'] < strlen($this->params[$value['Name']])) {
						throw new HttpException(400, 'Length exceeded for ' . $value['Name'], '421');
						break;
					}
					
					switch(strtolower($value['Type'])) {
						case 'int':
							$sql .= (empty($this->params[$value['Name']]) ? 0 :  $this->params[$value['Name']]) . ',';
							break;
						case 'text':
						case 'varchar':
						case 'char':
							$sql .= "'{$this->params[$value['Name']]}',";
							break;
						default :
							$sql .= "0,";
							break;
					}
				}
			}		
		}
		
		if($this->action == 'search') {
			$pagination = $this->getPagination();
			$sort = $this->getSort();	
			
			if(!empty($sort) && $sort !== false) {
				
				$orders = $sort->getOrders();
				$arrOrd = array(3 => 'DESC', 4 => 'ASC');
				$str = "";			
				if(count($orders) > 0) {
					foreach($orders AS $key => $value) {
						$str .= "'{$key} {$arrOrd[$value]},'";				
					} 
					$str = rtrim($str, ",'");
					$str .= "'";
				}
				else {
					$str .= "''";
				}
				$sql .= "{$str},";			
				//$sql .= ",''";
				
			}
			
			if(!empty($pagination) && $pagination !== false) {		
				//$pagination->totalCount = $this->getTotalCount();
				$sql_tmp = $sql . "0, 0)";
				$cntTotal = $this->db->createCommand($sql_tmp)->queryAll();			
				$this->totalCount = count($cntTotal);					
				$pagination->totalCount = $this->getTotalCount();
				
				$limit = $pagination->getLimit();
				$offset = $pagination->getOffset();			
				$total = true;
				
				$sql .= "{$offset},{$limit}";
			}				
		}
		
		$sql = rtrim($sql, ',');
		$sql .= ")";
		
		echo $this->sql = $sql;
	}
	
	/*
	* @inheritdoc
	*/
	protected function prepareModels() {					
		$this->createSql();				
		//echo $this->sql;
		return $this->db->createCommand($this->sql)->queryAll();
		//return $models;
	}
	
	/*
	* @inheritdoc
	*/
	protected function prepareKeys($models) {
		$keys = [];
		if($this->key != null) {
			foreach($models as $model) {
				if(is_string($this->key)) {
					$keys[] = $model[$this->key];
				}
				else {
					$keys[] = call_user_func($this->key, $model);
				}
			}
			
			return $keys;
		}
		else {
			return array_keys($models);
		}
	}
	
	 /**
     * @inheritdoc
     */
    protected function prepareTotalCount() {
		return 0;
	}
	
	
	protected function storeData() {
		$this->createSql();
		return $this->db->createCommand($this->sql)->execute();
	}
	
	
}