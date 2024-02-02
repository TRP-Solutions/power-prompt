<?php
trait PowerPromptIOTrait {
	public function echo($string,$length = null,$pad_string = ' ',$pad_type = STR_PAD_RIGHT) {
		if($length !== null) {
			$length_add = strlen($string) - mb_strlen($string);
			echo str_pad($string,$length + $length_add,$pad_string,$pad_type);
		}
		else {
			echo $string;
		}
	}
	public function lf() {
		echo PHP_EOL;
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
			return ['EOT',null];
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
	public function set_pos($row,$col) {
		echo chr(27).'['.$row.';'.$col.'H';
	}
	public function get_pos() {
		// https://stackoverflow.com/questions/55892416/how-to-get-cursor-position-with-php-cli
		while(true) {
			echo "\033[6n";
			$buf = fread(STDIN, 16);
			$matches = [];
			preg_match('/^\033\[(\d+);(\d+)R$/', $buf, $matches);

			if(!empty($matches[1]) && !empty($matches[2])) {
				$row = intval($matches[1]);
				$col = intval($matches[2]);
				return [$row,$col];
			}
		}
	}
	public function clear_screen() {
		echo chr(27).'[2H'.chr(27).'[J';
		$this->set_pos(3,1);
	}
	private function clear_line() {
		echo chr(27).'[K';
	}
	public function beep() {
		echo chr(7);
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
			'hide'			=> '[8m',
			'bold'			=> '[1m',
			'underline'	=> '[4m',
			'blink'			=> '[5m',
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
