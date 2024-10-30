<?php 
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
    require_once( PSEI_INCLUDES_PATH . 'salesforce.php' );

    use Productive_Salesforce_Elementor_Integration\Salesforce;

    $error = null;
    $token = get_option('psei_salesforce_token');
    $instance_url = get_option('psei_salesforce_instance_url');
    $user_name = get_option('psei_salesforce_username');
    $password = get_option('psei_salesforce_password');
    $client_id = get_option('psei_salesforce_client_id');
    $client_secret = get_option('psei_salesforce_client_secret');
    $isDataFilled = $token && $instance_url && $user_name && $password && $client_id && $client_secret;
    $sObjects = [];
    if ($isDataFilled) {
        $salesforce = Salesforce::instance(
            $client_id, 
            $client_secret, 
            $user_name, 
            $password, 
            $token, 
            $instance_url,
            ''
        );
        $results = $salesforce->PSEIGetObjects();
        error_log("====== SF Settings RESPONSE =================" . "\n", 3, ABSPATH . 'error_log');
        error_log(json_encode($results) . "\n", 3, ABSPATH . 'error_log');
        error_log("==========================" . "\n", 3, ABSPATH . 'error_log');
        if ($results['error']) {
            $error = $results['error_description'].'. Tables could not be fetched.';

           
        } else {
            $res = $results['data'];
            $response = $res->sobjects;
            $sObjects = array_column($response, 'name');
        }
    }

?>
<?php if($error): ?>
    <div class="error-message">
        <p class="exad-el-title"><?php printf(
       esc_html__( '%s.', 'elemetix' ),
       esc_html($error)
 ); ?></p>
    </div>
<?php endif; ?>
<div id="apikeys">
    <div class="exad-row">
        <div class="exad-full-width">
            <div class="exad-dashboard-text-container">

                <div class="exad-dashboard-text">
                    
                    <div class="exad-dashboard-text-title">
                        <p class="exad-el-title"><?php echo esc_html__( 'Salesforce Instance Url', 'elemetix' ); ?></p>
                    </div>
                    <div class="exad-dashboard-text-label">
                        <input type="text" class="exad-dashboard-tab-input" id="salesforce-instance-url" placeholder="<?php echo esc_html__( 'Salesforce Instance Url', 'elemetix' ); ?>" name="salesforce_instance_url" value="<?php echo esc_html(get_option('psei_salesforce_instance_url')); ?>">
                        <label for="Salesforce Instance Url"></label>
                    </div>
                    <div class="exad-dashboard-text-title">
                        <p class="exad-el-title"><?php echo esc_html__( 'Salesforce Username', 'elemetix' ); ?></p>
                    </div>
                    <div class="exad-dashboard-text-label">
                        <input type="text" class="exad-dashboard-tab-input" id="salesforce-username" placeholder="<?php echo esc_html__( 'Salesforce Username', 'elemetix' ); ?>" name="salesforce_username" value="<?php echo esc_html(get_option('psei_salesforce_username')); ?>">
                        <label for="Salesforce Username"></label>
                    </div>
                    <div class="exad-dashboard-text-title">
                        <p class="exad-el-title"><?php echo esc_html__( 'Salesforce Password', 'elemetix' ); ?></p>
                    </div>
                    <div class="exad-dashboard-text-label">
                        <input type="password" class="exad-dashboard-tab-input" id="salesforce-password" placeholder="<?php echo esc_html__( 'Salesforce Password', 'elemetix' ); ?>" name="salesforce_password" value="<?php echo esc_html(get_option('psei_salesforce_password')); ?>">
                        <label for="Salesforce Password"></label>
                    </div>
                    <div class="exad-dashboard-text-title">
                        <p class="exad-el-title"><?php echo esc_html__( 'Salesforce Client ID', 'elemetix' ); ?></p>
                    </div>
                    <div class="exad-dashboard-text-label">
                        <input type="text" class="exad-dashboard-tab-input" id="salesforce-client-id" placeholder="<?php echo esc_html__( 'Salesforce Customer Key', 'elemetix' ); ?>" name="salesforce_client_id" value="<?php echo esc_html(get_option('psei_salesforce_client_id')); ?>">
                        <label for="Salesforce Customer Key"></label>
                    </div>

                    <div class="exad-dashboard-text-title">
                        <p class="exad-el-title"><?php echo esc_html__( 'Salesforce Client Secret', 'elemetix' ); ?></p>
                    </div>
                    <div class="exad-dashboard-text-label">
                        <input type="password" class="exad-dashboard-tab-input" id="salesforce-client-secret" placeholder="<?php echo esc_html__( 'Salesforce Customer Secret', 'elemetix' ); ?>" name="salesforce_client_secret" value="<?php echo esc_html(get_option('psei_salesforce_client_secret')); ?>">
                        <label for="Salesforce Customer Secret"></label>
                    </div>

                    <div class="exad-dashboard-text-title">
                        <p class="exad-el-title"><?php echo esc_html__( 'Salesforce Security Token', 'elemetix' ); ?></p>
                    </div>
                    <div class="exad-dashboard-text-label">
                        <input type="text" class="exad-dashboard-tab-input" id="salesforce-token" placeholder="<?php echo esc_html__( 'Salesforce Security Token', 'elemetix' ); ?>" name="salesforce_token" value="<?php echo esc_html(get_option('psei_salesforce_token')); ?>">
                        <label for="Salesforce Security Token"></label>
                    </div>

                    <div class="exad-dashboard-text-title">
                        <p class="exad-el-title"><?php echo esc_html__( 'Salesforce Login Table', 'elemetix' ); ?></p>
                    </div>
                    <?php if($isDataFilled): ?>
                    <div class="exad-dashboard-text-label">
                        <select class="exad-dashboard-tab-input" id="salesforce-login-table" name="salesforce_login_table">
                            <option value=""><?php echo esc_html__( 'Select Salesforce Object', 'elemetix' ); ?></option>
                            <?php foreach($sObjects as $sObject): ?>
                                <option value="<?php echo $sObject; ?>" <?php echo $sObject == get_option('psei_salesforce_login_table') ? 'selected' : '' ?> ><?php echo $sObject; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="Salesforce Login Table"></label>
                    </div>
                    <?php else: ?>
                        <div class="warning">
                            <p class="exad-el-title"><?php echo esc_html__( 'Please fill all the fields above to get the login table', 'elemetix' ); ?></p>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
            </div>
        </div>
        
    </div>
</div>