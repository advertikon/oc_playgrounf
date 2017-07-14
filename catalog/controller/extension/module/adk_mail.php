<?php
/**
 * Catalog controller
 * @package Mail template manager
 * @author Advertikon
 * @version 0.7.2
 */
class ControllerModuleAdkMail extends Controller {

	protected $model = null;

	/**
	 * Returns subscription widget
	 * @param array $setting Widget settings
	 * @return type
	 */
	public function index( $setting ) {
		if ( ! empty( $setting['status'] ) ) {
			return $this->a->get_widget_script( $setting['widget_id'] );
		} 
	}

	/**
	 * Class constructor
	 * @param Object $registry 
	 * @return void
	 */
	public function __construct( $registry ) {
		parent::__construct( $registry );

		$this->a = \Advertikon\Mail\Advertikon::instance();
	}

	/**
	 * Unsubscribe action
	 * @return void
	 */
	public function unsubscribe() {
		$data['title'] = $this->a->__( 'Subscription cancellation' );

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link(
				'common/home',
				'SSL'
			)
		);

		try {

			if( ! isset( $this->request->get['code'] ) ) {
				throw new \Advertikon\Exception( $this->a->caption( 'caption_cancellation_code_missing' ) );
			}

			$c = $this->request->get['code'];

			// If test mode skip all the stuff
			if( 'test' !== $c ) {
				$this->a->remove_expired_code();

				$code = $this->a->q( array(
					'table' => $this->a->newsletter_code_table,
					'query' => 'select',
					'where' => array(
						array(
							'field'     => 'code',
							'operation' => '=',
							'value'     => $c,
						),
						array(
							'field'     => 'operation',
							'operation' => '=',
							'value'     => \Advertikon\Mail\Advertikon::NEWSLETTER_CODE_CANCEL,
						),
					),
				) );

				if ( ! count( $code ) ) {
					throw new \Advertikon\Exception( $this->a->caption( 'caption_cancellation_code_expired' ) );
				}

				// Non-opencart newsletter
				if ( $code['newsletter'] > 0 ) {
					$newsletter = $this->a->q( array(
						'table' => $this->a->newsletter_list_table,
						'query' => 'select',
						'where' => array(
							array(
								'field'     => 'id',
								'operation' => '=',
								'value'     => $code['newsletter'],
							)
						),
					) );

					// Subscriber can cancel even removed subscription
					if ( ! count( $newsletter ) ) {
						$this->a->adk_newsletter_id = '';
						$this->a->adk_newsletter_name = '';

					} else {
						$this->a->adk_newsletter_id = $code['newsletter'];
						$this->a->adk_newsletter_name = $newsletter['name'];
					}

					$subscriber = $this->a->q( array(
						'table' => $this->a->newsletter_subscribers_table,
						'query' => 'select',
						'where' => array(
							array(
								'field'     => 'email',
								'operation' => '=',
								'value'     => $code['email'] 
							),
							array(
								'field'     => 'newsletter',
								'operation' => '=',
								'value'     => $code['newsletter']
							),
							array(
								'field'     => 'status',
								'operation' => '<>',
								'value'     => \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_CANCELLED,
							),
						),
					) );

					if ( ! count( $subscriber ) ) {
						$this->a->remove_newsletter_code( $c );
						throw new \Advertikon\Exception( $this->a->caption( 'caption_cancellation_newsletter_missing' ) );
					}

					$this->a->adk_subscriber_name = $subscriber['name'];
					$this->a->adk_subscriber_email = $subscriber['email'];

				} else {

					// All this just to get subscriber's name
					$subscriber = $this->a->q( array(
						'table' => 'customer',
						'query' => 'select',
						'where' => array(
							array(
								'field'     => 'email',
								'operation' => '=',
								'value'     => $code['email'],  
							),
							array(
								'field'     => 'newsletter',
								'operation' => '=',
								'value'     => 1,
							),
						),
					) );

					if ( ! count( $subscriber ) ) {
						$this->a->remove_newsletter_code( $c );
						throw new \Advertikon\Exception( $this->a->caption( 'caption_cancellation_newsletter_missing' ) );
					}

					$this->a->adk_newsletter_id = 0;
					$this->a->adk_newsletter_name = $this->config->get( 'config_name' ) . ' newsletter';
					$this->a->adk_subscriber_name = $subscriber['firstname'] . ' ' . $subscriber['lastname'];
					$this->a->adk_subscriber_email = $subscriber['email'];
				}

				if ( ! $this->a->unsubscribe( $code ) ) {
					throw new \Advertikon\Exception( $this->a->caption( 'caption_cancellation_server_error' ) );
				}

				$this->a->remove_newsletter_code( $c );

				if ( 0 == $code['newsletter'] || $newsletter['notify_unsubscription'] ) {
					global $adk_mail_hook;

					$mail = new Mail();
					$this->a->init_mail( $mail, array(
						'to'      => $subscriber['email'],
						'subject' => 'Newsletter cancellation',
						'html'    => 'You subscription to newsletter ' . $this->adk_newsletter_name . ' has been successfully canceled<br>' . $this->config->get( 'config_name' ),
						'sender'  => $this->config->get( 'config_name' )
					) );

					$adk_mail_hook = 'newsletter.unsubscribe.' . $this->adk_newsletter_id;
					$mail->send();
				}
			}

			$data['success'] = $this->a->caption( 'caption_cancellation_success' );

		} catch ( \Advertikon\Exception $e ) {
			$data['error_warning'] = $e->getMessage();
		}

