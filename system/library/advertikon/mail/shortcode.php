<?php
/**
 * Advertikon Short-code Class
 * @author Advertikon
 * @package Stripe
 * @version 2.6.4
 */

namespace Advertikon\Mail;

class Shortcode extends \Advertikon\Shortcode {

	public function __construct() {
		parent::__construct(
			array(
				'vitrine' => array(
					'callback'    => array( $this, 'shortcode_vitrine' ),
					'hint'        => 'vitrine(ID)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows brief information of several products in some category (bestsellers, latest etc)' ) .
						' ' . ADK( __NAMESPACE__ )->__( 'Need to be created prior to use '),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'social' => array(
					'callback'    => array( $this, 'shortcode_social' ),
					'hint'        => 'social(ID)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows set of social media icons' ) . ' ' .
						ADK( __NAMESPACE__ )->__( 'Need to be created prior to use '),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'button' => array(
					'callback'    => array( $this, 'shortcode_button' ),
					'hint'        => 'button(ID)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows call to action button' ) . ' ' .
						ADK( __NAMESPACE__ )->__( 'Need to be created prior to use '),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'qrcode' => array(
					'callback'    => array( $this, 'shortcode_qrcode' ),
					'hint'        => 'qrcode(ID)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows QR Code' ) . ' ' .
						ADK( __NAMESPACE__ )->__( 'Need to be created prior to use '),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'open_in_browser' => array(
					'callback'    => array( $this, 'shortcode_open_in_browser' ),
					'hint'        => 'open_in_browser(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Renders link to open email contents in browser' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'restore_password_url' => array(
					'callback'    => array( $this, 'shortcode_restore_password_url' ),
					'hint'        => 'restore_password_url',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows URL link, which contains code to restore password' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Forgotten password' ) ),
				),
				'store_name' => array(
					'callback'    => array( $this, 'shortcode_store_name' ),
					'hint'        => 'store_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows name of current store' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'store_url' => array(
					'callback'    => array( $this, 'shortcode_store_url' ),
					'hint'        => 'store_url(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows link to the current store' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'ip' => array(
					'callback'    => array( $this, 'shortcode_ip' ),
					'hint'        => 'ip',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows IP for current session' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'customer_first_name' => array(
					'callback'    => array( $this, 'shortcode_customer_first_name' ),
					'hint'        => 'customer_first_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows first name for current customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ) ),
				),
				'customer_last_name' => array(
					'callback'    => array( $this, 'shortcode_customer_last_name' ),
					'hint'        => 'customer_last_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows last name for current customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ) ),
				),
				'customer_full_name' => array(
					'callback'    => array( $this, 'shortcode_customer_full_name' ),
					'hint'        => 'customer_full_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows full name for current customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ) ),
				),
				'affiliate_first_name' => array(
					'callback'    => array( $this, 'shortcode_affiliate_first_name' ),
					'hint'        => 'affiliate_first_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows first name for current affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - Approve' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate') ),
				),
				'affiliate_last_name' => array(
					'callback'    => array( $this, 'shortcode_affiliate_last_name' ),
					'hint'        => 'affiliate_last_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows last name for current affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - Approve' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate') ),
				),
				'affiliate_full_name' => array(
					'callback'    => array( $this, 'shortcode_affiliate_full_name' ),
					'hint'        => 'affiliate_full_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows full name for current affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - Approve' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate') ),
				),
				'initial_contents' => array(
					'callback'    => array( $this, 'shortcode_initial_contents' ),
					'hint'        => 'initial_contents',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows contents of the initial letter (eg predefined by OpenCart)' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'unsubscribe' => array(
					'callback'    => array( $this, 'shortcode_unsubscribe' ),
					'hint'        => 'unsubscribe(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Creates link to a page, where customer can cancel a newsletter subscription (if such exists)' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'account_login_url' => array(
					'callback'    => array( $this, 'shortcode_account_login_url' ),
					'hint'        => 'account_login_url(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Creates link to a customers\'s account login page' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'transaction_amount' => array(
					'callback'    => array( $this, 'shortcode_transaction_amount' ),
					'hint'        => 'transaction_amount',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows formatted amount of credit transaction upon customer\'s account' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add credit' ) ),
				),
				'transaction_description' => array(
					'callback'    => array( $this, 'shortcode_transaction_description' ),
					'hint'        => 'transaction_description',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows description for credit transaction upon customer\'s account' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add credit' ) ),
				),
				'if_transaction_description' => array(
					'callback'    => array( $this, 'shortcode_if_transaction_description' ),
					'hint'        => 'if_transaction_description}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_transaction_description' ,
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text up to the closing tag, if description for credit transaction, upon customer\'s account is present' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add credit' ) ),
				),
				'transaction_total' => array(
					'callback'    => array( $this, 'shortcode_transaction_total' ),
					'hint'        => 'transaction_total',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows formatted credit amount of customer\'s account' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add credit' ) ),
				),
				'reward_points' => array(
					'callback'    => array( $this, 'shortcode_reward_points' ),
					'hint'        => 'reward_points',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows amount of reward points, added to customer\'s account' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add reward points' ) ),
				),
				'reward_description' => array(
					'callback'    => array( $this, 'shortcode_reward_description' ),
					'hint'        => 'reward_description',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows description for reward points transaction upon customer\'s account' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add reward points' ) ),
				),
				'if_reward_description' => array(
					'callback'    => array( $this, 'shortcode_if_reward_description' ),
					'hint'        => 'if_reward_description}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_reward_description',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tag, if description for an add reward points transaction is present' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add reward points' ) ),
				),
				'reward_total' => array(
					'callback'    => array( $this, 'shortcode_reward_total' ),
					'hint'        => 'reward_total',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows total amount of customer\'s reward points' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Add reward points' ) ),
				),
				'affiliate_login_url' => array(
					'callback'    => array( $this, 'shortcode_affiliate_login_url' ),
					'hint'        => 'affiliate_login_url(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Creates link to a affiliate\'s account login page' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'affiliate_commission' => array(
					'callback'    => array( $this, 'shortcode_affiliate_commission' ),
					'hint'        => 'affiliate_commission',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows amount of commission, added to affiliate\'s account' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - Add commission' ) ),
				),
				'affiliate_commission_total' => array(
					'callback'    => array( $this, 'shortcode_affiliate_commission_total' ),
					'hint'        => 'affiliate_commission_total',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows total amount of affiliate\'s commission' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - Add commission' ) ),
				),
				'affiliate_commission_description' => array(
					'callback'    => array( $this, 'shortcode_affiliate_commission_description' ),
					'hint'        => 'affiliate_commission_description',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows description to affiliate commission transaction' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - Add commission' ) ),
				),
				'if_affiliate_commission_description' => array(
					'callback'    => array( $this, 'shortcode_if_affiliate_commission_description' ),
					'hint'        => 'if_affiliate_commission_description}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_affiliate_commission_description',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tag, if description for an add affiliate commission transaction is present' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - Add commission' ) ),
				),
				'return_id' => array(
					'callback'    => array( $this, 'shortcode_return_id' ),
					'hint'        => 'return_id',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows ID of a return' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Return update' ) ),
				),
				'return_date' => array(
					'callback'    => array( $this, 'shortcode_return_date' ),
					'hint'        => 'return_date',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows creation date of a return' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Return update' ) )
				),
				'return_status' => array(
					'callback'    => array( $this, 'shortcode_return_status' ),
					'hint'        => 'return_status',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows status of a return' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Return update' ) )
				),
				'return_comment' => array(
					'callback'    => array( $this, 'shortcode_return_comment' ),
					'hint'        => 'return_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows description of a changing status transaction for current return' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Return update' ) )
				),
				'if_return_comment' => array(
					'callback'    => array( $this, 'shortcode_if_return_comment' ),
					'hint'        => 'if_return_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_return_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows "conditional text" up to closing tag, if return transaction has comment' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Return update' ) )
				),
				'voucher_from' => array(
					'callback'    => array( $this, 'shortcode_voucher_from' ),
					'hint'        => 'voucher_from',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows voucher sender name' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Voucher' ) )
				),
				'voucher_amount' => array(
					'callback'    => array( $this, 'shortcode_voucher_amount' ),
					'hint'        => 'voucher_amount',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows voucher total amount' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Voucher' ) )
				),
				'voucher_code' => array(
					'callback'    => array( $this, 'shortcode_voucher_code' ),
					'hint'        => 'voucher_code',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows voucher code' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Voucher' ) )
				),
				'voucher_theme_image' => array(
					'callback'    => array( $this, 'shortcode_voucher_theme_image' ),
					'hint'        => 'voucher_theme_image(width,height)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows voucher\'s theme image, if present' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Voucher' ) )
				),
				'voucher_message' => array(
					'callback'    => array( $this, 'shortcode_voucher_message' ),
					'hint'        => 'voucher_message',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows voucher message' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Voucher' ) )
				),
				'voucher_to' => array(
					'callback'    => array( $this, 'shortcode_voucher_to' ),
					'hint'        => 'voucher_to',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows the name of the recipient of the voucher' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Voucher' ) )
				),
				'voucher_from_email' => array(
					'callback'    => array( $this, 'shortcode_voucher_from_email' ),
					'hint'        => 'voucher_from_email',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows the name of the email of the voucher sender' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - Voucher' ) )
				),
				'enquiry_from_email' => array(
					'callback'    => array( $this, 'shortcode_enquiry_from_email' ),
					'hint'        => 'enquiry_from_email',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows email address of a person, who has sent an inquiry' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Enquiry' ) )
				),
				'enquiry_from_name' => array(
					'callback'    => array( $this, 'shortcode_enquiry_from_name' ),
					'hint'        => 'enquiry_from_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows name of a person, who has sent an inquiry' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Enquiry' ) )
				),
				'enquiry' => array(
					'callback'    => array( $this, 'shortcode_enquiry' ),
					'hint'        => 'enquiry',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows contents of an inquiry' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Enquiry' ) )
				),
				'if_account_approve' => array(
					'callback'    => array( $this, 'shortcode_if_account_approve' ),
					'hint'        => 'if_account_approve}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_account_approve',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to closing tag, if a newly created customer\'s account need to be approved before became active' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ) )
				),
				'if_account_no_approve' => array(
					'callback'    => array( $this, 'shortcode_if_account_no_approve' ),
					'hint'        => 'if_account_no_approve}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_account_no_approve',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to closing tag, if a newly created customer\'s account has no need to be approved before became active' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ) )
				),
				'new_customer_first_name' => array(
					'callback'    => array( $this, 'shortcode_new_customer_first_name' ),
					'hint'        => 'new_customer_first_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows first name of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_last_name' => array(
					'callback'    => array( $this, 'shortcode_new_customer_last_name' ),
					'hint'        => 'new_customer_last_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows last name of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_group' => array(
					'callback'    => array( $this, 'shortcode_new_customer_group' ),
					'hint'        => 'new_customer_group',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows group for newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_email' => array(
					'callback'    => array( $this, 'shortcode_new_customer_email' ),
					'hint'        => 'new_customer_email',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows email of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_telephone' => array(
					'callback'    => array( $this, 'shortcode_new_customer_telephone' ),
					'hint'        => 'new_customer_first_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows telephone of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_address_1' => array(
					'callback'    => array( $this, 'shortcode_new_customer_address_1' ),
					'hint'        => 'new_customer_address_1',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows address line 1 of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_city' => array(
					'callback'    => array( $this, 'shortcode_new_customer_city' ),
					'hint'        => 'new_customer_city',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows city of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_country' => array(
					'callback'    => array( $this, 'shortcode_new_customer_country' ),
					'hint'        => 'new_customer_country',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows country of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_first_name' => array(
					'callback'    => array( $this, 'shortcode_new_customer_first_name' ),
					'hint'        => 'new_customer_first_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows first name of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_customer_region' => array(
					'callback'    => array( $this, 'shortcode_new_customer_region' ),
					'hint'        => 'new_customer_region',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows address region of newly registered customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New customer' ) )
				),
				'new_affiliate_first_name' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_first_name' ),
					'hint'        => 'new_affiliate_first_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows first name of newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_last_name' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_last_name' ),
					'hint'        => 'new_affiliate_last_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows last name of newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_email' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_email' ),
					'hint'        => 'new_affiliate_email',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows email of newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_telephone' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_telephone' ),
					'hint'        => 'new_affiliate_telephone',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows first telephone number of newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_company' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_company' ),
					'hint'        => 'new_affiliate_company',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows company name of newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_website' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_website' ),
					'hint'        => 'new_affiliate_website',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows website name of newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_address_1' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_address_1' ),
					'hint'        => 'new_affiliate_address_1',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows address line 1 for newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_city' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_city' ),
					'hint'        => 'new_affiliate_city',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows city for newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_country' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_country' ),
					'hint'        => 'new_affiliate_country',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows country for newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'new_affiliate_region' => array(
					'callback'    => array( $this, 'shortcode_new_affiliate_region' ),
					'hint'        => 'new_affiliate_region',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows region for newly registered affiliate' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'if_affiliate_approve' => array(
					'callback'    => array( $this, 'shortcode_if_affiliate_approve' ),
					'hint'        => 'if_affiliate_approve}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_affiliate_approve',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tag, if a newly created affiliate account need to be approved, before became active' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'if_affiliate_no_approve' => array(
					'callback'    => array( $this, 'shortcode_if_affiliate_no_approve' ),
					'hint'        => 'if_affiliate_no_approve}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_affiliate_no_approve',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tag, if a newly created affiliate account has no need to be approved, before became active' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Affiliate - New' ), ADK( __NAMESPACE__ )->__( 'Dashboard - New affiliate' ) )
				),
				'review_product' => array(
					'callback'    => array( $this, 'shortcode_review_product' ),
					'hint'        => 'review_product',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows product name, which has been reviewed' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Review' ) )
				),
				'review_person' => array(
					'callback'    => array( $this, 'shortcode_review_person' ),
					'hint'        => 'review_person',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows name of a reviewer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Review' ) )
				),
				'review_rating' => array(
					'callback'    => array( $this, 'shortcode_review_rating' ),
					'hint'        => 'review_rating',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows review rating' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Review' ) )
				),
				'review_text' => array(
					'callback'    => array( $this, 'shortcode_review_text' ), 
					'hint'        => 'review_text',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows text contents of a review' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - Review' ) )
				),
				'order_id' => array(
					'callback'    => array( $this, 'shortcode_order_id' ), 
					'hint'        => 'order_id',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows order ID' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_order_download' => array(
					'callback'    => array( $this, 'shortcode_if_order_download' ), 
					'hint'        => 'if_order_download}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_download',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tag, if a newly created order contains downloadable product' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'invoice_table' => array(
					'callback'    => array( $this, 'shortcode_invoice_table' ), 
					'hint'        => 'invoice_table',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows tabulated invoice data for current order' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ) )
				),
				'invoice' => array(
					'callback'    => array( $this, 'shortcode_invoice' ), 
					'hint'        => 'invoice(ID)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows a customizable inlined invoice table' ) . ' ' .
						ADK( __NAMESPACE__ )->__( 'Need to be created prior to use '),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ) )
				),
				'invoice_table_text' => array(
					'callback'    => array( $this, 'shortcode_invoice_table_text' ), 
					'hint'        => 'invoice_table_text',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows textul invoice data for current order' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_order_approve' => array(
					'callback'    => array( $this, 'shortcode_if_order_approve' ), 
					'hint'        => 'if_order_approve}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_approve',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tag, if a newly created order is need to be approved (has uncompleted status)' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_order_no_approve' => array(
					'callback'    => array( $this, 'shortcode_if_order_no_approve' ), 
					'hint'        => 'if_order_no_approve}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_no_approve',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tag, if a newly created order has no need to be approved (has completed status)' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_url' => array(
					'callback'    => array( $this, 'shortcode_order_url' ), 
					'hint'        => 'order_url(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows link to the order page' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'download_url' => array(
					'callback'    => array( $this, 'shortcode_download_url' ), 
					'hint'        => 'download_url(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows link to customer\'s account download page' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Customer' ), ADK( __NAMESPACE__ )->__( 'Dashboard'), ADK( __NAMESPACE__ )->__( 'Affiliate' ) ),
				),
				'order_date_added' => array(
					'callback'    => array( $this, 'shortcode_order_date_added' ), 
					'hint'        => 'order_date_added',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows date of order placement' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_status' => array(
					'callback'    => array( $this, 'shortcode_order_status' ), 
					'hint'        => 'order_status',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows order status' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_status_new' => array(
					'callback'    => array( $this, 'shortcode_order_status_new' ), 
					'hint'        => 'order_status_new',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows new status for an order' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_status_old' => array(
					'callback'    => array( $this, 'shortcode_order_status_old' ), 
					'hint'        => 'order_status_old',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows old status for an order' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_products' => array(
					'callback'    => array( $this, 'shortcode_order_products' ), 
					'hint'        => 'order_products',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows a list of order\'s products' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_products_sku' => array(
					'callback'    => array( $this, 'shortcode_if_products_sku' ), 
					'hint'        => 'if_products_sku(SKU1,SKU2,...)}Conditional text{/if_products_sku',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional tag if an order contains at least one product with specific SKU' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_no_products_sku' => array(
					'callback'    => array( $this, 'shortcode_if_no_products_sku' ), 
					'hint'        => 'if_no_products_sku(SKU1,SKU2,...)}Conditional text{/if_no_products_sku',
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional tag if an order does not contain at least one product with specific SKU' ),
				),
				'if_products_sku_all' => array(
					'callback'    => array( $this, 'shortcode_if_products_sku_all' ), 
					'hint'        => 'if_products_sku_all(SKU1,SKU2,...)}Conditional text{/if_products_sku_all',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional tag if an order contains all the products with specific SKU' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_no_products_sku_all' => array(
					'callback'    => array( $this, 'shortcode_if_no_products_sku_all' ), 
					'hint'        => 'if_no_products_sku_all(SKU1,SKU2,...)}Conditional text{/if_no_products_sku_all',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional tag if an order does not contain all of the products with specific SKU' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_totals' => array(
					'callback'    => array( $this, 'shortcode_order_totals' ), 
					'hint'        => 'order_totals',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows totals for an order' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_comment' => array(
					'callback'    => array( $this, 'shortcode_order_comment' ), 
					'hint'        => 'order_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows comment. left by customer' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'order_status_comment' => array(
					'callback'    => array( $this, 'shortcode_order_status_comment' ), 
					'hint'        => 'order_status_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows comment, pertain to an order status' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_order_status_comment' => array(
					'callback'    => array( $this, 'shortcode_if_order_status_comment' ), 
					'hint'        => 'if_order_status_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_status_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tab, when comment, pertain to an order status is present' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_order_status_no_comment' => array(
					'callback'    => array( $this, 'shortcode_if_order_status_no_comment' ), 
					'hint'        => 'if_order_status_no_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_status_no_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tab, when comment, pertain to an order status is not present' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_order_comment' => array(
					'callback'    => array( $this, 'shortcode_if_order_comment' ), 
					'hint'        => 'if_order_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tab, when order contains customer\'s comment' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'if_order_no_comment' => array(
					'callback'    => array( $this, 'shortcode_if_order_no_comment' ), 
					'hint'        => 'if_order_no_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_no_comment',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				),
				'newsletter_name' => array(
					'callback'    => array( $this, 'shortcode_newsletter_name' ), 
					'hint'        => 'newsletter_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows name for current newsletter' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Newsletter' ), ),
				),
				'subscriber_name' => array(
					'callback'    => array( $this, 'shortcode_subscriber_name' ), 
					'hint'        => 'subscriber_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows subscribers name' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Newsletter' ), ),
				),
				'subscriber_email' => array(
					'callback'    => array( $this, 'shortcode_subscriber_email' ), 
					'hint'        => 'subscriber_email',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows subscriber email address' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Newsletter' ), ),
				),
				'confirm_subscription_url' => array(
					'callback'    => array( $this, 'shortcode_confirm_subscription_url' ),
					'hint'        => 'confirm_subscription_url(Text)',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows link to a subscription confirmation page' ),
					'context'     => array( ADK( __NAMESPACE__ )->__( 'Newsletter' ), ),
				),
				'shipping_address_line1' => array(
					'callback'    => array( $this, 'shortcode_shipping_address_line1' ),
					'hint'        => 'shipping_address_line1',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows shipping address line1 part for specific order' ),
					'context'     =>  array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
				),
				'shipping_address_line2' => array(
					'callback'    => array( $this, 'shortcode_shipping_address_line2' ),
					'hint'        => 'shipping_address_line2',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows shipping address line2 part for specific order' ),
					'context'     =>  array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
				),
				'shipping_city' => array(
					'callback'    => array( $this, 'shortcode_shipping_city' ),
					'hint'        => 'shipping_city',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows the city where to send the order' ),
					'context'     =>  array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
				),
				'shipping_country' => array(
					'callback'    => array( $this, 'shortcode_shipping_country' ),
					'hint'        => 'shipping_country',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows the country where to send the order' ),
					'context'     =>  array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
				),
				'shipping_state' => array(
					'callback'    => array( $this, 'shortcode_shipping_state' ),
					'hint'        => 'shipping_state',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows the state where to send the order' ),
					'context'     =>  array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
				),
				'shipping_postcode' => array(
					'callback'    => array( $this, 'shortcode_shipping_postcode' ),
					'hint'        => 'shipping_postcode',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows postcode for shipping address' ),
					'context'     =>  array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
				),
				'shipping_name' => array(
					'callback'    => array( $this, 'shortcode_shipping_name' ),
					'hint'        => 'shipping_name',
					'description' => ADK( __NAMESPACE__ )->__( 'Shows person\'s name to whom send the order' ),
					'context'     =>  array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) ),
				),
				// 'r1' => array(
				// 	'callback'    => array( $this, 'r1' ), 
				// 	'hint'        => 'if_order_no_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_no_comment',
				// 	'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
				// 	'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				// ),
				// 'r2' => array(
				// 	'callback'    => array( $this, 'r2' ), 
				// 	'hint'        => 'if_order_no_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_no_comment',
				// 	'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
				// 	'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				// ),
				// 'r3' => array(
				// 	'callback'    => array( $this, 'r3' ), 
				// 	'hint'        => 'if_order_no_comment}' . ADK( __NAMESPACE__ )->__( 'Conditional text' ) . '{/if_order_no_comment',
				// 	'description' => ADK( __NAMESPACE__ )->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
				// 	'context'     => array( ADK( __NAMESPACE__ )->__( 'Dashboard - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - New order' ), ADK( __NAMESPACE__ )->__( 'Customer - Order update' ) )
				// ),
			)
		);
	}

	/**
	 * Returns hints for all the shortcodes
	 * @return array
	 */
	public function get_shortcodes_hint() {
		$ret = array();

		foreach( $this->get_shortcode_data() as $shortcode ) {
			$ret[] = $this->brace_shortcode_name( $shortcode['hint'] );
		}

		return $ret;
	}

	/**
	 * Renders vitrine shortcode
	 * @return string
	 */
	public function shortcode_vitrine() {
		$args = func_get_args();
		$shortcode_id = isset( $args[1] ) ? $args[1] : null;

		if( is_null( $shortcode_id ) ) {
			return '';
		}

		$shortcode = ADK( __NAMESPACE__ )->get_shortcode( $shortcode_id );

		if( ! $shortcode ) {
			return '';
		}

		$img_width = isset( $shortcode['data']['img']['width'] ) ?
			$shortcode['data']['img']['width'] : 100;
		$img_header_height = isset( $shortcode['data']['img']['header']['height'] ) ?
			$shortcode['data']['img']['header']['height'] : 0;

		$products = $this->get_vitrine_products( $shortcode );

		$embed = ! empty( $shortcode['data']['img']['embed'] );
		$height = ! empty( $shortcode['data']['element']['height'] ) ?
			$shortcode['data']['element']['height'] : 200;

		$width = ! empty( $shortcode['data']['element']['width'] ) ?
			$shortcode['data']['element']['width'] : 120;

		$width += 2;
		$margins = ( $width - $img_width ) / 2;
		ADK( __NAMESPACE__ )->load->model( 'tool/image' );

		$ret =
		'<!--[if lt mso 12]>-->' .
		'<table width="100%" cellpadding="0" cellspacing="0" style="table-layout: fixed" class="vitrine-table">' .
			'<tr>' .
				'<td style="font-size:' . $shortcode['data']['title']['height'] . 'px;color:' . $shortcode['data']['title']['color'] . ';" align="' . $shortcode['data']['title']['align'] . '">' .
					$shortcode['data']['title']['text'] .
				'</td>' .
			'</tr>' .
			'<tr>' .
				'<td>';

		foreach( $products as $product ) {
			$currency_from = ADK( __NAMESPACE__ )->config->get( 'config_currency' );

			$currency_to = isset( ADK( __NAMESPACE__ )->session->data['currency'] ) ?
				ADK( __NAMESPACE__ )->session->data['currency'] : $currency_from;

			$price = ADK( __NAMESPACE__ )->currency->format(
				ADK( __NAMESPACE__ )->currency->convert( 
					$product['price'],
					$currency_from, $currency_to
				),
				$currency_to
			);

			$special = null;

			if( $product['special'] ) {
				$special = ADK( __NAMESPACE__ )->currency->format(
					ADK( __NAMESPACE__ )->currency->convert(
						$product['special'],
						$currency_from,
						$currency_to
					),
					$currency_to
				);
			}

			$file_name = ADK( __NAMESPACE__ )->model_tool_image->resize( $product['image'], $img_width, $img_width );

			// Image URL need to have protocol
			$file_name = str_replace( ADK( __NAMESPACE__ )->u()->catalog_url( true ) . 'image/', '' , $file_name );
			$url  = ADK( __NAMESPACE__ )->get_img( $file_name, $embed );

			$ret .=
				'<div style="float: left; width: ' . $width . 'px; height: ' . $height . 'px;">' .
					'<table cellpadding="0" cellspacing="0" valign="top" align="center" class="vitrine-element">' .
						'<tr ' .  ( $img_header_height > 0 ? 'style="height: ' . $img_header_height . 'px"' : '' ) . '>' .
							'<td align="center">' .
								$product['name'] .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<td align="center">' .
								'<a href="' . ADK( __NAMESPACE__ )->get_store_url( true ) . 'index.php?route=product/product&product_id=' . $product['product_id'] . '" target="_blank">' .
									'<img src="' . $url . '" width="' . $img_width . '" height="' . $img_width . '" style="max-width=' . $img_width . 'px; width=' . $img_width . 'px;" />' .
								'</a>' .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<td align="center">' .
								( $special ? $special . '<br>' : '' ) . 
								( $special ? '<strike>' . $price . '</strike>' : $price ) .
							'</td>' .
						'</tr>' .
					'</table>' .
				'</div>';
		}

		$ret .=
				'</td>' .
			'</tr>' .
		'</table>' .
		'<!--<![endif]-->';

		return $ret;
	}

	/**
	 * Renders contents of social shortcode tab
	 * @return sting
	 */
	public function shortcode_social() {
		$args = func_get_args();
		$shortcode_id = isset( $args[1] ) ? $args[1] : null;

		if( is_null( $shortcode_id ) ) {
			return '';
		}

		$shortcode = ADK( __NAMESPACE__ )->get_shortcode( $shortcode_id );

		if( ! $shortcode ) {
			return '';
		}

		$margin = isset( $shortcode['data']['icon']['margin'] ) ? $shortcode['data']['icon']['margin'] : 0;

		ADK( __NAMESPACE__ )->load->model( 'tool/image' );

		$ret = '';

		$ret .=
		'<table width="100%" cellpadding="0" cellspacing="0">' .
			'<tr>' .
				'<td style="font-size:' . $shortcode['data']['title']['height'] . 'px;color:' . $shortcode['data']['title']['color'] . ';" align="' . $shortcode['data']['title']['align'] . '">' .
					$shortcode['data']['title']['text'] .
				'<td>' .
			'</tr>' .
			'<tr>' .
				'<td align="' . $shortcode['data']['title']['align'] . '"><div>';

		$items = isset( $shortcode['data']['item'] ) ? $shortcode['data']['item'] : array();
		$appearance = isset( $shortcode['data']['appearance'] ) ? $shortcode['data']['appearance' ] : '';
		$size = isset( $shortcode['data']['icon']['height'] ) ? $shortcode['data']['icon']['height'] : 40;

		foreach( $items as $name => $item ) {
			if( empty( $item['status'] ) ) {
				continue;
			}

			ADK( __NAMESPACE__ )->model_tool_image->resize( 'social/' . $appearance . '/' . $name . '.png' , $size, $size );
			$src = ADK( __NAMESPACE__ )->get_img( 'social/' . $appearance . '/' . $name . '.png', ! empty( $shortcode['data']['icon']['embed'] ) );
			$href = isset( $item['url'] ) ? $item['url'] : '#';

			$ret .=
					'<a href="' . $href . '" target="_blank" style="margin-right: ' . $margin . 'px;">' .
						'<img src="' . $src . '" width="' . $size . '" height="' . $size . '"/>' .
					'</a>';
		}

		$ret .=
				'</div></td>' .
			'</tr>' .
		'</table>';

		return $ret;
	}

	/**
	 * Renders call to action shortcode
	 * @return string
	 */
	public function shortcode_button() {
		$args = func_get_args();
		$shortcode_id = isset( $args[1] ) ? $args[1] : null;

		if( is_null( $shortcode_id ) ) {
			return '';
		}

		$shortcode = ADK( __NAMESPACE__ )->get_shortcode( $shortcode_id );

		if( ! $shortcode ) {
			return '';
		}

		$href = ! empty( $shortcode['data']['url'] ) ? $shortcode['data']['url'] : '';
		$height = isset( $shortcode['data']['height'] ) ? $shortcode['data']['height'] : '40';
		$padding = isset( $shortcode['data']['padding'] ) ? $shortcode['data']['padding'] : '5';
		$height_px = $height . 'px';
		$width = isset( $shortcode['data']['width'] ) ? $shortcode['data']['width'] : '140';
		$width_px = $width . 'px';
		$bg_color =  isset( $shortcode['data']['color'] ) ? $shortcode['data']['color'] : '#0000ff';
		$border_color =  isset( $shortcode['data']['border']['color'] ) ? $shortcode['data']['border']['color'] : '#0000ff';
		$caption_color = isset( $shortcode['data']['caption']['color'] ) ? $shortcode['data']['caption']['color'] : '#000000';
		$caption_height = ( isset( $shortcode['data']['caption']['height'] ) ? $shortcode['data']['caption']['height'] : '16' ) . 'px';
		$caption = isset( $shortcode['data']['caption']['text'] ) ? $shortcode['data']['caption']['text'] : ' ';
		$border_width = ( isset( $shortcode['data']['border']['width'] ) ? $shortcode['data']['border']['width'] : '1' ) . 'px';
		$border_radius = ( isset( $shortcode['data']['border']['radius'] ) ? $shortcode['data']['border']['radius'] : '3' ) . 'px';
		$align = isset( $shortcode['data']['align'] ) ? $shortcode['data']['align'] : 'center';
		if( ! empty( $shortcode['data']['fullwidth'] ) ) {
			$width = "100%";
			$width_px = "100%";
		}

		// Call to action URL supports shortcodes
		if( $href ) {
			$href = $this->do_shortcode( $href );
		}
		
		// Get href attributr from the anchor
		if( preg_match( '/<a.+href=("|\')(.+?)\1/', $href, $m ) ) {
			$href = $m[2];
		}

		$ret =
		'<center><div>' .
			'<!--[if mso]>' .
			'<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $href . '" style="height:' . $height_px . ';v-text-anchor:middle;width:' . $width_px . ';" arcsize="10%" stroke="f" fillcolor="' . $bg_color . '">' .
			'<w:anchorlock/>' .
			'<center style="color:' . $caption_color . ';font-family:sans-serif;font-size:' . $caption_height . ';font-weight:bold;">' .
				$caption .
			'</center>' .
			'</v:roundrect>' .
			'<![endif]-->' .

			'<!--[if !mso]>-->' .
			'<table cellspacing="0" cellpadding="' . $padding .'px" width="100%">' .
				'<tr align="' . $align . '">' . 
					'<td align="center" width="' . $width .'" height="' . $height .'" bgcolor="' . $bg_color . '" style="-webkit-border-radius: ' . $border_radius . '; -moz-border-radius: ' . $border_radius . '; border-radius: ' . $border_radius . '; color: ' . $caption_color . '; display: block; border: solid ' . $border_width . ' ' . $border_color . '; height: ' . $height . 'px; ">' .
						'<a href="' . $href . '" style="font-size: ' . $caption_height . '; font-family: sans-serif; line-height: ' . $height_px . '; width: 100%; display: inline-block; text-decoration: none; font-weight: bold; color: ' . $caption_color . '; " target="_blank">' .
							'<span style="color: ' . $caption_color . '; line-height: ' . $height . 'px; height: ' . $height . 'px; ">' .
								$caption .
							'</span>' .
						'</a>' .
					'</td>' . 
				'</tr>' .
			'</table>' . 
			'<!--<![endif]-->' .
		'</div></center>';

		return $ret;
	}

	/**
	 * Renders QR Code. Supports recursion
	 * @return string
	 */
	public function shortcode_qrcode() {
		$args = func_get_args();
		$shortcode_id = isset( $args[1] ) ? $args[1] : null;

		if( is_null( $shortcode_id ) ) {
			return '';
		}

		$shortcode = ADK( __NAMESPACE__ )->get_shortcode( $shortcode_id );

		if( ! $shortcode ) {
			return '';
		}

		require_once( __DIR__ . '/phpqrcode/qrlib.php' );

		$fs = new \Advertikon\Fs();
		$fs->mkdir( ADK( __NAMESPACE__ )->tmp_dir );
		$tmp_file = ADK( __NAMESPACE__ )->tmp_dir . '/shortcode' . uniqid();
		file_put_contents( $tmp_file, '' );

		$text = htmlspecialchars_decode( $shortcode['data']['content'] );
		$text = str_replace(array( '&nbsp;', '<p>', '<br>' ), ' ', $text );
		$text = str_replace( array( '</p>' ), '', $text );

		$text = $this->do_shortcode( $text );

		// Wrap off links, if any
		$text = preg_replace( '/<a[^>]+?href=(\'|")(.*?)\1[^>]+>(.*?)<\/a>/', '$2 $3', $text );

		\QRcode::png(

			// QR Code contents
			$text,

			// Target file
			$tmp_file,

			// Error correction level
			$shortcode['data']['level'],

			// Code square size
			$shortcode['data']['square'],

			// White border width
			$shortcode['data']['border']
		);

		$ret = '<img src="data:image/png;base64,' . base64_encode( file_get_contents( $tmp_file ) ) . '" />';

		unlink( $tmp_file );

		return $ret;
	}

	/**
	 * Returns URL to restore admin password
	 * @return string
	 */
	public function shortcode_restore_password_url() {
		if( empty( ADK( __NAMESPACE__ )->request->post['email'] ) ) {
			return '';
		}

		$email = ADK( __NAMESPACE__ )->request->post['email'];
		$code = ADK( __NAMESPACE__ )->get_admin_restore_password_code( $email );

		if( ! $code ) {
			return '';
		}

		return ADK( __NAMESPACE__ )->url->link( 'common/reset', 'code=' . $code, 'SSL' );
	}

	/**
	 * Returns current store name
	 * @return string
	 */
	public function shortcode_store_name() {
		return ADK( __NAMESPACE__ )->config->get( 'config_name' );
	}

	/**
	 * Returns client IP address
	 * @return string
	 */
	public function shortcode_ip() {
		$ip = '';

		if ( isset( $_SERVER ) ) {
			if( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
				$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];

				if( strpos( $ip, "," ) ){
					$exp_ip = explode( ",", $ip );
					$ip = $exp_ip[0];
				}

			} else if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
				$ip = $_SERVER["HTTP_CLIENT_IP"];

			} else{
				$ip = $_SERVER["REMOTE_ADDR"];
			}

		} else {
			if( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED_FOR' );

				if( strpos( $ip, "," ) ) {
					$exp_ip = explode( ",", $ip );
					$ip = $exp_ip[0];
				}

			} else if( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ip = getenv( 'HTTP_CLIENT_IP' );

			} else {
				$ip = getenv( 'REMOTE_ADDR' );
			}
		}

		return $ip;
	}

	/**
	 * Returns customer's full name
	 * @return string
	 */
	public function shortcode_customer_full_name() {
		$customer = ADK( __NAMESPACE__ )->get_mail_customer();
		$ret = '';
		
		if( isset( $customer['firstname'] ) && isset( $customer['lastname'] ) ) {
			$ret = $customer['firstname'] . ' ' . $customer['lastname'];
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'John Smith';
		}

		return $ret;
	}

	/**
	 * Returns customer's first name
	 * @return string
	 */
	public function shortcode_customer_first_name() {
		$customer = ADK( __NAMESPACE__ )->get_mail_customer();
		$ret = '';
		
		if( isset( $customer['firstname'] ) ) {
			$ret = $customer['firstname'];
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'John';
		}

		return $ret;
	}

	/**
	 * Returns customers last name
	 * @return string
	 */
	public function shortcode_customer_last_name() {
		$customer = ADK( __NAMESPACE__ )->get_mail_customer();
		$ret = '';
		
		if( isset( $customer['lastname'] ) ) {
			$ret = $customer['lastname'];
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'Smith';
		}

		return $ret;
	}

	/**
	 * Returns affiliate full name
	 * @return string
	 */
	public function shortcode_affiliate_full_name() {
		$affiliate = ADK( __NAMESPACE__ )->get_mail_affiliate();
		$ret = '';
		
		if( isset( $affiliate['firstname'] ) && isset( $affiliate['lastname'] ) ) {
			$ret = $affiliate['firstname'] . ' ' . $affiliate['lastname'];
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'John Smith';
		}

		return $ret;
	}

	/**
	 * Returns affiliate first name
	 * @return string
	 */
	public function shortcode_affiliate_first_name() {
		$affiliate = ADK( __NAMESPACE__ )->get_mail_affiliate();
		$ret = '';
		
		if( isset( $affiliate['firstname'] ) ) {
			$ret = $affiliate['firstname'];
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'John';
		}

		return $ret;
	}

	/**
	 * Returns affiliate last name
	 * @return string
	 */
	public function shortcode_affiliate_last_name() {
		$affiliate = ADK( __NAMESPACE__ )->get_mail_affiliate();
		$ret = '';
		
		if( isset( $affiliate['lastname'] ) ) {
			$ret = $affiliate['lastname'];
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'Smith';
		}

		return $ret;
	}

	/**
	 * Returns contents of original email letter
	 * @return string
	 */
	public function shortcode_initial_contents() {
		$ret = '';

		if( ! is_null( ADK( __NAMESPACE__ )->modified_mail ) ) {

			if( ADK( __NAMESPACE__ )->modified_mail->html ) {
				$ret = ADK( __NAMESPACE__ )->modified_mail->html;

			} elseif ( ADK( __NAMESPACE__ )->modified_mail->text ) {
				$ret = ADK( __NAMESPACE__ )->modified_mail->text;
			}
		}

		return $ret;
	}

	/**
	 * Returns link to page where customer can unsubscribe from newsletter
	 * @return string
	 */
	public function shortcode_unsubscribe() {
		$ret = '';
		$args = func_get_args();
		$text = empty( $args[1] ) ? ADK( __NAMESPACE__ )->__( 'Cancel subscription' ) : $args[1];
		$email = null;

		if ( ADK( __NAMESPACE__ )->adk_newsletter_id && ADK( __NAMESPACE__ )->adk_subscriber_email ) {
			$n = ADK( __NAMESPACE__ )->q( array(
				'table' => ADK( __NAMESPACE__ )->newsletter_subscribers_table,
				'query' => 'select',
				'where' => array(
					array(
						'field'     => 'email',
						'operation' => '=',
						'value'     => ADK( __NAMESPACE__ )->adk_subscriber_email,
					),
					array(
						'field'     => 'newsletter',
						'operation' => '=',
						'value'     => ADK( __NAMESPACE__ )->adk_newsletter_id,
					),
					array(
						'field'     => 'status',
						'operation' => '=',
						'value'     => \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE,
					),
				),
			) );

			if ( count( $n ) ) {
				$newsletter = ADK( __NAMESPACE__ )->adk_newsletter_id;
				$email = ADK( __NAMESPACE__ )->adk_subscriber_email;
			}

		// Presume OpenCart newsletter
		} else {
			$customer = ADK( __NAMESPACE__ )->get_mail_customer();

			if ( $customer && ! empty( $customer['newsletter'] ) && ! empty( $customer['email'] ) ) {

				// 0 -is OpenCart newsletter's code
				$newsletter = 0;
				$email = $customer['email'];
				ADK( __NAMESPACE__ )->adk_newsletter_id = $newsletter;
				ADK( __NAMESPACE__ )->adk_subscriber_email = $email;
			}

		}

		// Do not show cancellation link in admin email
		if ( ADK( __NAMESPACE__ )->modified_mail && ADK( __NAMESPACE__ )->modified_mail->to != $email ) {
			$newsletter = null;
		}

		if( isset( $newsletter ) ) {
			$code = uniqid();

			$values = array(
				'code'        => $code,
				'expiration'  => ADK( __NAMESPACE__ )->get_sql_expiration_date( 'unsubscribe' ),
				'email'       => $email,
				'newsletter'  => $newsletter,
				'operation'   => \Advertikon\Mail\Advertikon::NEWSLETTER_CODE_CANCEL,
			);

			if ( 0 === $newsletter ) {
				$values['customer_id'] = $customer['customer_id'];
			}

			$result = ADK( __NAMESPACE__ )->q( array(
				'table' => ADK( __NAMESPACE__ )->newsletter_code_table,
				'query' => 'insert',
				'values' => $values,
			) );

			if( $result ) {
				$ret = ADK( __NAMESPACE__ )->u()->catalog_url( true ) .
				'index.php?route=' . ADK( __NAMESPACE__ )->type . '/' . ADK( __NAMESPACE__ )->code . '/unsubscribe&code=' . $code;

			} else {
				trigger_error( 'Failed to render unsubscrine shortcode\'s content due to DB error' );
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->u()->catalog_url( true ) .
				'index.php?route=' . ADK( __NAMESPACE__ )->type . '/' . ADK( __NAMESPACE__ )->code . '/unsubscribe&code=test';
		}

		if( $ret ) {
			$ret = sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
		}

		return $ret;
	}

	/**
	 * Returns link to the log-in to customer account page 
	 * @return string
	 */
	public function shortcode_account_login_url() {
		$args = func_get_args();
		$text = empty( $args[1] ) ? ADK( __NAMESPACE__ )->__( 'Sign in' ) : $args[1];

		$ret =  ( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER ) .
			'index.php?route=account/login';

		return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	}

	/**
	 * Returns link to open email in browser
	 * @return string
	 */
	public function shortcode_open_in_browser() {

		// Do not save archive copy for email containing sensitive data
		if ( ADK( __NAMESPACE__ )->private_template ) {
			return '';
		}

		$args = func_get_args();
		$text = empty( $args[1] ) ? ADK( __NAMESPACE__ )->__( 'Open in browser' ) : $args[1];

		if ( ADK( __NAMESPACE__ )->archive_file ) {
			$file_name = substr( ADK( __NAMESPACE__ )->archive_file, strlen( ADK( __NAMESPACE__ )->archive_dir ) );

		} else {
			$date = new \DateTime();
			$file_name = $date->format( 'Y' ) .'/' . $date->format( 'm' ) . '/' .
				$date->format( 'd' ) . '/' . uniqid();
			ADK( __NAMESPACE__ )->archive_file = ADK( __NAMESPACE__ )->archive_dir . $file_name;
		}

		$ret =  ADK( __NAMESPACE__ )->get_store_url() . '/' .
			'index.php?route=' . ADK( __NAMESPACE__ )->type . '/' . ADK( __NAMESPACE__ )->code . '/archive&email=' . $file_name;

		return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	}

	/**
	 * Returns link to the log-in to affiliate account page
	 * @return type
	 */
	public function shortcode_affiliate_login_url() {
		$args = func_get_args();
		$text = empty( $args[1] ) ? ADK( __NAMESPACE__ )->__( 'Sign in' ) : $args[1];

		$ret = ( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER ) .
			'index.php?route=affiliate/login';

		return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	}

	/**
	 * Returns amount of add credit transaction
	 * @return string
	 */
	public function shortcode_transaction_amount() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] )
				&& 'customer/customer/addtransaction' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {

			if( isset( ADK( __NAMESPACE__ )->request->post['amount'] ) ) {
				$ret = ADK( __NAMESPACE__ )->currency->format( ADK( __NAMESPACE__ )->request->post['amount'], ADK( __NAMESPACE__ )->config->get( 'config_currency' ) );
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->currency->format( 100, ADK( __NAMESPACE__ )->config->get( 'config_currency' ) );
		}

		return $ret;
	}

	/**
	 * Returns current credit balance for customer account 
	 * @return string
	 */
	public function shortcode_transaction_total() {
		$ret = '';
		$customer = ADK( __NAMESPACE__ )->get_mail_customer();

		if( $customer && isset( $customer['customer_id'] ) ) {
			$query = ADK( __NAMESPACE__ )->db->query( "SELECT SUM(`amount`) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer['customer_id'] . "'" );

			$ret = ADK( __NAMESPACE__ )->currency->format( $query->row['total'], ADK( __NAMESPACE__ )->config->get( 'config_currency' ) );

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->currency->format( 1000, ADK( __NAMESPACE__ )->config->get( 'config_currency' ) );
		}

		return $ret;
	}

	/**
	 * Returns description for credit balance transaction
	 * @return string
	 */
	public function shortcode_transaction_description() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
				'customer/customer/addtransaction' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['description'] ) ) {
				$ret = ADK( __NAMESPACE__ )->request->post['description'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->__( 'Order #123 partial refund' );
		}

		return $ret;
	}

	/**
	 * Conditional shortcode, shows contents if there is transaction description
	 * @return boolean
	 */
	public function shortcode_if_transaction_description() {
		return (boolean)$this->shortcode_transaction_description();
	}

	/**
	 * Returns the add reward points transaction amount
	 * @return string
	 */
	public function shortcode_reward_points() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] )
				&& 'customer/customer/addreward' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {

			if( isset( ADK( __NAMESPACE__ )->request->post['points'] ) ) {
				$ret = ADK( __NAMESPACE__ )->request->post['points'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 100;
		}

		return $ret;
	}

	/**
	 * Returns customer account's total reward points amount
	 * @return string
	 */
	public function shortcode_reward_total() {
		$ret = '';

		$customer = ADK( __NAMESPACE__ )->get_mail_customer();

		if( $customer && isset( $customer['customer_id'] ) ) {
			$query = ADK( __NAMESPACE__ )->db->query( "SELECT SUM(`points`) AS total FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer['customer_id'] . "'" );

			$ret = $query->row['total'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 1200;
		}

		return $ret;
	}

	/**
	 * Returns description to add reward points transaction
	 * @return string
	 */
	public function shortcode_reward_description() {
		$ret = '';
		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
				'customer/customer/addreward' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {

			if( isset( ADK( __NAMESPACE__ )->request->post['description'] ) ) {
				$ret = ADK( __NAMESPACE__ )->request->post['description'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Reward points to order #243';
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents if an add reward points transaction description is present
	 * @return boolean
	 */
	public function shortcode_if_reward_description() {
		return (boolean)$this->shortcode_reward_description();
	}

	/**
	 * Shows an add affiliate commissions balance transaction
	 * @return string
	 */
	public function shortcode_affiliate_commission() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( 'marketing/affiliate/addtransaction' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
				if( isset( ADK( __NAMESPACE__ )->request->post['amount'] ) ) {
					$ret = ADK( __NAMESPACE__ )->currency->format(
						ADK( __NAMESPACE__ )->request->post['amount'],
						ADK( __NAMESPACE__ )->config->get( 'config_currency' )
					);
				}

			// One of the payment methods
			} elseif ( strpos( strtolower( ADK( __NAMESPACE__ )->request->get['route'] ), 'payment' ) !== false  ) {
				if( ADK( __NAMESPACE__ )->caller_args && isset( ADK( __NAMESPACE__ )->caller_args[1] ) ) {
					$ret = ADK( __NAMESPACE__ )->currency->format(
						$ret = ADK( __NAMESPACE__ )->caller_args[1],
						ADK( __NAMESPACE__ )->session->data['currency']
					);
				}
			}
		}

		if( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->currency->format( 102.44, ADK( __NAMESPACE__ )->config->get( 'config_currency' ) );
		}

		return $ret;
	}

	/**
	 * Returns description to an add affiliate commission balance transaction
	 * @return string
	 */
	public function shortcode_affiliate_commission_description() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] )
				&& 'marketing/affiliate/addtransaction' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {

			if( isset( ADK( __NAMESPACE__ )->request->post['description'] ) ) {
				$ret = ADK( __NAMESPACE__ )->request->post['description'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->__( 'Commission for order #324' );
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents if there is description to an add affiliate commission transaction
	 * @return string
	 */
	public function shortcode_if_affiliate_commission_description() {
		return (boolean)$this->shortcode_affiliate_commission_description();
	}

	/**
	 * Shows total amount of commissions for affiliate account
	 * @return string
	 */
	public function shortcode_affiliate_commission_total() {
		$ret = '';
		$affiliate_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['affiliate_id' ] ) ) {
			$affiliate_id = ADK( __NAMESPACE__ )->request->get['affiliate_id'];

		// One of the payment methods
		} elseif ( isset( ADK( __NAMESPACE__ )->request->get['route'] )
			&& strpos( strtolower( ADK( __NAMESPACE__ )->request->get['route'] ), 'payment' ) !== false ) {

			if( ADK( __NAMESPACE__ )->caller_args && isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
				$affiliate_id = ADK( __NAMESPACE__ )->caller_args[0];
			}
		}

		if( $affiliate_id ) {
			$query = ADK( __NAMESPACE__ )->db->query( "SELECT SUM(`amount`) AS total FROM `" . DB_PREFIX . "affiliate_transaction` WHERE `affiliate_id` = '" . (int)$affiliate_id . "'" );

			$ret = ADK( __NAMESPACE__ )->currency->format(
				$query->row['total'],
				ADK( __NAMESPACE__ )->config->get( 'config_currency' )
			);
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->currency->format(
				1023.21,
				ADK( __NAMESPACE__ )->config->get( 'config_currency' )
			);
		}

		return $ret;
	}

	/**
	 * Returns return ID
	 * @return string
	 */
	public function shortcode_return_id() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['return_id'] ) ) {
				$ret = ADK( __NAMESPACE__ )->request->request['return_id'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = '23';
		}

		return $ret;
	}

	/**
	 * Returns return creation date
	 * @return sting
	 */
	public function shortcode_return_date() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['return_id'] ) ) {
			$data = ADK( __NAMESPACE__ )->get_return_data( ADK( __NAMESPACE__ )->request->request['return_id'] );

			if( $data && isset( $data['date_added'] ) ) {
				$d = new DateTime( $data['date_added'] );
				$ret = $d->format( 'd/m/Y' );
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$d = new \DateTime( 'today' );
			$ret = $d->format( 'd/m/Y' );
		}

		return $ret;
	}

	/**
	 * Returns current a Return status
	 * @return sting
	 */
	public function shortcode_return_status() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['return_id'] ) ) {
			$data = ADK( __NAMESPACE__ )->get_return_data( ADK( __NAMESPACE__ )->request->request['return_id'] );

			if( $data && isset( $data['status'] ) ) {
				$ret = $data['status'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->__( 'Awaiting Products' );
		}

		return $ret;
	}

	/**
	 * Returns comment to a return transaction
	 * @return string
	 */
	public function shortcode_return_comment() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'sale/return/history' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['comment'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['comment'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->__( 'Wrong product' );
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents if a return comment is present
	 * @return string
	 */
	public function shortcode_if_return_comment() {
		return (boolean)$this->shortcode_return_comment();
	}

	/**
	 * Returns link to the store
	 * @return string
	 */
	public function shortcode_store_url() {
		$ret = '';

		$args = func_get_args();
		$text = empty( $args[1] ) ? ADK( __NAMESPACE__ )->__( 'Store' ) : $args[1]; 
		$ret = ADK( __NAMESPACE__ )->get_store_href( true );

		return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	}

	/**
	 * Returns voucher render name
	 * @return string
	 */
	public function shortcode_voucher_from() {
		$ret = '';
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'sale/voucher/send' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['voucher_id'] ) ) {

				$voucher_id = ADK( __NAMESPACE__ )->request->post['voucher_id'];
			} elseif ( isset( ADK( __NAMESPACE__ )->request->post['selected'] ) ) {
				if( ADK( __NAMESPACE__ )->caller_args ) {

					// Voucher ID passed to model's sendVoucher method
					$voucher_id = ADK( __NAMESPACE__ )->caller_args[0];
				}
			}
		}

		if( ! is_null( $voucher_id ) ) {
			$voucher = ADK( __NAMESPACE__ )->get_voucher( $voucher_id );
			$ret = isset( $voucher['from_name'] ) ? $voucher['from_name'] : '';
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'Jane Smith';
		}

		return $ret;
	}

	/**
	 * Returns voucher amount
	 * @return string
	 */
	public function shortcode_voucher_amount() {
		$ret = '';
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'sale/voucher/send' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['voucher_id'] ) ) {
				$voucher_id = ADK( __NAMESPACE__ )->request->post['voucher_id'];

			} elseif ( isset( ADK( __NAMESPACE__ )->request->post['selected'] ) ) {
				if( ADK( __NAMESPACE__ )->caller_args ) {

					// Voucher ID passed to model's sendVoucher method
					$voucher_id = ADK( __NAMESPACE__ )->caller_args[0];
				}
			}
		}

		if( ! is_null( $voucher_id ) ) {
			$voucher = ADK( __NAMESPACE__ )->get_voucher( $voucher_id );
			$ret = isset( $voucher['amount'] ) ?
				ADK( __NAMESPACE__ )->currency->format( $voucher['amount'], ADK( __NAMESPACE__ )->config->get( 'config_currency' ) ) : '';
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->currency->format( 200, ADK( __NAMESPACE__ )->config->get( 'config_currency' ) );
		}

		return $ret;
	}

	/**
	 * Returns gift voucher message
	 * @return string
	 */
	public function shortcode_voucher_message() {
		$ret = '';
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'sale/voucher/send' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['voucher_id'] ) ) {
				$voucher_id = ADK( __NAMESPACE__ )->request->post['voucher_id'];

			} elseif ( isset( ADK( __NAMESPACE__ )->request->post['selected'] ) ) {
				if( ADK( __NAMESPACE__ )->caller_args ) {

					// Voucher ID passed to model's sendVoucher method
					$voucher_id = ADK( __NAMESPACE__ )->caller_args[0];
				}
			}
		}

		if( ! is_null( $voucher_id ) ) {
			$voucher = ADK( __NAMESPACE__ )->get_voucher( $voucher_id );
			$ret = isset( $voucher['message'] ) ? $voucher['message'] : '';
		}

		if ( ! $ret & defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->__( 'With best regards from Jane Smith' );
		}

		return $ret;
	}

	/**
	 * Returns gift voucher recipient
	 * @return string
	 */
	public function shortcode_voucher_to() {
		$ret = '';
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'sale/voucher/send' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['voucher_id'] ) ) {
				$voucher_id = ADK( __NAMESPACE__ )->request->post['voucher_id'];

			} elseif ( isset( ADK( __NAMESPACE__ )->request->post['selected'] ) ) {
				if( ADK( __NAMESPACE__ )->caller_args ) {

					// Voucher ID passed to model's sendVoucher method
					$voucher_id = ADK( __NAMESPACE__ )->caller_args[0];
				}
			}
		}

		if( ! is_null( $voucher_id ) ) {
			$voucher = ADK( __NAMESPACE__ )->get_voucher( $voucher_id );
			$ret = isset( $voucher['to_name'] ) ? $voucher['to_name'] : '';
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'John Smith';
		}

		return $ret;
	}

	/**
	 * Returns an email address of voucher sender
	 * @return string
	 */
	public function shortcode_voucher_from_email() {
		$ret = '';
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'sale/voucher/send' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['voucher_id'] ) ) {
				$voucher_id = ADK( __NAMESPACE__ )->request->post['voucher_id'];

			} elseif ( isset( ADK( __NAMESPACE__ )->request->post['selected'] ) ) {
				if( ADK( __NAMESPACE__ )->caller_args ) {

					// Voucher ID passed to model's sendVoucher method
					$voucher_id = ADK( __NAMESPACE__ )->caller_args[0];
				}
			}
		}

		if( ! is_null( $voucher_id ) ) {
			$voucher = ADK( __NAMESPACE__ )->get_voucher( $voucher_id );
			$ret = isset( $voucher['from_email'] ) ? $voucher['from_email'] : '';
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = 'jane_smith@google.com';
		}

		return $ret;
	}

	/**
	 * Returns gift voucher code
	 * @return string
	 */
	public function shortcode_voucher_code() {
		$ret = '';
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'sale/voucher/send' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['voucher_id'] ) ) {
				$voucher_id = ADK( __NAMESPACE__ )->request->post['voucher_id'];

			} elseif ( isset( ADK( __NAMESPACE__ )->request->post['selected'] ) ) {
				if( ADK( __NAMESPACE__ )->caller_args ) {

					// Voucher ID passed to model's sendVoucher method
					$voucher_id = ADK( __NAMESPACE__ )->caller_args[0];
				}
			}
		}

		if( ! is_null( $voucher_id ) ) {
			$voucher = ADK( __NAMESPACE__ )->get_voucher( $voucher_id );
			$ret = isset( $voucher['code'] ) ? $voucher['code'] : '';
		}

		if ( ! $ret && defined( 'PREVIEW' ) ) {
			$ret = '2345332';
		}

		return $ret;
	}

	/**
	 * Returns a gift voucher theme image
	 * @return string
	 */
	public function shortcode_voucher_theme_image() {
		$ret = '';
		$voucher_id = null;
		$args = func_get_args();

		$width = empty( $args[1] ) ? 0 : (int)$args[1];
		$height = empty( $args[2] ) ? 0 : (int)$args[2];

		if ( $width <= 0 && $height <= 0 ) {
			$width = $height = 200;

		} else {
			if( $width <= 0 ) {
				$width = $height;

			} elseif ( $height <= 0 ) {
				$height = $width;
			}
		}

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'sale/voucher/send' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			if( isset( ADK( __NAMESPACE__ )->request->post['voucher_id'] ) ) {
				$voucher_id = ADK( __NAMESPACE__ )->request->post['voucher_id'];

			} elseif ( isset( ADK( __NAMESPACE__ )->request->post['selected'] ) ) {
				if( ADK( __NAMESPACE__ )->caller_args ) {

					// Voucher ID passed to model's sendVoucher method
					$voucher_id = ADK( __NAMESPACE__ )->caller_args[0];
				}
			}
		}

		if( ! is_null( $voucher_id ) ) {
			$voucher = ADK( __NAMESPACE__ )->get_voucher( $voucher_id );

			if( isset( $voucher['image'] ) && is_file( DIR_IMAGE . $voucher['image'] ) ) {
				$ret = $voucher['image'];
			}
		}

		if ( ! $ret && defined( 'PREVIEW' ) && is_file( DIR_IMAGE . 'no_image.png') ) {
			$ret = 'no_image.png';
		}

		 if ( $ret ) {
		 	ADK( __NAMESPACE__ )->load->model( 'tool/image' );

		 	$ret = sprintf(
	 			'<div style="float: right; margin-left: 20px;">' .
					'<a href="%1$s" title="%2$s">' .
						'<img src="%3$s" alt="%2$s" />' .
					'</a>' .
				'</div>',
				ADK( __NAMESPACE__ )->get_store_href(),
				$this->shortcode_store_name(),
				ADK( __NAMESPACE__ )->model_tool_image->resize( $ret, $width, $height )
	 		);
		 }

		return $ret;
	}

	/**
	 * Returns email address of enquirer
	 * @return string
	 */
	public function shortcode_enquiry_from_email() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'information/contact' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['email'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['email'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'john_smith@google.com';
		}

		return $ret;
	}

	/**
	 * Returns name of enquirer
	 * @return string
	 */
	public function shortcode_enquiry_from_name() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'information/contact' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['name'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['name'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'John Smith';
		}

		return $ret;
	}

	/**
	 * Returns enquiry text
	 * @return type
	 */
	public function shortcode_enquiry() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'information/contact' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['enquiry'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['enquiry'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Hi, just want to say that your store is marvelous :)';
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents in newly created customer account need to be approved
	 * @return boolean
	 */
	public function shortcode_if_account_approve() {
		$status = null;
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->request['customer_group_id'] ) ) {
			$group_info = ADK( __NAMESPACE__ )->get_customer_group_info( ADK( __NAMESPACE__ )->request->request['customer_group_id']  );
			$status = ! empty( $group_info['approval'] );
		}

		return $status;
	}

	/**
	 * Conditional tag. Shows contents in newly created customer account has no need to be approved
	 * @return boolean
	 */
	public function shortcode_if_account_no_approve() {
		return ! $this->shortcode_if_account_approve();
	}

	/**
	 * Returns first name of newly registered customer
	 * @return string
	 */
	public function shortcode_new_customer_first_name() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['firstname'] ) ) {
			$ret = ADK( __NAMESPACE__ )->request->post['firstname'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'John';
		}

		return $ret;
	}

	/**
	 * Returns last name of newly registered customer
	 * @return string
	 */
	public function shortcode_new_customer_last_name() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['lastname'] ) ) {
			$ret = ADK( __NAMESPACE__ )->request->request['lastname'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Smith';
		}

		return $ret;
	}

	/**
	 * Returns email of newly created customer
	 * @return string
	 */
	public function shortcode_new_customer_email() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			( 'account/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ||
				'checkout/register/save' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) &&
			isset( ADK( __NAMESPACE__ )->request->post['email'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['email'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'john_smith@google.com';
		}

		return $ret;
	}

	/**
	 * Returns telephone number of newly created customer
	 * @return type
	 */
	public function shortcode_new_customer_telephone() {
		$ret = '';
		if( isset( ADK( __NAMESPACE__ )->request->request['telephone'] ) ) {
			$ret = ADK( __NAMESPACE__ )->request->request['telephone'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = '+7(123)234 45 56';
		}

		return $ret;
	}

	/**
	 * Returns address line 1 of newly created customer
	 * @return string
	 */
	public function shortcode_new_customer_address_1() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['address_1'] ) ) {
			$ret = ADK( __NAMESPACE__ )->request->request['address_1'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Puddledock 22';
		}

		return $ret;
	}

	/**
	 * Returns city of newly created customer
	 * @return string
	 */
	public function shortcode_new_customer_city() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['city'] ) ) {
			$ret = ADK( __NAMESPACE__ )->request->request['city'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'London';
		}

		return $ret;
	}

	/**
	 * Returns customer group name for newly created customer
	 * @return type
	 */
	public function shortcode_new_customer_group() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['customer_group_id'] ) ) {
			$group = ADK( __NAMESPACE__ )->get_customer_group_info( ADK( __NAMESPACE__ )->request->request['customer_group_id'] );

			if( isset( $group['name'] ) ) {
				$ret = $group['name'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Default';
		}

		return $ret;
	}

	/**
	 * Returns country of newly registered customer
	 * @return string
	 */
	public function shortcode_new_customer_country() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['zone_id'] ) ) {
			$region = ADK( __NAMESPACE__ )->get_region_info( ADK( __NAMESPACE__ )->request->request['zone_id'] );

			if( isset( $region['country_name'] ) ) {
				$ret = $region['country_name'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'United Kingdom';
		}

		return $ret;
	}

	/**
	 * Returns region of newly created customer
	 * @return sting
	 */
	public function shortcode_new_customer_region() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->request['zone_id'] ) ) {
			$region = ADK( __NAMESPACE__ )->get_region_info( ADK( __NAMESPACE__ )->request->request['zone_id'] );

			if( isset( $region['zone_name'] ) ) {
				$ret = $region['zone_name'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Yorkshire';
		}

		return $ret;
	}

	/**
	 * Returns first name of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_first_name() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['firstname'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['firstname'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'John';
		}

		return $ret;
	}

	/**
	 * Returns last name of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_last_name() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['lastname'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['lastname'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Smith';
		}

		return $ret;
	}

	/**
	 * Returns email of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_email() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['email'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['email'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'john_smith@google.com';
		}

		return $ret;
	}

	/**
	 * returns telephone of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_telephone() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['telephone'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['telephone'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = '+7(123)345 43 56';
		}

		return $ret;
	}

	/**
	 * Returns company name of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_company() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['company'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['company'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Wholesale LLC';
		}

		return $ret;
	}

	/**
	 * Returns website URl of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_website() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['website'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['website'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'http://wholesale.com';
		}

		return $ret;
	}

	/**
	 * Returns address line 1 of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_address_1() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['address_1'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['address_1'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Puddledock 11';
		}

		return $ret;
	}

	/**
	 * Returns city name of newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_city() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['city'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['city'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'London';
		}

		return $ret;
	}

	/**
	 * Returns country name for newly registered affiliate
	 * @return string
	 */
	public function shortcode_new_affiliate_country() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->caller_args[0]['zone_id'] ) ) {

			$region = ADK( __NAMESPACE__ )->get_region_info( ADK( __NAMESPACE__ )->caller_args[0]['zone_id'] );

			if( isset( $region['country_name'] ) ) {
				$ret = $region['country_name'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'United Kingdom';
		}

		return $ret;
	}

	/**
	 * Returns region of newly created affiliate
	 * @return type
	 */
	public function shortcode_new_affiliate_region() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->caller_args[0]['zone_id'] ) ) {

			$region = ADK( __NAMESPACE__ )->get_region_info( ADK( __NAMESPACE__ )->caller_args[0]['zone_id'] );

			if( isset( $region['zone_name'] ) ) {
				$ret = $region['zone_name'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Yorkshire';
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents if affiliate account need to be approved
	 * @return boolean
	 */
	public function shortcode_if_affiliate_approve() {
		$status = null;
		$voucher_id = null;

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) && 'affiliate/register' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) ) {
			$status = ADK( __NAMESPACE__ )->config->get( 'config_affiliate_approval' );
		}

		return $status;;
	}

	/**
	 * Conditional tag. Shows contents if affiliate account has no need to be approved
	 * @return boolean
	 */
	public function shortcode_if_affiliate_no_approve() {
		return ! $this->shortcode_if_affiliate_approve();
	}

	/**
	 * Returns a product review text
	 * @return string
	 */
	public function shortcode_review_text() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'product/product/write' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['text'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['text'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Awesome product';
		}

		return $ret;
	}

	/**
	 * Returns name of reviewer
	 * @return string
	 */
	public function shortcode_review_person() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'product/product/write' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['name'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['name'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'John Smith';
		}

		return $ret;
	}

	/**
	 * Returns review rating
	 * @return string
	 */
	public function shortcode_review_rating() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'product/product/write' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->post['rating'] ) ) {

			$ret = ADK( __NAMESPACE__ )->request->post['rating'];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 5;
		}

		return $ret;
	}

	/**
	 * Returns reviewed product name
	 * @return string
	 */
	public function shortcode_review_product() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			'product/product/write' === strtolower( ADK( __NAMESPACE__ )->request->get['route'] ) &&
			isset( ADK( __NAMESPACE__ )->request->get['product_id'] ) ) {

			$product_id = ADK( __NAMESPACE__ )->request->get['product_id'];
			$product = $this->get_product_info( $product_id );

			if( $product ) {
				$ret = $product['name'];
			}

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Canon EOS 5D';
		}

		return $ret;
	}

	/**
	 * Returns order ID
	 * @return string
	 */
	public function shortcode_order_id() {
		$ret = '';

		$order = ADK( __NAMESPACE__ )->get_from_cache( 'old_order' );
		if( $order && isset( $order['order_id'] ) ) {
			$ret = $order['order_id'];

		} elseif ( defined( 'PREVIEW' ) && ( $sample_order = ADK( __NAMESPACE__ )->get_sample_order() ) ) {
			$ret = $sample_order['order_id'];
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows content if order contains download-able product
	 * @return boolean
	 */
	public function shortcode_if_order_download() {
		$status = null;

		$order = ADK( __NAMESPACE__ )->get_from_cache( 'old_order' );
		if( $order && isset( $order['order_id'] ) ) {
			$products = ADK( __NAMESPACE__ )->get_order_downloaded_products( $order['order_id'] );

			$status = (boolean)$products;
		}

		return $status;;
	}

	/**
	 * Conditional tag. Shows contents if order need to be approved
	 * @return boolean
	 */
	public function shortcode_if_order_approve() {
		$status = null;

		if( isset( ADK( __NAMESPACE__ )->caller_args[1] ) ) {
			$status = in_array( ADK( __NAMESPACE__ )->caller_args[1], ADK( __NAMESPACE__ )->config->get( 'config_complete_status' ) );
		}

		return $status;
	}
	/**
	 * Conditional tab. Shows contents if order has no need to be approved
	 * @return type
	 */
	public function shortcode_if_order_no_approve( $shortcode ) {
		return ! $this->shortcode_if_order_approve();
	}

	/**
	 * Returns tabulated invoice details
	 * @return string
	 */
	public function shortcode_invoice_table() {
		$comment = '';

		$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		if( ! $data && defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

			$products = ADK( __NAMESPACE__ )->get_order_products( $data['order_id' ] );
			$vouchers = ADK( __NAMESPACE__ )->get_order_vouchers( $data['order_id'] );
			$totals = ADK( __NAMESPACE__ )->get_order_totals( $data['order_id'] );
			ADK( __NAMESPACE__ )->sort_by( $totals, 'sort_order' );
		}
		if( ! $data ) {
			return '';
		}

		if( isset( ADK( __NAMESPACE__ )->caller_args[2] ) ) {
			$comment = ADK( __NAMESPACE__ )->caller_args[2];
		}

		$text_order_id = ADK( __NAMESPACE__ )->__( 'Order ID' );
		$text_date_added = ADK( __NAMESPACE__ )->__( 'Date added' );
		$text_payment_method = ADK( __NAMESPACE__ )->__( 'Payment method' );
		$text_shipping_method = ADK( __NAMESPACE__ )->__( 'Shipping method' );
		$text_email = ADK( __NAMESPACE__ )->__( 'Email' );
		$text_telephone = ADK( __NAMESPACE__ )->__( 'Telephone' );
		$text_ip = ADK( __NAMESPACE__ )->__( 'IP address' );
		$text_order_status = ADK( __NAMESPACE__ )->__( 'Order status' );
		$text_instruction = ADK( __NAMESPACE__ )->__( 'Instructions' );
		$text_payment_address = ADK( __NAMESPACE__ )->__( 'Payment address' );
		$text_shipping_address = ADK( __NAMESPACE__ )->__( 'Shipping address' );
		$text_product = ADK( __NAMESPACE__ )->__( 'Product' );
		$text_model = ADK( __NAMESPACE__ )->__( 'Model' );
		$text_quantity = ADK( __NAMESPACE__ )->__( 'Quantity' );
		$text_price = ADK( __NAMESPACE__ )->__( 'Price' );
		$text_total = ADK( __NAMESPACE__ )->__( 'Total' );
		$text_order_detail = ADK( __NAMESPACE__ )->__( 'Order details' );

		$payment_address = ADK( __NAMESPACE__ )->format_address( $data, 'payment' );
		$shipping_address = ADK( __NAMESPACE__ )->format_address( $data, 'shipping' );

		extract( $data );

		$ret =
<<<HTML
<style>
.invoice-table td {
	margin: 1px;
	line-height: 1em;
	height: 1em;
}
</style>
<table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
	<thead>
		<tr>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">
				$text_order_detail
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
				<b>$text_order_id</b> $order_id
				<br/>
				<b>$text_date_added</b> $date_added
				<br/>
				<b>$text_payment_method</b> $payment_method
				<br/>
HTML;

		if ( $shipping_method ) {
			$ret .=
				"<b>$text_shipping_method</b> $shipping_method";
		}

		$ret .=
			<<<HTML
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
				<b>$text_email</b> $email
				<br/>
				<b>$text_telephone</b> $telephone
				<br/>
				<b>$text_ip</b> $ip
				<br/>
				<b>$text_order_status</b> $order_status
				<br/>
			</td>
		</tr>
	</tbody>
</table>
HTML;

		if ($comment) {
			$ret .=
<<<HTML
<table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
	<thead>
		<tr>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
				$text_instruction
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
				$comment
			</td>
		</tr>
	</tbody>
</table>
HTML;
		}

		$ret .=
<<<HTML
<table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
	<thead>
		<tr>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
				$text_payment_address
			</td>
HTML;
		if ($shipping_address) {
			$ret .=
       		"<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">
       			$text_shipping_address
       		</td>";
		}

		$ret .=
<<<HTML
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
				$payment_address
			</td>
HTML;
		if ($shipping_address) {
			$ret .=
			"<td style=\"font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">
				$shipping_address
			</td>";
		}

		$ret .=
<<<HTML
		</tr>
	</tbody>
</table>
<table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
	<thead>
		<tr>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
				$text_product
			</td>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
				$text_model
			</td>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
				$text_quantity
			</td>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
				$text_price
			</td>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
				$text_total
			</td>
		</tr>
	</thead>
	<tbody>
HTML;
	     foreach ( $products as $product ) {
			$ret .=
		"<tr>
			<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">
				{$product['name']}";

			if ( isset( $product['option'] ) ) {
				foreach ($product['option'] as $option) {
					$ret .=
					"<br/>
					&nbsp;<small>
						{$option['name']} : {$option['value']}
					</small>";
				}
			}

			$ret .=
<<<HTML
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
				{$product['model']}
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				{$product['quantity']}
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				{$product['price']}
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				{$product['total']}
			</td>
		</tr>
HTML;
		}

		foreach ( $vouchers as $voucher ) {
			$ret .=
<<<HTML
		<tr>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
				{$voucher['description']}
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"></td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">1</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				{$voucher['amount']}
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				{$voucher['amount']}
			</td>
		</tr>
HTML;
		}

		$ret .=
	'</tbody>
	<tfoot>';

		foreach ($totals as $total) {
			$total_text = '';

			if ( isset( $total['text'] ) ) {
				$total_text = $total['text'];

			// Preview mode
			} elseif ( isset( $total['value'] ) && isset( $currency_code ) ) {
				$total_text = ADK( __NAMESPACE__ )->currency->format( $total['value'], $currency_code );
			}

			$ret .=
<<<HTML
		<tr>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;" colspan="4">
				<b>{$total['title']}:</b>
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				$total_text
			</td>
		</tr>
HTML;
		}

		$ret .=
	'</tfoot>
</table>';

	return '<template_variant><html_variant>' . $ret . '</html_variant><text_variant>' . $this->shortcode_invoice_table_text() . '</text_variant></template_variant>';

	}

	/**
	 * Returns tabulated invoice details
	 * @return string
	 */
	public function shortcode_invoice() {
		$args = func_get_args();

		if( ! isset( $args[1] ) ) {
			return '';
		}

		$shortcode = ADK( __NAMESPACE__ )->get_shortcode( $args[1] );

		if ( ! $shortcode ) {
			trigger_error( sprintf( 'Shortcode with ID "#%s" is missing', $args[1] ) );
			return '';
		}

		$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		if( ! $data && defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();
			$products = ADK( __NAMESPACE__ )->get_order_products( $data['order_id' ] );
			$vouchers = ADK( __NAMESPACE__ )->get_order_vouchers( $data['order_id'] );
			$totals = ADK( __NAMESPACE__ )->get_order_totals( $data['order_id'] );
			ADK( __NAMESPACE__ )->sort_by( $totals, 'sort_order' );
			$payment_address = ADK( __NAMESPACE__ )->format_address( $data, 'payment' );

			if ( empty( $data['shipping_firstname'] ) ) {
				$shipping_address = ADK( __NAMESPACE__ )->format_address( $data, 'payment' );

			} else {
				$shipping_address = ADK( __NAMESPACE__ )->format_address( $data, 'shipping' );	
			}
		}

		if( ! $data ) {
			return '';
		}

		$text_order_id = ADK( __NAMESPACE__ )->__( 'Order ID' );
		$text_date_added = ADK( __NAMESPACE__ )->__( 'Date added' );
		$text_payment_method = ADK( __NAMESPACE__ )->__( 'Payment method' );
		$text_shipping_method = ADK( __NAMESPACE__ )->__( 'Shipping method' );
		$text_email = ADK( __NAMESPACE__ )->__( 'Email' );
		$text_telephone = ADK( __NAMESPACE__ )->__( 'Telephone' );
		$text_ip = ADK( __NAMESPACE__ )->__( 'IP address' );
		$text_order_status = ADK( __NAMESPACE__ )->__( 'Order status' );
		$text_instruction = ADK( __NAMESPACE__ )->__( 'Instructions' );
		$text_payment_address = ADK( __NAMESPACE__ )->__( 'Payment address' );
		$text_shipping_address = ADK( __NAMESPACE__ )->__( 'Shipping address' );
		$text_product = ADK( __NAMESPACE__ )->__( 'Product' );
		$text_model = ADK( __NAMESPACE__ )->__( 'Model' );
		$text_quantity = ADK( __NAMESPACE__ )->__( 'Quantity' );
		$text_price = ADK( __NAMESPACE__ )->__( 'Price' );
		$text_total = ADK( __NAMESPACE__ )->__( 'Total' );
		$text_order_detail = ADK( __NAMESPACE__ )->__( 'Order details' );

		$header_color = $shortcode['data']['header']['color'];
		$header_text_color = $shortcode['data']['header']['text']['color'];
		$header_text_height = $shortcode['data']['header']['text']['height'];
		$body_color = $shortcode['data']['body']['color'];
		$body_text_color = $shortcode['data']['body']['text']['color'];
		$body_text_height = $shortcode['data']['body']['text']['height'];
		$table_border = "{$shortcode['data']['table']['border']['width']}px solid {$shortcode['data']['table']['border']['color']}";
		$header_border = "{$shortcode['data']['header']['border']['width']}px solid {$shortcode['data']['header']['border']['color']}";
		$body_border = "{$shortcode['data']['body']['border']['width']}px solid {$shortcode['data']['body']['border']['color']}";

		$product_width = $shortcode['data']['product']['image']['width'];

		$show_order_details = ! empty( $shortcode['data']['fields']['order'] );
		$show_shipping_address = ! empty( $shortcode['data']['fields']['shipping'] );
		$show_payment_address = ! empty( $shortcode['data']['fields']['payment'] );
		$show_products = ! empty( $shortcode['data']['fields']['products'] );
		$show_image = ! empty( $shortcode['data']['fields']['image'] );
		$show_totals = ! empty( $shortcode['data']['fields']['totals'] );
		$show_comment = ! empty( $shortcode['data']['fields']['comment'] );

		extract( $data );

		// Comment will be empty, in order's data, if a notify customer upon new order setting will be disabled
		if( empty( $data['comment'] ) && ADK( __NAMESPACE__ )->has_in_cache( 'old_order' ) ) {
			$old_order = ADK( __NAMESPACE__ )->get_from_cache( 'old_order' );
			$comment = isset( $old_order['comment'] ) ? $old_order['comment'] : '';
		}

		if ( ! $comment && defined( 'PREVIEW' ) ) {
			$comment = ADK( __NAMESPACE__ )->__( 'Deliver the order between 2pm and 4pm' );
		}

		if ( $show_image ) {
			ADK( __NAMESPACE__ )->load->model( 'tool/image' );

			// Wee need full product information in order to show image
			if ( defined( 'PREVIEW' ) ) {
				foreach( $products as $p ) {
					$products_info[ $p['model'] ] = $p;
				}

			} else {
				foreach( ADK( __NAMESPACE__ )->get_order_products( $data['order_id' ] ) as $p ) {
					$products_info[ $p['model'] ] = $p;
				}
			}
		}

		$ret =
<<<HTML
<style>
	.invoice-table {
		border-collapse: collapse;
		width: 100%;
		border-top: $table_border;
		border-left: $table_border;
		margin-bottom: 20px;
	}

	.invoice-table img {
		float: left;
		margin-right: 10px;
	}

	.invoice-table td {
		margin: 1px;
		line-height: 1em;
		height: 1em;
	}

	.invoice-table p {
		padding-top: 4px;
	}

	.invoice-head{
		font-size: {$header_text_height}px;
		color: $header_text_color;
		background-color: $header_color;
		border-right: $header_border;
		border-bottom: $header_border;
		font-weight: bold;
		text-align: left;
		padding: 7px;
	}

	.invoice-body {
		font-size: {$body_text_height}px;
		border-right: $body_border;
		border-bottom: $body_border;
		text-align: left;
		padding: 7px;
		background-color: $body_color;
	}
</style>
HTML;

		if ( $show_order_details ) :
			$ret .=
<<<HTML
<table class="invoice-table">
	<thead>
		<tr>
			<td class="invoice-head" colspan="2">
				$text_order_detail
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="invoice-body">
				<p><b>$text_order_id</b> $order_id</p>
				
				<p><b>$text_date_added</b> $date_added</p>
				
				<p><b>$text_payment_method</b> $payment_method</p>
				
HTML;

		if ( $shipping_method ) {
			$ret .=
				"<p><b>$text_shipping_method</b> $shipping_method</p>";
		}

		$ret .=
<<<HTML
			</td>
			<td class="invoice-body">
				<p><b>$text_email</b> $email</p>
				
				<p><b>$text_telephone</b> $telephone</p>
				
				<p><b>$text_ip</b> $ip</p>
				
				<p><b>$text_order_status</b> $order_status</p>
				
			</td>
		</tr>
	</tbody>
</table>
HTML;

		endif;

		if ( $comment && $show_comment ) :
			$ret .=
<<<HTML
<table class="invoice-table">
	<thead>
		<tr>
			<td class="invoice-head">
				$text_instruction
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="invoice-body">
				$comment
			</td>
		</tr>
	</tbody>
</table>
HTML;
		endif;

		if ( $show_payment_address || $show_shipping_address ) :

		$ret .=
<<<HTML
<table class="invoice-table">
	<thead>
		<tr>
HTML;
		if ( $show_payment_address ) :
			$ret .=
<<<HTML
			<td class="invoice-head">
				$text_payment_address
			</td>
HTML;
		endif;

		if ( $shipping_address && $show_shipping_address ) :
			$ret .=
<<<HTML
       		<td class="invoice-head">
       			$text_shipping_address
       		</td>
HTML;
		endif;

		$ret .=
<<<HTML
		</tr>
	</thead>
	<tbody>
		<tr>
HTML;
		if ( $show_payment_address ) : 
			$ret .= <<<HTML
			<td class="invoice-body">
				$payment_address
			</td>
HTML;
		endif;

		if ( $shipping_address && $show_shipping_address ) :
			$ret .= <<<HTML
			<td class="invoice-body">
				$shipping_address
			</td>
HTML;
		endif;

		$ret .= <<<HTML
		</tr>
	</tbody>
</table>
HTML;
		endif;

		if ( $show_products ) :
			$ret .=
<<<HTML
<table class="invoice-table">
	<thead>
		<tr>
			<td class="invoice-head">
				$text_product
			</td>
			<td class="invoice-head">
				$text_model
			</td>
			<td class="invoice-head">
				$text_quantity
			</td>
			<td class="invoice-head">
				$text_price
			</td>
			<td class="invoice-head">
				$text_total
			</td>
		</tr>
	</thead>
	<tbody>
HTML;
	     foreach ( $products as $product ) {

	     	if ( $show_image ) {
	     		$resized_image = ADK( __NAMESPACE__ )->model_tool_image->resize(
	     			$products_info[ $product['model'] ]['image'], $product_width, $product_width
	     		);
	     	}

			$ret .=
		"<tr>
			<td class='invoice-body'>" .
			( $show_image ? '<img src="' . ADK( __NAMESPACE__ )->get_img( $resized_image, true ) . '" />' : '' ) .
				"<div style='" . ( $show_image ? 'padding-top:' . ( ( $product_width - $body_text_height ) / 2 ) . 'px' : '' ) .
				"'>{$product['name']}</div>";

			if ( isset( $product['option'] ) ) {
				foreach ($product['option'] as $option) {
					$ret .=
					"<br/>
					&nbsp;<small>
						{$option['name']} : {$option['value']}
					</small>";
				}
			}

			$ret .=
<<<HTML
			</td>
			<td class="invoice-body">
				{$product['model']}
			</td>
			<td class="invoice-body">
				{$product['quantity']}
			</td>
			<td class="invoice-body">
				{$product['price']}
			</td>
			<td class="invoice-body">
				{$product['total']}
			</td>
		</tr>
HTML;
		}

		foreach ( $vouchers as $voucher ) {
			$ret .=
<<<HTML
		<tr>
			<td class="invoice-body">
				{$voucher['description']}
			</td>
			<td class="invoice-body"></td>
			<td class="invoice-body">1</td>
			<td class="invoice-body">
				{$voucher['amount']}
			</td>
			<td class="invoice-body">
				{$voucher['amount']}
			</td>
		</tr>
HTML;
		}

		$ret .=
	'</tbody>';

	if ( $show_totals ) :
			$ret .=
	'<tfoot>';

		foreach ( $totals as $total ) {
			$total_value = '';

			if( isset( $total['text'] ) ) {
				$total_value = $total['text'];

			} elseif ( isset( $total['value'] ) && isset( $currency_code ) ) {
				$total_value = ADK( __NAMESPACE__ )->currency->format( $total['value'], $currency_code );
			}

			$ret .=
<<<HTML
		<tr>
			<td class="invoice-body" colspan="4">
				<b>{$total['title']}:</b>
			</td>
			<td class="invoice-body">
				{$total_value}
			</td>
		</tr>
HTML;
		}

		$ret .=
	'</tfoot>';

		endif;
		$ret .=
'</table>';

		endif;

	return '<template_variant><html_variant>' . $ret . '</html_variant><text_variant>' . $this->shortcode_invoice_table_text() . '</text_variant></template_variant>';

	}

	/**
	 * Returns invoice text representation
	 * @return string
	 */
	public function shortcode_invoice_table_text() {

		if( ! defined( 'PREVIEW' ) ) {
			if( ! isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
				return '';
			}

			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} else {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( ! $order_info ) {
			return '';
		}

		$order_id = $order_info['order_id'];
		ADK( __NAMESPACE__ )->load->model( 'tool/upload' );

		$comment = '';
		if( isset( ADK( __NAMESPACE__ )->caller_args[2] ) ) {
			$comment = ADK( __NAMESPACE__ )->caller_args[2];
		}

		$text  = '';
		$text .= ADK( __NAMESPACE__ )->__( 'Order ID' ) . ' ' . $order_info['order_id'] . '<br>';
		$text .= ADK( __NAMESPACE__ )->__( 'Date added' ) . ' ' .
			date( 'd/m/Y', strtotime( $order_info['date_added'] ) ) . '<br>';

		$text .= ADK( __NAMESPACE__ )->__( 'Order status' ) . ' ' .
			ADK( __NAMESPACE__ )->get_order_status_name( $order_info['order_status_id'], $order_info['language_id'] ) .
			'<br><br>';

		if ( $comment ) {
			$text .= ADK( __NAMESPACE__ )->__( 'Instructions' ) . '<br><br>';
			$text .= $comment . '<br><br>';
		}

		// Products
		$text .= ADK( __NAMESPACE__ )->__( 'Products' ) . '<br>';
		$text .= $this->shortcode_order_products(); 
		$text .= $this->shortcode_order_vouchers();
		$text .= '<br>';

		$text .= ADK( __NAMESPACE__ )->__( 'Total' ) . '<br>';
		$text .= $this->shortcode_order_totals();
		$text .= '<br>';

		if ($order_info['customer_id']) {
			$text .= ADK( __NAMESPACE__ )->__( 'Link to the order' ) . '<br>';
			$text .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . '<br><br>';
		}

		if ( ADK( __NAMESPACE__ )->get_order_downloaded_products( $order_id ) ) {
			$text .= ADK( __NAMESPACE__ )->__( 'Download' ) . '<br>';
			$text .= $order_info['store_url'] . 'index.php?route=account/download' . '<br><br>';
		}

		// Comment
		if ($order_info['comment']) {
			$text .= ADK( __NAMESPACE__ )->__( 'Comment' ) . '<br><br>';
			$text .= $order_info['comment'] . '<br><br>';
		}

		return $text;
	}

	/**
	 * Returns order creation data
	 * @return sting
	 */
	public function shortcode_order_date_added() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( isset( $order_info['date_added'] ) ) {
			$ret = $order_info['date_added' ];
		}

		return $ret;
	}

	/**
	 * Returns comment added by customer to the order
	 * @return string
	 */
	public function shortcode_order_comment() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( isset( $order_info['comment'] ) ) {
			$ret = $order_info['comment' ];
		}

		return $ret;
	}

	/**
	 * Returns comment added on order status change
	 * @return string
	 */
	public function shortcode_order_status_comment() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->caller_args[2] ) ) {
			$ret = ADK( __NAMESPACE__ )->caller_args[2];

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = ADK( __NAMESPACE__ )->__( 'Your order has been approved' );
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents if an order change status comment is present
	 * @return string
	 */
	public function shortcode_if_order_status_comment( $shortcode ) {
		return (boolean)$this->shortcode_order_status_comment();
	}

	/**
	 * Conditional tag. Shows contents if an order change status comment is not present
	 * @return string
	 */
	public function shortcode_if_order_status_no_comment( $shortcode ) {
		return ! $this->shortcode_order_status_comment();
	}

	/**
	 * Conditional tag. Shows contents if comment to an order is present
	 * @return string
	 */
	public function shortcode_if_order_comment( $shortcode ) {
		return (boolean)$this->shortcode_order_comment();
	}

	/**
	 * Conditional tag. Shows contents if comment to an order is not present
	 * @return string
	 */
	public function shortcode_if_order_no_comment( $shortcode ) {
		return ! $this->shortcode_order_comment();
	}

	/**
	 * Returns a new order status name
	 * @return string
	 */
	public function shortcode_order_status() {
		return $this->shortcode_order_status_new();
	}

	/**
	 * Returns a new order status name
	 * @return string
	 */
	public function shortcode_order_status_new() {
		$ret = '';

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( $order_info && isset( $order_info['order_status_id'] ) && isset( $order_info['language_id'] ) ) {
			$ret = ADK( __NAMESPACE__ )->get_order_status_name( $order_info['order_status_id'], $order_info['language_id'] );
		}

		return $ret;
	}

	/**
	 * Returns a previous order status name
	 * @return string
	 */
	public function shortcode_order_status_old() {
		$ret = '';

		if( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( $order_info && isset( $order_info['order_status'] ) ) {
			$ret = $order_info['order_status'];
		}

		return $ret;
	}

	/**
	 * Returns link to an order page
	 * @return string
	 */
	public function shortcode_order_url() {
		$ret = '';
		$args = func_get_args();
		$text = empty( $args[1] ) ? ADK( __NAMESPACE__ )->__( 'Order' ) : $args[1];
		$order = ADK( __NAMESPACE__ )->get_from_cache( 'old_order' );

		if( ! $order && defined( 'PREVIEW' ) ) {
			$order = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if ( isset( $order['order_id'] ) && isset( $order['store_url'] ) ) {
			$ret = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				$order['store_url'] . 'index.php?route=account/order/info&order_id=' . $order['order_id'],
				$text
			);

		}

		return $ret;
	}

	/**
	 * Returns link to the download order page
	 * @return type
	 */
	public function shortcode_download_url() {
		$ret = '';
		$args = func_get_args();
		$text = empty( $args[1] ) ? ADK( __NAMESPACE__ )->__( 'Download' ) : $args[1];

		$ret = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER ) . 'index.php?route=account/download',
			$text
		);

		return $ret;
	}

	/**
	 * Returns product(s) details for an order
	 * @return string
	 */
	public function shortcode_order_products() {
		$text = '';

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( $order_info && isset( $order_info['order_id'] ) ) {
			$order_id = $order_info['order_id'];

			foreach ( ADK( __NAMESPACE__ )->get_order_products( $order_id ) as $product ) {
				$text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' .
					html_entity_decode(
						ADK( __NAMESPACE__ )->currency->format(
							$product['total'] + ( ADK( __NAMESPACE__ )->config->get( 'config_tax' ) ? ( $product['tax'] * $product['quantity'] ) : 0 ),
							$order_info['currency_code'],
							$order_info['currency_value']
						),
						ENT_NOQUOTES,
						'UTF-8'
					) . '<br>';

				$order_option_query = ADK( __NAMESPACE__ )->db->query( "SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'" );

				foreach ($order_option_query->rows as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];

					} else {
						$upload_info = ADK( __NAMESPACE__ )->model_tool_upload->getUploadByCode( $option['value'] );

						if ( $upload_info ) {
							$value = $upload_info['name'];

						} else {
							$value = '';
						}
					}

					$text .= chr(9) . '-' . $option['name'] . ' ' . ( utf8_strlen( $value ) > 20 ? utf8_substr( $value, 0, 20 ) . '..' : $value ) . '<br>';
				}
			}
		}

		return $text;
	}

	/**
	 * Conditional tag. Shows contents if order does not contain all of the products with specific SKU
	 * @return string
	 */
	public function shortcode_if_no_products_sku_all() {
		return ! call_user_func_array( array( $this, 'shortcode_if_products_sku' ), func_get_args() );
	}

	/**
	 * Conditional tag. Shows contents if order contains at least one product with specific SKU
	 * @return string
	 */
	public function shortcode_if_products_sku() {
		$ret = false;

		// List of products SKUs
		$products = array_slice( func_get_args(), 1 );

		if( ! $products ) {
			return $ret;
		}

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( $order_info && isset( $order_info['order_id'] ) ) {
			$product_ids = array();

			foreach ( ADK( __NAMESPACE__ )->get_order_products( $order_info['order_id'] ) as $product ) {
				$product_ids[] = $product['product_id'];
			}

			foreach( ADK( __NAMESPACE__ )->get_products_by_id( $product_ids ) as $product ) {
				if( in_array( $product['sku'], $products ) ) {
					$ret = true;
					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents if order contains all the product with specific SKU
	 * @return string
	 */
	public function shortcode_if_products_sku_all() {
		$ret = false;

		// List of products SKUs
		$products = array_slice( func_get_args(), 1 );

		if( ! $products ) {
			return $ret;
		}

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( $order_info && isset( $order_info['order_id'] ) ) {
			$product_ids = array();

			foreach ( ADK( __NAMESPACE__ )->get_order_products( $order_info['order_id'] ) as $product ) {
				$product_ids[] = $product['product_id'];
			}

			$skus = array();

			foreach( ADK( __NAMESPACE__ )->get_products_by_id( $product_ids ) as $product ) {
				$skus[] = $product['sku'];
			}

			$ret = (boolean)$skus;

			foreach( $products as $product_sku ) {
				if( ! in_array( $product_sku, $skus ) ) {
					$ret = false;
					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Conditional tag. Shows contents if order does not contain at least one of the product with specific SKU
	 * @return string
	 */
	public function shortcode_if_no_products_sku() {
		return ! call_user_func_array( array( $this, 'shortcode_if_products_sku_all' ), func_get_args() );
	}

	/**
	 * Returns list of vouchers pertain to an order
	 * @return string
	 */
	public function shortcode_order_vouchers() {
		$text = '';

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( $order_info && ( isset( $order_info['order_id'] ) ) ) {
			$order_id = $order_info['order_id'];

			foreach ( ADK( __NAMESPACE__ )->get_order_vouchers( $order_id ) as $voucher) {
				$text .= '1x ' . $voucher['description'] . ' ' .
					ADK( __NAMESPACE__ )->currency->format(
						$voucher['amount'],
						$order_info['currency_code'],
						$order_info['currency_value']
					);
			}
		}

		return $text;
	}

	/**
	 * Returns an order totals list
	 * @return string
	 */
	public function shortcode_order_totals() {
		$text = '';

		if( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_order_info( ADK( __NAMESPACE__ )->caller_args[0] );

		} elseif ( defined( 'PREVIEW' ) ) {
			$order_info = ADK( __NAMESPACE__ )->get_sample_order();
		}

		if( $order_info && ( isset( $order_info['order_id'] ) ) ) {
			$order_id = $order_info['order_id'];

			foreach ( ADK( __NAMESPACE__ )->get_order_totals( $order_id ) as $total ) {
				$text .= $total['title'] . ': ' . html_entity_decode(
					ADK( __NAMESPACE__ )->currency->format(
						$total['value'],
						$order_info['currency_code'],
						$order_info['currency_value']
					),
					ENT_NOQUOTES,
					'UTF-8'
				) . '<br>';
			}
		}

		return $text;
	}

	/**
	 * Returns newsletter subscriber's email
	 * @return type
	 */
	public function shortcode_subscriber_email() {
		$ret = '';

		if ( ADK( __NAMESPACE__ )->adk_subscriber_email ) {
			$ret = ADK( __NAMESPACE__ )->adk_subscriber_email;

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'john_smith@gmail.com';
		}

		return $ret;
	}

	/**
	 * Returns newsletter subscriber's name
	 * @return type
	 */
	public function shortcode_subscriber_name() {
		$ret = '';

		if ( ADK( __NAMESPACE__ )->adk_subscriber_name ) {
			$ret = ADK( __NAMESPACE__ )->adk_subscriber_name;

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'John Smith';
		}

		return $ret;
	}

	/**
	 * Returns newsletter name
	 * @return type
	 */
	public function shortcode_newsletter_name() {
		$ret = '';

		if ( ADK( __NAMESPACE__ )->adk_newsletter_name ) {
			$ret = ADK( __NAMESPACE__ )->adk_newsletter_name;

		} elseif ( ADK( __NAMESPACE__ )->adk_newsletter_id == 0 ) {
			$ret = ADK( __NAMESPACE__ )->__( '%s subscription', $this->shortcode_store_name() );

		} elseif ( defined( 'PREVIEW' ) ) {
			$ret = 'Arrival of a new product';
		}

		return $ret;
	}

	/**
	 * Returns confirm subscription link
	 * @return string
	 */
	public function shortcode_confirm_subscription_url() {
		$args = func_get_args();
		$text = empty( $atgs[1] ) ? ADK( __NAMESPACE__ )->__( 'Confirm subscription' ) : $args[1];
		$code = uniqid();
		$url = ADK( __NAMESPACE__ )->u()->catalog_url( true ) .
			'index.php?route=' . ADK( __NAMESPACE__ )->type . '/' . ADK( __NAMESPACE__ )->code . '/confirm_subscription&code=' . $code;

		if ( ADK( __NAMESPACE__ )->adk_subscriber_email && ADK( __NAMESPACE__ )->adk_newsletter_id ) {

			$result = ADK( __NAMESPACE__ )->q( array(
				'table' => ADK( __NAMESPACE__ )->newsletter_code_table,
				'query' => 'insert',
				'values' => array(
					'code'       => $code,
					'newsletter' => ADK( __NAMESPACE__ )->adk_newsletter_id,
					'operation'  => 1,
					'expiration' => ADK( __NAMESPACE__ )->get_sql_expiration_date( 'confirm_subscription' ),
					'email'      => ADK( __NAMESPACE__ )->adk_subscriber_email,
				),
			) );

			if ( ! $result ) {
				trigger_error(
					sprintf(
						'Failed to add subscription confirmation code into DB, email: "%s", newsletter ID: "%s"',
						ADK( __NAMESPACE__ )->adk_subscriber_email,
						ADK( __NAMESPACE__ )->adk_newsletter_id
					)
				);

				return '';
			}

		} elseif ( ! defined( 'PREVIEW' ) ) {
			return '';

		}

		return sprintf(
			'<a href="%s" target="_blank">%s</a>',
			$url,
			$text
		);
	}

	/**
	 * Returns shipping address line 1
	 * @return string
	 */
	public function shortcode_shipping_address_line1() {
		$ret = '';
		$data = array();

		if ( defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$data['order_id'] = ADK( __NAMESPACE__ )->caller_args[0];
		}

		if ( ! empty( $data['order_id'] ) ) {
			$order = ADK( __NAMESPACE__ )->get_order_info( $data['order_id'] );

			if ( $order['shipping_address_1'] ) {
				$ret = $order['shipping_address_1'];
			}
		}

		return $ret;
	}

	/**
	 * Returns shipping address line 2
	 * @return string
	 */
	public function shortcode_shipping_address_line2() {
		$ret = '';
		$data = array();

		if ( defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$data['order_id'] = ADK( __NAMESPACE__ )->caller_args[0];
		}

		if ( ! empty( $data['order_id'] ) ) {
			$order = ADK( __NAMESPACE__ )->get_order_info( $data['order_id'] );

			if ( $order['shipping_address_2'] ) {
				$ret = $order['shipping_address_2'];
			}
		}

		return $ret;
	}

	/**
	 * Returns shipping address city
	 * @return string
	 */
	public function shortcode_shipping_city() {
		$ret = '';
		$data = array();

		if ( defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$data['order_id'] = ADK( __NAMESPACE__ )->caller_args[0];
		}

		if ( ! empty( $data['order_id'] ) ) {
			$order = ADK( __NAMESPACE__ )->get_order_info( $data['order_id'] );

			if ( $order['shipping_city'] ) {
				$ret = $order['shipping_city'];
			}
		}

		return $ret;
	}

	/**
	 * Returns shipping address country
	 * @return string
	 */
	public function shortcode_shipping_country() {
		$ret = '';
		$data = array();

		if ( defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$data['order_id'] = ADK( __NAMESPACE__ )->caller_args[0];
		}

		if ( ! empty( $data['order_id'] ) ) {
			$order = ADK( __NAMESPACE__ )->get_order_info( $data['order_id'] );

			if ( $order['shipping_country'] ) {
				$ret = $order['shipping_country'];
			}
		}

		return $ret;
	}

	/**
	 * Returns shipping address postcode
	 * @return string
	 */
	public function shortcode_shipping_postcode() {
		$ret = '';
		$data = array();

		if ( defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$data['order_id'] = ADK( __NAMESPACE__ )->caller_args[0];
		}

		if ( ! empty( $data['order_id'] ) ) {
			$order = ADK( __NAMESPACE__ )->get_order_info( $data['order_id'] );

			if ( $order['shipping_postcode'] ) {
				$ret = $order['shipping_postcode'];
			}
		}

		return $ret;
	}

	/**
	 * Returns shipping address line 1
	 * @return string
	 */
	public function shortcode_shipping_state() {
		$ret = '';
		$data = array();

		if ( defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$data['order_id'] = ADK( __NAMESPACE__ )->caller_args[0];
		}

		if ( ! empty( $data['order_id'] ) ) {
			$order = ADK( __NAMESPACE__ )->get_order_info( $data['order_id'] );

			if ( $order['shipping_zone'] ) {
				$ret = $order['shipping_zone'];
			}
		}

		return $ret;
	}

	/**
	 * Returns shipping address name
	 * @return string
	 */
	public function shortcode_shipping_name() {
		$ret = '';
		$data = array();

		if ( defined( 'PREVIEW' ) ) {
			$data = ADK( __NAMESPACE__ )->get_sample_order();

		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data' ) ) {
			$data = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

		} elseif( isset( ADK( __NAMESPACE__ )->caller_args[0] ) ) {
			$data['order_id'] = ADK( __NAMESPACE__ )->caller_args[0];
		}

		if ( ! empty( $data['order_id'] ) ) {
			$order = ADK( __NAMESPACE__ )->get_order_info( $data['order_id'] );

			if ( $order['shipping_firstname'] && $order['shipping_lastname'] ) {
				$ret = $order['shipping_firstname'] . ' ' . $order['shipping_lastname'];
			}
		}

		return $ret;
	}

	/**
	 * Auto detect product(s) pertain to email 
	 * @param int|null $default_product_id Bounce product ID
	 * @return int|array
	 */
	public function get_shortcode_product_id( $default_product_id = null ) {
		$ret = $default_product_id;

		// Newsletter to a specific product
		if( isset( ADK( __NAMESPACE__ )->request->post['product'] ) ) {
			$ret = ADK( __NAMESPACE__ )->request->post['product'];

		// New order case
		} elseif ( ADK( __NAMESPACE__ )->has_in_cache( 'old_order_data') ) {
			$order = ADK( __NAMESPACE__ )->get_from_cache( 'old_order_data' );

			$ids = ADK( __NAMESPACE__ )->q( array(
				'table'  => 'order_product',
				'query'  => 'select',
				'fields' => 'product_id',
				'where'  => array(
					'field'     => 'order_id',
					'operation' => '=',
					'value'     => $order['order_id']
				),
			) );

			$r = array();

			foreach ( $ids as $id ) {
				$r[] = $id['product_id'];
			}

			if ( $r ) {
				$ret = $r;
			}

		// Return case
		} elseif ( $return_id = $this->shortcode_return_id() ) {
			$return = ADK( __NAMESPACE__ )->get_return_data( $return_id );
			if( isset( $return['product_id'] ) ) {
				$ret = $return['product_id'];
			}
		}

		return $ret;
	}

	/**
	 * Auto-detects customer group ID for customer pertain to email
	 * @return int
	 */
	public function get_shortcode_customer_group_id() {
		$ret = 1;
		$customer = ADK( __NAMESPACE__ )->get_mail_customer();

		if( $customer && isset( $customer['customer_group_id'] ) ) {
			$ret =  $customer['customer_group_id'];
		} 

		return $ret;
	}

	/**
	 * Returns vitrine shortcode specific products
	 * @param array $shortcode Vitrine shortcode
	 * @return array
	 */
	public function get_vitrine_products( $shortcode ) {
		$type = isset( $shortcode['data']['type'] ) ? $shortcode['data']['type'] : 'bestseller';
		$limit = isset( $shortcode['data']['number'] ) ? $shortcode['data']['number'] : 3;

		if( ! empty( $shortcode['data']['related'] ) || 'related' === $type ) {
			$default_product_id = isset( $shortcode['data']['product']['default'] ) ?
				$shortcode['data']['product']['default'] : null;

			$product_id = $this->get_shortcode_product_id( $default_product_id );

		} else {
			$product_id = null;
		}

		switch( $type ) {
			case 'bestseller' :
				$products = ADK( __NAMESPACE__ )->get_products( array(
					'limit'             => $limit,
					'sort'              => 'sold',
					'order'             => 'DESC',
					'customer_group_id' => $this->get_shortcode_customer_group_id(),
					'product_id'        => $product_id,
				) );
				break;
			case 'latest' :
				$products = ADK( __NAMESPACE__ )->get_products( array(
					'limit'             => $limit,
					'sort'              => 'added',
					'order'             => 'DESC',
					'customer_group_id' => $this->get_shortcode_customer_group_id(),
					'product_id'        => $product_id,
				) );
				break;
			case 'popular' :
				$products = ADK( __NAMESPACE__ )->get_products( array(
					'limit'             => $limit,
					'sort'              => 'viewed',
					'order'             => 'DESC',
					'customer_group_id' => $this->get_shortcode_customer_group_id(),
					'product_id'        => $product_id,
				) );
				break;
			case 'special' :
				$products = ADK( __NAMESPACE__ )->get_products( array(
					'sort'              => 'viewed',
					'order'             => 'DESC',
					'customer_group_id' => $this->get_shortcode_customer_group_id(),
					'product_id'        => $product_id,
				) );

				$p = array();
				foreach( $products as $product ) {
					if( isset( $product['special'] ) ) {
						$p[] = $product;
						if( count( $p ) >= $limit ) {
							break;
						} 
					}
				}

				$products = $p;
				break;
			case 'related' :
				$products = ADK( __NAMESPACE__ )->get_products( array(
					'limit'             => $limit,
					'sort'              => 'viewed',
					'order'             => 'DESC',
					'customer_group_id' => $this->get_shortcode_customer_group_id(),
					'product_id'        => $product_id,
					'related'           => true,
				) );
				break;
			case 'arbitrary' :
				$arbitrary = isset( $shortcode['data']['product']['arbitrary'] ) ?
					$shortcode['data']['product']['arbitrary'] : array();

				$products = ADK( __NAMESPACE__ )->get_products( array(
					'limit'             => $limit,
					'sort'              => 'viewed',
					'order'             => 'DESC',
					'customer_group_id' => $this->get_shortcode_customer_group_id(),
					'arbitrary'         => $arbitrary,
				) );
				break;
			default:
				$products = ADK( __NAMESPACE__ )->get_products();
				break;
		}

		return $products;
	}
}
