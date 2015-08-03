<?php
namespace valiant\Tree\NestedSet;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class TreeOrderModelTrait
 * @package valiant\Tree\NestedSet
 *
 * @property string $orderAttribute
 * @property string $parentAttribute
 *
 * @method ActiveRecord|null findOne($condition)
 *
 * NestedSetsBehavior:
 * @property string|FALSE $treeAttribute
 * @property string $leftAttribute
 * @property string $rightAttribute
 * @property string $depthAttribute
 *
 * @method boolean makeRoot($runValidation = true, $attributes = null)
 * @method boolean prependTo($node, $runValidation = true, $attributes = null)
 * @method boolean appendTo($node, $runValidation = true, $attributes = null)
 * @method boolean insertBefore($node, $runValidation = true, $attributes = null)
 * @method boolean insertAfter($node, $runValidation = true, $attributes = null)
 * @method integer|false deleteWithChildren()
 * @method integer|false deleteWithChildrenInternal()
 * @method ActiveQuery parents($depth = null)
 * @method ActiveQuery children($depth = null)
 * @method ActiveQuery leaves()
 * @method ActiveQuery prev()
 * @method ActiveQuery next()
 * @method boolean isRoot()
 * @method boolean isChildOf(ActiveRecord $node)
 * @method boolean isLeaf()
 */
trait TreeOrderModelTrait
{
	public $orderAttribute = 'treeOrder';
	public $parentAttribute = 'treeParent';

	private $values = [];

	/**
	 * @param $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if ($name == $this->parentAttribute) {
			return $this->getParentValue();
		}

		if ($name == $this->orderAttribute) {
			return $this->getOrderValue();
		}

		/** @noinspection PhpUndefinedMethodInspection */
		return parent::__get($name);
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value)
	{
		if ($name == $this->parentAttribute || $name == $this->orderAttribute) {
			$this->values[$name] = $value;
		} else {
			/** @noinspection PhpUndefinedMethodInspection */
			parent::__set($name, $value);
		}
	}


	/**
	 * @return mixed|null
	 */
	public function getParentValue()
	{
		$value = ArrayHelper::getValue($this->values, $this->parentAttribute);
		if (is_null($value)) {
			/** @var mixed $parent */
			$value = !is_null($parent = $this->parents(1)->one()) ? $parent->id : null;
			$this->values[$this->parentAttribute] = $value;
		}

		return $value;

	}

	/**
	 * @return mixed|null
	 */
	public function getOrderValue()
	{
		$value = ArrayHelper::getValue($this->values, $this->orderAttribute);
		if (is_null($value)) {
			/** @var mixed $prev */
			$value = !is_null($prev = $this->prev()->one()) ? $prev->id : null;
			if (is_null($value)) {
				/** @var self $parent */
				$parent = $this->parents(1)->one();
				$value = is_null($parent) || $parent->children()->orderBy([])->count() < 2 ? TreeOrderWidget::END_OF_LIST
					: TreeOrderWidget::TOP_OF_LIST;
			}
			$this->values[$this->orderAttribute] = $value;
		}

		return $value;
	}

	/**
	 * @param bool|true $runValidation
	 * @param null $attributeNames
	 * @return bool
	 */
	public function saveNode($runValidation = true, $attributeNames = null)
	{
		if ($this->{$this->parentAttribute} == TreeOrderWidget::MAKE_A_TREE) {
			$result = $this->makeRoot($runValidation, $attributeNames);
		} else if ($this->{$this->orderAttribute} == TreeOrderWidget::TOP_OF_LIST) {
			$result = $this->prependTo(static::findOne(['id' => $this->{$this->parentAttribute}]));
		} else if ($this->{$this->orderAttribute} == TreeOrderWidget::END_OF_LIST) {
			$result = $this->appendTo(static::findOne(['id' => $this->{$this->parentAttribute}]));
		} else {
			$result = $this->insertAfter(static::findOne(['id' => $this->{$this->orderAttribute}]));
		}

		return $result;
	}
}