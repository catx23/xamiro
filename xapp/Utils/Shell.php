<?php

class XApp_Shell_Utils
{

	const OPTION_WORKING_PATH = "run_working_path";
	const OPTION_BACKGROUND = "run_background";
	const OPTION_PRE_DELAY = "pre_delay";
	const OPTION_STDOUT_TO = "stdout_to";
	const OPTION_FILTER_PROCESSES = "filter";
	const OPTION_RETURN_PROCESSES_TREE = "return_tree";

	/***
	 * Returns a list of processes for command cmd
	 *
	 * @param $cmd : full path
	 * @param array $options
	 * @return array
	 */
	public static function getProcesses($cmd, $options = Array())
	{
		$command = "ps -f -C $cmd";

		if (PHP_OS !=='WINNT' && xo_has(self::OPTION_FILTER_PROCESSES, $options)) {
			$command .= " | grep -w " . xo_get(self::OPTION_FILTER_PROCESSES, $options);
		}

		$result = self::run(
			$command,
			array(),
			null,
			Array(
				XApp_Shell_Utils::OPTION_BACKGROUND => false
			)
		);

		// Format process list
		$ps_fields = explode(",", "user,pid,ppid,c,stime,tty,time");
		$pattern = array();
		foreach ($ps_fields as $field) {
			$pattern[] = "(?P<$field>[^\s]+)";
		}
		$ps_fields[] = "cmd";
		$pattern[] = "(?P<cmd>.*)";
		$pattern = "/" . implode("\s+", $pattern) . "/";


		$result_list = explode("\n", trim($result));
		$ret_list = array();
		foreach ($result_list as $n => $line) {
			preg_match($pattern, $line, $mt);
			if (count($mt) > 0) {
				$ret_list[$mt["pid"]] = array();
				foreach ($ps_fields as $field) {
					$ret_list[$mt["pid"]][$field] = $mt[$field];
				}
			}
		}

		// Return as tree
		if (xo_get(self::OPTION_RETURN_PROCESSES_TREE, $options)) {
			// Iterate from the end: assuming list is sorted by PID
			$node = end($ret_list);
			do {
				if (array_key_exists($node["ppid"], $ret_list)) {
					$ret_list[$node["ppid"]]["childs"][] = $node;
					$ret_list[$node["ppid"]]["childs_count"] = count($ret_list[$node["ppid"]]["childs"]);
					$ret_list[$node["pid"]]["delete_me"] = true;
				}
				$node = prev($ret_list);
			} while ($node !== false);

			// delete marked nodes
			foreach ($ret_list as $key => $value) {
				if (isset($value["delete_me"])) {
					unset($ret_list[$key]);
				}
			}
		}

		return array_values($ret_list);
	}

	/**
	 * @param $cmd
	 * @param array $args
	 * @param null $return
	 * @param array $options
	 * @return string
	 */
	public static function run($cmd, Array $args = array(), $return = null, $options = Array())
	{


		self::_escapeArguments($args);

		if (!xo_has(self::OPTION_BACKGROUND, $options)) {
			$options[self::OPTION_BACKGROUND] = true;
		}

		$command = $cmd . " " . implode(" ", $args);
		$os = PHP_OS;
		$isWindows = false;

		switch ($os) {
			case "WINNT": {
				$isWindows = true;
				$command = str_replace('/', '\\', $command);
			}
		}
		if (xo_has(self::OPTION_STDOUT_TO, $options)) {
			$command .= " >> " . xo_get(self::OPTION_STDOUT_TO, $options);
		}

		if (xo_has(self::OPTION_WORKING_PATH, $options)) {
			$working_path = xo_get(self::OPTION_WORKING_PATH, $options);
			if (!$isWindows) {
				$command = "cd $working_path ; " . $command;
			} else {
				if (xo_get(self::OPTION_BACKGROUND, $options)) {

					$command = "cd $working_path & start " . $command;
				} else {
					$command = "cd $working_path & " . $command;
				}
			}
		}

		
		if (xo_get(self::OPTION_BACKGROUND, $options)) {
			if (xo_get(self::OPTION_PRE_DELAY, $options)) {
				// Add 1 second delay
				$command = "sleep 1 ; " . $command;
			}

			if (!$isWindows) {
				$command .= " & ";
				ignore_user_abort(true);
				register_shutdown_function(array(new self(), '__exec'), $command);
				$result = "Postponed: $command";
			} else {
				ignore_user_abort(true);
				register_shutdown_function(array(new self(), '__exec'), $command);
				$result = "Postponed: $command";
			}

		} else {
			$result = self::__exec($command);
		}
		return $result;

	}

	/**
	 * @return bool
	 */
	protected static function isWindows()
	{
		$os = PHP_OS;
		switch ($os) {
			case "WINNT": {
				return true;
			}
		}

		return false;
	}

	/**
	 * Cross platform shell execution, expects a absolute command
	 * @param $command
	 * @return string
	 */
	public static function __exec($command)
	{
		$shell_result_final = '';
		if (is_callable('system')) {

			ob_start();
			@system($command);
			$shell_result_final = ob_get_contents();
			ob_end_clean();

		} elseif (is_callable('shell_exec')) {
			$shell_result_final = @shell_exec($command);

		} elseif (is_callable('exec')) {

			@exec($command, $shell_result);
			if (!empty($shell_result)) {
				foreach ($shell_result as $shell_res_item) {
					$shell_result_final .= $shell_res_item;
				}
			}

		} elseif (is_callable('passthru')) {

			ob_start();
			@passthru($command);
			$shell_result_final = ob_get_contents();
			ob_end_clean();

		} elseif (is_callable('proc_open')) {

			$shell_descriptor_spec = array(

				0 => array("pipe", "r"),

				1 => array("pipe", "w"),

				2 => array("pipe", "w")
			);

			$shell_proc = @proc_open($command, $shell_descriptor_spec, $shell_pipes, getcwd(), array());

			if (is_resource($shell_proc)) {

				while ($shell_in_proc_open = fgets($shell_pipes[1])) {

					if (!empty($shell_in_proc_open)) {
						$shell_result_final .= $shell_in_proc_open;
					}
				}
				while ($s_se = fgets($shell_pipes[2])) {
					if (!empty($s_se)) {
						$shell_result_final .= $s_se;
					}
				}
			}
			@proc_close($shell_proc);

		} elseif (is_callable('popen')) {
			$shell_open = @popen($command, 'r');
			if ($shell_open) {
				while (!feof($shell_open)) {
					$shell_result_final .= fread($shell_open, 2096);
				}
				pclose($shell_open);
			}

		}

		if (!empty($shell_result_final)) {
			return $shell_result_final;
		}
		return false;
	}

	/***
	 * Sanitize arguments
	 * @TODO : deserves additional attention
	 * @param $args
	 */
	private static function _escapeArguments(&$args)
	{
		foreach ($args as $k => &$v) {
			$v = escapeshellcmd(trim($v));
		}
	}
}