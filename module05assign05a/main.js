$(document).ready(function() {
  $("select").imagepicker();
  document.getElementById("p1DeckDisplay").style.visibility = "hidden";
  document.getElementById("p2DeckDisplay").style.visibility = "hidden";
});

function hideDeck(which) {
  which = which == 1 ? "p1DeckDisplay" : "p2DeckDisplay";
  var cur = document.getElementById(which).style.visibility;
  console.log(cur);
  if(cur == undefined || cur != "hidden")
    document.getElementById(which).style.visibility = "hidden";
  else
    document.getElementById(which).style.visibility = "visible";
    
}

