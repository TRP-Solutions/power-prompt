#!/usr/bin/env php
<?php
/*
PowerPrompt is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/power-prompt/blob/main/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/../lib/PowerPrompt.php';

date_default_timezone_set('Europe/Copenhagen');
DEFINE('FILE',__DIR__.'/timestamp');

$param = getopt('',['background']);
$background = isset($param['background']);

main($background);

function main(bool $background) : void {
	$pp = PowerPrompt::getInstance();
	$timestamp = is_file(FILE) ? file_get_contents(FILE) : '';
	$feedback = null;

	while(!$background) {
		$pp->header('PowerPrompt :: Sample');
		$pp->clear_screen();

		$pp->style('blue');
		$pp->echo('Timestamp: "'.$timestamp.'"');
		$pp->lf();
		if($feedback) {
			$pp->style('blink','yellow');
			$pp->echo('Feedback: '.$feedback);
		}
		$pp->style();

		$pp->add_option('custom','C: Custom','C',5,1);
		$pp->add_option('update','U: Update','U',5,16);
		$pp->add_option('save','S: Save','S',5,31);
		$pp->add_option('ESC','ESC: Quit','ESC',5,46);

		$option = $pp->select_option('Select action');
		switch($option) {
			case 'custom':
				$pp->update_string('Timestamp',$timestamp);
				$feedback = 'Entered';
				break;
			case 'update':
				$timestamp = date('r');
				$feedback = 'Updated';
				break;
			case 'save':
				file_put_contents(FILE,$timestamp);
				$feedback = 'Saved';
				break;
			case 'ESC':
				$pp->exit();
		}
	}

	syslog(LOG_NOTICE,'PowerPrompt :: Background');
	file_put_contents(FILE,date('r'));
	$pp->exit(null);
}
