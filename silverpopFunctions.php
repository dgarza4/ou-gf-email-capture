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
        $params = array(
          'fields' => array(
            'EMAIL' => $data['email']
          ),
          'contactLists' => array($data['newsletter'])
        );
        if (isset($data['name']['first']) && $data['name']['first'] !== '') $params['fields']['FIRST NAME'] = $data['name']['first'];
        if (isset($data['name']['last']) && $data['name']['last'] !== '') $params['fields']['LAST NAME'] = $data['name']['last'];
        if (isset($data['address']['street']) && $data['address']['street'] !== '') $params['fields']['ADDRESS'] = $data['address']['street'];
        if (isset($data['address']['street2']) && $data['address']['street2'] !== '') $params['fields']['ADDRESS2'] = $data['address']['street2'];
        if (isset($data['address']['city']) && $data['address']['city'] !== '') $params['fields']['CITY'] = $data['address']['city'];
        if (isset($data['address']['state']) && $data['address']['state'] !== '') $params['fields']['STATE'] = $data['address']['state'];
        if (isset($data['address']['zip']) && $data['address']['zip'] !== '') $params['fields']['POSTAL CODE'] = $data['address']['zip'];
        if (isset($data['address']['country']) && $data['address']['country'] !== '') $params['fields']['COUNTRY'] = $data['address']['country'];
        if (isset($data['form']) && $data['form'] !== '') $data['fields']['COLLECTION NAME'] = $params['form'];
          
        curl_setopt($ch, CURLOPT_URL, 'https://orthodox:ohFahl2tuse9aige@silverpop-api-staging.dokku03.aws.oustatic.com/api/databases/'.$databaseID.'/contact');  
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));  
        
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
