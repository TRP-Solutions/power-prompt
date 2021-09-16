<?php
trait PowerPromptOptionTrait {
	private $option = [];

	public function reset_option() {
		$this->option = [];
	}
	public function add_option($command,$function,$selected = false) {
		$command = mb_strtoupper($command);

		list($row,$col) = $this->get_pos();
		$this->option[] = [
			'command' => $command,
			'function' => $function,
			'row' => $row,
			'col' => $col,
			'selected' => $selected,
		];
	}
	private function update_option() {
		foreach($this->option as $key => $value) {
			$this->option[$key]['selected'] = (bool) !$value['selected'];
		}
	}
	private function draw_option() {
		foreach($this->option as $key => $value) {
			$this->set_pos($value['row'],$value['col']);
			$this->style('bold');
			if($value['selected']) {
				$this->style('underline','blue');
			}
			$this->echo('['.$value['command'].']');
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
				$this->echo('Not fould ('.$command.')');
				$this->style();
			}

			list($cmd,$key) = $this->get_key();
			switch($cmd) {
				case null:
					$command = mb_strtoupper($key);
					$wrong = true;
					foreach($this->option as $var) {
						if($var['command']===$command) {
							$wrong = false;
							$func = $var['function'];
							$func(self::$instance);
							return;
						}
					}
					break;
				case 'CUF': $this->update_option(); $this->draw_option(); break;
				case 'ESC':
					return;
					break;
			}
		}
	}
}
