<?php
/**
 * Advertikon Task Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 * 
 * To install it run self::install() (as a rule you need to do it during the main extension installation)
 * To uninstall run self::uninstall() (as a rule you need to do it during the mail extension installation)
 * Extension need to have Catalog::controller::amend_task action
 * 
 * To run tasks call self::run(). It should be called via cronjob of some sort
 * In order to correctly end task each task action should have self::stop_task( ID). ID is passed via GET['id']
 */

namespace Advertikon;

class Task {

	public $task = '';
	public $schedule = '';
	public $status = '';
	public $last_run = '';
	public $p_id = '';
	public $threshold = '';
	public $h = '';
	private $tasks = '';
	public $id = '';
	public $table = 'adk_task';
	protected $connector = null;

	public function __construct() {
		$this->connector = array( $this, 'socket_connector' );
	}

	/**
	 * Initializes object
	 * @return void
	 */
	public function init() {
		if ( ! $this->tasks ) {
			$this->tasks = ADK()->q( array(
				'table' => $this->table,
				'query' => 'select',
				'where' => array(
					'field'     => 'status',
					'operation' => '<>',
					'value'     => 1,
				), 
			) );
		}
	}

	/**
	 * Installs task manager into system
	 * @return object
	 */
	public function install() {
		ADK()->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->table . "`
		(`id` INT UNSIGNED AUTO_INCREMENT KEY,
		 `task` TEXT,
		 `schedule` VARCHAR(20),
		 `status` TINYINT UNSIGNED DEFAULT 0,
		 `last_run` DATETIME,
		 `p_id` VARCHAR(50),
		 `threshold` INT UNSIGNED
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		// Add "Amend tasks" task
		if ( ADK()->type && ADK()->code ) {
			$action = ADK()->get_store_url() . 'index.php?route=' . ADK()->type . '/' . ADK()->code . '/amend_task';
			$schedule = '*/10 * * * *';
			$threshold = 10;

			if ( ! $this->task_exists( $action, $schedule, $threshold ) ) {
				$this->add_task( $action, $schedule, $threshold );
			}
		}

		return $this;
	}

	/**
	 * Removes task manager from the system
	 * @return object
	 */
	public function uninstall() {
		ADK()->db->query( "DROP TABLE IF EXISTS `" . DB_PREFIX . $this->table . "`" );

		return $this;
	}

	/**
	 * Adds cron task
	 * @param string $task Task action (OpenCart action absolute URL)
	 * @param string $schedule Schedule structure (something like * * * * *)
	 * @param int $threshold Staleness threshold in seconds
	 * @return object
	 */
	public function add_task( $task, $schedule, $threshold ) {
		ADK()->q( array(
			'table' => $this->table,
			'query' => 'insert',
			'values' => array(
				'task'      => '"' . $task . '"',
				'schedule'  => $schedule,
				'threshold' => $threshold,
			),
		) );

		return $this;
	}

	/**
	 * Deletes cron task
	 * @param string $task Task action (OpenCart action absolute URL)
	 * @param string $schedule Schedule structure (something like * * * * *)
	 * @param int $threshold Staleness threshold in seconds
	 * @return object
	 */
	public function delete_task( $task, $schedule, $threshold ) {
		ADK()->q( array(
			'table' => $this->table,
			'query' => 'delete',
			'where' => array(
				array(
					'value'     => $task,
					'operation' => '=',
					'field'     => 'task',
				),
				array(
					'field'     => 'schedule',
					'operation' => '=',
					'value'     => $schedule
				),
				array(
					'field'     => 'threshold',
					'operation' => '=',
					'value'     => $threshold
				),
			),
		) );

		return $this;
	}

	/**
	 * Checks whether task is exists
	 * @param string $action Task's task action 
	 * @param string $schedule Task's schedule
	 * @param int $threshold Task's threshold 
	 * @return boolean
	 */
	public function task_exists( $action, $schedule, $threshold ) {
		$query = ADK()->q( array(
			'table'     => $this->table,
			'field'     => array( 'count' => 'count(*)' ),
			'where'     => array(
				array(
					'field'     => 'task',
					'operation' => '=',
					'value'     => $action,
				),
				array(
					'field'     => 'schedule',
					'operation' => '=',
					'value'     => $schedule,
				),
				array(
					'field'     => 'threshold',
					'operation' => '=',
					'value'     => $threshold,
				),
			),
		) );

		return (boolean)$query['count'];
	}

	/**
	 * Run tasks
	 * @return void
	 */
	public function run() {
		while( $this->fetch_new() ) {
			$this->run_task();
			var_dump( $this->connect( $this->task . '&id=' . $this->id ) );

			// $this->stop_task() need to be called from the end of task controller
		}
	}

	/**
	 * Connects to task URL
	 * @param string $url URL
	 * @return string Connector's output
	 */
	public function connect( $url ) {
		return call_user_func_array( $this->connector, array( $url ) );
	}

	/**
	 * Socket connector interface
	 * @param string $url URL to connect with
	 * @return string Connector's output
	 */
	protected function socket_connector( $url ) {
		$socket = new Socket();

		return $socket->socket( $url, 'GET' );
	}

	/**
	 * Fetches new task from queue
	 * @return boolean Operation result
	 */
	public function fetch_new() {
		$this->init();

		if ( $this->task ) {
			$this->reset();
			$this->tasks->next();
		}

		while (  $this->tasks->valid() && ! $this->is_scheduled() ) {
			$this->tasks->next();
		}

		if ( $this->tasks->valid() ) {
			$task = $this->tasks->current();

			$this->task      = $task['task'];
			$this->schedule  = $task['schedule'];
			$this->status    = $task['status'];
			$this->last_run  = $task['last_run'];
			$this->p_id      = $task['p_id'];
			$this->threshold = $task['threshold'];
			$this->id        = $task['id'];

			return true;
		}

		return false;
	}

	/**
	 * Resets task
	 * @return void
	 */
	public function reset() {
		$this->task      = '';
		$this->schedule  = '';
		$this->status    = '';
		$this->last_run  = '';
		$this->p_id      = '';
		$this->threshold = '';
		$this->id        = '';
	}

	/**
	 * Checks whether task is scheduled to run NOW
	 * @return boolean
	 */
	public function is_scheduled( $schedule = null ) {
		if ( is_null( $schedule ) ) {
			$task = $this->tasks->current();
			$schedule = $task['schedule'];
		}

		$date = new \DateTime();
		$parts = explode( ' ', $schedule );

		if ( ! isset( $parts[ 4 ] ) ) {
			trigger_error( sprintf( 'Task schedule: invalid schedule format: "%s"', $schedule ) );
			return false;
		}

		return  $this->is_min( $parts[0], $date ) &&
				$this->is_hour( $parts[1], $date ) &&
				$this->is_month( $parts[3], $date ) &&
				( $this->is_day( $parts[2], $date ) ||
				$this->is_week_day( $parts[4], $date ) );
	}

	/**
	 * Checks minute part of task schedule
	 * @param string $min Minutes part of schedule
	 * @param object $date DateTime object
	 * @return boolean
	 */
	public function is_min( $min, $date ) {
		try {

			if ( false === ( $parts = $this->parse_part( $min ) ) ) {
				$this->h->exception( 'error' );
			}

			if ( '*' === $parts['from'] ) {
				return true;
			}

			$min = (int)$date->format( 'i' );

			if ( $parts['from'] < 0 || $parts['from'] > 59 ) {
				$this->h->exception( 'error' );
			}

			if ( $parts['to'] < 0 || $parts['to'] > 59 ) {
				$this->h->exception( 'error' );
			}

			if ( $min < $parts['from'] || $min > $parts['to'] || 0 !== $min % $parts['divider'] ) {
				return false;
			}

		} catch ( Adk_Exception $e ) {
			trigger_error( 'Task schedule: invalid format of schedule\'s minutes part' );
			return false;
		}

		return true;
	}

	/**
	 * Checks hour's part of task schedule
	 * @param string $min Hour's part of schedule
	 * @param object $date DateTime object
	 * @return boolean
	 */
	public function is_hour( $hour, $date ) {
		try {

			if ( false === ( $parts = $this->parse_part( $hour ) ) ) {
				$this->h->exception( 'error' );
			}

			if ( '*' === $parts['from'] ) {
				return true;
			}

			$min = (int)$date->format( 'H' );

			if ( $parts['from'] < 0 || $parts['from'] > 23 ) {
				$this->h->exception( 'error' );
			}

			if ( $parts['to'] < 0 || $parts['to'] > 23 ) {
				$this->h->exception( 'error' );
			}

			if ( $hour < $parts['from'] || $hour > $parts['to'] || 0 !== $hour % $parts['divider'] ) {
				return false;
			}

		} catch ( Adk_Exception $e ) {
			trigger_error( 'Task schedule: invalid format of schedule\'s hours part' );
			return false;
		}

		return true;
	}

	/**
	 * Checks day's part of task schedule
	 * @param string $min Day's part of schedule
	 * @param object $date DateTime object
	 * @return boolean
	 */
	public function is_day( $day, $date ) {
		try {

			if ( false === ( $parts = $this->parse_part( $day ) ) ) {
				$this->h->exception( 'error' );
			}

			if ( '*' === $parts['from'] ) {
				return true;
			}

			$day = (int)$date->format( 'd' );

			if ( $parts['from'] < 1 || $parts['from'] > 31 ) {
				$this->h->exception( 'error' );
			}

			if ( $parts['to'] < 1 || $parts['to'] > 31 ) {
				$this->h->exception( 'error' );
			}

			if ( $day < $parts['from'] || $day > $parts['to'] || 0 !== $day % $parts['divider'] ) {
				return false;
			}

		} catch ( Adk_Exception $e ) {
			trigger_error( 'Task schedule: invalid format of schedule\'s day part' );
			return false;
		}

		return true;
	}

	/**
	 * Checks month's part of task schedule
	 * @param string $min Month's part of schedule
	 * @param object $date DateTime object
	 * @return boolean
	 */
	public function is_month( $month, $date ) {
		try {

			if ( false === ( $parts = $this->parse_part( $month ) ) ) {
				$this->h->exception( 'error' );
			}

			if ( '*' === $parts['from'] ) {
				return true;
			}

			$month = (int)$date->format( 'm' );

			if ( $parts['from'] < 1 || $parts['from'] > 12 ) {
				$this->h->exception( 'error' );
			}

			if ( $parts['to'] < 1 || $parts['to'] > 12 ) {
				$this->h->exception( 'error' );
			}

			if ( $month < $parts['from'] || $month > $parts['to'] || 0 !== $month % $parts['divider'] ) {
				return false;
			}

		} catch ( Adk_Exception $e ) {
			trigger_error( 'Task schedule: invalid format of schedule\'s month part' );
			return false;
		}

		return true;
	}

	/**
	 * Checks minute part of task schedule
	 * @param string $min Minutes part of schedule
	 * @param object $date DateTime object
	 * @return boolean
	 */
	public function is_week_day( $day, $date ) {
		try {

			if ( false === ( $parts = $this->parse_part( $day ) ) ) {
				$this->h->exception( 'error' );
			}

			if ( '*' === $parts['from'] ) {
				return true;
			}

			// 1 though 7
			$day = (int)$date->format( 'N' );

			if ( $parts['from'] < 0 || $parts['from'] > 7 ) {
				$this->h->exception( 'error' );
			}

			if ( $parts['to'] < 0 || $parts['to'] > 7 ) {
				$this->h->exception( 'error' );
			}

			if ( 0 === $parts['from'] ) {
				$parts['from'] = 7;
			}

			if ( 0 === $parts['to'] ) {
				$parts['to'] = 7;
			}

			if ( $day < $parts['from'] || $day > $parts['to'] || 0 !== $day % $parts['divider'] ) {
				return false;
			}

		} catch ( Adk_Exception $e ) {
			trigger_error( 'Task schedule: invalid format of schedule\'s week\'s day part' );
			return false;
		}

		return true;
	}

	/**
	 * Parses schedule's parts (0/2, * / 1, 2-6/2 )
	 * @param strong $part Schedule's part
	 * @return array|false
	 */
	public function parse_part( $part ) {
		if ( ! preg_match( '/(\*|\d+)(?:\s*-\s*(\d+))?(?:\s*\/\s*(\d+))?/', $part, $m ) ) {
			return false;
		}

		return array(
			'from'    => '*' === $m[1] ? '*' : (int)$m[1],
			'to'      => isset( $m[2] ) ? (int)$m[2] : (int)$m[1],
			'divider' => isset( $m[3] ) ? (int)$m[3] : 1,
		);
	}

	/**
	 * Removes hanged tasks
	 * @return void
	 */
	public function amend_task() {
		ADK()->db->query(
			"UPDATE `" . DB_PREFIX . $this->table . "`
			SET `status` = 0
			WHERE `status` = 1 AND DATE_ADD( `last_run`,INTERVAL `threshold` SECOND ) < NOW() "
		);
	}

	/**
	 * Marks task as running
	 * @param int $id Task ID
	 * @return boolean Operation status
	 */
	public function run_task( $id = null ) {
		if ( is_null( $id ) ) {
			if ( $this->id ) {
				$id = $this->id;
				
			} else {
				trigger_error( 'Task schedule: failed to run task - task ID is missing' );
				return false;
			}
		}

		$result = ADK()->q( array(
			'table' => $this->table,
			'query' => 'update',
			'set'   => array(
				'last_run' => 'now()',
				'status'   => 1,
			),
			'where' => array(
				'field'     => 'id',
				'operation' => '=',
				'value'     => $id,
			),
		) );

		return $result;
	}

	/**
	 * Marks task as stopped
	 * @param int $id Task ID
	 * @return boolean Operation status
	 */
	public function stop_task( $id = null ) {
		if ( is_null( $id ) ) {
			if ( $this->id ) {
				$id = $this->id;
				
			} else {
				trigger_error( 'Task schedule: failed to stop task - task ID is missing' );
				return false;
			}
		}

		$result = ADK()->q( array(
			'table' => $this->table,
			'query' => 'update',
			'set'   => array(
				'status'   => 0,
			),
			'where' => array(
				'field'     => 'id',
				'operation' => '=',
				'value'     => $id,
			),
		) );

		return $result;
	}
}
