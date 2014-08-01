<?php

use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var bariew\moduleModule\models\ItemSearch $searchModel
 */

$this->title = Yii::t('modules/module', 'CMS Modules');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-index">

    <h1><?php echo Html::encode($this->title) ?></h1>


    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'name',
            'downloads',
            'owner_name',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
