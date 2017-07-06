<?php
/**
 * Advertikon Socket Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 * 
 * @depend Url
 */

namespace Advertikon;

class Socket {

	/**
	 * Open socket connection
	 * @param String $address URL
	 * @param string $method Method (Default - HEAD)
	 * @param Integer $folow_redirects Maximum redirects count
	 * @param Array $data Data to be send
	 * @param integer $timeout Connection timeout
	 * @return Array
	 */
	public function socket( $address, $method = 'HEAD', $folow_redirects = 5, $data = array(), $timeout = 10 ) {

		$this->error_prefix[] = '[Socket]';
		$this->debug_prefix[] = '[Socket]';

		$output = array();

		if( is_null( $method ) ) {
			$method = 'HEAD';
		}

		if( is_null( $folow_redirects ) ) {
			$folow_redirects = 5;
		}

		if( is_null( $data ) ) {
			$data = array();
		}

		if( is_null( $timeout ) ) {
			$timeout = 10;
		}


		do {
			$socket = $this->socket_create( $address, $timeout );
			$this->socket_write( $socket, $address, $method, $data, $timeout );
			$output = $this->socket_read( $socket, $method , $timeout);

			fclose( $socket );

			$code = isset( $output['code'] ) ? $output['code'] : null;
			$location = isset( $output['headers']['Location'] ) ?
				$output['headers']['Location'] : '';

			if( $code == 302 || $code == 301 ) {
				ADK()->log(
					sprintf(
						'Redirect to %s detected, redirect counts remain: %s',
						$location,
						$folow_redirects
					),
					ADK()->log_debug_flag
				);
			}

			if( $folow_redirects > 0 && ( $code == 302 || $code == 301 ) && $location ) {
				$address = $location;
				$folow_redirects--;

			} else {
				$folow_redirects = 0;
			}

		} while( $folow_redirects > 0 );


		if( isset( $socket ) && 'resource' === gettype( $socket ) ) {
			fclose( $socket );
		}

		array_pop( $this->error_prefix );
		array_pop( $this->debug_prefix );

		return $output;
	}

