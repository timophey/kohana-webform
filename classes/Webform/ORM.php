<?php defined('SYSPATH') or die('No direct script access.');

abstract class Webform_ORM extends Webform{
	
	protected $_model;
	protected $_model_columns;
	
	public function __construct(array $data=null,$id=null){
		
		if($this->_config == null) $this->_config = $this->meta();
		$this->_model = ORM::factory($this->_config['options']['model'],$id);
		$this->_model_columns = $this->_model->table_columns();
		$_primary_key = $this->_model->primary_key();
		
		foreach($this->_model_columns as $column){
			$column = Arr::extract($column,['type','data_type','column_name','is_nullable','character_maximum_length','comment']);
			$name = $column['column_name'];
			$title = $column['comment'] or $title = $column['column_name'];
			$field=[
				'name'=>$name,
				'type'=>$column['type'],
				'value'=>NULL,
				'data_type'=>$column['data_type'],
				'required'=>($column['is_nullable']!==true),//Arr::get($column,'is_nullable',0),//,(bool)$column['is_nullable'],
				'title'=>$title,//.'('.$column['is_nullable'].')',
				'maxlength'=>$column['character_maximum_length'],
				'readonly'=>($name == $_primary_key)?true:null
				//'s'=>json_encode($column)
				];
			//echo"\n column $name : <pre>";var_dump($column);echo"</pre>";
			$this->_fields[$name]=$field;
			}
			if($id) $data = $this->_model->as_array();
		parent::__construct($data,$id);
		}
	
	}