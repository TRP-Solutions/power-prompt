<?php
class PowerPrompt {
	private static $instance = null;

	private $width = null;
	private $height = null;
	private $stdin = null;

	private $option = [];

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
		echo str_pad($command.':',3,' ');
		$this->style();
		echo $title.PHP_EOL;
	}
	public function echo($str) {
		echo $str;
	}
	public function select_option($title,$preset = '') {
		while(true) {
			$this->setpos(2,1);
			echo $title.': ';
			$this->clearline();

			list($cmd,$key) = $this->get_key();
			switch($cmd) {
				case null;
				if(true) {
					$this->execute_option($key);
					return;
				}
				case 'ESC'; return;
			}

		}
	}
	public function input_string($title,$input = '') {
		$this->setpos(2,1);
		echo $title.': ';
		$offset = mb_strlen($title.': ');
		$cursor = mb_strlen($input);

		while(true) {
			$this->setpos(2,$offset);
			$this->style('red','bold');
			echo $input;
			$this->style();
			$this->clearline();

			$this->setpos(2,$offset+$cursor);

			$a = mb_substr($input, 0, $cursor);
			$b = mb_substr($input, $cursor);

			list($cmd,$key) = $this->get_key();
			switch($cmd) {
				case null; $a .= $key; $cursor++; break;
				case 'BS'; $a = mb_substr($a,0,-1); $cursor--; break;
				case 'ENT'; return $input;
				case 'ESC'; return null;
				case 'DEL'; $b = mb_substr($b,1); break;
				case 'CUF'; $cursor++; break;
				case 'CUB'; $cursor--; break;
			}
			$input = $a.$b;
			if($cursor<0) $cursor=0;
			if($cursor>mb_strlen($input)) $cursor=mb_strlen($input);
		}
	}
	public function clear_screen() {
		echo chr(27).'[2H'.chr(27).'[J';
	}
	private function execute_option($input) {
		foreach($this->option as $var) {
			if($var['command']===$input) {
				$func = $var['function'];
				$func(self::$instance);
			}
		}
	}
	private function get_key() {
		$key = fgetc($this->stdin);
		if($key===chr(10)) { // Line Feed
			return ['ENT',null];
		}
		elseif($key===chr(127) || $key==chr(8)) { // Backspace
			return ['BS',null];
		}
		elseif($key===chr(9)) { // Horizontal Tab
			return ['TAB',null];
		}
		elseif($key===chr(27)) { // Escape
			$subkey = $this->fgetc_purge();
			switch($subkey) {
				case ''; // ESC Key
					return ['ESC',null];
				case chr(91).chr(65): // Cursor Up
					return ['CUU',null];
				case chr(91).chr(66): // Cursor Down
					return ['CUD',null];
				case chr(91).chr(67): // Cursor Forward
					return ['CUF',null];
				case chr(91).chr(68): // Cursor Back
					return ['CUB',null];
				case chr(91).chr(51).chr(126): // Delete
					return ['DEL',null];
				default:
					return ['UNK',bin2hex($subkey)];
			}
		}
		elseif($key===chr(195) || $key===chr(240)) { // Extended table
			$subkey = $this->fgetc_purge();
			return [null,$key.$subkey];
		}
		elseif($key===chr(4)) { // End of Transmission
			$this->exit();
		}
		else {
			return [null,$key];
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
