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
                    'class' => \yii\grid\CheckboxColumn::className(),
                    'name'  => 'install',
                    'header' => Yii::t('modules/module', 'Install'),
                    'checkboxOptions'   => function ($model, $key, $index, $column) {
                        $options = ["value" => $model["name"]];
                        return $model->isInstalled
                            ? array_merge($options, ['disabled' => true, 'checked' => 'checked'])
                            : $options;
                    }
                ],
            ],
        ]); ?>
    <?= Html::submitButton(Yii::t('modules/module', "Save"), ['class' => 'btn btn-primary']); ?>

    <?php ActiveForm::end(); ?>
</div>
