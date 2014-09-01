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
                    'label' => Yii::t('modules/module', 'Installed'),
                    'format'=> 'raw',
                    'value' => function ($data) {
                        return Html::activeCheckbox($data, 'isInstalled', [
                            'name'  => "isInstalled[{$data->name}]"
                        ]);
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
                            if (!$data->hasLocalParams()) {
                                return '';
                            }
                            return Html::a('<i class="glyphicon glyphicon-wrench"></i>', $url, ['title' => Yii::t('modules/module', 'Params')]);
                        },
                    ]
                ]
            ],
        ]); ?>
    <?= Html::submitButton(Yii::t('modules/module', "Save"), ['class' => 'btn btn-primary']); ?>
    <?= Html::a(Yii::t('modules/module', "Migrate all"), ["migrate"], ['class' => 'btn btn-success']); ?>

    <?php ActiveForm::end(); ?>
</div>
