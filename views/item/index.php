<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \yii\widgets\ActiveForm;
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var bariew\moduleModule\models\ItemSearch $searchModel
 */

$this->title = 'CMS Modules';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-index">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(['action' => ['install']]); ?>
    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'description',
            'downloads',
            [
                'label' => 'Installed',
                'format'=> 'raw',
                'value' => function ($data) {
                    return Html::activeCheckbox($data, 'isInstalled', [
                        'name'  => "isInstalled[{$data->name}]"
                    ]);
                }
            ]
        ],
    ]); ?>
    <?= Html::submitButton("Save", ['class' => 'btn btn-primary']); ?>
    <?php ActiveForm::end(); ?>
</div>
