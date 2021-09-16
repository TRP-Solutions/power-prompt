<?php
trait PowerPromptStringTrait {
	public function get_string($title,$input = '') {
		$this->set_pos(2,1);
		$this->echo($title.': ');
		$offset = mb_strlen($title.': ');
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
			}
			$input = $a.$b;
			if($cursor<0) $cursor=0;
			if($cursor>mb_strlen($input)) $cursor=mb_strlen($input);
		}
	}

	public function update_string($title,&$input) {
		$new = $this->get_string($title,$input);
		if($new!==null) {
			$input = trim($new);
		}
	}
}
