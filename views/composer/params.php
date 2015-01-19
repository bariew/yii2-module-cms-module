<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use bariew\moduleModule\models\Param;

/**
 * @var yii\web\View $this
 * @var Param $model
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\Item $model
 * @var yii\widgets\ActiveForm $form
 */

$this->title = Yii::t('modules/module', 'Update {title}', ['title' => $model->item->moduleName]);
$this->params['breadcrumbs'][] = ['label' =>Yii::t('modules/module', 'Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="module-update">

    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="module-form">

        <?php $form = ActiveForm::begin(); ?>
        <?php foreach ($model->attributes() as $attribute): ?>
            <?php if($model->isSerializable($attribute)): ?>
                <?php echo $form->field($model, $attribute)->textarea() ?>
            <?php else : ?>
                <?php echo $form->field($model, $attribute)->textInput(['maxlength' => 255]) ?>
            <?php endif ; ?>
        <?php endforeach; ?>
        <div class="form-group">
            <?php echo Html::submitButton(Yii::t('modules/module', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>