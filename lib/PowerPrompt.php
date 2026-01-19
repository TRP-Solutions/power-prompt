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

	private static $instance = null;

	private $width = null;
	private $height = null;
	private $stdin = null;

	private function __construct() {
		if($this->stdin===null) {
			$this->width = exec('tput cols');
			$this->height = exec('tput lines');

			$this->stty_state = shell_exec('stty -g');

			system('stty cbreak -echo');
			$this->clear_screen();
			$this->stdin = fopen('php://stdin', 'r');
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
	public function exit(string $msg = 'Bye') : void {
		$this->clear_screen();
		$this->echo($msg);
		$this->lf();
		system('stty '.$this->stty_state);
		exit;
	}
	public static function getInstance() : PowerPrompt {
		if(self::$instance == null) {
			self::$instance = new PowerPrompt();
		}
		return self::$instance;
	}
}
