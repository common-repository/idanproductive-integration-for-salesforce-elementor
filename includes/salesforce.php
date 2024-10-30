<?php

namespace Productive_Salesforce_Elementor_Integration;

final class Salesforce
{
    private $client_id = '';
    private $client_secret = '';
    private $username = '';
    private $password = '';
    private $token = '';
    private $instance_url = '';
    private $access_token = '';

    private static $_instance = null;

    public static function instance($client_id, $client_secret, $username, $password, $token, $instance_url, $access_token)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($client_id, $client_secret, $username, $password, $token, $instance_url, $access_token);
        }
        return self::$_instance;
    }

    public function __construct($client_id, $client_secret, $username, $password, $token, $instance_url, $access_token = '')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->username = $username;
        $this->password = $password;
        $this->token = $token;
        $this->instance_url = $instance_url;
        $this->access_token = $access_token;
    }

    public function PSEIGetAccessToken()
    {
        $url = $this->instance_url . '/services/oauth2/token';
        $data_string = 'grant_type=password&client_id=' . $this->client_id . '&client_secret=' . $this->client_secret . '&username=' . $this->username . '&password=' . $this->password . $this->token;
        error_log("OAuth Token" . "\n", 3, ABSPATH . 'error_log');
        error_log($url . "\n", 3, ABSPATH . 'error_log');
        error_log($data_string . "\n", 3, ABSPATH . 'error_log');
        
        $this->PSEISendDataToProductive('get_access_token');

        $response = wp_remote_post_with_cache($url, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Content-Length' => strlen($data_string)
            ),
            'body' => $data_string,
        ));

         $result = wp_remote_retrieve_body($response);

        $result = json_decode($result);

        error_log("====== ACCESS TOKEN RESPONSE =================" . "\n", 3, ABSPATH . 'error_log');
        error_log(json_encode($result) . "\n", 3, ABSPATH . 'error_log');
        error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

        if($result) {
            if (!isset($result->error)) {
                update_option('psei_salesforce_access_token', $result->access_token);
            }

            return $result;
        }

        return null;        
    }

    public function PSEIGetObjects()
{
    try {
        $results = $this->PSEIGetAccessToken();
        if(!$results){
            $res = array(
                'error' => is_array($results) ? $results[0]->errorCode : esc_html('500'),
                'error_description' => is_array($results) ? $results[0]->message : esc_html__('Failed to fetch connect to salesforce instance. Check Credentials', 'elemetix'),
                'data' => null,
                'from' => 'getAccessToken'
            );

            return $res;
        }
        $access_token = $results->access_token;  

        $url = $this->instance_url . '/services/data/v55.0/sobjects';
        
        error_log("======= FETCHING OBJECT ===================" . "\n", 3, ABSPATH . 'error_log');
        error_log($url . "\n", 3, ABSPATH . 'error_log');

        $this->PSEISendDataToProductive('fetch_objects');

        $args = array(
            'headers'     => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-type' => 'application/json'
            ),
        ); 

        $response = wp_remote_get_with_cache($url, $args);

        $result = wp_remote_retrieve_body($response);
        $result = json_decode($result);

        error_log("=======  OBJECT RESULTS ===================" . "\n", 3, ABSPATH . 'error_log');
        error_log(json_encode($result) . "\n", 3, ABSPATH . 'error_log');
        error_log("==========================" . "\n", 3, ABSPATH . 'error_log');
        
        if(!isset($result->sobjects)){
            $res = array(
                'error' => is_array($result) ? $result[0]->errorCode : esc_html('500'),
                'error_description' => is_array($result) ? $result[0]->message : esc_html__('Failed to fetch sObjects', 'elemetix'),
                'data' => null,
                'from' => 'getObjects'
            );

            return $res;
        }

        // Filter the objects
        $filtered_objects = array_filter($result->sobjects, function($object) {
            // Filter criteria:
            // 1. Object is createable (publicly accessible for creation)
            // 2. Object is updateable (publicly accessible for updates)
            // 3. Object is not a system object (doesn't start with underscore)
            // 4. Object is either in our whitelist or not in our blacklist
            $whitelist = ['Account', 'Contact', 'Opportunity', 'Lead', 'Case']; 
            $blacklist = ['ActivityHistory', 'UserRecordAccess'];

            return $object->createable && 
                   $object->updateable && 
                   !str_starts_with($object->name, '_') &&
                   (in_array($object->name, $whitelist) || !in_array($object->name, $blacklist));
        });

        // Replace the original sobjects with the filtered list
        $result->sobjects = array_values($filtered_objects);

        $res = array(
            'error' => null,
            'error_description' => null,
            'data' => $result,
            'from' => 'getObjects'
        );

        return $res;
    } catch (\Exception $e) {
        $res = array(
            'error' => $e,
            'error_description' => esc_html__('Fatal error! Try again later', 'elemetix'),
            'data' => null,
            'from' => 'getObjects'
        );

        return $res;
    }
}

    public function PSEIFetchObjectRecords($table, $fields, $limit)
    {
        try {
            $fields = implode(',+', $fields);
            $results = $this->PSEIGetAccessToken();
            $access_token = $results->access_token;
            $url = $this->instance_url . '/services/data/v55.0/query/?q=SELECT+Id,+' . $fields . '+FROM+' . $table . '+LIMIT+' . $limit;
            error_log("======= FETCHING OBJECT RECORDS ===================" . "\n", 3, ABSPATH . 'error_log');
            error_log($url . "\n", 3, ABSPATH . 'error_log');
            
            $this->PSEISendDataToProductive('fetch_object_records');

            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            ); 

            $response = wp_remote_get_with_cache($url, $args);
    
            $result = wp_remote_retrieve_body($response);
            $result = json_decode($result);

            error_log("=======  OBJECT RESULTS ===================" . "\n", 3, ABSPATH . 'error_log');
            error_log(json_encode($result) . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

            if (!isset($result->records)) {
                // return an an array with the error message
                $res = array(
                    'error' => $result[0]->errorCode,
                    'error_description' => $result->message,
                    'data' => null,
                    'from' => 'fetchObjectRecords'
                );

                return $res;
            }
            $res = array(
                'error' => null,
                'error_description' => null,
                'data' => $result->records,
                'from' => 'fetchObjectRecords'
            );

            return $res;
        } catch (\Exception $e) {
            $res = array(
                'error' => $e,
                'error_description' => 'Fatal error! Try again later',
                'data' => null,
                'from' => 'fetchObjectRecords'
            );
            return $res;
        }
    }

    public function PSEIFetchObjectFields($table)
    {
        try {
            $results = $this->PSEIGetAccessToken();
            $access_token = $results->access_token;
            $url = $this->instance_url . '/services/data/v55.0/sobjects/' . $table . '/describe';
  
            error_log("====== FETCH OBJECT FIELDS ========" . "\n", 3, ABSPATH . 'error_log');
            error_log($url . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

            $this->PSEISendDataToProductive('fetch_object_fields');

            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            ); 
            $response = wp_remote_get_with_cache($url, $args);
            $result = wp_remote_retrieve_body($response);
            $result = json_decode($result);

            error_log("======= FETCH OBJECT FIELDS RESULTS =======" . "\n", 3, ABSPATH . 'error_log');

            error_log(json_encode($result) . "\n", 3, ABSPATH . 'error_log');

            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

            if (!isset($result->fields)) {
                // return an array with the error message
                $res = array(
                    'error' => is_object($result) ? $result : 'Unknown error',
                    'error_description' => is_object($result) && isset($result->message) ? $result->message : 
                                          (is_array($result) && isset($result[0]->message) ? $result[0]->message : 'Unknown error occurred'),
                    'data' => null,
                    'from' => 'fetchObjectFields'
                );
            
                return $res;
            }

            $res = array(
                'error' => null,
                'error_description' => null,
                'data' => $result->fields,
                'from' => 'fetchObjectFields'
            );

            return $res;
        } catch (\Exception $e) {
            $res = array(
                'error' => $e,
                'error_description' => 'Fatal error! Try again later',
                'data' => null,
                'from' => 'fetchObjectFields'
            );
            return $res;
        }
    }

    public function PSEILogin($phone_number) {
        try {
            $login_table = get_option('psei_salesforce_login_table');
    
            $results = $this->PSEIGetAccessToken();
            if (!$results || !isset($results->access_token)) {
                error_log("Failed to get access token. Results: " . print_r($results, true));
                return array(
                    'error' => 'ACCESS_TOKEN_ERROR',
                    'error_description' => 'Failed to get access token',
                    'data' => null,
                    'from' => 'login'
                );
            }
            $access_token = $results->access_token;    
            $url = $this->instance_url . '/services/data/v55.0/query/?q=SELECT+Id+FROM+' . $login_table . '+WHERE+Phone+=+\'' . $phone_number . '\'';
    
            $this->PSEISendDataToProductive('login');
    
            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            ); 
            $response = wp_remote_get($url, $args);
            
            if (is_wp_error($response)) {
                error_log("WP_Error in API call: " . $response->get_error_message());
                return array(
                    'error' => 'API_CALL_ERROR',
                    'error_description' => $response->get_error_message(),
                    'data' => null,
                    'from' => 'login'
                );
            }
    
            $result = wp_remote_retrieve_body($response);
    
            $result = json_decode($result);
    
            if (!$result) {
                error_log("Failed to decode JSON response");
                return array(
                    'error' => 'JSON_DECODE_ERROR',
                    'error_description' => 'Failed to decode API response',
                    'data' => null,
                    'from' => 'login'
                );
            }
    
            if (!isset($result->records)) {
                return array(
                    'error' => isset($result->errorCode) ? $result->errorCode : 'UNKNOWN_ERROR',
                    'error_description' => isset($result->message) ? $result->message : 'Unknown error occurred',
                    'data' => null,
                    'from' => 'login'
                );
            }
    
            return array(
                'error' => null,
                'error_description' => null,
                'data' => $result->records,
                'from' => 'login'
            );
        } catch (\Exception $e) {
            error_log("Exception in PSEILogin: " . $e->getMessage());
            return array(
                'error' => 'EXCEPTION',
                'error_description' => $e->getMessage(),
                'data' => null,
                'from' => 'login'
            );
        }
    }


    public function PSEIFetchUserProfileRecords($fields, $AccountId)
    {
        try {
            if ($fields != 'FIELDS(all)') {
                $fields = implode(',', $fields);
            }

            // $fields = implode(',', []);
            $results = $this->PSEIGetAccessToken();
            $access_token = $results->access_token;
            $table = get_option('psei_salesforce_login_table');
            $url = $this->instance_url.'/services/data/v55.0/query/?q=SELECT+'. $fields .'+FROM+'. $table .'+WHERE+Id+=+\''.$AccountId.'\'';
            // $table = 'User';
            // $url = $this->instance_url . '/services/data/v55.0/query/?q=SELECT+FIELDS(all)+FROM+' . $table . '+WHERE+Id+=+\'' . $AccountId . '\'';
            error_log("============= PROFILE REQUEST ====================" . "\n", 3, ABSPATH . 'error_log');
            error_log(json_encode($url) . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

            $this->PSEISendDataToProductive('fetch_user_profile_records');

            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            ); 

            $response = wp_remote_get_with_cache($url, $args);
            $result = wp_remote_retrieve_body($response);
            $result = json_decode($result);

            error_log("============= PROFILE RESPONSE ====================" . "\n", 3, ABSPATH . 'error_log');
            error_log(json_encode($response) . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');
            if (!isset($result->records)) {
                // return an an array with the error message
                $res = array(
                    // 'error' => $result[0]['errorCode'],
                    'error' => 'Error Code',
                    'error_description' => $result->message,
                    'data' => null,
                    'from' => 'fetchObjectRecords'
                );

                return $res;
            }
            $res = array(
                'error' => null,
                'error_description' => null,
                'data' => $result->records,
                'from' => 'fetchObjectRecords'
            );

            

            return $res;
        } catch (\Exception $e) {
            $res = array(
                'error' => $e,
                'error_description' => 'Fatal error! Try again later',
                'data' => null,
                'from' => 'fetchObjectRecords'
            );
            return $res;
        }
    }

    public function PSEIUpdateProfile($id, $data)
    {
        try {

            $results = $this->PSEIGetAccessToken();
            $access_token = $results->access_token;
            $table = get_option('psei_salesforce_login_table');
            $url = $this->instance_url . '/services/data/v55.0/sobjects/' . $table . '/' . $id;

            $this->PSEISendDataToProductive('update_profile');

            $response = wp_remote_get_with_cache($url, $args);
            $result = wp_remote_retrieve_body($response);
            $result = json_decode($result);

            error_log("==== PROFILE RESPONE === " . json_encode($result) . "\n", 3, ABSPATH . 'error_log');

            if (!isset($result->success)) {
                // return an array with the error message
                $res = array(
                    'error' => $result,
                    'error_description' => $result[0]->message,
                    'data' => null,
                    'from' => 'updateProfile'
                );

                return $res;
            }
            $res = array(
                'error' => null,
                'error_description' => null,
                'data' => $result,
                'from' => 'updateProfile'
            );

            
            return $res;
        } catch (\Exception $e) {
            $res = array(
                'error' => $e,
                'error_description' => 'Fatal error! Try again later',
                'data' => null,
                'from' => 'updateProfile'
            );
            return $res;
        }
    }

    public function PSEICreateNewSObjectRecord($table, $data)
    {
        try {
            $results = $this->PSEIGetAccessToken();
            $access_token = $results->access_token;
            $url = $this->instance_url . '/services/data/v55.0/sobjects/' . $table;

            error_log(" == POST URL ==" . "\n", 3, ABSPATH . 'error_log');
            error_log(json_encode($url) . "\n", 3, ABSPATH . 'error_log');
            error_log("==============" . "\n", 3, ABSPATH . 'error_log');

            $this->PSEISendDataToProductive('create_new_sobject_record');


            $response = wp_remote_get_with_cache($url, $args);
            $result = wp_remote_retrieve_body($response);
            
            $result = json_decode($result);

            error_log(" == POST Response ==" . "\n", 3, ABSPATH . 'error_log');
            error_log(json_encode($result) . "\n", 3, ABSPATH . 'error_log');
            error_log("==============" . "\n", 3, ABSPATH . 'error_log');
            if (!isset($result->success)) {
                // return an array with the error message
                $res = array(
                    'error' => $result,
                    'error_description' => $result[0]->message,
                    'data' => null,
                    'from' => 'New SF Object'
                );

                return $res;
            }
            $res = array(
                'error' => null,
                'error_description' => null,
                'data' => $result,
                'from' => 'updateProfile'
            );

            
            return $res;
        } catch (\Exception $e) {
            $res = array(
                'error' => $e,
                'error_description' => 'Fatal error! Try again later',
                'data' => null,
                'from' => 'updateProfile'
            );
            return $res;
        }
    }

    public function PSEIFetchUserOrderRecords($fields, $AccountId, $limit, $table)
    {
        error_log("Fetch User Records" . "\n", 3, ABSPATH . 'error_log');
        error_log("Fields = " . json_encode($fields) . "\n", 3, ABSPATH . 'error_log');

        try {
            // =======  ORIGINAL. UNCOMMENT THIS ==============
            $fields = implode(',', $fields);
            $results = $this->PSEIGetAccessToken();
            $access_token = $results->access_token;

            $url = $this->instance_url.'/services/data/v55.0/query/?q=SELECT+'. $fields .'+FROM+'. $table .'+WHERE+Id+=+\''.$AccountId.'\'+LIMIT+'. $limit .'';

            // ================================================

            error_log("Fetch User Records URL ==" . "\n", 3, ABSPATH . 'error_log');
            error_log($url . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

            $this->PSEISendDataToProductive('fetch_user_order_records');

            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            ); 

            $response = wp_remote_get_with_cache($url, $args);
            $result = wp_remote_retrieve_body($response);

            $result = json_decode($result);

            if (!isset($result->records)) {

                error_log("Order Results ==" . "\n", 3, ABSPATH . 'error_log');
                error_log(json_encode($result) . "\n", 3, ABSPATH . 'error_log');
                error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

                // return an an array with the error message
                $res = array(
                    'error' => $result[0]->errorCode,
                    'error_description' => $result[0]->message,
                    'data' => null,
                    'from' => 'fetchObjectRecords'
                );

                return $res;
            }
            if ($result->totalSize == 0) {
                $res = array(
                    'error' => 'NO_RECORDS_ERROR',
                    'error_description' => 'No records found',
                    'data' => null,
                    'from' => 'fetchObjectRecords'
                );
                return $res;
            }
            $res = array(
                'error' => null,
                'error_description' => null,
                'data' => $result->records,
                'from' => 'fetchObjectRecords'
            );
            return $res;
        } catch (\Exception $e) {
            $res = array(
                'error' => $e,
                'error_description' => 'Fatal error! Try again later',
                'data' => null,
                'from' => 'fetchObjectRecords'
            );
            return $res;
        }
    }

    public function PSEIGetReports() {
        try {
            $results = $this->PSEIGetAccessToken();
            if ( ! $results || ! isset( $results->access_token ) ) {
                return array(
                    'error' => 'ACCESS_TOKEN_ERROR',
                    'error_description' => 'Failed to get access token',
                    'data' => null,
                );
            }
            $access_token = $results->access_token;
    
            // SOQL query to fetch all reports
            $query = "SELECT Id, Name, Description, Format FROM Report ORDER BY Name ASC";
            $url = $this->instance_url . "/services/data/v61.0/query/?q=" . urlencode( $query );

            $this->PSEISendDataToProductive( 'get_reports' );
    
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            );
    
            $response = wp_remote_get( $url, $args );
    
            if ( is_wp_error( $response ) ) {
                return array(
                    'error' => 'API_CALL_ERROR',
                    'error_description' => $response->get_error_message(),
                    'data' => null,
                );
            }
    
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );
    
            if ( ! $data || !isset( $data->records ) ) {
                return array(
                    'error' => 'INVALID_RESPONSE',
                    'error_description' => 'Invalid response from Salesforce API',
                    'data' => null,
                );
            }
    
            return array(
                'error' => null,
                'error_description' => null,
                'data' => $data->records,
            );
    
        } catch ( \Exception $e ) {
            return array(
                'error' => 'EXCEPTION',
                'error_description' => $e->getMessage(),
                'data' => null,
            );
        }
    }

    public function PSEIFetchReportRecords( $reportId, $parameters = []) {
        try {
            $results = $this->PSEIGetAccessToken();
            if ( ! $results || ! isset( $results->access_token ) ) {
                return array(
                    'error' => 'ACCESS_TOKEN_ERROR',
                    'error_description' => 'Failed to get access token',
                    'data' => null,
                );
            }
            $access_token = $results->access_token;

            $url = $this->instance_url . "/services/data/v61.0/analytics/reports/{$reportId}";

            $this->PSEISendDataToProductive( 'fetch_report_records' );

            // If user_id is provided, add it to the report filters
            if ( isset( $parameters[ 'user_id' ] ) ) {
                $url .= "/instances";
                $post_data = json_encode( [
                    "reportMetadata" => [
                        "reportFilters" => [
                            [
                                "column" => "USER_ID",  
                                "operator" => "equals",
                                "value" => $parameters[ 'user_id' ]
                            ]
                        ]
                    ]
                ]);
                $method = 'POST';
            } else {
                $post_data = null;
                $method = 'GET';
            }

            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
                'body' => $post_data,
                'method' => $method
            );

            $response = wp_remote_request( $url, $args );

            if ( is_wp_error( $response ) ) {
                return array(
                    'error' => 'API_CALL_ERROR',
                    'error_description' => $response->get_error_message(),
                    'data' => null,
                );
            }

            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );

            if ( ! $data ) {
                return array(
                    'error' => 'INVALID_RESPONSE',
                    'error_description' => 'Invalid response from Salesforce API',
                    'data' => null,
                );
            }

            return array(
                'error' => null,
                'error_description' => null,
                'data' => $data,
            );

        } catch ( \Exception $e ) {
            return array(
                'error' => 'EXCEPTION',
                'error_description' => $e->getMessage(),
                'data' => null,
            );
        }
    }

    public function PSEIFetchSObjectRelationships($table)
    {

        try {

            // $url = $this->instance_url.'/services/data/v55.0/query/?q=SELECT+'. $fields .'+FROM+'. $table .'+WHERE+Id+=+\''.$AccountId.'\'+LIMIT+'. $limit .'';
            $url = $this->instance_url . "/services/data/v55.0/sobjects/$table/describe";
            $results = $this->PSEIGetAccessToken();
            $access_token = $results->access_token;

            // ================================================

            error_log("Describe" . $table . " URL \n", 3, ABSPATH . 'error_log');
            error_log($url . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

            $this->PSEISendDataToProductive('fetch_sobject_relationship');

            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            ); 
            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-type' => 'application/json'
                ),
            ); 

            $response = wp_remote_get_with_cache($url, $args);
            $result = wp_remote_retrieve_body($response);
            $result = json_decode($result);

            $relationships = [];
            foreach ($result->fields as $field) {
                if ($field->relationshipName) {
                    array_push(
                        $relationships,
                        [
                            'name' => $field->name,
                            'label' => $field->label,
                            'relationshipName' => $field->relationshipName,
                        ]
                    );
                }
            }

            error_log("Describe Results" . $table . " URL \n", 3, ABSPATH . 'error_log');
            error_log(json_encode($relationships) . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');

            return $relationships;

        } catch (\Exception $e) {
            $res = array(
                'error' => $e,
                'error_description' => 'Fatal error! Try again later',
                'data' => null,
                'from' => 'fetchObjectRecords'
            );
            return $res;
        }
    }


    protected function PSEISendDataToProductive($request_type){
        wp_remote_post('http://sfusers.productivedev.co/api/v1/addData', array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'email' => $this->username,
                'request_type' => $request_type
            )),
        ));
    }
}