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

    <?php echo $form->field($model, 'active')->checkbox([]) ?>

    <div class="form-group">
        <?php echo Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
