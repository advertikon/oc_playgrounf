<?php
namespace Advertikon {

class Console {

	protected $registry = null;
	protected $current_session_errors = array();
	protected $last_session_errors = array();
	protected $is_session_reseted = false;

	protected $sock     = null;
	protected $msgsock  = null;
	protected $mutex_fd = null;
	protected $fifo_fd  = null;

	protected $pwd_url = 'https://shop.advertikon.com.ua/support/console_pwd.php';
	protected $address = 'localhost';
	protected $port    = 1;

	protected $f_log    = null;
	protected $fifo_log = '';
	protected $mutex    = '';
	protected $file_log = '';

	// protected $f_sql = null;
	// protected $sql_fifo = __DIR__ . '/sql_fifo';
	// protected $sql_list = [];
	// protected $sql_start = null;
	// protected $sql_total = 0;
	// protected $f_performance = null;
	protected $first_log_line = true;
	protected $terminal       = true;
	protected $off            = "\e[0m";

	protected $idle       = 300;
	protected $idle_max   = 3600;
	protected $idle_min   = 1;
	protected $time_limit = 60;

	protected $exits = array( 'exit', 'quit', 'bye' );
	protected $stops = array( 'stop' );
	protected $functions = array(
		'posix_mkfifo',
		'posix_access',
	);

	static protected $is_log = false;

# Regular Colors
	protected $Black="\e[0;30m";       # Black
	protected $Red="\e[0;31m";         # Red
	protected $Green="\e[0;32m";       # Green
	protected $Yellow="\e[0;33m";      # Yellow
	protected $Blue="\e[0;34m";        # Blue
	protected $Purple="\e[0;35m";      # Purple
	protected $Cyan="\e[0;36m";        # Cyan
	protected $White="\e[0;37m";       # White

# Bold
	protected $BBlack="\e[1;30m";      # Black
	protected $BRed="\e[1;31m";        # Red
	protected $BGreen="\e[1;32m";      # Green
	protected $BYellow="\e[1;33m";     # Yellow
	protected $BBlue="\e[1;34m";       # Blue
	protected $BPurple="\e[1;35m";     # Purple
	protected $BCyan="\e[1;36m";       # Cyan
	protected $BWhite="\e[1;37m";      # White

# Underline
	protected $UBlack="\e[4;30m";      # Black
	protected $URed="\e[4;31m";        # Red
	protected $UGreen="\e[4;32m";      # Green
	protected $UYellow="\e[4;33m";     # Yellow
	protected $UBlue="\e[4;34m";       # Blue
	protected $UPurple="\e[4;35m";     # Purple
	protected $UCyan="\e[4;36m";       # Cyan
	protected $UWhite="\e[4;37m";      # White

# Background
	protected $On_Black="\e[40m";      # Black
	protected $On_Red="\e[41m";        # Red
	protected $On_Green="\e[42m";      # Green
	protected $On_Yellow="\e[43m";     # Yellow
	protected $On_Blue="\e[44m";       # Blue
	protected $On_Purple="\e[45m";     # Purple
	protected $On_Cyan="\e[46m";       # Cyan
	protected $On_White="\e[47m";      # White

# High Intensity
	protected $IBlack="\e[0;90m";      # Black
	protected $IRed="\e[0;91m";        # Red
	protected $IGreen="\e[0;92m";      # Green
	protected $IYellow="\e[0;93m";     # Yellow
	protected $IBlue="\e[0;94m";       # Blue
	protected $IPurple="\e[0;95m";     # Purple
	protected $ICyan="\e[0;96m";       # Cyan
	protected $IWhite="\e[0;97m";      # White

# Bold High Intensity
	protected $BIBlack="\e[1;90m";     # Black
	protected $BIRed="\e[1;91m";       # Red
	protected $BIGreen="\e[1;92m";     # Green
	protected $BIYellow="\e[1;93m";    # Yellow
	protected $BIBlue="\e[1;94m";      # Blue
	protected $BIPurple="\e[1;95m";    # Purple
	protected $BICyan="\e[1;96m";      # Cyan
	protected $BIWhite="\e[1;97m";     # White

# High Intensity backgrounds
	protected $On_IBlack="\e[0;100m";  # Black
	protected $On_IRed="\e[0;101m";    # Red
	protected $On_IGreen="\e[0;102m";  # Green
	protected $On_IYellow="\e[0;103m"; # Yellow
	protected $On_IBlue="\e[0;104m";   # Blue
	protected $On_IPurple="\e[0;105m"; # Purple
	protected $On_ICyan="\e[0;106m";   # Cyan
	protected $On_IWhite="\e[0;107m";  # White

