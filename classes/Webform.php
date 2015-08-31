<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class Webform
 *
 * @package    Kohana/Webform
 * @author     Timophey Lanevich
 * @version    0.0.0.1
 */

abstract class Webform implements Iterator{
	
	/**
	 * Form fields in objects
	 * @var array
	 */
	protected $__fields = [];
	/**
	 * Form fields options
	 * @var array
	 */
	protected $_fields = null;
	protected $_config = null;
	protected $_data;
	private $_display = [];
	protected $_validation;
	protected $_validation_errors = [];

	private $_default = array(
			'fields'=>[], // look at WebformField::_default
			'options'=>[
				'model'=>null, // Model_ name
				'display_fields'=>[], // fields display order, keys of 'fields', will have all fields by default
				'except_fields'=>[],	// fields display exception, keys of 'fields'
//				'valiadation'=>[], // valiadation class rules
				'valid_messages_file'=>null, // messages file for valiadation class
				]
			);

	public function __construct(array $data=null,$where=null){
		$this->_data = $data;
		if($this->_config == null) $this->_config = Arr::merge($this->_default,$this->meta());
		$this->_fields = ($this->_fields == null) ? $this->_config['fields'] : Arr::merge($this->_fields,$this->_config['fields']);
		$this->_display = &$this->_config['options']['display_fields'] or $this->_display = [];
		// fill _display if it is empty
		if(empty($this->_display)) foreach(array_keys($this->_fields) as $name) if(!in_array($name,$this->_config['options']['except_fields'])) array_push($this->_display,$name);
		// make fields objects
		foreach($this->_fields as $name=>&$_field){
			if(!Arr::get($_field,'name')) $_field['name'] = $name;
			$_field['errors'] = &$this->_validation_errors[$name];
			if(in_array($name,$this->_display)){
				if($data !== null) $_field['value'] = Arr::get($data,$_field['name']);
				$this->__fields[$name]=WebformField::factory($_field); // ::__get
				}
			}
		}
	/* Factory
	 * */
	public static function factory($classname,array $data=null,$id=null){
		$class = "Webform_" . $classname;
		return new $class($data, $id);
		}
	public function config(){
		return $this->_config;
		}
	/* Use Validation class
	 * */
	public function validation(){
		if(!$this->_validation) $this->valiadate_init();
		// return object
		return $this->_validation;
		}
	public function check(){
		if(!$this->_validation) $this->valiadate_init();
		$passed = $this->_validation->check();
		if(!$passed){
			$errors = $this->_validation->errors(Arr::get($this->_config['options'],'valid_messages_file'));
			foreach($errors as $key=>$error){
				$this->__fields[$key]->errors = $error;
				}
			//$this->_validation_errors = 
			}
		return $passed;
		}
	public function errors(){
		return $this->_validation_errors;
		}
	private function valiadate_init(){
		$this->_validation = Validation::factory($this->_data);
		// add rules based on _fields
		// rules: /guide/kohana/security/validation#provided-rules
		foreach($this->_fields as $key=>$field) if(in_array($key,$this->_config['options']['display_fields']) && !in_array($key,$this->_config['options']['except_fields'])){
			if($rules = Arr::get($field,'valiadation'))
				foreach($rules as $rule){ 
					if(!is_array($rule)) $rule = [$rule];
					$this->_validation->rule($field['name'], $rule[0], Arr::get($rule,1)); }
			if(Arr::get($field,'required')) $this->_validation->rule($field['name'], 'not_empty');
			}
		}
	
	/**
	* Some magic
	* @return string
	*/
	public function __toString()
	{
		return $this->render();
	}
	public function __isset($key){
		return isset($this->_fields[$key]);
		}
	
	public function __get($key){
		$_field = $this->_fields[$key];
		$_field['errors'] = Arr::get($this->_validation_errors,$key,false);
		return $this->__fields[$key];//WebformField::factory($_field);
		}
	public function __set($key, $value){
		$this->_fields[$key] = $value;
		}
	/* iterator functions */
	public function current(){
		return $this->__get(current($this->_display));
		}
	public function rewind(){
		reset($this->_display);
		}
	public function key(){
		return key($this->_display);
		}
	public function next(){
		return next($this->_display);
		}
	public function valid(){
		$key = key($this->_display);
		return ($key !== NULL && $key !== FALSE);
		}
	/**/
	public function meta()
	{
		return array();
	}

	public function render(){
		$render = WebformField::factory(['type'=>'form','data_type'=>'open'])->__toString();
		$render .= implode($this);/*foreach($this->__fields as $field){$render .= $field;}*/
		$render .= WebformField::factory(['type'=>'form','data_type'=>'close'])->__toString();
		//return gettype($render);
		return $render;
		
		}
	}
