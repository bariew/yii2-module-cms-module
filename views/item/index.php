<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \yii\widgets\ActiveForm;
use bariew\moduleModule\models\Item;
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
                    'attribute' => 'moduleName',
                    'format' => 'raw',
                    'value' => function ($data) {
                        $form = ActiveForm::begin();
                        return $form->field($data, 'moduleName')
                            ->label('')->textInput(['name' => "Item[{$data->id}][moduleName]"])
                        ;
                    }
                ],
                [
                    'attribute' => 'installed',
                    'format' => 'raw',
                    'value' => function ($data) {
                        /**
                         * @var Item $data
                         */
                        $attributes = ['class' => 'form-control'];
                        if ($data->class == \bariew\moduleModule\Module::className()) {
                            $attributes['onclick'] = 'if (!confirm("'. Yii::t('modules/module', "This module is critically important") .'")){return false;} ';
                        }
                        return Html::checkbox("Item[{$data->id}][installed]", $data->installed, $attributes);
                    },
                ],
            ],
        ]); ?>
    <?= Html::submitButton(Yii::t('modules/module', "Save"), ['class' => 'btn btn-primary']); ?>
    <?php ActiveForm::end(); ?>
</div>