		$data['helper']      = $this->a;
		$data['header']      = $this->load->controller( 'common/header' );
		$data['column_left'] = $this->load->controller( 'common/column_left' );
		$data['footer']      = $this->load->controller( 'common/footer' );
		$data['a']           = $this->a;

		$this->response->setOutput(
			$this->load->view(
				$this->a->get_view_route(
					 $this->a->type . '/' . $this->a->code
					),
				$data
			)
		);
	}

	/**
	 * Show archive email action
	 * @return type
	 */
	public function archive() {
		if( isset( $this->request->get['email'] ) ) {
			$file = $this->request->get['email'];
			$path = $this->a->archive_dir . $file;

			if ( is_file( $path ) ) {
				echo file_get_contents( $path );
				die;
			}
		}

		$data['title'] = $this->a->__( 'Archive' );

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link(
				'common/home',
				'SSL'
			)
		);

		$data['error_warning'] = $this->a->caption( 'caption_archive_missing' );
		$data['helper']      = $this->a;
		$data['header']      = $this->load->controller( 'common/header' );
		$data['column_left'] = $this->load->controller( 'common/column_left' );
		$data['footer']      = $this->load->controller( 'common/footer' );
		$data['a']           = $this->a;

		$this->response->setOutput(
			$this->load->view(
				$this->a->get_view_route(
					 $this->a->type . '/' . $this->a->code
					),
				$data
			)
		);
	}

	/**
	 * Tracking pixel action
	 * @return void
	 */
	public function track() {
		if ( isset( $this->request->get['email_id'] ) ) {
			$this->a->mark_as_viwed( $this->request->get['email_id'] );
		}

		header( 'Content-Type: image/png' );
		$img = imagecreate( 1, 1 );
		$color = imagecolorallocate( $img, 0, 0, 0 );
		imagefill( $img, 0, 0, $color );
		imagepng( $img );
		imagedestroy( $img );
	}

	/**
	 * Subscription widget action
	 * @return void
	 */
	public function widget() {
		$data = $this->a->subscribe_widget_defaults;
		$ret = '';
		$id = '';

		if ( ! empty( $this->request->get['id'] ) ) {
			$id = $this->request->get['id'];

			$widget = $this->a->q( array(
				'table' => $this->a->newsletter_widget_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
			) );

			if ( count( $widget ) > 0 ) {
				$data = array_merge( $data, $this->a->object_to_array( json_decode( $widget['data'] ) ) );

			} else {
				trigger_error( sprintf( 'Widget with ID "%s" doesn\'t exist', $id ) );
			}
		}

		$action = '';

		if ( isset( $id ) ) {
			$action = htmlentities(
				$this->a->get_store_url() .
					'index.php?route=' . $this->a->type . '/' . $this->a->code . '/subscribe'
			);
		}

		$data['id'] = $id;
		$data['action'] = $action;
		$data['invalid_email'] = $this->a->caption( 'caption_widget_email_error' );
		$data['invalid_name'] = $this->a->caption( 'caption_widget_name_error' );
		$data['a'] = $this->a;

		$ret = $this->load->view( $this->a->get_template( $this->a->type . '/advertikon/mail/widget_body' ), $data );
		$this->response->addHeader( 'Content-Type: text/javascript' );
		$this->response->setOutput( $this->a->remove_whitespaces( $ret ) );
	}

	/**
	 * Subscription widget styles action
	 * @return void
	 */
	public function widget_css() {
		$data = $this->a->subscribe_widget_defaults;
		$ret = '';
		$id = '';

		if ( ! empty( $this->request->get['id'] ) ) {
			$id = $this->request->get['id'];

			$widget = $this->a->q( array(
				'table' => $this->a->newsletter_widget_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
			) );

			if ( $widget ) {
				$data = array_merge( $data, $this->a->object_to_array( json_decode( $widget['data'] ) ) );

			} else {
				trigger_error( sprintf( 'Widget with ID "%s" doesn\'t exist', $id ) );
			}
		}

		$data['id'] = $id;
		$data['a']  = $this->a;

		$ret = $ret = $this->load->view( $this->a->get_template( $this->a->type . '/advertikon/mail/widget_css' ), $data );
		header( 'Content-Type: text/css' );
		echo $this->a->remove_whitespaces( $ret );
		die;
	}

	public function log() {
		$this->a->console->tail();
	}

	/**
	 * Unprivileged subscribe action
	 * @return void
	 */
	public function subscribe() {
		$data = array();

		try {

			if ( ! $this->a->p( 'email' ) ) {
				trigger_error( 'Failed to create subscription - e-mail address is missing' );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_email_missing' ) );
			}

			$email = $this->a->p( 'email' );

			if ( ! $this->a->p( 'name' ) ) {
				trigger_error( 'Failed to create subscription - subscriber\'s name is missing' );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_name_missing') );
			}

			$name = $this->a->p( 'name' );

			if ( ! $this->a->p( 'widget_id' ) ) {
				trigger_error( 'Failed to create subscription - widget ID missing' );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_server_error' ) );
			}

			$widget_id = $this->a->p( 'widget_id' );

			$widget = $this->a->q( array(
				'table' => $this->a->newsletter_widget_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     =>$widget_id,
				),
			) );

			if ( ! count( $widget ) ) {
				trigger_error( sprintf( 'Failed to create subscription - widget with ID #"%s" is missing', $widget_id ) );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_server_error' ) );
			}

			$newsletter = $this->a->get_newsletter_by_widget( $widget_id );

			if ( ! count( $newsletter ) ) {
				trigger_error( sprintf( 'Failed to create subscription - widget"%s" is not associated with newsletter', $widget['name'] ) );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_server_error' ) );
			}

			// Fill in shortcodes data
			$this->a->adk_subscriber_email = $email;
			$this->a->adk_subscriber_name = $name;
			$this->a->adk_newsletter_id = $newsletter['id'];
			$this->a->adk_newsletter_name = $newsletter['name'];

			if ( $this->a->check_subscriber( $email, $newsletter['id'] ) ) {
				throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_exists' ) );
			}

			$s_data = array(
				'name'       => $name,
				'email'      => $email,
				'newsletter' => $newsletter['id'], 
			);

			if ( 1 == $newsletter['double_opt_in'] ) {

				// Waiting for verification 
				$s_data['status'] = \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_VERIFICATION;
				$data['success'] = $this->a->caption( 'caption_subscribe_double_opt_in' );

			} else {
				$s_data['status'] = \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE;
				$data['success'] = $this->a->caption( 'caption_subscribe_success' );
			}

			if( ! $this->a->subscribe( $s_data ) ) {
				trigger_error( sprintf( 'Failed to create subscription - DB error' ) );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_server_error' ) );
			}

			// Send verification email, if needed
			if ( 1 == $newsletter['double_opt_in' ] ) {
				$mail  = new Mail();
				$this->a->init_mail( $mail, array(
					'to'      => $email,
					'subject' => $this->a->__( 'Subscription confirmation' ),
					'html'    => 'This email address was subscribed to newsletter {newsletter_name} from {store_name}.<br>To confirm subscription follow {confirm_subscription_url(this link)}.<br>If you got this email by mistake - just ignore it.<br>Regards,<br>{store_name}',
					'sender'  => $this->config->get( 'config_name' ),
 				) );

 				global $adk_mail_hook;
 				$adk_mail_hook = 'newsletter.confirm';
 				$email_result = $mail->send();

 				if ( false === $email_result ) {
 					trigger_error( sprintf( 'Failed to create subscription - Mailing error' ) );
					throw new \Advertikon\Exception( $this->a->caption( 'caption_subscribe_server_error' ) );
 				}

			} elseif ( $newsletter['notify_subscription'] ) {
				global $adk_mail_hook;
				$mail = new Mail();
				$this->a->init_mail( $mail, array(
					'to'      => $email,
					'subject' => 'Newsletter subscription',
					'html'    => 'You have been successfully subscribed to newsletter {newsletter_name}<br>{store_name}',
				) );

				$adk_mail_hook = 'newsletter.subscribe.' . $this->adk_newsletter_id;
				$mail->send();
			}

		} catch ( \Advertikon\Exception $e ) {
			$data['error_warning'] = $e->getMessage();
		}

		$data['title'] = $this->a->__( 'Subscription to newsletter' );

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link(
				'common/home',
				'SSL'
			)
		);

		$data['helper']      = $this->a;
		$data['header']      = $this->load->controller( 'common/header' );
		$data['column_left'] = $this->load->controller( 'common/column_left' );
		$data['footer']      = $this->load->controller( 'common/footer' );
		$data['a']           = $this->a;

		$this->response->setOutput(
			$this->load->view(
				$this->a->get_view_route(
					 $this->a->type . '/' . $this->a->code
					),
				$data
			)
		);
	} 

	/**
	 * Confirm subscription action
	 * @return void
	 */
	public function confirm_subscription() {

		try {

			if ( null === $this->a->request( 'code' ) ) {
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_missing_code') );
			}

			$code = $this->a->request( 'code' );

			// Clear out all the expired codes
			$this->a->remove_expired_code();

			// Fetch data associated with the code 
			$query = $this->a->q( array(
				'table' => $this->a->newsletter_code_table,
				'query' => 'select',
				'where' => array(
					array(
						'field'     => 'code',
						'operation' => '=',
						'value'     => $code,
					),
					array(
						'field'     => 'operation',
						'operation' => '=',
						'value'     => \Advertikon\Mail\Advertikon::NEWSLETTER_CODE_SUBSCRIBE,
					),
				),
			) );

			if ( ! count( $query ) ) {
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_expire_code' ) );
			}

			if ( ! $query['email'] ) {
				trigger_error( 'Failed to confirm subscription to newsletter - missing email address to be confirmed' );
				$this->log->write( $query );
				$this->a->remove_newsletter_code( $code );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_server_error' ) );
			}

			if ( ! $query['newsletter'] ) {
				trigger_error( 'Failed to confirm subscription to newsletter - missing newsletter ID to be confirmed to' );
				$this->log->write( $query );
				$this->a->remove_newsletter_code( $code );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_server_error' ) );
			}

			// Fetch newsletter details
			$newsletter = $this->a->q( array(
				'table' => $this->a->newsletter_list_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $query['newsletter'],
				),
			) );

			if ( ! count( $newsletter ) ) {
				trigger_error( sprintf( 'Failed to confirm subscription - newsletter with ID "%s" doesn\'t exist') );
				$this->a->remove_newsletter_code( $code );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_missing_newsletter' ) );
			}

			// Fetch subscriber data
			$subscriber = $this->a->q( array(
				'table' => $this->a->newsletter_subscribers_table,
				'query' => 'select',
				'where' => array(
					array(
						'field'     => 'email',
						'operation' => '=',
						'value'     => $query['email'],
					),
					array(
						'field'     => 'newsletter',
						'operation' => '=',
						'value'     => $query['newsletter'],
					),
				),
			) );

			if ( ! count( $subscriber ) ) {
				$this->a->remove_newsletter_code( $code );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_missing_subscription' ) );
			}

			if ( ! $subscriber['status'] || $subscriber['status'] != \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_VERIFICATION ) {
				$this->a->remove_newsletter_code( $code );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_wrong_status' ) );
			}

			// Fill in data for shortcodes
			$this->a->adk_newsletter_name = $newsletter['name'];
			$this->a->adk_newsletter_id = $newsletter['id'];
			$this->a->adk_subscriber_name = $subscriber['name'];
			$this->a->adk_subscriber_email = $subscriber['email'];

			// Confirm the subscription
			$confirm = $this->a->q( array(
				'table' => $this->a->newsletter_subscribers_table,
				'query' => 'update',
				'set'   => array(
					'status' => \Advertikon\Mail\Advertikon::NEWSLETTER_STATUS_ACTIVE,
				),
				'where' => array(
					array(
						'field'     => 'email',
						'operation' => '=',
						'value'     => $query['email'],
					),
					array(
						'field'     => 'newsletter',
						'operation' => '=',
						'value'     => $query['newsletter'],
					),
				),
			) );

			if ( ! $confirm ) {
				trigger_error( sprintf( 'Failed to confirm subscription of "%s" to "%s"', $subscriber['email'], $newsletter['name'] ) );
				throw new \Advertikon\Exception( $this->a->caption( 'caption_confirm_server_error' ) );
			}

			$data['success'] = $this->a->caption( 'caption_confirm_success' );
			$this->a->remove_newsletter_code( $code );

			if ( $newsletter['notify_subscription'] ) {
				global $adk_mail_hook;
				$mail = new Mail();
				$this->a->init_mail( $mail, array(
					'to'      => $subscriber['email'],
					'subject' => 'Newsletter subscription',
					'html'    => 'You have been successfully subscribed to newsletter ' . $this->adk_newsletter_name . '<br>' . $this->config->get( 'config_name' ),
					'sender'  => $this->config->get( 'config_name' ),
				) );

				$adk_mail_hook = 'newsletter.subscribe.' . $this->adk_newsletter_id;
				$mail->send();
			}

		} catch ( \Advertikon\Exception $e ) {
			$data['error_warning'] = $e->getMessage();
		}

		$data['title'] = $this->a->__( 'Confirmation of newsletter subscription' );
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link(
				'common/home',
				'SSL'
			)
		);

		$data['helper']      = $this->a;
		$data['header']      = $this->load->controller( 'common/header' );
		$data['column_left'] = $this->load->controller( 'common/column_left' );
		$data['footer']      = $this->load->controller( 'common/footer' );
		$data['a']           = $this->a;

		$this->response->setOutput(
			$this->load->view(
				$this->a->get_view_route(
					 $this->a->type . '/' . $this->a->code
					),
				$data
			)
		);
	}

	/**
	 * Cron action
	 * @return void
	 */
	public function cron() {
		$task = new \Advertikon\Task( $this->a );

		while( $task->fetch_new() ) {
			var_dump( $this->a->socket( trim( $task->task, '"' ) . '&id=' . $task->id, 'GET' ) );
		}
	}

	public function run_queue() {
		$timeout = (int)$this->a->config( 'queue_time' );
		$timeout = $timeout > 0 ? $timeout : 30;

		set_time_limit( $timeout );

		$id = $this->request->get['id'];

		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		ignore_user_abort(true);
		header("Connection: close");
		header("Content-Encoding: none");  
		ob_start();

		// Need to be non-empty string for Content-length header to appear   
		echo ' aa';  
		$size = ob_get_length();   
		header( "Content-Length: $size" );  
		ob_end_flush();
		@ob_flush();
		flush();

		if ( ! $this->a->config( 'queue' ) || ! $this->a->config( 'status' ) ) {
			return;
		}

		if ( ! file_exists( $this->a->tmp_dir . 'queue_status' ) ) {
			file_put_contents( $this->a->tmp_dir . 'queue_status', '' );

		} else {
			touch( $this->a->tmp_dir . 'queue_status' );
		}

		$task = new \Advertikon\Task( $this->a );
		if( ! $task->run_task( $id ) ) {
			return;
		}

		$this->a->run_queue();
		$task->stop_task( $id );
	}

	/**
	 * Removes all hanged tasks
	 * CRON interface
	 * @return void
	 */
	public function amend_task() {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		$task = new \Advertikon\Task( $this->h );
		$task->amend_task();
	}

	/**
	 * Cron job action to blacklist bounced emails
	 * @return type
	 */
	public function check_bounced() {
		try {
			$this->a->do_blacklist();

		} catch( Exception $e ) {
			trigger_error( $e->getMessage() );
		}
	}
}
