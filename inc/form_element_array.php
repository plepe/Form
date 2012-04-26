<?
class form_element_array extends form_element {
  public $changed_count;

  function __construct($id, $def, $options, $parent) {
    parent::__construct($id, $def, $options, $parent);
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
    foreach($this->elements as $k=>$element) {
      if(!isset($data[$k])) {
	// TODO: remember removed elements?!
	unset($this->elements[$k]);
	//->set_data(null);
      }
    }

    foreach($data as $k=>$v) {
      if(!isset($this->elements[$k]))
	$this->create_element($k);

      $this->elements[$k]->set_data($v);
    }
  }

  function set_request_data($data) {
    $this->data=$data;
    if($this->data['__new']) {
      unset($this->data['__new']);
      $this->data[]=null;
      $this->changed_count=true;
    }
    $remove=array();
    if(isset($this->data['__remove'])) {
      $remove=$this->data['__remove'];
      unset($this->data['__remove']);
      $this->changed_count=true;
    }

    foreach($this->elements as $k=>$element) {
      if(isset($data[$k]))
	$element->set_data($data[$k]);
      else {
	// TODO: remember removed elements?!
	unset($this->elements[$k]);
	//->set_data(null);
      }
    }

    foreach($this->data as $k=>$v) {
      if(!isset($this->elements[$k])) {
	$this->create_element($k);
	$this->elements[$k]->set_request_data($v);
      }
    }

    foreach($remove as $k=>$dummy) {
      unset($this->data[$k]);
      unset($this->elements[$k]);
    }

    unset($this->data);
  }

  function set_orig_data($data) {
    if((!isset($data))||(!is_array($data)))
      $data=array();

    foreach($data as $k=>$v) {
      if(!isset($this->elements[$k]))
	$this->create_element($k);

      $this->elements[$k]->set_orig_data($v);
    }
  }

  function get_orig_data() {
    $data=array();
    foreach($this->elements as $k=>$element)
      $data[$k]=$element->get_orig_data();

    return $data;
  }

  function create_element($k) {
    $element_class="form_element_{$this->def['def']['type']}";
    $element_def=$this->def['def'];

    $element_id="{$this->id}_{$k}";
    $element_options=$this->options;
    $element_options['var_name']="{$this->options['var_name']}[{$k}]";
    $element_def['_name']="#".(sizeof($this->elements)+1);

    if(class_exists($element_class)) {
      $this->elements[$k]=new $element_class($element_id, $element_def, $element_options, $this);
    }

    return $this->elements[$k];
  }

  function build_form() {
    $this->elements=array();

    $data=array_fill(0, $this->def['count']['default'], null);
    if(isset($this->data))
      $data=$this->data;

    foreach($data as $k=>$v) {
      $this->create_element($k);
    }
  }

  function show_element_part($k, $element, $document) {
    // wrapper #k
    $div=$document->createElement("div");
    $div->setAttribute("form_element_num", $k);
    $div->setAttribute("class", "form_element_array_part");

    // element #k
    $el_div=$document->createElement("div");
    $el_div->setAttribute("form_element_num", $k);
    $el_div->setAttribute("class", "form_element_array_part_element");
    $div->appendChild($el_div);

    $el_div->appendChild($element->show_element($document));

    // Actions #k
    $el_div=$document->createElement("div");
    $el_div->setAttribute("form_element_num", $k);
    $el_div->setAttribute("class", "form_element_array_part_element_actions");
    $div->appendChild($el_div);

    $input=$document->createElement("input");
    $input->setAttribute("type", "submit");
    $input->setAttribute("name", "{$this->options['var_name']}[__remove][{$k}]");
    $input->setAttribute("value", "X");
    $el_div->appendChild($input);

    return $div;
  }

  function show_element($document) {
    $div=parent::show_element($document);

    foreach($this->elements as $k=>$element) {
      $part_div=$this->show_element_part($k, $element, $document);
      $div->appendChild($part_div);
    }

    $el_div=$document->createElement("div");
    $el_div->setAttribute("class", "form_element_array_actions");
    $div->appendChild($el_div);

    $input=$document->createElement("input");
    $input->setAttribute("type", "submit");
    $input->setAttribute("name", "{$this->options['var_name']}[__new]");
    $input->setAttribute("value", "Element hinzufügen");
    $el_div->appendChild($input);

    return $div;
  }
}
