<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \yii\widgets\ActiveForm;
use bariew\moduleModule\models\Item;
use bariew\moduleModule\models\Composer;
/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('modules/module', 'Installed Modules');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-index">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <?php $form = ActiveForm::begin(); ?>
    <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'name',
                'description',
                [
                    'label' => 'action',
                    'format' => 'raw',
                    'value' => function ($data) {
                        /**
                         * @var Composer $data
                         */
                        $attributes = ['class' => 'form-control'];
                        if ($data->class == \bariew\moduleModule\Module::className()) {
                            $attributes['onclick'] = 'if (!confirm("'. Yii::t('modules/module', "This module is critically important") .'")){return false;} ';
                        }
                        return Html::radioList("install[{$data->name}]", $data->getDefaultAction(), $data::actionList(), $attributes);
                    },
                ],
            ],
        ]); ?>
    <?= Html::submitButton(Yii::t('modules/module', "Save"), ['class' => 'btn btn-primary pull-right']); ?>
    <?php ActiveForm::end(); ?>
</div>
