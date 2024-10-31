<?php
	//load classes init method
	add_action('init', array('PMProGateway_paytm', 'init'));

	/**
	 * PMProGateway_paytm Class
	 */
	 
	class PMProGateway_paytm extends PMProGateway
	{
		function __construct($gateway = NULL)
		{
			$this->gateway = $gateway;
			return $this->gateway;
		}											

		/**
		 * Run on WP init
		 *
		 * @since 1.8
		 */
		static function init()
		{
			//make sure paytm is a gateway option
			add_filter('pmpro_gateways', array('PMProGateway_paytm', 'pmpro_gateways'));

			//add fields to payment settings
			add_filter('pmpro_payment_options', array('PMProGateway_paytm', 'pmpro_payment_options'));
			add_filter('pmpro_payment_option_fields', array('PMProGateway_paytm', 'pmpro_payment_option_fields'), 10, 2);
                        
			add_action('wp_ajax_nopriv_pmpro_capture_paytm_response', array('PMProGateway_paytm', 'pmpro_capture_paytm_response'));
			add_action('wp_ajax_pmpro_capture_paytm_response', array('PMProGateway_paytm', 'pmpro_capture_paytm_response')); 
			
			$gateway = pmpro_getGateway();
			
			if($gateway == "paytm")
			{				
				add_filter('pmpro_include_payment_information_fields', '__return_false');
				add_filter('pmpro_required_billing_fields', array('PMProGateway_paytm', 'pmpro_required_billing_fields'));
				add_filter('pmpro_checkout_default_submit_button', array('PMProGateway_paytm', 'pmpro_checkout_default_submit_button'));
				add_filter('pmpro_checkout_before_change_membership_level', array('PMProGateway_paytm', 'pmpro_checkout_before_change_membership_level'), 10, 2);
			}
		}

		/**
		 * Make sure paytm is in the gateways list
		 *
		 * @since 1.8
		 */
		static function pmpro_gateways($gateways)
		{
			if(empty($gateways['paytm']))
				$gateways['paytm'] = __('Paytm Pay', 'paid-memberships-pro');

			return $gateways;
		}

		/**
		 * Get a list of payment options that the paytm gateway needs/supports.
		 *
		 * @since 1.8
		 */
		static function getGatewayOptions()
		{
			$options = array(
				'sslseal',
				'nuclear_HTTPS',
				'gateway_environment',
				'merchantID',
				'merchant_key',
				'industry_type_id',
				'paytm_channel_id',
				'website',
				'callbackurl',
				'currency',
				'use_ssl',
				'tax_state',
				'tax_rate'
			);

			return $options;
		}

		/**
		 * Set payment options for payment settings page.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_options($options)
		{
			//get paytm options
			$paytm_options = PMProGateway_paytm::getGatewayOptions();

			//merge with others.
			$options = array_merge($paytm_options, $options);

			return $options;
		}

		/**
		 * Display fields for paytm options.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_option_fields($values, $gateway)
		{
		?>
		<tr class="pmpro_settings_divider gateway gateway_iyzico" <?php if($gateway != "paytm") { ?>style="display: none;"<?php } ?>>
			<td colspan="2">
				<?php _e('Paytm Sttings', 'paid-memberships-pro' ); ?>
			</td>
		</tr>
		<tr class="gateway gateway_paytm" <?php if($gateway != "paytm") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="merchantID"><?php _e('Merchant ID', 'paid-memberships-pro' );?>:</label>
			</th>
			<td>
				<input type="text" id="merchantID" name="merchantID" size="60" value="<?php echo esc_attr($values['merchantID'])?>" />
				<br /><small><?php _e('This id(USER ID) available at "Generate Secret Key" of "Integration -> Card payments integration at paytm."');?></small>
			</td>
		</tr>
		<tr class="gateway gateway_paytm" <?php if($gateway != "paytm") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="merchant_key"><?php _e('Merchant Key', 'paid-memberships-pro' );?>:</label>
			</th>
			<td>
				<input type="text" id="merchant_key" name="merchant_key" size="60" value="<?php echo esc_attr($values['merchant_key'])?>" />
				<br /><small><?php _e('Given to Merchant by paytm.');?></small>
			</td>
		</tr>
		<tr class="gateway gateway_paytm" <?php if($gateway != "paytm") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="industry_type_id"><?php _e('Industry Type ID', 'paid-memberships-pro' );?>:</label>
			</th>
			<td>
				<input type="text" id="industry_type_id" name="industry_type_id" size="60" value="<?php echo esc_attr($values['industry_type_id'])?>" />
				<br /><small><?php _e('Given to Merchant by paytm');?></small>
			</td>
		</tr>
		<tr class="gateway gateway_paytm" <?php if($gateway != "paytm") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="paytm_channel_id"><?php _e('Channel ID', 'paid-memberships-pro' );?>:</label>
			</th>
			<td>
				<input type="text" id="paytm_channel_id" name="paytm_channel_id" size="60" value="<?php echo esc_attr($values['paytm_channel_id'])?>" />
				<br /><small><?php _e('WEB - for desktop websites / WAP - for mobile websites');?></small>
			</td>
		</tr>
		<tr class="gateway gateway_paytm" <?php if($gateway != "paytm") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="website"><?php _e('Website', 'paid-memberships-pro' );?>:</label>
			</th>
			<td>
				<input type="text" id="website" name="website" size="60" value="<?php echo esc_attr($values['website'])?>" />
				<br /><small><?php _e('Given to Merchant by paytm');?></small>
			</td>
		</tr>
		<tr class="gateway gateway_paytm" <?php if($gateway != "paytm") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="callbackurl"><?php _e('Set CallBack URL', 'paid-memberships-pro' );?>:</label>
			</th>
			<td>
				<select id="callbackurl" name="callbackurl">
					<option value="yes" <?php selected(pmpro_getOption('callbackurl'), 'yes');?>>Yes</option>
					<option value="no" <?php selected(pmpro_getOption('callbackurl'), 'no');?>>No</option>
				</select>
			</td>
		</tr>
		<?php
		}
		
		/**
		 * Remove required billing fields
		 *		 
		 * @since 1.8
		 */
		static function pmpro_required_billing_fields($fields)
		{
			unset($fields['bfirstname']);
			unset($fields['blastname']);
			unset($fields['baddress1']);
			unset($fields['bcity']);
			unset($fields['bstate']);
			unset($fields['bzipcode']);
			unset($fields['bphone']);
			//unset($fields['bemail']);
			unset($fields['bcountry']);
			unset($fields['CardType']);
			unset($fields['AccountNumber']);
			unset($fields['ExpirationMonth']);
			unset($fields['ExpirationYear']);
			unset($fields['CVV']);
			return $fields;
		}
		
		/**
		 * Swap in our submit buttons.
		 *
		 * @since 1.8
		 */
		static function pmpro_checkout_default_submit_button($show)
		{
			global $gateway, $pmpro_requirebilling;
			//show our submit buttons
			?>			
			<span id="pmpro_submit_span">
				<input type="hidden" name="submit-checkout" value="1" />		
				<input type="submit" class="pmpro_btn pmpro_btn-submit-checkout" value="<?php if($pmpro_requirebilling) { _e('Pay with paytm', 'paid-memberships-pro' ); } else { _e('Submit and Confirm', 'paid-memberships-pro' );}?> &raquo;" />	
                               
			</span>
		<?php
			//don't show the default
			return false;
		}
		
		/**
		 * Instead of change membership levels, send users to Paytm pay.
		 *
		 * @since 1.8
		 */
		static function pmpro_checkout_before_change_membership_level($user_id, $morder)
		{
			global $wpdb, $discount_code_id;
			
			//if no order, no need to pay
			if(empty($morder))
				return;
			
			$morder->user_id = $user_id;
			$morder->payment_type = "Paytm pay";
			$morder->saveOrder();
			
			//save discount code use
			if(!empty($discount_code_id))
				$wpdb->query("INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . $discount_code_id . "', '" . $user_id . "', '" . $morder->id . "', now())");	
			
			do_action("pmpro_before_send_to_paytm", $user_id, $morder);
			
			$morder->Gateway->sendToPaytm($morder);
		}

		function process(&$order)
		{						
			if(empty($order->code))
				$order->code = $order->getRandomCode();			
			
			//clean up a couple values
			$order->payment_type = "Paytm Pay";
			$order->CardType = "";
			
			
			//just save, the user will go to paytm to pay
			$order->status = "review";														
			$order->saveOrder();
			return true;			
		}
		
		
                function sendToPaytm(&$order)
		{
                       
			//echo "<pre>"; print_r($order); die();
			global $pmpro_currency;		
			if(empty($order->code))
					$order->code = $order->getRandomCode();
			
			
			require_once 'function.php';                
			$txnDate=date('Y-m-d');			
            $milliseconds = (int) (1000 * (strtotime(date('Y-m-d'))));
			
			$amount = $order->InitialPayment;
			$amount = round((float)$amount);
			
			$paytm_merchant_id = trim(get_option('pmpro_merchantID'));
			$paytm_merchant_key = trim(get_option('pmpro_merchant_key'));
			$order_id = $order->id;
			$paytm_website = trim(get_option('pmpro_website'));
			$paytm_channel_id = trim(get_option('pmpro_paytm_channel_id'));
			$paytm_industry_type_id = trim(get_option('pmpro_industry_type_id'));
			$callbackurl = trim(get_option('pmpro_callbackurl'));
			$gateway_environment = get_option('pmpro_gateway_environment');
                        $txntype='1';
                        $ptmoption='1';
                        $purpose="1";
                        $productDescription='paytmpay';
                         $ip=$_SERVER['REMOTE_ADDR'];
			
                         $post_params = Array(
                            "MID" => $paytm_merchant_id,
                            "ORDER_ID" => $order_id,
                            "CUST_ID" => $order->Email,
                            "TXN_AMOUNT" => $amount,
                            "CHANNEL_ID" => $paytm_channel_id,
                            "INDUSTRY_TYPE_ID" => $paytm_industry_type_id,
                            "WEBSITE" => $paytm_website,
                            "EMAIL" => $order->Email,
                            "MOBILE_NO" => $order->billing->phone
                            );
			if($callbackurl == 'yes')
                        {
                                $post_params["CALLBACK_URL"] = admin_url("admin-ajax.php") . "?action=pmpro_capture_paytm_response";
                        }	

			$checksum = pmproPaytm_getChecksumFromArray($post_params, $paytm_merchant_key);
			
			$paytm_args = array(
                            'merchantID' => $paytm_merchant_id,
                            'orderId' => $order_id,
                            'returnUrl' => admin_url('admin-ajax.php') . '?action=pmpro_capture_paytm_response',
                            'buyerEmail' => $order->Email,
                            'buyerFirstName' => $order->FirstName,
                            'buyerLastName' => $order->LastName,
                            'buyerAddress' => $order->billing->street,
                            'buyerCity' => $order->billing->city,
                            'buyerState' => $order->billing->state,
                            'buyerCountry' => $order->billing->country,
                            'buyerPincode' => $order->billing->zip,
                            'buyerPhoneNumber' => $order->billing->phone,
                            'txnType' => $txntype,
                            'ptmoption' => $ptmoption,
                            'mode' => $gateway_environment,
                            'currency' => $pmpro_currency,
                            'amount' => $amount,
                            'merchantIpAddress' => $ip,
                            'purpose' => $purpose,
                            'productDescription' => $productDescription,
                            'txnDate' =>  $txnDate,
                            'checksum' => $checksum
			);
                        
                        foreach($paytm_args as $name => $value) {
                            if($name != 'checksum') {
                                if ($name == 'returnUrl') {
                                    $value = $value;

                                } else {
                                $value = $value;

                                }
                            }
                        }

			
                        $paytm_args_array = array();

                        $paytm_args_array[] = "<input type='hidden' name='MID' value='".  $paytm_merchant_id ."' />";
                        $paytm_args_array[] = "<input type='hidden' name='ORDER_ID' value='".  $order_id ."'/>";
                        $paytm_args_array[] = "<input type='hidden' name='WEBSITE' value='".$paytm_website."'/>";
                        $paytm_args_array[] = "<input type='hidden' name='INDUSTRY_TYPE_ID' value='".$paytm_industry_type_id."'/>";
                        $paytm_args_array[] = "<input type='hidden' name='CHANNEL_ID' value='".$paytm_channel_id."'/>";
                        $paytm_args_array[] = "<input type='hidden' name='TXN_AMOUNT' value='".  $amount ."'/>";
                        $paytm_args_array[] = "<input type='hidden' name='CUST_ID' value='".$order->Email."'/>";
                        $paytm_args_array[] = "<input type='hidden' name='EMAIL' value='".$order->Email."'/>";
                        $paytm_args_array[] = "<input type='hidden' name='MOBILE_NO' value='".$order->billing->phone."'/>";
			
                        if($callbackurl == 'yes')
                            {
                                    $call = admin_url("admin-ajax.php") . "?action=pmpro_capture_paytm_response";
                                    $paytm_args_array[] = "<input type='hidden' name='CALLBACK_URL' value='" . $call . "'/>";
                            }

                            $paytm_args_array[] = "<input type='hidden' name='txnDate' value='". date('Y-m-d H:i:s') ."'/>";
                            $paytm_args_array[] = "<input type='hidden' name='CHECKSUMHASH' value='". $checksum ."'/>";
                
                if($gateway_environment == "sandbox")
                {
						$action_url = "https://securegw-stage.paytm.in/order/process";
                }
                else
                {
                        $action_url = "https://securegw.paytm.in/order/process";
                }
                
                echo '<form action="'.$action_url.'" method="post" id="paytm_payment_form" name="gopaytm">
                            ' . implode('', $paytm_args_array) . '
                                <script type="text/javascript">
                                    document.gopaytm.submit();
                                 </script>
                    </form>';
                
                exit();
		}
			
		
		
		function pmpro_capture_paytm_response(){
                        //echo "<pre>"; print_r($_POST); die();
			if(isset($_POST['ORDERID']) && isset($_POST['RESPCODE'])){
			    $order_id = sanitize_text_field($_POST['ORDERID']);
			    $responseDescription = sanitize_text_field($_POST['RESPMSG']);
                            
                            
				if($_POST['RESPCODE'] == 01) {
                                    
				$payment_id = sanitize_text_field($_POST['TXNID']);    
				$morder = new MemberOrder($order_id);
				$morder->getMembershipLevel();
				$morder->getUser();
				$morder->status = $responseDescription;
				$morder->payment_transaction_id = $payment_id;
				$morder->saveOrder();
			
				//filter for level
				$morder->membership_level = apply_filters("pmpro_inshandler_level", $morder->membership_level, $morder->user_id);

				//set the start date to current_time('mysql') but allow filters (documented in preheaders/checkout.php)
				$startdate = apply_filters("pmpro_checkout_start_date", "'" . current_time('mysql') . "'", $morder->user_id, $morder->membership_level);

				//fix expiration date
				if(!empty($morder->membership_level->expiration_number))
				{
					$enddate = "'" . date_i18n("Y-m-d", strtotime("+ " . $morder->membership_level->expiration_number . " " . $morder->membership_level->expiration_period, current_time("timestamp"))) . "'";
				}
				else
				{
					$enddate = "NULL";
				}

				//filter the enddate (documented in preheaders/checkout.php)
				$enddate = apply_filters("pmpro_checkout_end_date", $enddate, $morder->user_id, $morder->membership_level, $startdate);

				//get discount code
				$morder->getDiscountCode();
				if(!empty($morder->discount_code))
				{
					//update membership level
					$morder->getMembershipLevel(true);
					$discount_code_id = $morder->discount_code->id;
				}
				else
					$discount_code_id = "";

				

				//custom level to change user to
				$custom_level = array(
					'user_id' => $morder->user_id,
					'membership_id' => $morder->membership_level->id,
					'code_id' => $discount_code_id,
					'initial_payment' => $morder->membership_level->initial_payment,
					'billing_amount' => $morder->membership_level->billing_amount,
					'cycle_number' => $morder->membership_level->cycle_number,
					'cycle_period' => $morder->membership_level->cycle_period,
					'billing_limit' => $morder->membership_level->billing_limit,
					'trial_amount' => $morder->membership_level->trial_amount,
					'trial_limit' => $morder->membership_level->trial_limit,
					'startdate' => $startdate,
					'enddate' => $enddate
					);

				

				if( pmpro_changeMembershipLevel($custom_level, $morder->user_id) !== false ) {
					//update order status and transaction ids
					$morder->status = "success";
					$morder->payment_transaction_id = $payment_id;
					
					$morder->saveOrder();

					//add discount code use
					if(!empty($discount_code) && !empty($use_discount_code))
					{
						$wpdb->query("INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . $discount_code_id . "', '" . $morder->user_id . "', '" . $morder->id . "', '" . current_time('mysql') . "')");
					}

					//save first and last name fields
					if(!empty($_POST['first_name']))
					{
						$old_firstname = get_user_meta($morder->user_id, "first_name", true);
						if(!empty($old_firstname))
							update_user_meta($morder->user_id, "first_name", $_POST['first_name']);
					}
					if(!empty($_POST['last_name']))
					{
						$old_lastname = get_user_meta($morder->user_id, "last_name", true);
						if(!empty($old_lastname))
							update_user_meta($morder->user_id, "last_name", $_POST['last_name']);
					}

					//hook
					//do_action("pmpro_after_checkout", $morder->user_id);

					//setup some values for the emails
					if(!empty($morder))
						$invoice = new MemberOrder($morder->id);
					else
						$invoice = NULL;

					$user = get_userdata($morder->user_id);
					if(empty($user))
						return false;

					$user->membership_level = $morder->membership_level;		//make sure they have the right level info

					//send email to member
					$pmproemail = new PMProEmail();
					$pmproemail->sendCheckoutEmail($user, $invoice);

					//send email to admin
					$pmproemail = new PMProEmail();
					$pmproemail->sendCheckoutAdminEmail($user, $invoice);
			
					}
                            
				} else {
                                        $old_order = new MemberOrder();
                                        //hook to do other stuff when payments fail
                                        do_action( "pmpro_subscription_payment_failed", $old_order );
                                        

                                        //create a blank order for the email
                                        $morder          = new MemberOrder();
                                        $morder->user_id = $old_order->user_id;

                                        $user                   = new WP_User( $old_order->user_id );
                                        $user->membership_level = pmpro_getMembershipLevelForUser( $user->ID );

                                        // Email the user and ask them to update their credit card information
                                        $pmproemail = new PMProEmail();
                                        $pmproemail->sendBillingFailureEmail( $user, $morder );

                                        // Email admin so they are aware of the failure
                                        $pmproemail = new PMProEmail();
                                        $pmproemail->sendBillingFailureAdminEmail( get_bloginfo( "admin_email" ), $morder );
                                        //$order->status = "error";
                                        $morder->errorcode = $_POST['RESPCODE'];
                                        $morder->error = $responseDescription;
                                        
                                        print ( "Payment failed. Emails sent to " . $user->user_email . " and " . get_bloginfo( "admin_email" ) . "." );
                                        return true;
                                        
                                    }
					
					
		}
                $redirect = pmpro_url("confirmation", "?level=" . $morder->membership_level->id);

                if(!empty($redirect))
                    wp_redirect($redirect);
                exit;            
		
	}
}        