	public function __construct( $registry ) {
		global $adk_console;
		$adk_console = $this;

		$this->registry = $registry;
		$this->fifo_log = __DIR__ . '/.stuff/fifo_log';
		$this->mutex    = __DIR__ . '/.stuff/mutex.pid';
		$this->file_log    = DIR_LOGS . 'adk.log';


		// $this->f_log = @fopen( $this->fifo_log, 'r+' );
		$this->f_log = fopen( $this->file_log, 'a+' );

		if ( $this->f_log ) {
			set_error_handler( array( $this, 'error' ) ); 
			// stream_set_blocking( $this->f_log, false );
		}

		// } else {
		// 	// $this->f_log = fopen( DIR_LOGS . 'console.log', 'a' );
		// }

		// if ( function_exists( 'posix_mkfifo' ) ) {
		// 	posix_mkfifo( $this->fifo_log, 0600 );
		// 	$this->f_sql = fopen( $this->sql_fifo, 'w' );
		// }


		// $this->f_sql = fopen( DIR_LOGS . 'sql.log', 'a' );
		// $this->f_performance = fopen( DIR_LOGS . 'performance.log', 'a' );

		// $sql_size = filesize( DIR_LOGS . 'sql.log' );
		// $log_size = filesize( DIR_LOGS . 'console.log' );
		// $p_size = filesize( DIR_LOGS . 'performance.log' );

		// if ( 1000000 < $log_size ) {
		// 	$this->truncate( 'log' );
		// }

		// if ( 1000000 < $p_size ) {
		// 	$this->truncate( 'performance' );
		// }

		// fwrite(
		// 	$this->f_sql,
		// 	$this->bg_color( $this->run_start( '(' . number_format( $sql_size ) . ')' ), 'blue' )
		// );
	}

	public function __destruct() {

		if ( is_resource( $this->f_log ) ) {
			$stat = fstat( $this->f_log );

			if ( $stat['size'] > 1024 * 1024 ) {
				ftruncate( $this->f_log, 0 );
			}
		}
		// $list = $this->sql_list;
		// $doubles = array();
		// $doubles_str = '';

		// Find repeated queries
		// while( $list ) {
		// 	$query = array_pop( $list );

		// 	$poss = array();
		// 	$pos = 0;

		// 	foreach( $list as $q ) {
		// 		if ( $q === $query ) {
		// 			$poss[] = $pos;
		// 		}

		// 		$pos++;
		// 	}

		// 	if ( $poss ) {
		// 		$doubles[] = array( 'count' => count( $poss ), 'query' => $query, );

		// 		foreach( $poss as $p ) {
		// 			array_splice( $list, $p, 1 );
		// 		}
		// 	}
		// }

		// if ( $doubles ) {
		// 	$doubles_str = PHP_EOL . $this->color( 'Repeated queries: ', 'red' ) . PHP_EOL;

		// 	foreach( $doubles as $q ) {
		// 		$count = $q['count'] + 1;

		// 		if ( $count > 3 ) {
		// 			$color = 'red';

		// 		} elseif ( $count > 2 ) {
		// 			$color = 'yellow';

		// 		} else {
		// 			$color = 'green';
		// 		}

		// 		$doubles_str .= $this->color( $count, $color ) . ' - ' . $q['query'] . PHP_EOL;
		// 	}
		// }

		// $this->sql(
		// 	$this->bg_color(
		// 		sprintf(
		// 			'TOTAL queries: %s, total time: %s',
		// 			count( $this->sql_list ),
		// 			$this->sql_total
		// 		),
		// 		'blue'
		// 	) . $doubles_str
		// );

		if ( is_resource( $this->f_log ) ) {
			@fclose( $this->f_log );
		}
		// fclose( $this->f_sql );
		// fclose( $this->f_performance );
	}
	
