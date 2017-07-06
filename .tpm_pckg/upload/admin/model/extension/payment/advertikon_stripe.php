<?php
/**
 * Advertikon Stripe admin model
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 */

class ModelExtensionPaymentAdvertikonStripe extends Model {
	protected $a = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = Advertikon\Stripe\Advertikon::instance();
	}

	/**
	 * Creates all the extension's tables
	 * @return void
	 */
	public function create_tables() {

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->plan_table . "`
			(
				`id`            INT(11)      UNSIGNED AUTO_INCREMENT,
				`oc_plan_id`    VARCHAR(255),
				`sp_plan_id`    VARCHAR(255),
				`date_modified` TIMESTAMP,
				`plan`          TEXT,
				PRIMARY KEY(`id`),
				UNIQUE (`sp_plan_id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		);

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->profile_table . "`
			(
				`id`    INT(11)      UNSIGNED AUTO_INCREMENT,
				`name`  VARCHAR(255),
				PRIMARY KEY(`id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		); 

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->profile_value_table . "`
			(
				`id`          INT(11) UNSIGNED AUTO_INCREMENT,
				`profile_id`  INT     UNSIGNED,
				`property_id` VARCHAR(255),
				`value`       VARCHAR(255),
				PRIMARY KEY(`id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		); 

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->profile_map_table . "`
			(
				`id`         INT(11) UNSIGNED AUTO_INCREMENT,
				`profile_id` INT     UNSIGNED,
				`oc_plan_id` INT     UNSIGNED,
				UNIQUE(`oc_plan_id`),
				PRIMARY KEY(`id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		);

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->recurring_table . "`
			(
				`id`                 INT(11)       UNSIGNED AUTO_INCREMENT,
				`recurring_order_id` INT           UNSIGNED,
				`subscription_id`    VARCHAR( 100 ),
				`next`               TEXT,
				`total_tax`          TEXT,
				`shipping_tax`       TEXT,
				`invoices`           TEXT,
				UNIQUE(`recurring_order_id`),
				PRIMARY KEY(`id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		); 

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->customer_table . "`
			(
				`id`             INT(11)      UNSIGNED AUTO_INCREMENT,
				`oc_customer_id` INT(11)      UNSIGNED,
				`stripe_id`      VARCHAR(255),
				`date_added`     DATETIME,
				`date_modified`  TIMESTAMP,
				`description`    VARCHAR(255),
				`metadata`       TEXT,
				PRIMARY KEY(`id`),
				INDEX(`oc_customer_id`,`stripe_id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		);

		$q = $this->db->query( "SELECT COUNT(*) FROM `" . DB_PREFIX . $this->a->profile_table . "` WHERE `name` = 'Default'" );

		if ( $q && $q->row['COUNT(*)'] == 0 ) {
			$this->db->query( "INSERT INTO `" . DB_PREFIX . $this->a->profile_table . "` SET `name` = 'Default'" );
			$this->db->query( "INSERT INTO `" . DB_PREFIX . $this->a->profile_table . "` SET `name` = 'NonRecurring'" );
		}
	}

	/**
	 * Adds compatibility to the old versions
	 * @return boolean True if at least one record was fixed, false otherwise
	 */
	public function fix_db() {

		// Remove trailing underscore from CODE name
		$this->db->query(
			"UPDATE `" . DB_PREFIX . "setting` SET 
				`code` = SUBSTRING( `code`, 1, CHAR_LENGTH( `code` ) - 1 )
				WHERE `code` IN ('adk_stripe_', 'advertikon_stripe_' )"
		);

		if ( $this->db->countAffected() ) {
			$this->a->log( 'DB fix: CODE name was fixed', $this->a->log_debug_flag );
		}

		$q = $this->db->query( "SHOW TABLES LIKE '" . DB_PREFIX . $this->a->profile_value_table  . "'" );

		if ( $q->num_rows > 0 ) {

			// Profile property table was removed
			$this->db->query(
				"ALTER TABLE `" . DB_PREFIX . $this->a->profile_value_table . "` MODIFY `property_id` VARCHAR(255)"
			);

			if ( $this->db->countAffected() ) {
				foreach( array(
					1 =>'totals_to_recurring',
					2 => 'user_abort',
					3 => 'add_force',
					4 => 'price_options',
					5 => 'first_order',
					6 => 'cancel_now', ) as $from => $to ) {

					$this->db->query(
						"UPDATE `" . DB_PREFIX . $this->a->profile_value_table . "`
						SET `property_id` = '$to'
						WHERE `property_id` = '$from'"
					);

					if ( $this->db->countAffected() ) {
						$this->a->log( sprintf(
							'DB fix: name of profile value was fixed from "%s" to "%s"',
							$from, $to
						), $this->a->log_debug_flag );
					}
				}
			}
		}

		return true;
	}

	/**
	 * Validates configuration data
	 * @param array $errors
	 * @return boolean
	 */
	public function validate_configs() {
		global $adk_errors;

		$errors = &$adk_errors;

		try {

			// Check permissions to modify the extension's settings
			if ( ! $this->a->has_permissions( 'modify' ) ) {
				$errors['warning'][] = $this->a->__( 'You have no permissions to modify extension settings' );
				throw new Advertikon\Exception( '' );
			}

			// Strip whitespace from Stripe API keys
			foreach( $this->a->post( 'account' ) as $name => $account ) {
				foreach( $account as $key => $val ) {
					if ( in_array( $key, array(
							'test_public_key',
							'test_secret_key',
							'live_public_key',
							'live_secret_key',
						) ) ) {
						$this->request->post[ $this->a->prefix_name( 'account' ) ][ $name ][ $key ] = trim( $val );
					}
				}
			}

			// Geo zone
			if ( ! $this->a->post( 'geo_zone' ) ) {
					$errors['input_errors']['geo_zone'] = $this->a->__( 'At least one Geo-zone should be selected' );
			}

			// Payment system
			if ( ! $this->a->post( 'avail_systems' ) ) {
					$errors['input_errors']['avail_systems'] = $this->a->__( 'At least one payment system should be selected' );
			}

			// Store
			if ( ! $this->a->post( 'stores' ) ) {
					$errors['input_errors']['stores'] = $this->a->__( 'At least one store should be selected' );
			}

			// Customer groups
			if ( ! $this->a->post( 'customer_groups' ) ) {
					$errors['input_errors']['customer_groups'] = $this->a->__( 'At least one customer group should be selected' );
			}

			// Statement descriptor check
			if( $sd = $this->a->post( 'statement_descriptor' ) ) {
				if( preg_match( '/[^0-9A-Za-z]/', $sd ) ) {
					$errors['input_errors']['statement_descriptor'] = $this->a->__( 'Statement descriptor may consist only of alphanumeric symbols' );

				} elseif( strlen( $sd ) > 22 ) {
					$errors['input_errors']['statement_descriptor'] = $this->a->__( 'Statement descriptor may not be longer than 22 characters' );
				}
			}

			if ( ! isset( $errors['input_errors']['account'] ) ) {
				$errors['input_errors']['account'] = array();
			}

			foreach( $this->a->post( 'account' )  as $name => $data ) {
				$name = $this->a->strip_prefix( $name );

				if ( ! isset( $errors['input_errors']['account'][ $name ] ) ) {
					$errors['input_errors']['account'][ $name ] = array();
				}

				// Presence of test or live key pair depend on sandbox mode
				if ( $this->a->post( 'test_mode' ) ) {
					if ( empty( $data['test_secret_key'] ) )	{
						$errors['input_errors']['account'][ $name ]['test_secret_key'] = $this->a->__( 'Test Secret Key Required!' );
					}

					if ( empty( $data['test_public_key'] ) )	{
						$errors['input_errors']['account'][ $name ]['test_public_key'] = $this->a->__( 'Test Public Key Required!' );
					}

				} else {
					if ( empty( $data['live_secret_key'] ) )	{
						$errors['input_errors']['account'][ $name ]['live_secret_key'] = $this->a->__( 'Live Secret Key Required!' );
					}

					if ( empty( $data['live_public_key'] ) )	{
						$errors['input_errors']['account'][ $name ]['live_public_key'] = $this->a->__( 'Live Public Key Required!' );
					}
				}

				// Account name
				if ( empty( $data['account_name'] ) ) {
					$errors['input_errors']['account'][ $name ]['account_name'] = $this->a->__( 'Account name required!' );
				}
			}
			
			// Minimum total amount adjusting (if needed)
			$min_total = $this->a->post( 'total_min' ) ? $this->a->post( 'total_min' ) : 0;
			$correct_min_total = $this->a->check_min_amount( $min_total, $this->config->get( 'config_currency' ) );

			if ( true !== $correct_min_total ) {
				$this->request->post[ $this->a->prefix_name( 'total_min' ) ] = $this->currency->format(
					$correct_min_total,
					$this->config->get( 'config_currency' ),
					1,
					false
				);

				$errors['info'][] = $this->a->__( 'Minimum total amount has been adjusted to meet the Stripe requirements' );
			}

			$this->request->post[  $this->a->prefix_name( 'total_max' ) ] = is_numeric( $this->a->post( 'total_max' ) ) ? $this->a->post( 'total_max' ) : null;

			if ( null !== $this->a->post( 'total_max' ) ) {
				if ( $this->a->post( 'total_max' ) < $this->a->post( 'total_min' ) ) {
					$errors['input_errors']['total_max'] = $this->a->__( 'Maximum total amount cannot be lesser then minimum total amount' );
				}
			}

			// Title
			if ( ! $this->a->post( 'title' ) ) {
				$errors['input_errors']['title'] = $this->a->__( 'Specify the title of some sort' );
			}

			// Sandbox title in test mode
			if ( $this->a->post( 'test_mode' ) && ! $this->a->post( 'sandbox_title' ) ) {
				$errors['input_errors']['sandbox_title'] = $this->a->__( 'Specify the title for sandbox mode' );
			}

			// Charge description
			if ( ! $this->a->post( 'charge_description' ) ) {
				$errors['input_errors']['charge_description'] = $this->a->__( 'Specify description for charge' );
			}

			// Customer description
			if ( ! $this->a->post( 'customer_description' ) ) {
				$errors['input_errors']['customer_description'] = $this->a->__( 'Specify customer description' );
			}

			// Payment error template
			if ( $this->a->post( 'error_order_notification' ) ) {
				$template = $this->a->post( 'template' );

				if ( empty( $template[ $this->a->prefix_name( 'template_error_order' )] ) ) {
					$errors['input_errors']['template'][ $this->a->prefix_name( 'template_error_order' )] = $this->a->__( 'Template should be specified in case to sent notification' );
				}
			}

			// Done without errors
			if ( $this->a->is_empty( $errors['input_errors'] ) ) {
				unset( $_POST['template'] );
				$errors['success'] = true;

			//Got errors
			} else {
				$errors['warning'][] = $this->a->__( 'In order to continue you have to correct some data' );
				$errors ['success'] = false; 
			}

		} catch( Advertikon\Exception $e ) {
			$errors['success'] = false;
		} 

		return $errors['success'];
	}

	/**
     * Selects label class for payment card verification field to show at admin area
     * @param string $status 
     * @return string
     */
    public function get_check_class( $status ) {
    	$ret = 'label';

    	switch ( $status ){
    		case 'pass' :
    			$ret .= ' label-success';
    			break;
    		case 'fail' :
    			$ret .= ' label-danger';
    			break;
    		case 'unavailable' :
    		case 'unchecked' :
    			$ret .= ' label-default';
    			break;
    	}

    	return $ret;
    }

	/**
     * Convert Stripe plan to OpenCart representation
     * Will adjust currency
     * @param Object $sp_plan Stripe plan object
     * @return Array
     */
    public function convert_sp_plan_to_oc( $sp_plan ) {
       	$lang = $this->load->model( 'localisation/language' );
    	$langs = $this->model_localisation_language->getLanguages();
    	$lang_id = (string)$langs[ $this->config->get( 'config_admin_language' ) ]['language_id'];

    	return array(
    			'sort_order'			=> 0,
    			'status'				=> 1,
    			'price'					=> $this->currency->convert(
    				$this->a->cents_to_amount( $sp_plan->amount, $sp_plan->currency ),
    				strtoupper( $sp_plan->currency ),
    				$this->config->get( 'config_currency' )
    			),
    			'frequency'				=> $sp_plan->interval,
    			'duration'				=> 0,
    			'cycle'					=> $sp_plan->interval_count,
    			'trial_status'			=> (int)$sp_plan->trial_period_days,
    			'trial_price'			=> 0,
    			'trial_frequency'		=> 'day',
    			'trial_duration'		=> 1,
    			'trial_cycle'			=> $sp_plan->trial_period_days,
    			'recurring_description' => array(
    					$lang_id => array( 'name' => $sp_plan->name ),
    				),
    		);
    }

    /**
     * Get matching OC plans for specific Stripe plan
     * @param object $plan Stripe plan object
     * @param String $name Plan name. Optional
     * @return Array
     */
    public function get_matching_oc_plans( $plan, $name = '' ) {

    	// Get all the Stripe recurring plans
    	if ( $this->stripe_plan_all ) {
    		$advertikon_plans = $this->stripe_plan_all;

    	} else {
	    	$advertikon_plans = new Advertikon\Stripe\Resource\Plan();
			$advertikon_plans->actualize();
			$this->stripe_plan_all = $advertikon_plans->all();
    	}

    	// Get OC recurring plan
    	$name = (string)$name;
    	$cache_name = 'oc_plan_' . $name;

    	if ( $this->$cache_name ) {
    		$oc_plans = $this->$cache_name;

    	} else {
			$this->load->model( 'catalog/recurring' );
			$filter = array();

			if ( $name ) {
				$filter['filter_name'] = $name;
			}

			$oc_plans = $this->model_catalog_recurring->getRecurrings( $filter );
			$this->$cache_name = $oc_plans;
    	}

		$list = array();

		//Filter
		foreach( $oc_plans as $item ) {
			foreach( $advertikon_plans as $p ) {

				// Already mapped - skip
				if ( $item['recurring_id'] === $p['oc_plan_id'] ) {
					continue( 2 );
				}
			}

			if ( ! $this->a->compare_plans( $plan, $item ) ) {
				continue;
			}

			$list[] = array( 'text' => $item['name'], 'value' => $item['recurring_id'] );
		}

		return $list;
    }

    /**
	 * Checks whether Stripe plan has OpenCart counterpart
	 * @param object $plan Stripe plan object
	 * @return boolean
	 */
	public function is_plan_exists( $plan ) {

		// Forbid to export plans with different currency other then default store currency
		if ( strtoupper( $plan->currency) !== strtoupper( $this->config->get( 'config_currency' ) ) ) {
			throw new Advertikon\Exception( $this->a->__( 'Plan\'s currency is different from store\'s default currency' ) );
		}

		if ( ! $this->a->has_in_cache( 'existed_plans/' . $plan->id ) ) {
			return $this->a->get_from_cache( 'existed_plans/' . $plan->id );

		} else {
			$exist = false;
			$recurring = $this->load->model( 'catalog/recurring' );
			$oc_plans = $this->model_catalog_recurring->getRecurrings();

			foreach( $oc_plans as $oc_plan ) {
				if ( $this->a->compare_plans( $plan, $oc_plan ) ) {
					$exist = true;
					break;
				}
			}

			$this->a->add_to_cache( 'existed_plans/' . $plan->id, $exist );

			return $exist;
		}
	}
}
 Selects label class for payment card verification field to show at admin area
     * @param string $status 
     * @return string
     */
    public function get_check_class( $status ) {
    	$ret = 'label';

    	switch ( $status ){
    		case 'pass' :
    			$ret .= ' label-success';
    			break;
    		case 'fail' :
    			$ret .= ' label-danger';
    			break;
    		case 'unavailable' :
    		case 'unchecked' :
    			$ret .= ' label-default';
    			break;
    	}

    	return $ret;
    }

	/**
     * Convert Stripe plan to OpenCart representation
     * Will adjust currency
     * @param Object $sp_plan Stripe plan object
     * @return Array
     */
    public function convert_sp_plan_to_oc( $sp_plan ) {
       	$lang = $this->load->model( 'localisation/language' );
    	$langs = $this->model_localisation_language->getLanguages();
    	$lang_id = (string)$langs[ $this->config->get( 'config_admin_language' ) ]['language_id'];

    	return array(
    			'sort_order'			=> 0,
    			'status'				=> 1,
    			'price'					=> $this->currency->convert(
    				$this->a->cents_to_amount( $sp_plan->amount, $sp_plan->currency ),
    				strtoupper( $sp_plan->currency ),
    				$this->config->get( 'config_currency' )
    			),
    			'frequency'				=> $sp_plan->interval,
    			'duration'				=> 0,
    			'cycle'					=> $sp_plan->interval_count,
    			'trial_status'			=> (int)$sp_plan->trial_period_days,
    			'trial_price'			=> 0,
    			'trial_frequency'		=> 'day',
    			'trial_duration'		=> 1,
    			'trial_cycle'			=> $sp_plan->trial_period_days,
    			'recurring_description' => array(
    					$lang_id => array( 'name' => $sp_plan->name ),
    				