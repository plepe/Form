form_element_radio.inherits_from(form_element);
function form_element_radio() {
}

form_element_radio.prototype.init=function(id, def, options, form_parent) {
  this.parent.init.call(this, id, def, options, form_parent);

  this.data=null;
}

form_element_radio.prototype.change_value=function(value) {
  var dom=this.dom_values[value];
  var on=dom.checked;
  var orig_data=this.get_orig_data();

  for(var k in this.dom_values) {
    if(orig_data==value)
      this.dom_values[k].parentNode.className="form_orig";
    else if((k==orig_data)||(k==value))
      this.dom_values[k].parentNode.className="form_modified";
    else
      this.dom_values[k].parentNode.className="form_orig";
  }
}

form_element_radio.prototype.connect=function(dom_parent) {
  this.parent.connect.call(this, dom_parent);

  var current=this.dom_parent.firstChild;
  this.dom_values={};
  while(current) {
    if(current.nodeName=="SPAN") {
      var dom=current.firstChild;
      this.dom_values[dom.value]=dom;

      dom.onchange=this.change_value.bind(this, dom.value);
    }

    current=current.nextSibling;
  }
}

form_element_radio.prototype.get_data=function() {
  if(!this.dom_values)
    return this.data;

  for(var i in this.dom_values) {
    this.data=null;

    if(this.dom_values[i].checked)
      return this.data=i;
  }

  return null;
}

form_element_radio.prototype.set_data=function(data) {
  this.parent.set_data.call(this, data);

  if(!this.dom_values)
    return;

  for(var k in this.dom_values) {
    if(this.data==k)
      this.dom_values[k].checked=true;
  }
}

form_element_radio.prototype.show_element=function() {
  var div=this.parent.show_element.call(this);
  this.get_data();
  this.dom_values={};

  for(var k in this.def.values) {
    var id=this.id+"-"+k;

    var cls="form_orig";
    // TODO: check for changed data

    var span=document.createElement("span");
    span.className=cls;
    div.appendChild(span);

    var input=document.createElement("input");
    input.type="radio";
    input.id=id;
    input.name=this.options.var_name;
    input.value=k;
    // TODO: indexOf not supported in IE8 and earlier
    if(this.data==k)
      input.checked=true;
    span.appendChild(input);
    this.dom_values[k]=input;

    input.onchange=this.change_value.bind(this, k);

    var label=document.createElement("label");
    label.setAttribute("for", id);
    var text=document.createTextNode(this.def.values[k]);
    label.appendChild(text);
    span.appendChild(label);

    var br=document.createElement("br");
    div.appendChild(br);
  }

  return div;
}