	// public function set_error( $err, $stack ) {
	// 	$this->reset_session();
	// 	$f_stack = array();
	// 	array_shift( $stack );

	// 	foreach( $stack as $line ) {
	// 		$str = '';

	// 		if( isset( $line['file'] ) ) {
	// 			$str .= 'File: <b>' . $line['file'] . '</b> ';
	// 		}

	// 		if( isset( $line['class'] ) ) {
	// 			$str .= '<b>' . $line['class'] . '</b>';

	// 			if( isset( $line['type'] ) ) {
	// 				$str .= '<b>' . $line['type'] . '</b>';
	// 			}
	// 		}

	// 		if( isset( $line['function'] ) ) {
	// 			$str .= '<b>' . $line['function'] . '</b>';
	// 		}

	// 		if( isset( $line['line'] ) ) {
	// 			$str .= ' in line <b>' . $line['line'] . '</b>';
	// 		}

	// 		$f_stack[] = $str;
	// 	}

	// 	$err = array( 'msg' => $err, 'stack' => $f_stack );
	// 	$this->current_session_errors[] = $err;
	// 	$this->registry->get( 'session' )->data['cron_error'][] = $err;
	// }

	// public function current_session_errors_count() {
	// 	return count( $this->get_current_session_errors() );
	// }

	// public function last_session_errors_count() {
	// 	return count( $this->get_last_session_errors() );
	// }

	// public function get_current_session_errors() {
	// 	return $this->current_session_errors;
	// }

	// public function get_last_session_errors() {
	// 	$this->reset_session();
	// 	return $this->last_session_errors;
	// }

	// protected function reset_session() {
	// 	if( ! $this->is_session_reseted && $this->registry->get( 'session' ) ) {
	// 		if ( isset( $this->registry->get( 'session' )->data['cron_error'] ) ) {
	// 			$this->last_session_errors = $this->registry->get( 'session' )->data['cron_error'];
	// 		}

	// 		$this->registry->get( 'session' )->data['cron_error'] = array();
	// 		$this->is_session_reseted = true;
	// 	}
	// }

	private function get_log_prefix() {
		return date( 'Y-m-d H:i:s' ) . ' : ';
	}

	public function log() {
		if ( ! is_resource( $this->f_log ) ) {
			return;
		}

		foreach( func_get_args() as $msg ) {
			if( is_numeric( $msg ) || is_string( $msg ) ) { 
				$msg = '(' . gettype( $msg ) . ') ' . $msg;

			} elseif ( is_bool( $msg ) ) {
				$msg = '(boolean) ' . ( $msg ? 'true' : 'false' ); 

			} elseif ( is_null( $msg ) ) {
				$msg = 'NULL';

			} else {
				$msg = print_r( $msg, 1 );
			}

			$msg = trim( $msg, PHP_EOL ) . PHP_EOL;

			if ( $this->first_log_line ) {
				$prefix = $this->color( $this->get_log_prefix(), 'blue' );
				$this->first_log_line = false;

			} else {
				$prefix = $this->get_log_prefix();
			}

			fwrite( $this->f_log , $prefix . $msg );
		}
	}

	public function error( $errno , $errstr, $errfile, $errline ) {
		if ( ! is_resource( $this->f_log ) ) {
			return;
		}

		$mess = $this->color( sprintf( '%s - Error[%s]: %s. In %s : %s', date( 'Y-m-d H:i:s' ), $errno, $errstr, $errfile, $errline ), 'red' );
		fwrite( $this->f_log, $mess . "\n" );

		$trace = array();
		foreach( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) as $pos => $line ) {
			$trace[] = sprintf(
				'%s - %s%s%s in %s : %s',
				$pos,
				isset( $line['class'] ) ? $line['class'] : '',
				isset( $line['type'] ) ? $line['type'] : '',
				isset( $line['function'] ) ? $line['function'] : '',
				isset( $line['file'] ) ? $line['file'] : '',
				isset( $line['line'] ) ? $line['line'] : ''
			);
		}
		
