<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;

/**
 * Class CallRenderer
 *
 * @since 1.0
 */
class CallRenderer extends AbstractXulRenderer
{
	/**
	 * doRender
	 *
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	protected static function doRender($name, \SimpleXmlElement $element, $data)
	{
		$static = XmlHelper::get($element, 'static');

		if ($static)
		{
			static::executeStatic($static, $element, $data);
		}
		else
		{
			$name = XmlHelper::get($element, 'data', XmlHelper::get($element, 'name'));

			static::executeInstance($name, $element, $data);
		}
	}

	protected static function executeStatic($static, $element, $data)
	{
		$static = explode('::', $static);

		// We don't check is_callable that can notice user is method callable or not.
		return call_user_func_array($static, static::getArguments($element, $data));
	}

	protected static function executeInstance($name, $element, $data)
	{
		$method   = XmlHelper::get($element, 'method');
		$instance = static::getArgumentFromData($name, $data);

		return call_user_func_array(array($instance, $method), static::getArguments($element, $data));
	}

	protected static function getArguments($element, $data)
	{
		$args = $element->xpath('argument');

		$return = array();

		foreach ($args as $arg)
		{
			if (isset($arg['data']))
			{
				$return[] = static::getArgumentFromData((string) $arg['data'], $data);
			}
			else
			{
				if (strtolower($arg) == 'null')
				{
					$arg = null;
				}

				if (strtolower($arg) == 'false')
				{
					$arg = false;
				}

				$return[] = (string) $arg;
			}
		}

		return $return;
	}

	protected static function getArgumentFromData($arguments, $data)
	{
		if (!$arguments)
		{
			return null;
		}

		$args = explode('.', $arguments);

		$dataTmp = $data;

		foreach ($args as $arg)
		{
			if (is_object($dataTmp) && !empty($dataTmp->$arg))
			{
				$dataTmp = $dataTmp->$arg;
			}
			elseif (is_array($dataTmp) && !empty($dataTmp[$arg]))
			{
				$dataTmp = $dataTmp[$arg];
			}
			else
			{
				return null;
			}
		}

		return $dataTmp;
	}
}
