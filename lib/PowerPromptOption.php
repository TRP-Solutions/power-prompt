<?php
/*
PowerPrompt is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/power-prompt/blob/main/LICENSE
*/
declare(strict_types=1);

trait PowerPromptOptionTrait {
	private $option = [];

	public function reset_option() {
		$this->option = [];
	}
	public function add_option($command,$function,$shortcut = false,$row = null,$col = null,$selected = false) {
		if(!$row || !$col) list($row,$col) = $this->get_pos();

		$this->option[] = [
			'command' => $command,
			'function' => $function,
			'shortcut' => mb_strtoupper((string) $shortcut),
			'row' => $row,
			'col' => $col,
			'selected' => $selected,
		];
	}
	private function update_option($operation) {
		$size = sizeof($this->option);
		$current = -1;
		foreach($this->option as $key => $value) {
			if($value['selected']) {
				$current = $key;
				break;
			}
		}

		if($operation=='start') $current = 0;
		if($operation=='end') $current = $size-1;
		if($operation=='prev') $current--;
		if($operation=='next') $current++;
		if($current<0) $current = $size-1;
		if($current>=$size) $current = 0;

		foreach($this->option as $key => $value) {
			$this->option[$key]['selected'] = (bool) ($current==$key);
		}
		$this->draw_option();
	}
	private function draw_option() {
		foreach($this->option as $key => $value) {
			$this->set_pos($value['row'],$value['col']);
			$this->style('bold');
			if($value['selected']) $this->style('cyan');
			$this->echo('['.$value['function'].']');
			$this->style();
		}
	}
	public function select_option($title,$preset = '') {
		$this->draw_option();
		$wrong = false;

		while(true) {
			$this->set_pos(2,1);
			$this->echo($title.': ');
			$this->clear_line();
			if($wrong) {
				$this->style('yellow','bold');
				$this->echo('Unknown shortcut ('.$shortcut.')');
				$this->style();
			}

			list($cmd,$key) = $this->get_key();
			switch($cmd) {
				case null:
					$shortcut = mb_strtoupper($key);
					$wrong = true;
					foreach($this->option as $var) {
						if($var['shortcut']===$shortcut) {
							return $var['command'];
						}
					}
					break;
				case 'ENT':
					foreach($this->option as $var) {
						if($var['selected']) {
							return $var['command'];
						}
					}
					break;
				case 'CUU': $this->update_option('prev'); break;
				case 'CUD': $this->update_option('next'); break;
				case 'CUB': $this->update_option('start'); break;
				case 'CUF': $this->update_option('end'); break;
				case 'EOT': $this->exit(); break;
				default:
					foreach($this->option as $var) {
						if($var['shortcut']===$cmd) {
							return $cmd;
						}
					}
					$shortcut = $cmd;
					$wrong = true;
					break;
			}
		}
	}
}