		fwrite( $this->f_log, implode( "\n", $trace ) . "\n" );
	}

	// public function performance() {

	// 	foreach( func_get_args() as $msg ) {
	// 		if( is_numeric( $msg ) || is_string( $msg ) ) {
	// 			$msg = '(' . gettype( $msg ) . ') ' . print_r( $msg, 1 );

	// 		} elseif ( is_bool( $msg ) ) {
	// 			$msg = '(boolean) ' . ( $msg ? 'true' : 'false' ); 

	// 		} elseif ( is_null( $msg ) ) {
	// 			$msg = 'NULL';

	// 		} else {
	// 			$msg = print_r( $msg, 1 );
	// 		}

	// 		$msg = trim( $msg, PHP_EOL ) . PHP_EOL;

	// 		fwrite( $this->f_performance , $this->get_log_prefix() . $msg );
	// 	}
	// }

	// public function truncate( $name ) {

	// 	switch( $name ) {
	// 	case 'log' :
	// 		file_put_contents(
	// 			DIR_LOGS . 'console.log',
	// 			$this->color( $this->get_log_prefix() . 'File was truncated', 'green' ) . PHP_EOL
	// 		);
	// 		break;
	// 	case 'sql' :
	// 		file_put_contents(
	// 			DIR_LOGS . 'sql.log',
	// 			$this->color( $this->get_log_prefix() . 'File was truncated', 'green' ) . PHP_EOL
	// 		);
	// 		break;
	// 	case 'performance' :
	// 		file_put_contents(
	// 			DIR_LOGS . 'performance.log',
	// 			$this->color( $this->get_log_prefix() . 'File was truncated', 'green' ) . PHP_EOL
	// 		);
	// 		break;
	// 	}
	// }

	// public function sql() {
	// 	global $adk_listen;

	// 	if ( ! $adk_listen || is_null( $this->f_sql ) ) {
	// 		return;
	// 	}

	// 	$arg = func_get_args();

	// 	if ( ! is_null( $this->sql_start ) ) {
	// 		$run = round( microtime( true ) - $this->sql_start, 4 );

	// 		if ( $run >= 0.1 ) {
	// 			$color = 'red';

	// 		} elseif ( $run >= 0.01 ) {
	// 			$color = 'yellow';

	// 		} else {
	// 			$color = 'green';
	// 		}

	// 		fwrite( $this->f_sql, $this->get_log_prefix() . $this->color( $run . ' mc', $color ) . PHP_EOL );
	// 		$this->sql_start = null;
	// 		$this->sql_total += $run;
	// 	}

	// 	if ( isset( $arg[0] ) && is_string( $arg[0] ) ) {
	// 		fwrite( $this->f_sql, $this->get_log_prefix() . $arg[0] . PHP_EOL );
	// 		$this->sql_list[] = $arg[0];
	// 		$this->sql_start = microtime( true );
	// 	} 
	// }

	public function run_start( $str ) {
		return '<-------------- NEW RUN ' . $str . ' ---------------->' . PHP_EOL;
	}

	public function run_end() {
		return '';
	}

	public function color( $text, $color = 'red' ) {
		if ( $this->terminal ) {
			$color = 'I' . ucfirst( $color );
			$text = $this->{$color} . $text . $this->off;
		}

		return $text;
	}

	public function bg_color( $text, $color = 'red' ) {
		if ( $this->terminal ) {
			$color = 'B' . ucfirst( $color );
			$text = $this->{$color} . $text . $this->off;
		}

		return $text;
	}

