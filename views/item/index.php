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
    <?php $form = ActiveForm::begin(['action' => ['install']]); ?>
    <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'name',
                'version',
                [
                    'class' => \yii\grid\CheckboxColumn::className(),
                    'name'  => 'uninstall',
                    'header' => Yii::t('modules/module', 'Uninstall')
                ],
                [
                    'class' => \yii\grid\CheckboxColumn::className(),
                    'name'  => 'update',
                    'header' => Yii::t('modules/module', 'Update')
                ],
                [
                    'class' => \yii\grid\ActionColumn::className(),
                    'template'  => '{params}',
                    'buttons'   => [
                        'params'  => function ($url, $data) {
                            /**
                             * @var Item $data
                             */
                            return $data->hasLocalParams()
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
