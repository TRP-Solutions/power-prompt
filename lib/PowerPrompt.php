<?php
/*
PowerPrompt is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/power-prompt/blob/main/LICENSE
*/
declare(strict_types=1);

require_once __DIR__.'/PowerPromptIO.php';
require_once __DIR__.'/PowerPromptOption.php';
require_once __DIR__.'/PowerPromptString.php';

class PowerPrompt {
	use PowerPromptIOTrait;
	use PowerPromptOptionTrait;
	use PowerPromptStringTrait;

	private static PowerPrompt $instance;

	private int $width;
	private int $height;
	private ?string $stty_state = null;
	private mixed $stdin;

	private function __construct() {
		$output = $result_code = null;
		$this->stty_state = exec('stty -g 2>/dev/null',$output,$result_code);
		if($result_code===0) {
			$this->width = (int) exec('tput cols');
			$this->height = (int) exec('tput lines');
			system('stty cbreak -echo');
			$this->stdin = fopen('php://stdin', 'r');
		}
		else {
			$this->stty_state = null;
		}
	}
	public function header(string $text) : void {
		$this->set_pos(1,1);

		$len = mb_strlen($text)+2;

		$this->style('white');
		$this->echo('',(int) ceil(($this->width-$len)/2),'=');
		$this->style('blue','bold');
		$this->echo(' '.$text.' ');
		$this->style('white','normal');
		$this->echo('',(int) floor(($this->width-$len)/2),'=');
		$this->style();
		$this->lf();
	}
	public function exit(?string $msg = 'Bye') : void {
		if($msg !== null) {
			$this->echo($msg);
			$this->lf();
		}
		if($this->stty_state !== null) {
			system('stty '.$this->stty_state);
		}
		exit;
	}
	public static function getInstance() : PowerPrompt {
		if(!isset(self::$instance)) {
			self::$instance = new PowerPrompt();
		}
		return self::$instance;
	}
}
