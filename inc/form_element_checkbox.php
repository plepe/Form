<?
class form_element_checkbox extends form_element {
  function get_changed_values() {
    if(!isset($this->orig_data)&&!isset($this->data))
      return array();

    if(!isset($this->orig_data))
      return $this->data;

    // make sure $data is an array
    $data=$this->data;
    if(!isset($data))
      $data=array();

    $ret=array();
    foreach($this->orig_data as $v) {
      if(!in_array($v, $data))
	$ret[]=$v;
    }

    foreach($data as $v) {
      if(!in_array($v, $this->orig_data))
	$ret[]=$v;
    }

    return $ret;
  }

  function show_element($document) {
    $div=parent::show_element($document);
    $changed_values=$this->get_changed_values();

    foreach($this->def['values'] as $k=>$v) {
      $id="{$this->id}-$k";

      // check for changes
      $class="form_orig";
      if(in_array($k, $changed_values))
	$class="form_modified";

      $span=$document->createElement("span");
      $span->setAttribute("class", $class);
      $div->appendChild($span);

      $input=$document->createElement("input");
      $input->setAttribute("type", "checkbox");
      $input->setAttribute("id", $id);
      $input->setAttribute("name", "{$this->options['var_name']}[]");
      $input->setAttribute("value", $k);
      if(in_array($k, $this->data))
	$input->setAttribute("checked", "checked");
      $span->appendChild($input);
      
      $label=$document->createElement("label");
      $label->setAttribute("for", $id);
      $text=$document->createTextNode($v);
      $label->appendChild($text);
      $span->appendChild($label);

      $br=$document->createElement("br");
      $div->appendChild($br);
    }

    return $div;
  }
}
