<?php defined('SYSPATH') or die('No direct script access.');
error_reporting(E_ALL);
/**
 * Class WebformField
 *
 * @package    Kohana/Webform
 * @author     Timophey Lanevich
 * @version    0.0.0.1
 */

class WebformField extends Form{
	protected $name = NULL;
	protected $title = NULL;
	
	protected $_data;
	protected $_default = [
		'name'=>NULL,	// input name
		'type'=>'varchar',
		'value'=>NULL, 
		'data_type'=>'string',
		'required'=>false,
		'title'=>NULL,
		'maxlength'=>0,
		'pattern'=>NULL,
		'inputtype'=>'text',
		'hidden'=>NULL,
		'readonly'=>NULL,
		'valiadation'=>[],
		'errors'=>NULL,
		'class'=>"",
		'size'=>"",
		];
	public function __construct($options){
		$op = array_merge($this->_default,$options);
		$this->_data = array_merge($this->_default,$op);
	}
	public static function factory($options){
		return new WebformField($options);
		}
	/*private function type_view($type){
		
		}*/
	public function __isset($key){
		return isset($this->_data[$key]);
		}
	public function __get($key){
		return $this->_data[$key];
		/*switch($key){
			case'html':
				return $this->__toString();
				break;
			default:
				return $this->_op[$key];
				break;
			}*/
		}
	/**
	* Some magic
	* @return string
	*/
	public function __toString()
	{
		return $this->render();
	}
	public function render(){
		// make field from column
		$fhp; // form_helper_params
		$value = $this->_data['value'];
		$form_helper = 'input';
		$attributes = Arr::extract($this->_data,['id','required','maxlength','title','pattern','readonly','class','size','placeholder','multiple','accept','options']);
		if(!in_array($this->_data['type'],['radio'])) unset($attributes['options']);
		if($attributes['required']!==true) unset($attributes['required']);
		if($attributes['readonly']!==true) unset($attributes['readonly']);
		if(!$attributes['maxlength'])  unset($attributes['maxlength']);
		if(!$attributes['size']) unset($attributes['size']);
		$fhp = [$this->_data['name'],$value,$attributes];
		//$name = $this->_data['name'];
		//open,close,input,button,checkbox,file,hidden,image,label,password,radio,select,submit,textarea,
		//$attr
		switch($this->_data['type']){
			case'form':
				$form_helper = $this->_data['data_type']; $fhp=[];
				break;
			case'select':
				//unset
				$form_helper='select'; $fhp=[$this->_data['name'],$this->_data['options'],$value,$attributes];
				break;
			case'checkbox':
				$fhp = [$this->_data['name'],1,!!($value),$attributes];
				$form_helper='checkbox';
				break;
			default:
				//if($this->_data['type'] == 'radio') $fhp[2]['options'] = Arr::get($this->_data,'options');
				if(in_array($this->_data['type'],['file','select','checkbox','radio','hidden'])) $this->_data['inputtype'] = $this->_data['type'];
				//if()
				switch($this->_data['data_type']){
					case'int':
						$fhp[2]['type']='number';
						break;
					case'text':
						$form_helper='textarea';
						break;
					default:
						
						break;
					}
				
				break;
			}
			
		if($form_helper=='input') $fhp[2]['type']=$this->_data['inputtype'];
		/*switch($this->_data['data_type']){
			case'form':
				
				
			}*/
		return call_user_func_array([$this,$form_helper],$fhp);
		//->($this->_data['name']);
		//return json_encode($this->_datations);
		//return 
		}
}