	/**
	 * Creates socket
	 * @param string $address URL
	 * @param integer $timeout Connection timeout
	 * @throws Adk_Exception
	 * @return Resource
	 */
	protected function socket_create( $address, &$timeout) {

		$start = time();

		ADK()->log( 'Socket create start', ADK()->log_debug_flag );

		$protocol = 'http';
		$transport = 'tcp';

		if( ! filter_var( $address, FILTER_VALIDATE_IP ) ) {

			ADK()->log( 'Address is URL', ADK()->log_debug_flag );

			$components = new Url( $address );

			if( ! $components->get_host() ) {
				$mess = sprintf( 'Socket: Unable to parse URL %s', $address );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$address = $components->get_host();

			if( $components->get_scheme() ) {
				$protocol = $components->get_scheme();

				if( 'https' === strtolower( $protocol ) ) {
					$transport = 'ssl';
				}
 			}

		}

		$port = getservbyname( $protocol, 'tcp' );

		ADK()->log(
			'Transport: ' .$transport,
			'Protocol: ' . $protocol,
			'Port: ' . $port,
			ADK()->log_debug_flag
		);

		if( false === ( $socket = @stream_socket_client( "$transport://$address:$port", $errno, $errstr, $timeout ) ) ) {
			$mess = sprintf( 'Unable to create socket connection to :%s', "$transport://$address:$port" );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		ADK()->log( 'Socket successfully created', ADK()->log_debug_flag  );

		$timeout = $timeout - ( time() - $start );

		return $socket;
	}

	/**
	 * Write data into socket
	 * @param Resource $socket Socket descriptor
	 * @param String $address URL
	 * @param string $method Method
	 * @param array $data Data to send
	 * @return boolean
	 */
	protected function socket_write( $socket, $address, $method, $data, &$timeout ) {

		$start = time();

		$res = false;

		ADK()->log( 'Socket data write start', ADK()->log_debug_flag );

		$components = new Url( $address );
		$path = $components->get_path() .
			( ! $components->get_query() ? '?' . $components->get_query() : '' ) .
			( ! $components->get_fragment() ? '#' . $components->get_fragment() : '' );

		$in = "$method $path HTTP/1.1\r\n";
		$in .= "Host: {$components->get_host()}\r\n";
		$in .= "Connection: Close\r\n\r\n";


		ADK()->log( 'Data to write into socket:', $in, ADK()->log_debug_flag );

		$write_res = fwrite( $socket, $in );

		if( $write_res === strlen( $in ) ) {
			ADK()->log( 'Data have been written successfully', ADK()->log_debug_flag );
			$res = true;
		}

		$timeout = $timeout - ( time() - $start );

		if( ! $res ) {
			ADK()->log( 'Can not write into socket', ADK()->log_error_flag );
		}

		return $res;
	}

	/**
	 * Read data from socket
	 * @param Resource $socket Socket descriptor
	 * @throws Adk_Exception
	 * @return Array
	 */
	protected function socket_read( $socket, $method, $timeout ) {

		$block_size = 8192;
		$output = '';
		$ret = '';
		$content_length = -1;
		$read_usleep  = 10000;
		$read_max_count = floor( ( 1000000 * $timeout ) / $read_usleep );

		stream_set_blocking( $socket, 0 );

		ADK()->log( sprintf( 'Start to read data from socket by %s chunks', $block_size ), ADK()->log_debug_flag );

		$read_count = 0;
		$read_start = time();

		while ( ! feof( $socket ) ) {
			usleep( $read_usleep );
			$output .= fread( $socket, $block_size );

			// Get HTTP headers
			if( ! $ret && ( $pos = strpos( $output, "\r\n\r\n" ) ) !== false ) {

				$ret = $this->parse_http_header( substr( $output, 0, $pos ) );

				if( isset( $ret['headers']['Connection'] ) &&
					'close' === strtolower( $ret['headers']['Connection'] ) ) {

					if( strtolower( $method ) === 'head' ) {
						$content_length = 0;

					} elseif ( isset( $ret['headers']['Content-Length'] ) ) {
						$content_length = $ret['headers']['Content-Length'];

					} else {
						$content_length = 0;
					}
				}

				$output = substr( $output, $pos + 4 );
			}

			// If we got close connection header - check to close connection
			if( -1 !== $content_length && strlen( $output ) >= $content_length ) {
				ADK()->log(
					sprintf( 'Close connection, content length %s', $content_length ),
					ADK()->log_debug_flag
				);
				break;
			}

			if( time() - $read_start >= $timeout ) {
				ADK()->log(
					sprintf( 'Data read partly - exceeded read timeout of %s sec', $timeout ),
					 ADK()->log_debug_flag
				);
				break;
			}

			if( $read_count >= $read_max_count ) {
				ADK()->log(
					sprintf( 'Data read partly - exceeded %d read counts', $read_max_count ),
					ADK()->log_debug_flag
				);
				break;
			}

			$read_max_count++;
		}

		$ret['body'] = $output;

		ADK()->log( 'Socket\'s output:', $output, ADK()->log_debug_flag );

		return $ret;
	}

	/**
	 * Parses HTTP response to array
	 * @param String $response 
	 * @return Array
	 */
	public function parse_http_header( $header_str ) {

		$header = str_replace( "\r", '', $header_str );
		$h = array();
		$ret = array();

		$headers = explode( "\n", $header );

		foreach( $headers as $header ) {
			$header = trim( $header );

			if( empty( $ret['code'] ) && 'HTTP' === strtoupper( substr( $header, 0, 4 ) ) ) {
				preg_match( '/^http[^ ]+\s+(\d+)\s+(.+)/i', $header, $m );

				if( isset( $m[1] ) ) {
					$ret['code'] = $m[1];
				}

				if( isset( $m[2] ) ) {
					$ret['code_descr'] = $m[2];
				}

				continue;
			}

			$parts = explode( ': ', $header );

			if( isset( $parts[0] ) && isset( $parts[1] ) &&
				( $p1 = trim( $parts[0] ) ) && ( $p2 = trim( $parts[1] ) ) ) {

				$h[ $p1 ] = $p2;
			}
		}

		$ret['headers'] = $h;

		return $ret;
	}
}
$start );

		return $socket;
	}

	/**
	 * Write data into socket
	 * @param Resource $socket Socket descriptor
	 * @param String $address URL
	 * @param string $method Method
	 * @param array $data Data to send
	 * @return boolean
	 */
	protected function socket_write( $socket, $address, $method, $data, &$timeout ) {

		$start = time();

		$res = false;

		ADK()->log( 'Socket data write start', ADK()->log_debug_flag );

		$components = new Url( $address );
		$path = $components->get_path() .
			( ! $components->get_query() ? '?' . $components->get_query() : '' ) .
			( ! $components->get_fragment() ? '#' . $components->get_fragment() : '' );

		$in = "$method $path HTTP/1.1\r\n";
		$in .= "Host: {$components->get_host()}\r\n";
		$in .= "Connection: Close\r\n\r\n";