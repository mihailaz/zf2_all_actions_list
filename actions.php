<?php
echo '<pre>';
function scan($dir)
{
	mysql_connect('localhost','root','qazwsx') or die('error 1');
	mysql_select_db('smotorom_development_2013') or die('error 2');;

	foreach (new DirectoryIterator($dir) as $dir_or_file)
	{
		if ($dir_or_file->isDot())
			continue;

		if ($dir_or_file->isDir())
		{
			scan($dir_or_file->getPathname());
			continue;
		}

		if (!$dir_or_file->isFile())
			throw new \Exception('Not file');

		if (strstr($dir_or_file->getPathname(), '.php'))
		{
			if (strstr($dir_or_file->getPathname(), 'Controller'))
			{
				$file = fopen($dir_or_file->getPathname(), 'r');

				$namespace = getNamespace($file);
				$class = getClass($file);
				$actions = getActions($file);
//				echo $namespace . '\\' . $class;
//				var_dump($actions);
//				die();

				if (!$class || !$namespace || !$actions)
					continue;

				foreach ($actions as $a)
				{
					$resource =  $namespace . '\\\\' . $class . '\\\\' . $a;
					$date = date('Y-m-d H:i:s');

					$sql = <<<QUERY
INSERT INTO `resources` (`parent_id`, `name`, `updated_at`, `created_at`)
VALUES
	(2, '$resource', '$date', '$date');
QUERY;
					mysql_query($sql) or die('error 3');
					echo $resource . " - OK\n";
				}
			}
		}

	}

}

function getClass($file)
{
	$class = '';

	while ($str = fgets($file))
	{
		$arr = explode(' ', $str);

		if (isset($arr[0]) && $arr[0] == 'class' && isset($arr[1]))
		{
			$class = $arr[1];
			$class = str_replace(' ', '', $class);
			$class = str_replace("\n", '', $class);
			$class = str_replace("\r", '', $class);
			$class = str_replace("Controller", '', $class);
			break;
		}
	}

	return $class;
}

function getNamespace($file)
{
	$name = '';

	while ($str = fgets($file))
	{
		$arr = explode(' ', $str);

		if (isset($arr[0]) && $arr[0] == 'namespace' && isset($arr[1]))
		{
			$name = str_replace(';', '', $arr[1]);
			$name = str_replace(' ', '', $name);
			$name = str_replace("\n", '', $name);
			$name = str_replace("\r", '', $name);
			$name = str_replace("\\", '\\\\', $name);
			break;
		}
	}

	return $name;
}

function getActions($file)
{
	$actions = array();

	while ($str = fgets($file))
	{
		if (preg_match('/public function ([a-zA-Z0-9]+)Action/', $str, $action))
		{
			if (isset($action[1]))
				$actions[] = $action[1];
		}
	}

	return $actions;
}

$root = 'D://htdocs/smotorom/src/module';

foreach (new DirectoryIterator($root) as $dir_or_file)
{
	if ($dir_or_file->isDot())
		continue;
	if ($dir_or_file->isDir())
		scan($dir_or_file->getPathname());
}
?>