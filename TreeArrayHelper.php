<?php
namespace valiant\Tree\NestedSet;

use yii\helpers\BaseArrayHelper;

class TreeArrayHelper extends BaseArrayHelper
{
	/**
	 * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
	 * The `$from` and `$to` parameters specify the key names or property names to set up the map.
	 * Optionally, one can further group the map according to a grouping field `$group`.
	 *
	 * For example,
	 *
	 * ~~~
	 * $array = [
	 *     ['id' => '123', 'name' => 'aaa', 'depth' => '0', 'class' => 'x'],
	 *     ['id' => '124', 'name' => 'bbb', 'depth' => '1', 'class' => 'x'],
	 *     ['id' => '345', 'name' => 'ccc', 'depth' => '0', 'class' => 'y'],
	 * ];
	 *
	 * $result = TreeArrayHelper::map($array, 'depth', 'id', 'name', null, '→');
	 * // the result is:
	 * // [
	 * //     '123' => 'aaa',
	 * //     '124' => '→ bbb',
	 * //     '345' => 'ccc',
	 * // ]
	 *
	 * $result = TreeArrayHelper::map($array, 'depth', 'id', 'name', 'class', '→');
	 * // the result is:
	 * // [
	 * //     'x' => [
	 * //         '123' => 'aaa',
	 * //         '124' => '→ bbb',
	 * //     ],
	 * //     'y' => [
	 * //         '345' => 'ccc',
	 * //     ],
	 * // ]
	 * ~~~
	 *
	 * @param array $array
	 * @param string $depthAttribute
	 * @param string|\Closure $from
	 * @param string|\Closure $to
	 * @param string $delimiter
	 * @param string|\Closure $group
	 * @return array
	 */
	public static function treeMap($array, $depthAttribute, $from, $to, $group = null, $delimiter = '→')
	{
		$result = [];
		foreach ($array as $element) {
			$key = static::getValue($element, $from);
			$value = str_repeat(sprintf(' %s ', $delimiter), (int)$element[$depthAttribute]) . static::getValue($element, $to);
			if ($group !== null) {
				$result[static::getValue($element, $group)][$key] = $value;
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}
}