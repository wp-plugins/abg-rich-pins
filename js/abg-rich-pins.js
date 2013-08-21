/*
 JavaScript for ABG Rich Pins
 Author: Antonio Borrero Granell
 Project URI: http://wordpress.org/plugins/abg-rich-pins/
*/
jQuery(document).ready( function($) {  
  var type = $("#abg_rp_pinType").val();
  var subform = "#abg_rp_" + type + "Form";
  $(subform).show();

  $("#abg_rp_pinType").change( function() {
    var newType = $("#abg_rp_pinType").val();
    var newSubform = "#abg_rp_" + newType + "Form";
    $(".pin_subform").hide(0);
    $(newSubform).show(1000);
  });
  $("#abg_rp_add_director").click( function() {
  	var numDirectors = $("#abg_rp_num_directors").val();
    if (numDirectors == 0){
      numDirectors++;
    }
	  var directorForm = "<div id=\"abg_rp_div_add_director" + numDirectors + "\"><label for=\"abg_rp_pinDirector" + numDirectors + "\">Enter a director </label><input type=\"text\" id=\"abg_rp_pinDirector" + numDirectors + "\" name=\"abg_rp_pinDirector" + numDirectors + "\" size=\"30\" /></div>";
    numDirectors++;
    $("#abg_rp_num_directors").val(numDirectors);
    $("#abg_rp_add_director").prev().before(directorForm);
    return;
  });
  $("#abg_rp_remove_director").click( function() {
    var numDirectors = $("#abg_rp_num_directors").val();
    if (numDirectors > 1){
      var directorId = "#abg_rp_div_add_director".concat(numDirectors - 1);
      numDirectors--;
      $("#abg_rp_num_directors").val(numDirectors);
      $(directorId).remove();
    }
    return;
  });  
  $("#abg_rp_add_actor").click( function() {
  	var numActors = $("#abg_rp_num_actors").val();
    if (numActors == 0){
      numActors++;
    }
	  var actorForm = "<div id=\"abg_rp_div_add_actor" + numActors + "\"><label for=\"abg_rp_pinActor" + numActors + "\">Enter an actor </label><input type=\"text\" id=\"abg_rp_pinDirector" + numActors + "\" name=\"abg_rp_pinActor" + numActors + "\" size=\"30\" /><br/>";
    numActors++;
    $("#abg_rp_num_actors").val(numActors);
    $("#abg_rp_add_actor").prev().before(actorForm);
    return;
  });
  $("#abg_rp_remove_actor").click( function() {
    var numActors = $("#abg_rp_num_actors").val();
    if (numActors > 1){
      var actorId = "#abg_rp_div_add_actor".concat(numActors - 1);
      numActors--;
      $("#abg_rp_num_actors").val(numActors);
      $(actorId).remove();
    }
    return;
  });  
  $("#abg_rp_add_ingredient").click( function() {
    var numIngredients = $("#abg_rp_num_ingredients").val();
    if (numIngredients == 0){
      numIngredients++;
    }
    var ingredientForm = "<div id=\"abg_rp_div_add_ingredient" + numIngredients + "\"><label for=\"abg_rp_pinIngredient" + numIngredients + "\">Enter an ingredient </label><input type=\"text\" id=\"abg_rp_pinIngredient" + numIngredients + "\" name=\"abg_rp_pinIngredient" + numIngredients + "\" size=\"30\" /></div>";
    numIngredients++;
    $("#abg_rp_num_ingredients").val(numIngredients);
    $("#abg_rp_add_ingredient").prev().before(ingredientForm);
    return;
  });
  $("#abg_rp_remove_ingredient").click( function() {
    var numIngredients = $("#abg_rp_num_ingredients").val();
    if (numIngredients > 1){
      var ingredientId = "#abg_rp_div_add_ingredient".concat(numIngredients - 1);
      numIngredients--;
      $("#abg_rp_num_ingredients").val(numIngredients);
      $(ingredientId).remove();
    }
    return;
  }); 
});