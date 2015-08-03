<?php
namespace valiant\Tree\NestedSet;

use yii\web\AssetBundle;

/**
 * Class TreeOrderWidgetAsset
 * @package valiant\Tree\NestedSet
 */
class TreeOrderWidgetAsset extends AssetBundle
{
	public $css = [
		'treeOrderWidget.css'
	];

	public $js = [
		'treeOrderWidget.js',
	];
	public $depends = [
		'yii\grid\GridViewAsset',
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/assets/tree-order-widget';
		parent::init();
	}
}