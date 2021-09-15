<?php
class PowerPrompt {
	private static $instance = null;

	private $width = null;
	private $height = null;
	private $stdin = null;

	private $option = [];
	private $input = '';

	private function __construct() {
		if($this->stdin===null) {
			$this->width = exec('tput cols');
			$this->height = exec('tput lines');

			system('stty cbreak');
			$this->clear_screen();
			$this->stdin = fopen('php://stdin', 'r');
		}
	}

	public function header($text) {
		$this->setpos(1,1);

		$len = mb_strlen($text)+2;

		$this->style('white');
		echo str_pad('',ceil(($this->width-$len)/2),'=');
		$this->style('blue','bold');
		echo ' '.$text.' ';
		$this->style('white','normal');
		echo str_pad('',floor(($this->width-$len)/2),'=');
		$this->style();
		echo PHP_EOL;
	}
	public function reset_option() {
		$this->option = [];
	}
	public function add_option($command,$title,$function,$type = null) {
		$this->option[] = [
			'command' => $command,
			'function' => $function,
		];

		$this->setpos(3+sizeof($this->option),1);

		$this->style('bold');
		echo str_pad($command.':',5,' ');
		$this->style();
		echo $title.PHP_EOL;
	}
	public function select_option($title,$preset = '') {
		$this->input = $preset;

		while(true) {
			$this->setpos(2,1);
			echo $title.': ';
			$this->style('red','bold');
			echo $this->input;
			$this->style();
			$this->clearline();
			$key = fgetc($this->stdin);

			$this->setpos(10,1);

			if($key==chr(10)) { // Enter
				$this->execute_option($this->input);
			}
			elseif($key==chr(4)) { // EOT
				$this->exit();
			}
			elseif($key==chr(127) || $key==chr(8)) { // Backspace
				$this->input = mb_substr($this->input,0,-1);
			}
			elseif($key==chr(9)) { // Tab
			}
			elseif($key==chr(27)) { // Escape
				$subkey = $this->fgetc_purge();
				switch($subkey) {
					case ''; // ESC Key
						$this->execute_option('ESC');
						break;
					case chr(91).chr(65): // Arrow Up
						break;
					case chr(91).chr(66): // Arrow Down
						break;
					default:
						echo 'Unknown:'.bin2hex($subkey);
				}
			}
			else {
				$this->input .= $key;
			}
		}
	}
	public function clear_screen() {
		echo chr(27).'[2H'.chr(27).'[J';
	}
	private function execute_option($input) {
		echo $input;
		foreach($this->option as $var) {
			if($var['command']===$input) {
				$func = $var['function'];
				$func(self::$instance);
			}
		}
	}
	private function fgetc_purge() {
		$return = '';

		while(true) {
			$read = [$this->stdin];
			$write = $except = [];
			if(stream_select($read, $write, $except, 0, 1)) {
				$return .= fgetc($this->stdin);
			}
			else {
				return $return;
			}
		}
	}
	private function exit() {
		$this->clear_screen();
		echo 'Bye'.PHP_EOL;
		exit;
	}
	private function setpos($i,$j) {
		echo chr(27).'['.$i.';'.$j.'H';
	}
	private function clearline() {
		echo chr(27).'[K';
	}
	public function style(...$styles) {
		static $_styles = [
			'red'				=> "[31m",
			'green'			=> "[32m",
			'yellow'		=> "[33m",
			'blue'			=> "[34m",
			'magenta'		=> "[35m",
			'cyan'			=> "[36m",
			'white'			=> "[37m",
			'normal'		=> '[22m',
			'bold'			=> '[1m',
		];

		if($styles) {
			foreach($styles as $style) {
				if(isset($_styles[$style])) {
					echo chr(27).$_styles[$style];
				}
			}
		}
		else {
			echo chr(27).'[0m';
		}
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new PowerPrompt();
		}
		return self::$instance;
	}
}
