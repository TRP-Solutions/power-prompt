<?php
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

			system('stty cbreak');
			$this->clear_screen();
			$this->stdin = fopen('php://stdin', 'r');
		}
	}
	public function header($text) {
		$this->set_pos(1,1);

		$len = mb_strlen($text)+2;

		$this->style('white');
		$this->echo(str_pad('',ceil(($this->width-$len)/2),'='));
		$this->style('blue','bold');
		$this->echo(' '.$text.' ');
		$this->style('white','normal');
		$this->echo(str_pad('',floor(($this->width-$len)/2),'='));
		$this->style();
		$this->lf();
	}
	private function exit() {
		$this->clear_screen();
		$this->echo('Bye');
		$this->lf();
		exit;
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new PowerPrompt();
		}
		return self::$instance;
	}
}