	public function start() {
		error_reporting( E_ALL );
		ob_implicit_flush();

		$this->e( date( 'H:i:s' ) . ' > Hello' );

		try {

			foreach( $this->functions as $function ) {
				if ( ! function_exists( $function ) ) {
					throw new Exception( sprintf( 'Function %s doesn\'t exist' ) );
				}
			}

			if ( isset( $_POST['h'] ) ) {
				usage();
				throw new Exception( ' ' );
			}

			if ( empty( $_POST['p'] ) ) {
				throw new Exception( 'Password is missing' );
			}

			$p_idle = isset( $_POST['t'] ) ? (int)$_POST['t'] : 0;

			if ( $p_idle <= $this->idle_max && $p_idle >= $this->idle_min ) {
				$this->idle = $p_idle;
			}

			$this->e( 'Timeout: ' . $this->idle );
			$this->check_pwd( $_POST['p'] );
			set_time_limit( $this->time_limit );
			$dir = dirname( $this->mutex );

			if ( ! is_file( $this->mutex ) ) {
				if ( ! is_dir( $dir ) ) {
					if( false === mkdir( $dir, 0777, true ) ) {
						throw new Exception( sprintf( 'Failde to create directory %s', $dir ) );
					}
				}

				if( false === file_put_contents( $this->mutex, '' ) ) {
					throw new Exception( 'Failed to create mutex %s', $this->mutex );
				}

				$this->e( sprintf( 'Mutex "%s" has been created', $this->mutex ) );
			}

			$this->mutex_fd = fopen( $this->mutex, 'r+' );

			if ( false === $this->mutex_fd ) {
				throw new Exception( 'Failed to open mutex' );
			}

			if ( ! flock( $this->mutex_fd , LOCK_EX  | LOCK_NB ) ) {
				throw new Exception( 'Another instance of server is already running' );
			}

			$this->e( 'Exclusive lock has been acquired on mutex' );

			if ( ( $this->sock = socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) ) === false) {
				throw new Exception( "Failed create socket: " . socket_strerror( socket_last_error() ) );
			}

			$this->e( 'Socket has been created' );

			if ( socket_set_nonblock( $this->sock ) === false ) {
				throw new Exception( 'Failed to set unblocking mode on socket: ' . socket_strerror( socket_last_error() ) );
			}

			$this->e( 'Socket has been set to nonblocking mode' );

			for( ; $this->port < 10010; $this->port++ ) {
				$res = @socket_bind( $this->sock, $this->address, $this->port );

				if ( $res === true ) {
					break;
				}
			}

			if ( $res === false) {
				throw new Exception( "Failed to bind socket: " . socket_strerror( socket_last_error( $this->sock ) ) );
			}

			$this->e( sprintf( 'Socket has been bent to listen to %s:%s', $this->address, $this->port ) );

			if ( socket_listen( $this->sock, 5 ) === false) {
				throw new Eception( "Failed to make socket listen: " . socket_strerror( socket_last_error( $this->sock ) ) );
			}

			$this->e( date( 'H:i:s' ) . ' > Socket is listening....' );

			$start = time();

			do {
				if ( time() > $start + $this->idle ) {
					$this->e( 'Time out' );
					break;
				}

				if ( ( $this->msgsock = socket_accept( $this->sock ) ) ) {
					$this->e( date( 'H:i:s' ) . ' Accepted new connection' );

					if ( socket_set_nonblock( $this->msgsock ) === false ) {
						socket_close( $this->msgsock );
						throw new Exception( 'Failed to set unblocking mode on message socket: ' . socket_strerror( socket_last_error() ) );
					}

					$this->e( 'Message socket has been set to nonblocking mode' );

					$msg = sprintf(
						"Server is running.\nTo stop it print: %s\n.To close current connection print: %s\n",
						implode( ', ', $this->stops ),
						implode( ', ', $this->exits )
					);

					socket_write( $this->msgsock, $msg, strlen( $msg ) );

					$msg = "Which stream you wish to read from\n.Available options: log, sql, perf.\n";
					socket_write( $this->msgsock, $msg, strlen( $msg ) );

					do {

						if ( time() > $start + $this->idle ) {
							$this->e( 'Time out' );
							break;
						}

						// Read cycle - read till there is something to read
						do {

							if ( false === ( $buf = socket_read( $this->msgsock, 2048 ) ) ) {

								// Nothing to read
								break;
							}

							if ( ! $buf = trim( $buf ) ) {
								continue;
							}

							$start = time();

							// Stop server command
							if ( in_array( $buf, $this->stops ) ) {
								$m = 'Stopping the server' . chr( 10 ) . 'Bye' . chr( 10 );
								socket_write( $this->msgsock, $m, strlen( $m ) );
								// clean_session();

								break 3;
							}

							// Stop current terminal session command
							if ( in_array( $buf, $this->exits ) ) {
								break 2;
							}

							$create_fifo = false;

							if ( in_array( $buf, array( 'log' ) ) ) {
								if ( true === $this->make_fifo( $buf ) ) {
									break;
								}

								break 2;
							}

							$mess = 'Unknown command' . chr( 10 );
							socket_write( $this->msgsock, $mess, strlen( $mess ) );

						} while ( true );

						// Write cycle
						do {

							if ( is_resource( $this->fifo_fd ) && $mess = fread( $this->fifo_fd, 2048 ) ) {
								socket_write( $this->msgsock, $mess, strlen( $mess ) );
								$start = time();

							} else {
								break;
							}

						} while ( true );

						sleep( 2 );

					} while ( true );

					$m = 'Closing connection' . chr( 10 ) . 'Bye' . chr( 10 );
					socket_write( $this->msgsock, $m, strlen( $m ) );
					$this->clean_session();
					$this->e( 'Connection is closed' );
				}

				// Wait for new connection
				sleep( 2 );

			} while ( true );

			$this->clean_server();

		} catch ( Exception $e ) {
			$this->e( $e->getMessage() );
			$this->clean_server();
		}

