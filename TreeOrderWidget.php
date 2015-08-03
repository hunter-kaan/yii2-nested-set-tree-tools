<?php
namespace valiant\Tree\NestedSet;

use creocoder\nestedsets\NestedSetsBehavior;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\widgets\InputWidget;

/**
 * Class TreeOrderWidget
 * @package valiant\Tree\NestedSet
 *
 * @property ActiveRecord|\creocoder\nestedsets\NestedSetsBehavior|TreeOrderModelTrait $model
 */
class TreeOrderWidget extends InputWidget
{
	const END_OF_LIST = -1;
	const TOP_OF_LIST = -2;
	const MAKE_A_TREE = -1;

	public $identityAttribute = 'id';
	public $nameAttribute = 'name';

	public $parentLabel = 'Parent';
	public $orderLabel = 'Insert After';

	protected $isCanMakeRoot;
	protected $tree = [];
	protected $orderTree = [];

	protected $parentOptions = [];
	protected $orderOptions = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (!$this->hasModel()) {
			throw new InvalidConfigException("Either 'model' properties must be specified.");
		}

		$this->checkModelNestedSetsBehavior();
		$this->checkModelTreeOrderTrait();


		if (!isset($this->options['id'])) {
			$this->options['id'] = $this->getId();
		}

		$this->initTree();
		$this->initOptions();
	}

	/**
	 * @return boolean whether this widget is associated with a data model.
	 */
	protected function hasModel()
	{
		return $this->model instanceof Model;
	}

	protected function checkModelNestedSetsBehavior()
	{
		foreach ($this->model->behaviors as $behavior) {
			if ($behavior instanceof NestedSetsBehavior) {
				return;
			}
		}

		throw new InvalidCallException('Missing ' . VarDumper::dumpAsString(NestedSetsBehavior::className()) . ' for model ' . VarDumper::dump($this->model->className()));
	}

	protected function checkModelTreeOrderTrait()
	{
		$uses = class_uses($this->model->className());
		foreach ($uses as $className) {
			if ($className === __NAMESPACE__ . '\TreeOrderModelTrait') {
				return;
			}
		}

		throw new InvalidCallException('Missing ' . VarDumper::dumpAsString(__NAMESPACE__ . '\TreeOrderModelTrait') . ' for model ' . VarDumper::dump($this->model->className()));

	}

	protected function initTree()
	{
		/** @var \creocoder\nestedsets\NestedSetsQueryBehavior|ActiveQuery $query */
		$query = $this->model->find();
		$this->isCanMakeRoot = !$query->roots()->exists();

		/** @var ActiveQuery $query */
		$query = $this->model->find();
		$this->tree = $query->orderBy([$this->model->leftAttribute => SORT_ASC])->all();
	}

	protected function initOptions()
	{
		$this->orderTree = [];
		if (!empty($this->tree)) {
			$this->prepareOrderTree(0);
		}

		if (is_null($this->model->parentAttribute)) {
			$this->model->parentAttribute = $this->attribute . 'Parent';
		}

		if (is_null($this->model->orderAttribute)) {
			$this->model->orderAttribute = $this->attribute . 'Order';
		}

		$this->parentOptions = ArrayHelper::getValue($this->options, 'parent', []);
		$this->orderOptions = ArrayHelper::getValue($this->options, 'order', []);
		unset($this->options['parent'], $this->options['order']);

		$id = $this->options['id'];
		$this->parentOptions['id'] = ArrayHelper::getValue($this->parentOptions, 'id', $id . '-parent');
		$this->orderOptions['id'] = ArrayHelper::getValue($this->orderOptions, 'id', $id . '-order');
		$this->options['data-parent-id'] = $this->parentOptions['id'];
		$this->options['data-order-id'] = $this->orderOptions['id'];

		Html::addCssClass($this->parentOptions, 'form-control');
		Html::addCssClass($this->orderOptions, 'form-control');

	}

	protected function prepareOrderTree($start)
	{
		$initialModel = $this->tree[$start];
		$left = $initialModel->{$this->model->leftAttribute};
		$right = $initialModel->{$this->model->rightAttribute};
		$depth = $initialModel->{$this->model->depthAttribute} + 1;

		if ($right - $left < 2) {
			return;// Is leaf.
		}

		$tree = array_filter(array_slice($this->tree, $start + 1, null, true), function ($model) use ($right, $depth) {
			return $right > $model->{$this->model->rightAttribute} && $model->{$this->model->depthAttribute} === $depth;
		});

		$this->orderTree[$initialModel->id] = ArrayHelper::map($tree, 'id', $this->nameAttribute);
		foreach (array_keys($tree) as $key) {
			$this->prepareOrderTree($key);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$parentChoices = ($this->isCanMakeRoot ? [TreeOrderWidget::MAKE_A_TREE => 'Make a tree'] : []) +
			TreeArrayHelper::treeMap($this->tree, $this->identityAttribute, $this->nameAttribute, null, null, $this->model->depthAttribute);
		$parentValue = $this->hasModel() ? $this->model->{$this->model->parentAttribute} : ArrayHelper::getValue($this->value, 'Parent', null);

		$orderChoices = [
			TreeOrderWidget::END_OF_LIST => 'End of list',
			TreeOrderWidget::TOP_OF_LIST => 'Top of list',
		];
		if (!is_null($parentValue)) {
			$orderChoices = $orderChoices + ArrayHelper::getValue($this->orderTree, $parentValue, []);
		}

		if ($this->hasModel()) {
			$parentControl = Html::activeDropDownList($this->model, $this->model->parentAttribute, $parentChoices, $this->parentOptions);
			$orderControl = Html::activeDropDownList($this->model, $this->model->orderAttribute, $orderChoices, $this->orderOptions);
		} else {
			$parentControl = Html::dropDownList($this->name . '[Parent]', ArrayHelper::getValue($this->value, 'Parent', null), $parentChoices, $this->parentOptions);
			$orderControl = Html::dropDownList($this->name . '[Order]', ArrayHelper::getValue($this->value, 'Order', null), $orderChoices, $this->orderOptions);
		}

		echo Html::beginTag('div', $this->options),
		Html::tag('label', $this->parentLabel),
		$parentControl,
		Html::tag('label', $this->orderLabel),
		$orderControl,
		Html::endTag('div');

		$this->registerWidget();
	}

	/**
	 * Registers Tree Order widget
	 */
	protected function registerWidget()
	{
		$js = [];

		$view = $this->getView();

		TreeOrderWidgetAsset::register($view);

		$id = $this->options['id'];

		$tree = Json::encode(is_array($this->orderTree) ? $this->orderTree : []);
		$js[] = 'valiant.treeOrderWidget.registerTreeOrderWidget("' . $id . '", ' . $tree . ');';

		$view->registerJs(implode(PHP_EOL, $js));
	}
}