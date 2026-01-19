<?php
/*
PowerPrompt is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/power-prompt/blob/main/LICENSE
*/
declare(strict_types=1);

trait PowerPromptStringTrait {
	public function get_string(string $title,string $input = '') : ?string {
		$this->set_pos(2,1);
		$this->echo($title.': ');
		$offset = mb_strlen($title.': ')+1;
		$cursor = mb_strlen($input);

		while(true) {
			$this->set_pos(2,$offset);
			$this->style('bold');
			$this->echo($input);
			$this->style();
			$this->clear_line();

			$this->set_pos(2,$offset+$cursor);

			$a = mb_substr($input, 0, $cursor);
			$b = mb_substr($input, $cursor);

			list($cmd,$key) = $this->get_key();
			switch($cmd) {
				case null: $a .= $key; $cursor += mb_strlen($key); break;
				case 'BS': $a = mb_substr($a,0,-1); $cursor--; break;
				case 'ENT': return $input;
				case 'ESC': return null;
				case 'DEL': $b = mb_substr($b,1); break;
				case 'CUF': $cursor++; break;
				case 'CUB': $cursor--; break;
				case 'EOT': $this->exit(); break;
			}
			$input = $a.$b;
			if($cursor<0) $cursor=0;
			if($cursor>mb_strlen($input)) $cursor=mb_strlen($input);
		}
	}
	public function get_binary(string $title) : ?string {
		$this->set_pos(2,1);
		$this->echo($title.': ');
		$input = '';

		while(true) {
			$this->set_pos(2,mb_strlen($title.': '));
			$this->style('bold');
			$this->echo(number_format(mb_strlen($input),0,'','.').' bytes');
			$this->style();
			$this->clear_line();

			list($cmd,$key) = $this->get_key();
			switch($cmd) {
				case null: $input .= $key; break;
				case 'ENT': $input .= PHP_EOL; break;
				case 'BS': $input = mb_substr($input,0,-1); break;
				case 'ESC': return null;
				case 'DEL': $input = ''; break;
				case 'EOT': return $input; break;
			}
		}
	}
	public function get_password(string $title) : ?string {
		$this->set_pos(2,1);
		$this->echo($title.': ');
		$input = '';

		while(true) {
			list($cmd,$key) = $this->get_key();
			switch($cmd) {
				case null: $input .= $key; break;
				case 'ENT': return $input;
				case 'BS': $input = mb_substr($input,0,-1); break;
				case 'ESC': return null;
				case 'DEL': $input = ''; break;
			}
		}
	}
	public function update_string(string $title,string &$input) : void {
		$new = $this->get_string($title,$input);
		if($new!==null) {
			$input = trim($new);
		}
	}
}
