<?php
namespace valiant\Tree\NestedSet;

use Closure;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\BaseDataProvider;
use yii\grid\Column;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * Class TreeView
 * @package valiant\Tree\NestedSet
 */
class TreeView extends GridView
{

	/**
	 * Renders a table row with the given data model and key.
	 * @param mixed $model the data model to be rendered
	 * @param mixed $key the key associated with the data model
	 * @param integer $index the zero-based index of the data model among the model array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	public function renderTableRow($model, $key, $index)
	{
		$cells = [];
		/* @var $column Column */
		foreach ($this->columns as $column) {
			$cells[] = $column->renderDataCell($model, $key, $index);
		}
		if ($this->rowOptions instanceof Closure) {
			$options = call_user_func($this->rowOptions, $model, $key, $index, $this);
		} else {
			$options = $this->rowOptions;
		}
		$options['data-key'] = is_array($key) ? json_encode($key) : (string)$key;

		/** @var $model \creocoder\nestedsets\NestedSetsBehavior */
		$options['data-level'] = $model->{$model->depthAttribute};
		$options['data-left'] = $model->{$model->leftAttribute};
		$options['data-right'] = $model->{$model->rightAttribute};

		return Html::tag('tr', implode('', $cells), $options);
	}


	/**
	 * Runs the widget.
	 */
	public function run()
	{
		$view = $this->getView();
		TreeViewAsset::register($view);

		if ($this->dataProvider instanceof BaseDataProvider) {
			$this->dataProvider->pagination = false;
			$this->dataProvider->sort = false;
		}
		parent::run();
	}
}