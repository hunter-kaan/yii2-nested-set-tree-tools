<?php
namespace valiant\Tree\NestedSet;

use yii\grid\DataColumn;
use yii\helpers\Html;

/**
 * Class TreeNameColumn
 * @package valiant\Tree\NestedSet
 */
class TreeNameColumn extends DataColumn
{
	public $levelSeparator = '<span class="tree-separator"></span>';
	public $leafIcon = '';//'<span class="glyphicon glyphicon-leaf"></span>';
	public $nodeIcon = '<span class="glyphicon node node-open"></span>';

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index)
	{
		$dataCellContent = parent::renderDataCellContent($model, $key, $index);

		/** @var $model \creocoder\nestedsets\NestedSetsBehavior */
		if ($model->isLeaf()) {
			$icon = Html::tag('span', $this->leafIcon, ['class' => 'tree-anchor']);
		} else {
			$icon = Html::tag('a', $this->nodeIcon, [
				'class' => 'tree-anchor',
				'data-level' => $model->{$model->depthAttribute},
				'data-left' => $model->{$model->leftAttribute},
				'data-right' => $model->{$model->rightAttribute},
			]);
		}

		return str_repeat($this->levelSeparator, (int)$model->{$model->depthAttribute}) . $icon . $dataCellContent;
	}
}