		$this->e( date( 'H:i:s' ) . ' < Bye' );
	}

	public function tail() {
		ob_implicit_flush();
		$min_chunk = 1024 * 1;

		$this->e( date( 'H:i:s' ) . ' > Hello' );

		try {

			if ( isset( $_POST['h'] ) ) {
				usage();
				throw new Exception( ' ' );
			}

			if ( empty( $_POST['p'] ) ) {
				throw new Exception( 'Password is missing' );
			}

			$p_idle = isset( $_POST['t'] ) ? (int)$_POST['t'] : 0;

			if ( $p_idle <= $this->idle_max && $p_idle >= $this->idle_min ) {
				$this->idle = $p_idle;
			}

			if ( empty( $_POST['s'] ) ) {
				error_reporting( E_ALL );
				$this->e( 'Show errors ON' );
			}

			$this->e( 'Timeout value: ' . $this->idle );

			$this->check_pwd( $_POST['p'] );

			set_time_limit( $this->time_limit );

			if ( is_file( $this->file_log ) ) {
				$fd = fopen( $this->file_log, 'r' );

				if ( false === $fd ) {
					throw new Exception( 'Failed to open log file' );
				}

			} else {
				throw new Exception( 'Log file does not exist' );
			}

			self::$is_log = true;

			// Inits
			$start = time();
			$stat = fstat( $fd );
			$size = $stat['size'];

			if ( isset( $_POST['dump'] ) ) {
				$this->e( 'Dumping log....' );
				$this->e( fread( $fd, $size ) );

				throw new Exception( 'end' );
			}

			$this->e( 'Start to reading log' );
			$offset = min( $min_chunk, $size );
			fseek( $fd, $offset * -1, SEEK_END );

			if ( $stat['size'] > 0 ) {
				$d = fread( $fd, $offset );
				$this->e( $d );
			}

			while ( true ) {
				if ( time() > $start + $this->idle ) {
					throw new Exception( 'Script timeout' );
				}

				if ( connection_aborted() ) {
					throw new Exception( 'disconnect' );
				}

				$stat = fstat( $fd );

				if ( $stat['size'] > $size ) {
					$this->e( fread( $fd, $stat['size'] - $size ) );
					$size = $stat['size'];
					$start = time();
				}

				sleep( 1 );
			}

		} catch ( Exception $e ) {
			$this->e( $e->getMessage() );
		}

		if ( is_resource( $fd ) ) {
			fclose( $fd );
		}

		self::$is_log = false;

		$this->e( date( 'H:i:s' ) . ' < Bye' );
	}

	public function e ( $m ) {
		echo $m . chr( 10 );
		ob_flush();
	}

	public function make_fifo( $type ) {
		$name = 'fifo_' . $type;

		if ( ! posix_access( $this->{$name} ) ) {
				$this->e( $type . ' FIFO doesn\'t exist' );
				$create_fifo = true;

		} else {
			$s = stat( $this->{$name} );
			$mode = decoct( $s['mode'] );

			if ( 0 === $mode & 010000 ) {
				$this->e( $type . ' FIFO is not a FIFO' );
				$create_fifo = true;
			}
		}

		if ( $create_fifo ) {
			if( false === posix_mkfifo( $this->{$name}, 0600 ) ) {
				$this->e( 'Failed to create FIFO' );

				return false;
			}

			$this->e( 'FIFO has been created' );
		}

		if( false === ( $this->fifo_fd = fopen( $this->{$name}, 'r+' ) ) ) {
			$this->e( 'Failed to open FIFO' );

			return false;
		}

		stream_set_blocking( $this->fifo_fd, false );
		$this->e( 'FIFO has been opened' );

		return true;
	}

	public function clean_server() {
		$this->clean_session();

		if ( is_resource( $this->sock ) ) {
			socket_close( $this->sock );
		}

		if ( is_resource( $this->mutex_fd ) ) {
			fclose( $this->mutex_fd );
		}

		if ( is_file( $this->mutex ) ) {
			unlink( $this->mutex );
		}

	}

	public function clean_session() {
		if ( is_resource( $this->msgsock ) ) {
			socket_close( $this->msgsock );
		}

		if ( is_resource( $this->fifo_fd ) ) {
			fclose( $this->fifo_fd );
		}

		@unlink( $this->fifo_log );

	}

	public function check_pwd( $pwd ) {
		$error = '';

		$this->e( 'Checking password...' );
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->pwd_url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, array( 'p' => $pwd ) );

		if( false === $ret = curl_exec( $ch ) ) {
			$error = curl_error( $ch );
		}

		curl_close( $ch );

		if ( $error ) {
			throw new Exception( 'CURL error: ' . $error );
		}

		if ( '1' !== $ret ) {
			throw new Exception( 'Invalid password' );
		}

		$this->e( 'Password is OK' );

		return true;
	}

	public function usage() {
		$this->e( 'Usage:' );
		$this->e( 'p=string - password' );
		$this->e( 't=int - timeout in seconds' );
		$this->e( 's=1 - show errors' );
		$this->e( 'dump=1 - get all the contents' );
	}

	public function is_log() {
		return self::$is_log;
	}
}

