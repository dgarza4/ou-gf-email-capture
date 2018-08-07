<?php
/**
 * OU GF Email Capture
 *
 * @author Yocheved Blum <cheved114@gmail.com>
 *
 * Plugin Name: OU GF Email Capture
 * Description: Adds email subscribe field connected to silverpop to gravity forms
 * Version: 0.5
 * Author: Yocheved Blum
 */

include_once 'silverpopFunctions.php'; 
 
// Add the custom field buttons to the Advanced Fields group
add_filter('gform_add_field_buttons', 'add_email_subscribe_field');

function add_email_subscribe_field($field_groups) 
{
	foreach($field_groups as &$group)
	{
		if($group["name"] == "advanced_fields")
		{
			$group["fields"][] = array(
				"class"=>"button",
				"value" => __("Email Subscribe", "gravityforms"),
                'data-type' => 'emailSubscribe',
				"onclick" => "StartAddField('emailSubscribe');"
			);
			break;													
		}
	}
	return $field_groups;
}

// Adds title to custom field
add_filter('gform_field_type_title' , 'set_email_subscription_title');
function set_email_subscription_title($type) 
{
	if($type == 'emailSubscribe')
		return __('Email Subscribe' , 'gravityforms');
}


/*************************************************************************************
				Email Subscribe Custom Field
*************************************************************************************/
// Adds the input area to the external side
add_action("gform_field_input" , "email_subscribe_field_input", 10, 5);
function email_subscribe_field_input ($input, $field, $value, $lead_id, $form_id)
{
	if($field["type"] == "emailSubscribe") {
		$tabindex = GFCommon::get_tabindex();
		$css = isset( $field['cssClass'] ) ? $field['cssClass'] : '';
		$labelText = esc_html($value) ?: "Yes, I would like to subscribe";
		if(!empty($_POST)) //its returning from validation so use post value instead of default value
			$checked = isset($_POST['input_'.$field['id']]) && $_POST['input_'.$field['id']] == $labelText ? 'checked' : '';
		else
			$checked = (isset($field['defaultCheck']) && $field['defaultCheck']) ? 'checked' : '';
		$newField = "<div class='ginput_container'>".
				"<label class='lbl' for='chkSubscribe".$field["id"]."' rel='chkSubscribe".$field["id"]."' style='font-weight:normal; text-transform:none;'>".
				"<input type='checkbox' value='$labelText' id='chkSubscribe".$field["id"]."' name='input_".$field["id"]."' class='subscription-chk' style='margin:0' $tabindex $checked/> ".
				"$labelText</label>".				
			"</div>";
		return $newField;		
	}
	return $input;
}

// Now we execute some javascript technicalitites for the field to load correctly
add_action( "gform_editor_js", "email_subscription_editor_js" );
function email_subscription_editor_js(){
?>
<script type='text/javascript'>
	jQuery(document).ready(function($) {
		fieldSettings["emailSubscribe"] = ".label_setting, .admin_label_setting, .visibility_setting, .description_setting, .css_class_setting, .default_value_setting, .newsletter_setting, .database_setting, .default_check_setting";
        
		//binding to the load field settings event to initialize the checkbox
        $(document).bind("gform_load_field_settings", function(event, field, form){
			$("#form_field_database").val(field["field_database"]);
            $("#form_field_database").trigger("change");
			$("#form_field_newsletter").val(field["field_newsletter"]);
			$("#field_newsletter_value").val(field["newsletter"]);
			$("#field_default_check").prop("checked", field["defaultCheck"] == true);
        });		
    });
</script>
<?php
}

