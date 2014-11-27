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
                'version',
                [
                    'attribute' => 'moduleName',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::textInput("Item[{$data->id}][moduleName]", $data->moduleName);
                    }
                ],
                [
                    'attribute' => 'installed',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::checkbox("Item[{$data->id}][installed]", $data->installed);
                    }
                ],
                [
                    'class' => \yii\grid\ActionColumn::className(),
                    'template'  => '{params}',
                    'buttons'   => [
                        'params'  => function ($url, $data) {
                            /**
                             * @var Item $data
                             */
                            return $data->params
                                ? Html::a('<i class="glyphicon glyphicon-wrench"></i>', $url, ['title' => Yii::t('modules/module', 'Params')])
                                : '';
                        },
                    ]
                ]
            ],
        ]); ?>
    <?= Html::submitButton(Yii::t('modules/module', "Save"), ['class' => 'btn btn-primary']); ?>
    <?php ActiveForm::end(); ?>
</div>
