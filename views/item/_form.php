<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\Item $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="module-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'address')->textInput(['maxlength' => 255]) ?>

    <?php echo $form->field($model, 'status')->dropDownList($model::statusList()); ?>

    <?php echo $form->field($model, 'type')->dropDownList($model::typeList()) ?>

    <div class="form-group">
        <?php echo Html::submitButton('Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
