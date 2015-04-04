<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use bariew\moduleModule\models\Snapshot;

/**
 * @var yii\web\View $this
 * @var Snapshot $model
 * @var yii\widgets\ActiveForm $form
 */

$this->title = Yii::t('modules/module', 'Clone module');
?>
<div class="module-update">

    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="module-form">
        <?php $form = ActiveForm::begin(); ?>
        <?php echo $form->field($model, 'tables')->checkboxList($model::tableList(), [
            'item' => function ($index, $label, $name, $checked, $value){
                $checked = $checked ? 'checked=""' : '';
                return "<li><label><input type='checkbox' name='$name' $checked value='$value' />$label</label></li>";
        }
        ]) ?>
        <?= $form->field($model, 'onlyMigration')->checkbox(); ?>
        <div class="form-group">
            <?php echo Html::submitButton(Yii::t('modules/module', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>