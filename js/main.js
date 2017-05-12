function url(path) {
  var len = path.length;
  if (path !== '' && path.substr(len - 5) != '.html' && path.substr(len - 1) != '/') {
    path += '/';
  }
  return basePath + path;
}
function getInputElement(name, value){
	return '<input name="' + name + '" type="text" value="' + value + '"/>';
}

function split( val ) {
    return val.split( /,\s*/ );
}
function extractLast( term ) {
    return split( term ).pop();
}

function removeContentText(selector){
	selector.contents().filter(function(){
		return this.nodeType = 3;
	}).remove();
}

function view2Edit(selectorName, pName, pContainer){
	var oldValue = $(pName + '.' + selectorName, pContainer).text();
	removeContentText($(pName +'.' + selectorName, pContainer));
	$(pName +'.' + selectorName, pContainer).append(getInputElement(selectorName, oldValue));
}

function selectView2Edit(selectorName, pName, pContainer){
	var oldValue = $(pName + '.' + selectorName, pContainer).text();
	removeContentText($(pName +'.' + selectorName, pContainer));
	$(pName +'.' + selectorName, pContainer).append(getSelectorElement(selectorName, oldValue));
}

function getNewValue(editName, pName, pContainer, editType){
	var newValue = '';
	if(editType == 'input'){
		newValue = $(pName + ' input[name='+ editName +']', pContainer).val();
	}else if(editType == 'select'){
		newValue = $(pName + ' select.'+editName).val();
	}
	return newValue;
}

function edit2View(selectorName, newValue, pName, pContainer, editType){
	
	//remove inputs.
	if(editType == 'input'){
		$(pName + ' input[name='+ selectorName +']', pContainer).remove();
	}else{
		select = $(pName + ' select.'+selectorName, pContainer);
		newValue = $('option:selected', select).text();
		$(pName + ' select.'+selectorName, pContainer).remove();
	}
	$(pName + '.' + selectorName, pContainer).text(newValue);
}


function autoRemind(selectorName, parent, data){
	//for autocomplete for colors and sizes.
	$(selectorName, parent)
      // don't navigate away from the field on tab when selecting an item
      .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
            $( this ).data( "ui-autocomplete" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        minLength: 0,
        source: function( request, response ) {
          // delegate back to autocomplete, but extract the last term
          response( $.ui.autocomplete.filter(
        	data, extractLast( request.term ) ) );
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui) {
          var terms = split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        }
	});
}