// if( ! function_exists( 'console_log' ) ) {
// 	function console_log() {
// 		global $console;
// 		call_user_func_array( array( $console, 'log' ), func_get_args() );
// 	}
// }
}//<--- Advertikon namespace end
namespace {
	if( ! function_exists( 'adk_log' ) ) {
		function adk_log() {
			global $adk_console;
			call_user_func_array( array( $adk_console, 'log' ), func_get_args() );
		}
	}
}

// if( ! function_exists( 'p' ) ) {
// 	function p() {
// 		global $console;
// 		call_user_func_array( array( $console, 'performance' ), func_get_args() );
// 	}
// }

// if( ! function_exists( 'truncate_log' ) ) {
// 	function truncate_log() {
// 		global $console;
// 		$console->log_truncate();
// 	}
// }

// if( ! function_exists( 'sql' ) ) {
// 	function sql() {
// 		global $console;
// 		call_user_func_array( array( $console, 'sql' ), func_get_args() );
// 	}
// }

// if( ! function_exists( 'truncate_sql' ) ) {
// 	function truncate_sql() {
// 		global $console;
// 		$console->sql_truncate();
// 	}
// }

// if ( ! function_exists( 'log_query' ) ) {
// 	function log_query( $value = true ) {
// 		global $log_query;
// 		$log_query = $value;
// 	}
// }

// if ( ! function_exists( 'is_log_query' ) ) {
// 	function is_log_query() {
// 		global $log_query;
// 		return (bool)$log_query;
// 	}
// }




