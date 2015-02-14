<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use bariew\moduleModule\models\Param;

/**
 * @var yii\web\View $this
 * @var Param $model
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\CloneModel $model
 * @var yii\widgets\ActiveForm $form
 */

$this->title = Yii::t('modules/module', 'Clone module');
?>
<div class="module-update">

    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="module-form">
        <?php $form = ActiveForm::begin(); ?>
        <?php $request = new \yii\web\JsExpression('function (request, response) {
            jQuery.get("'.\yii\helpers\Url::toRoute(['aliases']).'", {
                query: request.term
            }, function (data) { response(data); });
        }'); ?>
        <?php echo $form->field($model, 'source')->widget(\yii\jui\AutoComplete::className(), [
            'model' => $model,
            'attribute' => 'source',
            'options' => [ 
                'class' => 'form-control'
            ],
            'clientOptions' => [
                'source' => $request,
                'minLength' => 3,
            ],
        ]); ?>
        <?php echo $form->field($model, 'destination')->widget(\yii\jui\AutoComplete::className(), [
            'model' => $model,
            'attribute' => 'destination',
            'options' => [ 
                'class' => 'form-control'
            ],
            'clientOptions' => [
                'source' => $request,
                'minLength' => 3,
            ],
        ]); ?>        
        <div class="form-group">
            <?php echo Html::submitButton(Yii::t('modules/module', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>