<?php
use Silverpop\EngagePod;

add_action("wp_ajax_executeSilverpop", "executeSilverpop");
add_action("wp_ajax_nopriv_executeSilverpop", "executeSilverpop");

if (!function_exists('executeSilverpop')) {
  function executeSilverpop($action = '', $data = array()){
      //get $data and $action if was ajax call
  	if($action == '') $action = isset($_POST['data']['action']) ? $_POST['data']['action'] : null;
  	if(empty($data)) $data = isset($_POST['data']) ? $_POST['data'] : array();
  	$databaseID = isset($data['database']) ? $data['database'] : '1847712';

  	// Initialize the library
  	$silverpop = new EngagePod(array(
  	  'username'       => 'api@ou.org',
  	  'password'       => '@Ounion900',
  	  'engage_server'  => 1,
  	));

  	if($action == 'contactLists'){
  		$response = $silverpop->GetLists(18, false, null, 'True');
  		$response = $response ? $response : array();
  	}  
  	else if($action == 'addRecipient'){
  		if(isset($data['email']) && $data['email'] !== '' && isset($data['newsletter']) && $data['email'] !== ''){
  			$response = $silverpop->addContact(
  			  $databaseID,
  			  true,
  			  array(
  				'First Name'  => isset($data['name']['first']) ? $data['name']['first'] : '',
  				'Last Name'  => isset($data['name']['last']) ? $data['name']['last'] : '',
  				'Email' => $data['email'],
  				'Address'  => isset($data['address']['street']) ? $data['address']['street'] : '',
  				'Address2'  => isset($data['address']['street2']) ? $data['address']['street2'] : '',
  				'City'  => isset($data['address']['city']) ? $data['address']['city'] : '',
  				'State'  => isset($data['address']['state']) ? $data['address']['state'] : '',
  				'Postal Code'  => isset($data['address']['zip']) ? $data['address']['zip'] : '',
  				'Country'  => isset($data['address']['country']) ? $data['address']['country'] : '',
  				'Collection Name' => isset($data['form']) ? $data['form'] : '',
  			  ),
  			  $data['newsletter']
  			);
  			if(isset($response['SUCCESS']) && $response['SUCCESS'] == 'TRUE'){
  				$response = array('success' => true, 'message' => 'You have been subscribed to the newsletter.');
  			}
  			else{
  				$response = array('success' => false, 'message' => 'There was an error subscribing you to the newsletter.');
  			}
  		}
  		else{
  			$response = array('success' => false, 'message' => 'Could not submit subscription. Missing Information.');
  		}
  	}
  	else{
  		$response = array('success' => false, 'message' => 'Unknown action.');
  	}
  	if(isset($data['ajax']) && $data['ajax']){
  		echo json_encode( $response);
  		exit();
  	}
  	else
  		return $response;
  }
}
