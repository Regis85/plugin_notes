
function cache_montre_aide() {
  cache_montre_aide_coef();
  cache_montre_aide_display();
  cache_montre_aide_parents();
  cache_montre_aide_calcul();
}

function cache_montre_aide_mat() {
  cache_montre_aide_coef();
  cache_montre_aide_calcul();
  cache_montre_aide_mode();
}

function cache_montre_aide_coef() {
  cache_montre(document.getElementById('coef_long1'));
  cache_montre(document.getElementById('coef_long2'));
}

function cache_montre_aide_display() {
  cache_montre(document.getElementById('aide_display_date'));
}

function cache_montre_aide_parents() {
  cache_montre(document.getElementById('aide_visible_parent'));
}

function cache_montre_aide_calcul() {
  cache_montre(document.getElementById('aide_calcul_note'));
}

function cache_montre_aide_mode() {
  cache_montre(document.getElementById('mode_calcul_2'));
  cache_montre(document.getElementById('mode_calcul_1'));
}

function cache_montre (e) {
  if (e.className=='invisible') {
    e.className='';
  } else {
    e.className='invisible';
  }
}

function cache_photos() {
  var a = document.getElementsByClassName("photos");
  for (i=0; i<a.length; i++){
    a[i].style.display='none';
  }
}

function affiche_photo(e) {
  document.getElementById(e).style.display='block';
}

function change_photo(e) {
  cache_photos();
  e="img_"+e;
  affiche_photo(e);
}

function change_stats() {
    cache_toutes_repartition()
  if (document.getElementById('stats').className=='stats'){
    cache_stats();
  } else {
    affiche_stats();
  }
}

function affiche_stats() {
  document.getElementById('stats').className='stats';
}

function cache_stats() {
  document.getElementById('stats').className='invisible';
}

function change_repartition(e) {
  cache_stats()
  if (document.getElementById('repart_'+e).className=='repartition'){
    cache_repartition(e);
  } else {
    cache_toutes_repartition()
    affiche_repartition(e);
  }
}

function affiche_repartition(e) {
  document.getElementById('repart_'+e).className='repartition';
}

function cache_repartition(e) {
  document.getElementById('repart_'+e).className='invisible';
}

function cache_toutes_repartition() {
  var a = document.getElementsByClassName("repartition");
  for (i=0; i<a.length; i++){
    a[i].className='invisible';
  }
}

function verifie_note(num_id,noteMax) {
  document.getElementById('n'+num_id).value=document.getElementById('n'+num_id).value.toLowerCase();
  if(document.getElementById('n'+num_id).value=='a'){
    document.getElementById('n'+num_id).value='abs';
  } else if(document.getElementById('n'+num_id).value=='d'){
    document.getElementById('n'+num_id).value='disp';
  } else if(document.getElementById('n'+num_id).value=='n'){
    document.getElementById('n'+num_id).value='-';
  }
  note=document.getElementById('n'+num_id).value;
  if((note!='-')&&(note!='disp')&&(note!='abs')&&(note!='')){
    if(((note.search(/^[0-9.]+$/)!=-1)&&(note.lastIndexOf('.')==note.indexOf('.',0)))||
  ((note.search(/^[0-9,]+$/)!=-1)&&(note.lastIndexOf(',')==note.indexOf(',',0)))){
      if((note>noteMax)||(note<0)){
	alert (note+' : La note est hors référentiel (note maxi '+noteMax+')');
	document.getElementById('td_'+num_id).className="fond_rouge";
      }
      else{
	document.getElementById('td_'+num_id).className="entre_note";
      }
    }
    else{
      alert (note+' : Caractère interdit dans le champ note');
      document.getElementById('td_'+num_id).className="fond_rouge";
    }
  }
  else{
    document.getElementById('td_'+num_id).className="entre_note";
  }
  
}

function cache_commentaires() {
  var a = document.getElementsByClassName("entre_observ");
  for (i=0; i<a.length; i++){
    if (a[i].style.display=='none') {
      a[i].style.display='';
    } else {
      a[i].style.display='none';
    } 
  }
}

function ajout_btn_comment(){
  lieu_ajout=document.getElementById("nom_prenom");
  texte="Commentaires";
  nouveauSaut=document.createElement("br");
  lieu_ajout.appendChild(nouveauSaut);
  
  nouveauBtn=document.createElement("input");
  
  attr_nom=document.createAttribute("type");
  attr_nom.nodeValue="button";
  nouveauBtn.setAttributeNode(attr_nom);
  
  attr_nom=document.createAttribute("value");
  attr_nom.nodeValue=texte;
  nouveauBtn.setAttributeNode(attr_nom);
  
  attr_nom=document.createAttribute("name");
  attr_nom.nodeValue="cacheComms";
  nouveauBtn.setAttributeNode(attr_nom);
    
  attr_value=document.createAttribute("title");
  attr_value.nodeValue="Cacher/Afficher les commentaires";
  nouveauBtn.setAttributeNode(attr_value);
    
  attr_value=document.createAttribute("onclick");
  attr_value.nodeValue="cache_commentaires();return FALSE;";
  nouveauBtn.setAttributeNode(attr_value);
  
  lieu_ajout.appendChild(nouveauBtn);
}

function disableAutocomplete(elementId) {
  var e = document.getElementById(elementId);
  if(e != null) {
    e.setAttribute("autocomplete", "off");
  }
}