<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\Item $model
 */

$this->params['breadcrumbs'][] = ['label' => Yii::t('modules/module', 'Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-view">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php $form = \yii\bootstrap\ActiveForm::begin([
        'options' => [
            "enctype"=>"multipart/form-data"
        ]
    ]) ; ?>
    <?= Html::fileInput('upload'); ?>
    <button class="btn btn-primary pull-right">upload</button>
    <?= Html::button('Upload', ['class' => 'btn btn-primary pull-right']); ?>
    <?php $form->end() ; ?>
</div>
