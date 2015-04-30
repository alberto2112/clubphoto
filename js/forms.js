  function count_selected_checkboxes() {
   // JavaScript & jQuery Course - http://coursesweb.net/javascript/
    var selchbox = ""; //[];        // array that will store the value of selected checkboxes

    // gets all the input tags in frm, and their number
    var inpfields = document.getElementsByTagName('input');
    var nr_inpfields = inpfields.length;

    // traverse the inpfields elements, and adds the value of selected (checked) checkbox in selchbox
    for(var i=0; i<nr_inpfields; i++) {
      if(inpfields[i].type == 'checkbox' && inpfields[i].checked == true){
        //selchbox.push(inpfields[i].value);
        selchbox += inpfields[i].value+";";
      }
    }
  }