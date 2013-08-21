<?php
/*
Plugin Name: ABG Rich Pins
Plugin URI: http://wordpress.org/plugins/abg-rich-pins/
Description: Designed to help users to implement and customize Rich Pins easily.
Version: 1.1
Author: Antonio Borrero Granell
Author URI: http://es.linkedin.com/pub/antonio-borrero-granell/62/486/a99
License: GPL2
*/

function abg_rp_plugin_get_version() {
	if ( ! function_exists( 'get_plugins' ) )
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$plugin_file = basename( ( __FILE__ ) );
	return $plugin_folder[$plugin_file]['Version'];
}

/**
 * Styles & JavaScript 
 */

function abg_rp_add_styles_admin() {
	wp_register_style( 'abg-rp-style-admin', plugins_url('css/abg-rich-pins.css', __FILE__));
	wp_enqueue_style( 'abg-rp-style-admin' );
}

function abg_rp_add_styles() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'abg-rp-style', plugins_url('css/abg-rp-style.css', __FILE__) );
    wp_enqueue_style( 'abg-rp-style' );
}


function abg_rp_list_scripts() {
	wp_enqueue_script('abg-rp-script', plugins_url('js/abg-rich-pins.js', __FILE__), array( 'jquery' ) );
}
add_action( 'admin_head', 'abg_rp_add_styles_admin' );

add_action( 'wp_enqueue_scripts', 'abg_rp_add_styles' );

add_action('wp_print_scripts', 'abg_rp_list_scripts');

/**
 * Main functions
 */

add_action( 'add_meta_boxes', 'abg_rp_add_box' );

add_action( 'save_post', 'abg_rp_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function abg_rp_add_box() {
    $screens = array( 'post', 'page' );
    foreach ($screens as $screen) {
        add_meta_box(
            'abg_rp_sectionid',
            __( 'Rich Pins', 'abg_rp_textdomain' ),
            'abg_rp_inner_box',
            $screen
        );
    }
}

