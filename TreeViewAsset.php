<?php
namespace valiant\Tree\NestedSet;

use yii\web\AssetBundle;

/**
 * Class TreeViewAsset
 * @package valiant\Tree\NestedSet
 */
class TreeViewAsset extends AssetBundle
{
	public $css = [
		'treeView.css'
	];

	public $js = [
		'treeView.js',
	];
	public $depends = [
		'yii\grid\GridViewAsset',
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/assets/tree-view';
		parent::init();
	}
}