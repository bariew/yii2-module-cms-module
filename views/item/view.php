<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use Yii;

/**
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\Item $model
 */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('modules/module', 'Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-view">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <p>
        <?php echo Html::a(Yii::t('modules/module', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php echo Html::a(Yii::t('modules/module', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('modules/module', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'address',
            'title',
            'content',
            'owner_name',
            'owner_event',
            'owner_id',
            'type',
            'status',
            'created_at',
        ],
    ]) ?>

</div>
