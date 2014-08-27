<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Yii;
/**
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\ItemSearch $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="module-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?php echo $form->field($model, 'id') ?>

    <?php echo $form->field($model, 'address') ?>

    <?php echo $form->field($model, 'title') ?>

    <?php echo $form->field($model, 'content') ?>

    <?php echo $form->field($model, 'owner_name') ?>


    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('modules/module', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('modules/module', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