/* Prints the box content */
function abg_rp_inner_box( $post ) {

	wp_nonce_field( plugin_basename( __FILE__ ), 'abg_rp_noncename' );

	$pinType = get_post_meta( $post->ID, 'pinType', true );
	print('
		<p>
        	<label for="abg_rp_pinType">Enter the pin type</label>
	        <select name="abg_rp_pinType" id="abg_rp_pinType">
			<option value="movie" '.selected( $pinType, "movie", false).'>movie</option>
			<option value="product" '.selected( $pinType, "product",false).'>product</option>
			<option value="recipe"'.selected( $pinType, "recipe", false ).'>recipe</option>
			</select><br/>
			<small>Save the post, after that you will be able to edit the additional parameters</small>
        </p>
	');

	// Movie form
	$numDirectors = get_post_meta($post->ID, 'numDirectors', true);
	$numActors = get_post_meta($post->ID, 'numActors', true);

	if(empty($numDirectors)) {
		$numDirectors = 0;
	} 
	if(empty($numActors)){
		$numActors = 0;
	}
	$directors = array();
	for($i = 0; $i < $numDirectors; $i++){
		$directorName = 'pinDirector'.$i;
		$directors[] = get_post_meta($post->ID, $directorName, true);
	}
	$actors = array();
	for($i = 0; $i < $numActors; $i++){
		$actorName = 'pinActor'.$i;
		$actors[] = get_post_meta($post->ID, $actorName, true);
	}
	$pinName = get_post_meta( $post->ID, 'pinName', true );
	$pinDescription = get_post_meta( $post->ID, 'pinDescription', true );
	$pinRating = get_post_meta( $post->ID, 'pinRating', true );
	$pinRatingMax = get_post_meta( $post->ID, 'pinRatingMax', true );
	$pinContent = get_post_meta( $post->ID, 'pinContent', true );
	$pinDate = get_post_meta( $post->ID, 'pinDate', true);
    $pinForm = '
    	<div id="abg_rp_movieForm" class="pin_subform">
	        <p>
	        	<label for="abg_rp_pinMovieName">Enter the title of the movie *</label><br/>
	        	<input type="text" id="abg_rp_pinMovieName" name="abg_rp_pinMovieName" value="'.esc_attr($pinName).'" size="30" />
	        </p>
	        <p>
	        	<label for="abg_rp_pinMovieDescription">Enter the movie description</label><br/>
	        	<input type="text" id="abg_rp_pinMovieDescription" name="abg_rp_pinMovieDescription" value="'.esc_attr($pinDescription).'" size="30" />
	        </p>
	        <p>
	        	<div id="abg_rp_movie_directors">
	        	Directors:'
    ;
    $i = 0;
    if (empty($directors)){
    	$pinForm .= '
    		<div id="abg_rp_div_add_director0">
        	<label for="abg_rp_pinDirector0">Enter a director</label>
        	<input type="text" id="abg_rp_pinDirector0" name="abg_rp_pinDirector0" size="30" />
        	</div>
        ';		    
    } else {
	    foreach($directors as $pinDirector) {
	    	$pinForm .= '
	    		<div id="abg_rp_div_add_director'.$i.'">
	        	<label for="abg_rp_pinDirector'.$i.'">Enter a director</label>
	        	<input type="text" id="abg_rp_pinDirector'.$i.'" name="abg_rp_pinDirector'.$i.'" value="'.esc_attr($pinDirector).'" size="30" />
	        	</div>
	        ';
	    	$i += 1;
	    }		    	
    }

    $pinForm .= '
    			<br/>
    			<a id="abg_rp_add_director">Add director  </a>
    			<a id="abg_rp_remove_director">  Remove director</a>

        	</div>
        </p>
        <p>
        	<div id="abg_rp_movie_actors">
        	Actors:
    ';
    $i = 0;
    if (empty($actors)){
    	$pinForm .= '
    		<div id="abg_rp_div_add_actor0">
        	<label for="abg_rp_pinActor0">Enter an actor</label>
        	<input type="text" id="abg_rp_pinActor0" name="abg_rp_pinActor0"  size="30" />
        	</div>
    	';
    }
    else {
	    foreach($actors as $pinActor) {
	    	$pinForm .= '
	    		<div id="abg_rp_div_add_actor'.$i.'">
	        	<label for="abg_rp_pinActor'.$i.'">Enter an actor</label>
	        	<input type="text" id="abg_rp_pinActor'.$i.'" name="abg_rp_pinActor'.$i.'" value="'.esc_attr($pinActor).'" size="30" />
	        	</div>
	    	';
	    	$i += 1;
	    }		    	
    }

    $pinForm .= '
    				<br/>
    		    	<a id="abg_rp_add_actor">Add actor  </a>|
    		    	<a id="abg_rp_remove_actor">  Remove actor</a>
        	</div>
        </p>
        <p>
        	<label for="abg_rp_pinContent">Select a content rating</label><br/>
	        <select name="abg_rp_pinContent" id="abg_rp_pinContent">
			<option value="G" '.selected( $pinContent, "G", false).'>G - General Audiences</option>
			<option value="PG" '.selected( $pinContent, "PG", false).'>PG - Parental Guidance Suggested</option>
			<option value="PG-13" '.selected( $pinContent, "PG-13", false).'>PG-13 — Parents Strongly Cautioned</option>
			<option value="R" '.selected( $pinContent, "R", false).'>R — Restricted</option>
			<option value="NC-17" '.selected( $pinContent, "NC-17", false).'>NC-17 — No One 17 and Under Admitted</option>
			</select>
        </p>
        <p>
        	<label for="abg_rp_pinDate">Enter the movie release date</label><br/>
        	<input type="date" id="abg_rp_pinDate" name="abg_rp_pinDate" value="'.$pinDate.'" size="30" />
        </p>
        <p>
        	<label for="abg_rp_pinRating">Enter the movie rating</label><br/>
        	<input type="text" id="abg_rp_pinRating" name="abg_rp_pinRating" value="'.esc_attr($pinRating).'" size="30" />
        </p>
        <p>
        	<label for="abg_rp_pinRatingMax">Enter the maximum rating</label><br/>
        	<input type="text" id="abg_rp_pinRatingMax" name="abg_rp_pinRatingMax" value="'.esc_attr($pinRatingMax).'" size="30" />
        </p>
        <p class="pin_form_hidden">
        	<input type="text"  id="abg_rp_num_directors"
        		name="abg_rp_num_directors" value="'.esc_attr($numDirectors).'"/>
        	<input type="text"  id="abg_rp_num_actors"
        		name="abg_rp_num_actors" value="'.esc_attr($numActors).'"/>
        </p>
        </div>
    ';

    //Product form
	$pinTitle = get_post_meta( $post->ID, 'pinTitle', true );
	$pinDescription = get_post_meta( $post->ID, 'pinDescription', true );
	$pinAmount = get_post_meta( $post->ID, 'pinAmount', true );
	$pinCurrency = get_post_meta( $post->ID, 'pinCurrency', true );
	$pinAvailability = get_post_meta( $post->ID, 'pinAvailability', true);
	$pinBrand = get_post_meta( $post->ID, 'pinBrand', true);
    $pinForm .= '
    	<div id="abg_rp_productForm" class="pin_subform">
	        <p>
	        	<label for="abg_rp_pinTitle">Enter the product name *</label><br/>
	        	<input type="text" id="abg_rp_pinTitle" name="abg_rp_pinTitle" value="'.esc_attr($pinTitle).'" size="30" />
	        </p>
	        <p>
	        	<label for="abg_rp_pinProductDescription">Enter the product description</label><br/>
	        	<input type="text" id="abg_rp_pinProductDescription" name="abg_rp_pinProductDescription" value="'.esc_attr($pinDescription).'" size="30" />
	        </p>
	        <p>
	        	<label for="abg_rp_pinAmount">Enter the price *</label><br/>
	        	<input type="text" id="abg_rp_pinAmount" name="abg_rp_pinAmount" value="'.esc_attr($pinAmount).'" size="30" />
	        </p>
	        <p>
	        	<label for="abg_rp_pinCurrency">Select a currency *</label><br/>
				<select id="abg_rp_pinCurrency" name="abg_rp_pinCurrency">  
				    <option value="USD" '.selected( $pinCurrency, "USD",false).'>USD United States Dollars</option>
				    <option value="EUR" '.selected( $pinCurrency, "EUR",false).'>EUR Euro</option>
				    <option value="CAD" '.selected( $pinCurrency, "CAD",false).'>CAD Canada Dollars</option>
				    <option value="GBP" '.selected( $pinCurrency, "GBP",false).'>GBP United Kingdom Pounds</option>
				    <option value="JPY" '.selected( $pinCurrency, "JPY",false).'>JPY Japan Yen</option>
				    <option value="CHF" '.selected( $pinCurrency, "CHF",false).'>CHF Switzerland Francs</option>
				    <option value="DZD" '.selected( $pinCurrency, "DZD",false).'>DZD Algeria Dinars</option>
				    <option value="ARP" '.selected( $pinCurrency, "ARP",false).'>ARP Argentina Pesos</option>
				    <option value="AUD" '.selected( $pinCurrency, "AUD",false).'>AUD Australia Dollars</option>
				    <option value="BSD" '.selected( $pinCurrency, "BSD",false).'>BSD Bahamas Dollars</option>
				    <option value="BBD" '.selected( $pinCurrency, "BBD",false).'>BBD Barbados Dollars</option>
				    <option value="BMD" '.selected( $pinCurrency, "BMD",false).'>BMD Bermuda Dollars</option>
				    <option value="BRR" '.selected( $pinCurrency, "BRR",false).'>BRR Brazil Real</option>
				    <option value="BGL" '.selected( $pinCurrency, "BGL",false).'>BGL Bulgaria Lev</option>
				    <option value="CLP" '.selected( $pinCurrency, "CLP",false).'>CLP Chile Pesos</option>
				    <option value="CNY" '.selected( $pinCurrency, "CNY",false).'>CNY China Yuan Renmimbi</option>
				    <option value="CYP" '.selected( $pinCurrency, "CYP",false).'>CYP Cyprus Pounds</option>
				    <option value="CSK" '.selected( $pinCurrency, "CSK",false).'>CSK Czech Republic Koruna</option>
				    <option value="DKK" '.selected( $pinCurrency, "DKK",false).'>DKK Denmark Kroner</option>
				    <option value="XCD" '.selected( $pinCurrency, "XCD",false).'>XCD Eastern Caribbean Dollars</option>
				    <option value="EGP" '.selected( $pinCurrency, "EGP",false).'>EGP Egypt Pounds</option>
				    <option value="FJD" '.selected( $pinCurrency, "FJD",false).'>FJD Fiji Dollars</option>
				    <option value="HKD" '.selected( $pinCurrency, "HKD",false).'>HKD Hong Kong Dollars</option>
				    <option value="HUF" '.selected( $pinCurrency, "HUF",false).'>HUF Hungary Forint</option>
				    <option value="ISK" '.selected( $pinCurrency, "ISK",false).'>ISK Iceland Krona</option>
				    <option value="INR" '.selected( $pinCurrency, "INR",false).'>INR India Rupees</option>
				    <option value="IDR" '.selected( $pinCurrency, "IDR",false).'>IDR Indonesia Rupiah</option>
				    <option value="ILS" '.selected( $pinCurrency, "ILS",false).'>ILS Israel New Shekels</option>
				    <option value="JMD" '.selected( $pinCurrency, "JMD",false).'>JMD Jamaica Dollars</option>
				    <option value="JOD" '.selected( $pinCurrency, "JOD",false).'>JOD Jordan Dinar</option>
				    <option value="KRW" '.selected( $pinCurrency, "KRW",false).'>KRW Korea (South) Won</option>
				    <option value="LBP" '.selected( $pinCurrency, "LBP",false).'>LBP Lebanon Pounds</option>
				    <option value="MYR" '.selected( $pinCurrency, "MYR",false).'>MYR Malaysia Ringgit</option>
				    <option value="MXP" '.selected( $pinCurrency, "MXP",false).'>MXP Mexico Pesos</option>
				    <option value="NZD" '.selected( $pinCurrency, "NZD",false).'>NZD New Zealand Dollars</option>
				    <option value="NOK" '.selected( $pinCurrency, "NOK",false).'>NOK Norway Kroner</option>
				    <option value="PKR" '.selected( $pinCurrency, "PKR",false).'>PKR Pakistan Rupees</option>
				    <option value="PHP" '.selected( $pinCurrency, "PHP",false).'>PHP Philippines Pesos</option>
				    <option value="PLZ" '.selected( $pinCurrency, "PLZ",false).'>PLZ Poland Zloty</option>
				    <option value="ROL" '.selected( $pinCurrency, "ROL",false).'>ROL Romania Leu</option>
				    <option value="RUR" '.selected( $pinCurrency, "RUR",false).'>RUR Russia Rubles</option>
				    <option value="SAR" '.selected( $pinCurrency, "SAR",false).'>SAR Saudi Arabia Riyal</option>
				    <option value="SGD" '.selected( $pinCurrency, "SGD",false).'>SGD Singapore Dollars</option>
				    <option value="ZAR" '.selected( $pinCurrency, "ZAR",false).'>ZAR South Africa Rand</option>
				    <option value="SDD" '.selected( $pinCurrency, "SDD",false).'>SDD Sudan Dinar</option>
				    <option value="SEK" '.selected( $pinCurrency, "SEK",false).'>SEK Sweden Krona</option>
				    <option value="TWD" '.selected( $pinCurrency, "TWD",false).'>TWD Taiwan Dollars</option>
				    <option value="THB" '.selected( $pinCurrency, "THB",false).'>THB Thailand Baht</option>
				    <option value="TTD" '.selected( $pinCurrency, "TTD",false).'>TTD Trinidad and Tobago Dollars</option>
				    <option value="TRL" '.selected( $pinCurrency, "TRL",false).'>TRL Turkey Lira</option>
				    <option value="VEB" '.selected( $pinCurrency, "VEB",false).'>VEB Venezuela Bolivar</option>
				    <option value="ZMK" '.selected( $pinCurrency, "ZMK",false).'>ZMK Zambia Kwacha</option>
				</select>
	        </p>
	        <p>
	        	<label for="abg_rp_pinAvailability">Enter the product availability</label><br/>
		        <select name="abg_rp_pinAvailability" id="abg_rp_pinAvailability">
				<option value="InStock" '.selected( $pinAvailability, "InStock", false).'>in stock</option>
				<option value="PreOrder" '.selected( $pinAvailability, "PreOrder", false).'>pre-order</option>
				<option value="InStoreOnly" '.selected( $pinAvailability, "InStoreOnly", false).'>in store only</option>
				<option value="OnlineOnly" '.selected( $pinAvailability, "OnlineOnly", false).'>online only</option>					
				<option value="OutOfStock" '.selected( $pinAvailability, "OutOfStock", false).'>out of stock</option>
				<option value="Discontinued" '.selected( $pinAvailability, "Discontinued", false).'>discontinued</option>
				</select>
	        </p>
	        <p>
	        	<label for="abg_rp_pinBrand">Enter the product brand</label><br/>
	        	<input type="text" id="abg_rp_pinBrand" name="abg_rp_pinBrand" value="'.esc_attr($pinBrand).'" size="30" />
	        </p>
	       </div>
    ';

    //Recipe form
	$numIngredients = get_post_meta($post->ID, 'numIngredients', true);

	if(empty($numIngredients)) {
		$numDirectors = 0;
	} 

	$ingredients = array();
	for($i = 0; $i < $numIngredients; $i++){
		$ingredient = 'pinIngredient'.$i;
		$ingredients[] = get_post_meta($post->ID, $ingredient, true);
	}

	$pinName = get_post_meta( $post->ID, 'pinName', true );
	$pinDescription = get_post_meta( $post->ID, 'pinDescription', true );
	$pinTime = get_post_meta( $post->ID, 'pinTime', true );
	$pinYield = get_post_meta( $post->ID, 'pinYield', true );

    $pinForm .= '
    	<div id="abg_rp_recipeForm" class="pin_subform">
	        <p>
	        	<label for="abg_rp_pinRecipeName">Enter the recipe name *</label><br/>
	        	<input type="text" id="abg_rp_pinRecipeName" name="abg_rp_pinRecipeName" value="'.esc_attr($pinName).'" size="30" />
	        </p>
	        <p>
	        	<label for="abg_rp_pinRecipeDescription">Enter the recipe description</label><br/>
	        	<input type="text" id="abg_rp_pinRecipeDescription" name="abg_rp_pinRecipeDescription" value="'.esc_attr($pinDescription).'" size="30" />
	        </p>
	        <p>
	        	<div id="abg_rp_recipe_ingredients">
	        	Ingredients (<small>Ingredient name must be written in English, otherwise Pinterest will NOT validate your Rich Pin</small>):
	        	<br/>
	        	<small> (e.g. "1/3 cup ice" or "1 cup orange juice" or "1/2 banana")</small>'
    ;
    $i = 0;
    if (empty($ingredients)){
    	$pinForm .= '
    		<div id="abg_rp_div_add_ingredient0">
        	<label for="abg_rp_pinIngredient0">Enter an ingredient</label>
        	<input type="text" id="abg_rp_pinIngredient0" name="abg_rp_pinIngredient0" size="30" />
        	</div>
        ';		    
    } else {
	    foreach($ingredients as $pinIngredient) {
	    	$pinForm .= '
	    		<div id="abg_rp_div_add_ingredient'.$i.'">
	        	<label for="abg_rp_pinIngredient'.$i.'">Enter an ingredient</label>
	        	<input type="text" id="abg_rp_pinIngredient'.$i.'" name="abg_rp_pinIngredient'.$i.'" value="'.esc_attr($pinIngredient).'" size="30" />
	        	</div>
	        ';
	    	$i += 1;
	    }		    	
    }

    $pinForm .= '
    			<br/>

    			<a id="abg_rp_add_ingredient">Add ingredient  </a>|
    			<a id="abg_rp_remove_ingredient">  Remove ingredient</a>

        	</div>
        </p>
        <p>
        	<label for="abg_rp_pinTime">Enter the time it takes to cook it </label><small>(in minutes)</small><br/>
	        <input type="number" id="abg_rp_pinTime" name="abg_rp_pinTime" min="1" value="'.esc_attr($pinTime).'"/>
        </p>
        <p>
        	<label for="abg_rp_pinYield">Enter the number of servings made by this recipe</label><br/><small> (e.g. "5 servings" or "Serves 4-6" or "Yields 10 burgers")</small><br/>
        	<input type="text" id="abg_rp_pinYield" name="abg_rp_pinYield" value="'.esc_attr($pinYield).'" size="30" />
        	
        </p>
        <p class="pin_form_hidden">
        	<input type="text"  id="abg_rp_num_ingredients"
        		name="abg_rp_num_ingredients" value="'.esc_attr($numIngredients).'"/>
        </p>
        </div>
    ';
    print($pinForm);
	echo 'Do NOT forget to validate your rich pin at <a href="http://developers.pinterest.com/rich_pins/validator/">Pin validator</a>';
}

/* Saves meta data into the DB */
function abg_rp_save_postdata( $post_id ) {

	if ( 'page' == $_REQUEST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
		    return;
		} else {
	if ( ! current_user_can( 'edit_post', $post_id ) )
	    return;
	}

	if ( ! isset( $_POST['abg_rp_noncename'] ) || ! wp_verify_nonce( $_POST['abg_rp_noncename'], plugin_basename( __FILE__ ) ) )
	  return;


	$post_ID = $_POST['post_ID'];
	$pinType = sanitize_text_field($_POST['abg_rp_pinType']);
	add_post_meta($post_ID, 'pinType', $pinType, true) or
	update_post_meta($post_ID, 'pinType', $pinType);
	switch ($pinType) {
		case 'movie':
			$pinName = sanitize_text_field($_POST['abg_rp_pinMovieName']);
			add_post_meta($post_ID, 'pinName', $pinName, true) or
			update_post_meta($post_ID, 'pinName', $pinName);

			$pinDescription = sanitize_text_field($_POST['abg_rp_pinMovieDescription']);
			add_post_meta($post_ID, 'pinDescription', $pinDescription, true) or
			update_post_meta($post_ID, 'pinDescription', $pinDescription);

			$pinRating = sanitize_text_field($_POST['abg_rp_pinRating']);
			add_post_meta($post_ID, 'pinRating', $pinRating, true) or
			update_post_meta($post_ID, 'pinRating', $pinRating);

			$pinRatingMax = sanitize_text_field($_POST['abg_rp_pinRatingMax']);
			add_post_meta($post_ID, 'pinRatingMax', $pinRatingMax, true) or
			update_post_meta($post_ID, 'pinRatingMax', $pinRatingMax);

			$pinContent = sanitize_text_field($_POST['abg_rp_pinContent']);
			add_post_meta($post_ID, 'pinContent', $pinContent, true) or
			update_post_meta($post_ID, 'pinContent', $pinContent);

			$pinDate = sanitize_text_field($_POST['abg_rp_pinDate']);
			add_post_meta($post_ID, 'pinDate', $pinDate, true) or
			update_post_meta($post_ID, 'pinDate', $pinDate);

			$numDir = $_POST['abg_rp_num_directors'];

			$j = 0;
			for($i = 0; $i < $numDir; $i++) {
				$currentDir = sanitize_text_field($_POST['abg_rp_pinDirector'.$i]);
				if (!empty($currentDir)){
					add_post_meta($post_ID, 'pinDirector'.$i, $currentDir, true) or
					update_post_meta($post_ID, 'pinDirector'.$i, $currentDir);
					$j++;				
				}

			}
			$numDir = $j;
			add_post_meta($post_ID, 'numDirectors', $numDir, true) or
			update_post_meta($post_ID, 'numDirectors', $numDir);	

			$numAct = $_POST['abg_rp_num_actors'];

			$j = 0;	
			for($i = 0; $i < $numAct; $i++) {
				$currentAct = sanitize_text_field($_POST['abg_rp_pinActor'.$i]);
				if (!empty($currentAct)){
					add_post_meta($post_ID, 'pinActor'.$i, $currentAct, true) or
					update_post_meta($post_ID, 'pinActor'.$i, $currentAct);
					$j++;
				}
			}
			$numAct = $j;
			add_post_meta($post_ID, 'numActors', $numAct, true) or
			update_post_meta($post_ID, 'numActors', $numAct);
			break;
		case 'product' :
			$pinTitle = sanitize_text_field($_POST['abg_rp_pinTitle']);
			$pinDescription = sanitize_text_field($_POST['abg_rp_pinProductDescription']);
			$pinAmount = $_POST['abg_rp_pinAmount'];
			$pinCurrency = sanitize_text_field($_POST['abg_rp_pinCurrency']);
			$pinAvailability = sanitize_text_field($_POST['abg_rp_pinAvailability']);
			$pinBrand = sanitize_text_field($_POST['abg_rp_pinBrand']);
			// Do something with $mydata 
			// either using 

			add_post_meta($post_ID, 'pinTitle', $pinTitle, true) or
			update_post_meta($post_ID, 'pinTitle', $pinTitle);
			add_post_meta($post_ID, 'pinDescription', $pinDescription, true) or
			update_post_meta($post_ID, 'pinDescription', $pinDescription);
			add_post_meta($post_ID, 'pinAmount', $pinAmount, true) or
			update_post_meta($post_ID, 'pinAmount', $pinAmount);
			add_post_meta($post_ID, 'pinCurrency', $pinCurrency, true) or
			update_post_meta($post_ID, 'pinCurrency', $pinCurrency);
			add_post_meta($post_ID, 'pinAvailability', $pinAvailability, true) or
			update_post_meta($post_ID, 'pinAvailability', $pinAvailability);
			add_post_meta($post_ID, 'pinBrand', $pinBrand, true) or
			update_post_meta($post_ID, 'pinBrand', $pinBrand);
			break;
		case 'recipe':
			$pinName = sanitize_text_field($_POST['abg_rp_pinRecipeName']);
			add_post_meta($post_ID, 'pinName', $pinName, true) or
			update_post_meta($post_ID, 'pinName', $pinName);

			$pinDescription = sanitize_text_field($_POST['abg_rp_pinRecipeDescription']);
			add_post_meta($post_ID, 'pinDescription', $pinDescription, true) or
			update_post_meta($post_ID, 'pinDescription', $pinDescription);

			$pinTime = sanitize_text_field($_POST['abg_rp_pinTime']);
			add_post_meta($post_ID, 'pinTime', $pinTime, true) or
			update_post_meta($post_ID, 'pinTime', $pinTime);

			$pinYield = sanitize_text_field($_POST['abg_rp_pinYield']);
			add_post_meta($post_ID, 'pinYield', $pinYield, true) or
			update_post_meta($post_ID, 'pinYield', $pinYield);

			$numIng = $_POST['abg_rp_num_ingredients'];

			$j = 0;
			for($i = 0; $i < $numIng; $i++) {
				$currentIng = sanitize_text_field($_POST['abg_rp_pinIngredient'.$i]);
				if (!empty($currentIng)){
					add_post_meta($post_ID, 'pinIngredient'.$i, $currentIng, true) or
					update_post_meta($post_ID, 'pinIngredient'.$i, $currentIng);
					$j++;				
				}

			}
			$numIng = $j;
			add_post_meta($post_ID, 'numIngredients', $numIng, true) or
			update_post_meta($post_ID, 'numIngredients', $numIng);	
			break;

	}
}

/* Add meta-data to the post */
function abg_rp_add_pin_info() {
	global $post;	
	if( is_singular() && !is_front_page() && !is_home() && !is_404() && !is_tag()) {
		// get current post meta data
		$pinType    	= get_post_meta($post->ID, 'pinType', true);

		switch ($pinType) {
			case 'movie':
				echo "\n".'<div class="abg_rp_movie_div">'."\n";
				echo '<!-- ABG Rich Pins by Antonio Borrero Granell '.abg_rp_plugin_get_version().' -->'."\n";  
				$numDirectors = get_post_meta($post->ID, 'numDirectors', true);
				$numActors = get_post_meta($post->ID, 'numActors', true);
				if(empty($numDirectors)) {
					$numDirectors = 0;
				} 
				if(empty($numActors)){
					$numActors = 0;
				}
				$directors = array();
				for($i = 0; $i < $numDirectors; $i++){
					$directorName = 'pinDirector'.$i;
					$directors[] = get_post_meta($post->ID, $directorName, true);
				}
				$actors = array();
				for($i = 0; $i < $numActors; $i++){
					$actorName = 'pinActor'.$i;
					$actors[] = get_post_meta($post->ID, $actorName, true);
				}
				$pinName = get_post_meta( $post->ID, 'pinName', true );
				$pinDescription = get_post_meta( $post->ID, 'pinDescription', true );
				$pinRating = get_post_meta( $post->ID, 'pinRating', true );
				$pinRatingMax = get_post_meta( $post->ID, 'pinRatingMax', true );
				$pinContent = get_post_meta( $post->ID, 'pinContent', true );
				$pinDate = get_post_meta( $post->ID, 'pinDate', true);	
				if ( function_exists('abg_rp_remove_spaces')) 
					$pinDescription = abg_rp_remove_spaces($pinDescription);
				echo '<meta property="og:site_name" content="'.get_bloginfo('name').'"/>'."\n";
    			echo '<div itemscope itemtype="http://schema.org/Movie">'."\n";
   			    echo '<meta itemprop="url" content="'.curPageURL().'" />'."\n";
    			echo '<h1 itemprop="name">'.$pinName.'</h1>'."\n";
    			echo '<span itemprop="description">'.$pinDescription.'</span>'."\n";
       			foreach($directors as $pinDirector) {
        			echo '<div itemprop="director" itemscope itemtype="http://schema.org/Person">'."\n";
    				echo '<span itemprop="name">'.$pinDirector.'</span>'."\n";
        			echo '</div>'."\n";
    			}	
    			foreach($actors as $pinActor) {
    				echo '<div itemprop="actor" itemscope itemtype="http://schema.org/Person">'."\n";
    				echo '<span itemprop="name">'.$pinActor.'</span>'."\n";
    				echo '</div>'."\n";
    			}
    			echo '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">'."\n";
    			echo '<span itemprop="ratingValue">'.$pinRating.'</span>'."\n";
        		echo '<span itemprop="bestRating">'.$pinRatingMax.'</span>'."\n";
      			echo '</div>'."\n";
      			echo '<meta itemprop="datePublished" content="'.$pinDate.'">'."\n";
		        echo '<meta itemprop="contentRating" content="'.$pinContent.'"/>'."\n";
		        echo '</div>'."\n";
		        echo '<!-- /ABG Rich Pins -->'."\n";      
		        echo '</div>'."\n\n";
		        break;
		    case 'product':
		        echo "\n".'<div class="abg_rp_product_div">'."\n";
				echo '<!-- ABG Rich Pins by Antonio Borrero Granell '.abg_rp_plugin_get_version().' -->'."\n";  
				$pinTitle		= get_post_meta(get_the_ID(),'pinTitle',true);
				$pinDescription = get_post_meta(get_the_ID(),'pinDescription',true);
				$pinAmount        = get_post_meta(get_the_ID(),'pinAmount',true);
				$pinCurrency         = get_post_meta(get_the_ID(),'pinCurrency',true);
				$pinAvailability        = get_post_meta(get_the_ID(),'pinAvailability',true);
				$pinBrand       = get_post_meta(get_the_ID(),'pinBrand',true);

				// apply filter
				if ( function_exists('abg_rp_remove_spaces')) 
					$pinDescription = abg_rp_remove_spaces($pinDescription);
				echo '<meta property="og:site_name" content="'.get_bloginfo('name').'"/>'."\n";
				echo '<div itemscope itemtype="http://schema.org/Product">'."\n";
				echo '<meta itemprop="name" content="'.$pinTitle.'"/>'."\n";
				echo '<meta itemprop="url" content="'.curPageURL().'"/>'."\n";
				echo '<span itemprop="description">'.$pinDescription.'</span>'."\n";
				echo '<span itemprop="brand">'.$pinBrand.'</span>'."\n";
				echo '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">'."\n";
				echo '<span itemprop="price">'.$pinAmount.'</span>'."\n";
				echo '<meta itemprop="priceCurrency" content="'.$pinCurrency.'"/>'."\n";
				echo '<meta itemprop="availability" itemtype="http://schema.org/ItemAvailability" content="'.$pinAvailability.'"/>'."\n";
				echo '</div>'."\n";
				echo '</div>'."\n";
				echo '<!-- /ABG Rich Pins -->'."\n";   
				echo '</div>'."\n";
				break;
			case 'recipe':
		        echo "\n".'<div class="abg_rp_recipe_div">'."\n";
		        echo '<!-- ABG Rich Pins by Antonio Borrero Granell '.abg_rp_plugin_get_version().' -->'."\n";  
		        $numIngredients = get_post_meta($post->ID, 'numIngredients', true);
		        if(empty($numIngredients)) {
		          $numIngredients = 0;
		        } 
		        $ingredients = array();
		        for($i = 0; $i < $numIngredients; $i++){
		          $ingredientName = 'pinIngredient'.$i;
		          $ingredients[] = get_post_meta($post->ID, $ingredientName, true);
		        }
		        $pinName = get_post_meta( $post->ID, 'pinName', true );
		        $pinDescription = get_post_meta( $post->ID, 'pinDescription', true );
		        $pinTime = get_post_meta( $post->ID, 'pinTime', true );
		        $pinYield = get_post_meta( $post->ID, 'pinYield', true );
		        if ( function_exists('abg_rp_remove_spaces')) 
		          $pinDescription = abg_rp_remove_spaces($pinDescription);
		        echo '<meta property="og:site_name" content="'.get_bloginfo('name').'"/>'."\n";
		        echo '<div itemscope itemtype="http://schema.org/Recipe">'."\n";
		        echo '<span itemprop="name">'.$pinName.'</span>'."\n";
		        echo '<span itemprop="description">'.$pinDescription.'</span>'."\n";
		        echo '<meta itemprop="url" content="'.curPageURL().'" />'."\n";
		        echo '<meta itemprop="totalTime" content="PT'.$pinTime.'M" />'."\n";
		        echo '<span itemprop="recipeYield">'.$pinYield.'</span>'."\n";
		        foreach($ingredients as $pinIngredient) {
		          echo '<span itemprop="ingredients">'.$pinIngredient.'</span>'."\n";
		        } 
		        echo '</div>'."\n";
		        echo '<!-- /ABG Rich Pins -->'."\n";      
		        echo '</div>'."\n\n";  
		        break;  				
		}
	}
}
add_action( 'wp_head', 'abg_rp_add_pin_info', 99);

/**
 * Helpers 
 */

/* Get the current URL */
function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}
/* Remove spaces from a string */
function abg_rp_remove_spaces($string) {
	$result = preg_replace("/\s+/", " ", $string); // remove strip tags and strip shortcodes I already use in get_excerpt_by_id()
	return $result;
}