<?
class form_element_array extends form_element {
  public $changed_count;

  function __construct($id, $def, $options, $parent) {
    parent::__construct($id, $def, $options);
    $this->changed_count=false;

    $this->build_form();
  }

  function is_complete() {
    if($this->changed_count)
      return false;

    foreach($this->elements as $k=>$element) {
      if(!$element->is_complete())
	return false;
    }

    return true;
  }

  function errors(&$errors) {
    foreach($this->elements as $k=>$element) {
      $element->errors(&$errors);
    }
  }

  function get_data() {
    $data=array();
    foreach($this->elements as $k=>$element) {
      $data[$k]=$element->get_data();
    }

    return $data;
  }

  function set_data($data) {
    $this->data=$data;
    $this->build_form();

    foreach($this->elements as $k=>$element) {
      if(isset($data[$k]))
	$element->set_data($data[$k]);
      else
	$element->set_data(null);
    }

    unset($this->data);
  }

  function set_request_data($data) {
    $this->data=$data;
    if($this->data['__new']) {
      unset($this->data['__new']);
      $this->data[]=null;
      $this->changed_count=true;
    }
    if(isset($this->data['__remove'])) {
      $remove=$this->data['__remove'];
      unset($this->data['__remove']);
    }

    $this->build_form();

    foreach($this->elements as $k=>$element) {
      if(isset($data[$k]))
	$element->set_data($data[$k]);
      else
	$element->set_data(null);
    }

    if(isset($remove)) {
      foreach($remove as $k=>$dummy) {
	unset($this->data[$k]);
	unset($this->elements[$k]);
      }
      $this->changed_count=true;
    }

    unset($this->data);
  }

  function set_orig_data($data) {
    $this->orig_data=$data;

    $element_class="form_element_{$this->def['def']['type']}";
    $element_def=$this->def['def'];

    foreach($data as $k=>$v) {
      if(isset($this->elements[$k])) {
	$this->elements[$k]->set_orig_data($v);
      }
    }
  }

  function get_orig_data($data) {
    return $this->orig_data;
  }

  function build_form() {
    $this->elements=array();

    $data=array_fill(0, $this->def['count']['default'], null);
    if(isset($this->data))
      $data=$this->data;

    $element_class="form_element_{$this->def['def']['type']}";
    $element_def=$this->def['def'];
    foreach($data as $k=>$v) {
      $element_id="{$this->id}_{$k}";
      $element_options=$this->options;
      $element_options['var_name']="{$this->options['var_name']}[{$k}]";
      $element_def['_name']="#".(sizeof($this->elements)+1);

      if(class_exists($element_class)) {
	$this->elements[$k]=new $element_class($element_id, $element_def, $element_options, $this);
      }
    }
  }

  function show_element() {
    $ret="";

    foreach($this->elements as $k=>$element) {
      $ret.="<div id='{$this->id}-$k'>\n";
      $ret.=$element->show_element();
      $ret.="<input type='submit' name='{$this->options['var_name']}[__remove][{$k}]' value='X'>";
      $ret.="</div>\n";
    }
    $ret.="<input type='submit' name='{$this->options['var_name']}[__new]' value='Element hinzufügen'>\n";

    return $ret;
  }
}
