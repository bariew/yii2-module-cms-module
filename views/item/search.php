<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \yii\widgets\ActiveForm;
/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('modules/module', 'Search Modules');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-index">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <?php $form = ActiveForm::begin(['action' => ['install']]); ?>
    <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                'name',
                [
                    'attribute' => 'description',
                    'filter'    => false,
                ],
                [
                    'attribute' => 'downloads',
                    'filter'    => false,
                ],
                [
                    'label' => Yii::t('modules/module', 'Installed'),
                    'format'=> 'raw',
                    'value' => function ($data) {
                        return Html::activeCheckbox($data, 'isInstalled', ['name'  => "isInstalled[{$data->name}]"]);
                    }
                ],
            ],
        ]); ?>
    <?= Html::submitButton(Yii::t('modules/module', "Save"), ['class' => 'btn btn-primary']); ?>

    <?php ActiveForm::end(); ?>
</div>
