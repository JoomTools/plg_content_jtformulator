<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
 *
 * @see http://robo.li/
 */

require_once 'vendor/autoload.php';

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', __DIR__);
}

class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use \joomla_projects\robo\loadTasks;
	use \Joomla\Jorobo\Tasks\loadTasks;
	
	/**
	 * Build the joomla extension package
	 *
	 * @param   array  $params  Additional params
	 *
	 * @return  void
	 */
	public function build($params = ['dev' => false])
	{
		if (!file_exists('jorobo.ini'))
		{
			$this->_copy('jorobo.dist.ini', 'jorobo.ini');
		}

		$this->taskBuild($params)->run();
	}
}