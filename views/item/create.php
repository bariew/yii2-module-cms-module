<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\Item $model
 */

$this->title = Yii::t('modules/module', 'Create {modelClass}', [
  'modelClass' => 'Item',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('modules/module', 'Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-create">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
