<?php
add_action("wp_ajax_executeSilverpop", "executeSilverpop");
add_action("wp_ajax_nopriv_executeSilverpop", "executeSilverpop");

if (!function_exists('executeSilverpop')) {
  function executeSilverpop($action = '', $data = array()){
      //get $data and $action if was ajax call
  	if($action == '') $action = isset($_POST['data']['action']) ? $_POST['data']['action'] : null;
  	if(empty($data)) $data = isset($_POST['data']) ? $_POST['data'] : array();
  	$databaseID = isset($data['database']) ? $data['database'] : '1847712';
    
    //initialize curl object
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  	if ($action == 'contactLists') {
      curl_setopt($ch, CURLOPT_URL, 'https://orthodox:ohFahl2tuse9aige@silverpop-api-staging.dokku03.aws.oustatic.com/api/databases');
      
      $results = json_decode(curl_exec($ch), true);
      $response = isset($results['results']) && is_array($results['results']) ? array_values($results['results']) : array();
  	} else if($action == 'addRecipient') {
  		if (isset($data['email']) && $data['email'] !== '' && isset($data['newsletter']) && $data['newsletter'] !== '') {
        $data = array(
          'fields' => array(
            'EMAIL' => $data['email'],
            'FIRST NAME'  => isset($data['name']['first']) ? $data['name']['first'] : '',
            'LAST NAME'  => isset($data['name']['last']) ? $data['name']['last'] : '',
    				'ADDRESS'  => isset($data['address']['street']) ? $data['address']['street'] : '',
    				'ADDRESS2'  => isset($data['address']['street2']) ? $data['address']['street2'] : '',
    				'CITY'  => isset($data['address']['city']) ? $data['address']['city'] : '',
    				'STATE'  => isset($data['address']['state']) ? $data['address']['state'] : '',
    				'POSTAL CODE'  => isset($data['address']['zip']) ? $data['address']['zip'] : '',
    				'COUNTRY'  => isset($data['address']['country']) ? $data['address']['country'] : '',
    				'COLLECTION NAME' => isset($data['form']) ? $data['form'] : ''
          ),
          'contactLists' => array($data['newsletter'])
        );
          
        curl_setopt($ch, CURLOPT_URL, 'https://orthodox:ohFahl2tuse9aige@silverpop-api-staging.dokku03.aws.oustatic.com/api/databases/'.$databaseID.'/contact');  
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));  
        
        $results = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        if (!$results || $http_code !== 200 || isset($results['errors'])) {
          $response = array('success' => false, 'message' => isset($results['message']) ? $results['message'] : 'Unknown error');
        } else {
          $response = array('success' => true, 'message' => 'You have been subscribed to the newsletter.');
        }

  		} else {
  			$response = array('success' => false, 'message' => 'Could not submit subscription. Missing Information.');
  		}
  	} else {
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
