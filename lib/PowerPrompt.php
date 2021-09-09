<?php
class PowerPrompt {
	private static $width = null;
	private static $height = null;
	private static $stdin = null;
	
	private static $option = [];
	private static $input = '';
	
	static public function header($text) {
		self::init();
		self::setpos(1,1);
		
		$len = mb_strlen($text)+2;
		
		self::style('white');
		echo mb_str_pad('',ceil((self::$width-$len)/2),'=');
		self::style('blue','bold');
		echo ' '.$text.' ';
		self::style('white','normal');
		echo mb_str_pad('',floor((self::$width-$len)/2),'=');
		self::style();
		echo PHP_EOL;
	}
	static public function reset_option() {
		self::$option = [];
	}
	static public function add_option($command,$title,$function,$type = null) {
		self::init();
		
		self::$option[] = [
			'command' => $command,
			'function' => $function,
		];
		
		self::setpos(3+sizeof(self::$option),1);
		
		self::style('bold');
		echo mb_str_pad($command.':',5,' ');
		self::style();
		echo $title.PHP_EOL;
	}
	static public function select_option($title,$preset = '') {
		self::init();
		
		self::$input = $preset;
		
		while(true) {
			self::setpos(2,1);
			echo $title.': ';
			self::style('red','bold');
			echo self::$input;
			self::style();
			self::clearline();
			$key = fgetc(self::$stdin);
			
			self::setpos(10,1);
			
			if($key==chr(10)) { // Enter
				echo "ENTER";
				self::execute_option(self::$input);
			}
			elseif($key==chr(4)) { // EOT
				self::exit();
			}
			elseif($key==chr(127) || $key==chr(8)) { // Backspace
				self::$input = mb_substr(self::$input,0,-1);
			}
			elseif($key==chr(9)) { // Tab
			}
			elseif($key==chr(27)) { // Escape
				$subkey = self::fgetc_purge();
				switch($subkey) {
					case '';
						//echo "ESC";
						self::execute_option("ESC");
						break;
					case chr(91).chr(65): // Arrow Up
						echo "UP";
						break;
					case chr(91).chr(66): // Arrow Down
						echo "DOWN";
						break;
					default:
						echo bin2hex($subkey);
				}
			}
			else {
				//echo bin2hex($key).': ';
				self::$input .= $key;
			}
		}
	}
	static public function clear_screen() {
		echo chr(27).'[2H'.chr(27).'[J';
	}
	static private function execute_option($input) {
		echo $input;
		foreach(self::$option as $var) {
			if($var['command']===$input) {
				$func = $var['function'];
				$func();
			}
		}
	}
	static private function fgetc_purge() {
		$return = '';
		
		while(true) {
			$read = [self::$stdin];
			$write = $except = [];
			if(stream_select($read, $write, $except, 0, 1)) {
				$return .= fgetc(self::$stdin);
			}
			else {
				return $return;
			}
		}
	}
	static private function init() {
		if(self::$stdin===null) {
			self::$width = exec('tput cols');
			self::$height = exec('tput lines');
			
			system('stty cbreak');
			self::clear_screen();
			self::$stdin = fopen('php://stdin', 'r');
			stream_set_timeout(self::$stdin, 2);
		}
	}
	static private function exit() {
		self::clear_screen();
		echo "Bye".PHP_EOL;
		exit;
	}
	static private function setpos($i,$j) {
		echo chr(27).'['.$i.';'.$j.'H';
	}
	static private function clearline() {
		echo chr(27).'[K';
	}
	static public function style(...$styles) {
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
}
