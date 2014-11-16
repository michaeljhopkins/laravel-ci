<?php

namespace App\Services\Watcher\Support;

use Closure;

class ShellExec {

	public function exec($command, $exec_path = '', Closure $callable = null)
	{
		if ($exec_path)
		{
			$command = 'cd '.$exec_path.'; ' . $command;
		}

		$lines = [$command];

		flush();

		$fp = popen($command, "r");

		while( ! feof($fp))
		{
			// send the current file part to the browser
			$lines[] = $line = fread($fp, 1024);

			if ($callable)
			{
				$callable($line);
			}

			// flush the content to the browser
			flush();
		}

		fclose($fp);

		return $lines;
	}

} 
