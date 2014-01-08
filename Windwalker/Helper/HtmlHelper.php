<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Helper;

// No direct access
use Windwalker\Helper\Html\HtmlElement;

defined('_JEXEC') or die;

/**
 * HTML Helper to handle some text.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class HtmlHelper
{
	/**
	 * @var  array  Property coupleTags.
	 */
	protected static $unpairedTags = array(
		'img', 'br', 'hr', 'area', 'param', 'wbr', 'base', 'link', 'meta', 'input', 'option'
	);

	/**
	 * buildTag
	 *
	 * @param string $name
	 * @param mixed  $content
	 * @param array  $attribs
	 *
	 * @return  string
	 */
	public static function buildTag($name, $content = '', $attribs = array())
	{
		$name = trim($name);

		$unpaired = in_array(strtolower($name), static::$unpairedTags);

		$tag = '<' . $name;

		foreach ((array) $attribs as $key => $value)
		{
			if ($value !== null && $value !== false && $value !== '')
			{
				$tag .= ' ' . $key . '=' . StringHelper::quote($value, '""');
			}
		}

		if ($content)
		{
			if (!($content instanceof HtmlElement))
			{
				$content = implode(PHP_EOL, (array) $content);
			}

			$tag .= '>' . PHP_EOL . "\t" . $content . PHP_EOL . '</' . $name . '>';
		}
		else
		{
			$tag .= $unpaired ? ' />' : '></' . $name . '>';
		}

		return $tag;
	}

	/**
	 * Repair HTML. If Tidy not exists, use repair function.
	 *
	 * @param   string  $html     The HTML string to repair.
	 * @param   boolean $use_tidy Force tidy or not.
	 *
	 * @return  string  Repaired HTML.
	 */
	public static function repair($html, $use_tidy = true)
	{
		if (function_exists('tidy_repair_string') && $use_tidy)
		{
			$TidyConfig = array('indent'         => true,
								'output-xhtml'   => true,
								'show-body-only' => true,
								'wrap'           => false
			);

			return tidy_repair_string($html, $TidyConfig, 'utf8');
		}
		else
		{
			$arr_single_tags = array('meta', 'img', 'br', 'link', 'area');

			// Put all opened tags into an array
			preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
			$openedtags = $result[1];

			// Put all closed tags into an array
			preg_match_all("#</([a-z]+)>#iU", $html, $result);
			$closedtags = $result[1];
			$len_opened = count($openedtags);

			// All tags are closed
			if (count($closedtags) == $len_opened)
			{
				return $html;
			}

			$openedtags = array_reverse($openedtags);

			// Close tags
			for ($i = 0; $i < $len_opened; $i++)
			{
				if (!in_array($openedtags[$i], $closedtags))
				{
					if (!in_array($openedtags[$i], $arr_single_tags))
					{
						$html .= "</" . $openedtags[$i] . ">";
					}
				}
				else
				{
					unset ($closedtags[array_search($openedtags[$i], $closedtags)]);
				}
			}

			return $html;
		}
	}

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param   array  $array  The array to convert to JavaScript object notation
	 *
	 * @return  string  JavaScript object notation representation of the array
	 *
	 * @since   3.0
	 */
	public static function getJSObject(array $array = array())
	{
		$elements = array();

		foreach ($array as $k => $v)
		{
			// Don't encode either of these types
			if (is_null($v) || is_resource($v))
			{
				continue;
			}

			// Safely encode as a Javascript string
			$key = json_encode((string) $k);

			if (is_bool($v))
			{
				$elements[] = $key . ': ' . ($v ? 'true' : 'false');
			}
			elseif (is_numeric($v))
			{
				$elements[] = $key . ': ' . ($v + 0);
			}
			elseif (is_string($v))
			{
				if (strpos($v, '\\') === 0)
				{
					// Items such as functions and JSON objects are prefixed with \, strip the prefix and don't encode them
					$elements[] = $key . ': ' . substr($v, 1);
				}
				else
				{
					// The safest way to insert a string
					$elements[] = $key . ': ' . json_encode((string) $v);
				}
			}
			else
			{
				$elements[] = $key . ': ' . static::getJSObject(is_object($v) ? get_object_vars($v) : $v);
			}
		}

		return '{' . implode(',', $elements) . '}';
	}
}