// Add a custom setting to the standard field
add_action( "gform_field_standard_settings" , "newsletter_settings" , 10, 2 );
function newsletter_settings( $position, $form_id ){

	// Create settings on position 50 (right after Field Label)
	if( $position == 50 ){
		$data['ajax'] = false;
		$contactLists = executeSilverpop('contactLists', $data);
	?>	
    
    <script>
        function ShowContactLists(db){
            var cl = <?php echo json_encode($contactLists) ?>;
            document.getElementById('form_field_newsletter').innerHTML = "";
            for(var i=0; i < cl.length; i++){   
                if(db == cl[i]['PARENT_NAME'] && cl[i]['IS_FOLDER'] == 'false' && cl[i]['TYPE'] === "18"){
                    var option = document.createElement("option");
                    option.text = cl[i]['NAME'];
                    option.name = "newsletter";
                    option.id = "field_newsletter";
                    option.value = cl[i]['ID'];
                    document.getElementById('form_field_newsletter').add(option);            
                }
            }
        }
    </script>
	
    <li class="database_setting field_setting">
        <?php 
            $databases = array(
                'OU Lists' => '1847712',
                'Main IFS List' => '3336350',
                'IFS Main Referrals DB' => '3390925',
                'NCSY' => '3954994',
                'JLIC Main' => '3955085',
                'Yachad Regions' => '4659496'
            );
        ?>
    
		<label for="field_database" class="inline">
			<?php _e("Choose Database:", "gravityforms"); ?>
			<?php gform_tooltip("form_field_database"); ?>
		</label><br/>
		<select id="form_field_database" onchange="ShowContactLists(this.options[this.selectedIndex].innerHTML); SetFieldProperty('field_database', this.value);">
			<option name="database" id="field_database" value="">Choose a Database</option>
		<?php foreach($databases as $name => $id): ?>
			<option name="database" id="field_database" value="<?php echo $id ?>"><?php echo $name ?></option>
		<?php endforeach; ?>
		</select>
	</li>    
    
	<li class="newsletter_setting field_setting">

		<label for="field_newsletter" class="inline">
			<?php _e("Choose Newsletter:", "gravityforms"); ?>
			<?php gform_tooltip("form_field_newsletter"); ?>
		</label><br/>
		<select id="form_field_newsletter" onchange="SetFieldProperty('field_newsletter', this.value);">
			<option name="newsletter" id="field_newsletter" value="">Choose a Newsletter</option>
		<?php 
			foreach($contactLists as $contactList):
				if($contactList['IS_FOLDER'] == 'false'): ?>
			<option name="newsletter" id="field_newsletter" data-parent="<?php echo $contactList['PARENT_NAME'] ?>" value="<?php echo $contactList['ID'] ?>"><?php echo $contactList['NAME'] ?></option>
		<?php 	endif; 
			endforeach; ?>
		</select>
	</li>
    
	<li class="default_check_setting field_setting">

		<label for="field_default_check" class="inline">
		<input type="checkbox" id="field_default_check" onclick="SetFieldProperty('defaultCheck', this.checked);"/>
			<?php _e("Default Checkbox", "gravityforms"); ?>
			<?php gform_tooltip("form_field_default_check"); ?>
		</label><br/>
	</li>
	
	<?php
	}
}

//Filter to add a new tooltip
add_filter('gform_tooltips', 'add_tooltips');
function add_tooltips($tooltips){
   $tooltips["form_field_database"] = "<h6>Choose Database</h6>Select from the databases we have which database the user should get saved into.";
   $tooltips["form_field_newsletter"] = "<h6>Choose Newsletter</h6>Select from the newsletters we have which newsletter the user is choosing to subscribe to.";
   $tooltips["form_field_default_check"] = "<h6>Default Checkbox</h6>What should the checkbox default to?";
   return $tooltips;
}

//send info to silverpop after form is submitted
add_action("gform_after_submission", "send_silverpop", 10, 2);
function send_silverpop($entry, $form){
    $data = array();

	$data['form'] = $form['title'];

	foreach($form['fields'] as $field){
		if($field['type'] == 'emailSubscribe'){
			if($entry[$field['id']] != ''){ //they checked yes
				$data['newsletter'] = $field['field_newsletter']; //which newsletter
                $data['database'] = $field['field_database']; //which db to save to
			}
		}
		else if($field['type'] == 'email'){
			$data['email'] = $entry[$field['id']];
		}
		else if($field['type'] == 'name'){
			$data['name']['first'] = $entry[$field['id'].'.3'];
			$data['name']['last'] = $entry[$field['id'].'.6'];
		}
		else if($field['type'] == 'address'){
			$data['address']['street'] = $entry[$field['id'].'.1'];
			$data['address']['street2'] = $entry[$field['id'].'.2'];
			$data['address']['city'] = $entry[$field['id'].'.3'];
			$data['address']['state'] = $entry[$field['id'].'.4'];
			$data['address']['zip'] = $entry[$field['id'].'.5'];
			$data['address']['country'] = $entry[$field['id'].'.6'];
		}
	}
	if(isset($data['newsletter'])){
		$data['ajax'] = false;
		$response = executeSilverpop('addRecipient', $data);
	}
}
?>
