<?php
namespace valiant\Tree\NestedSet;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Class TreeDropDownList
 * @package valiant\Tree\NestedSet
 *
 * @property \yii\db\BaseActiveRecord|\creocoder\nestedsets\NestedSetsBehavior $model
 */
class TreeDropDownList extends InputWidget
{
	public $items = [];

	public function init()
	{
		parent::init();
		Html::addCssClass($this->options, 'form-control');
	}

	public function run()
	{
		parent::run();

		// TODO: check model behavior

		if($this->model->isNewRecord && $this->model->treeAttribute) {
			$this->options['prompt'] = ArrayHelper::getValue($this->options, 'prompt', 'Make root');
		}
		else {
			unset($this->options['prompt']);
		}

		echo Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
	}


	public static function widget($config = [])
	{
		return parent::widget($config);
	}


}