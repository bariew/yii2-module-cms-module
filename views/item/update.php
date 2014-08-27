<?php

use yii\helpers\Html;
use Yii;

/**
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\Item $model
 */

$this->title = Yii::t('modules/module', 'Update {title}', ['title' => $model->title]);
$this->params['breadcrumbs'][] = ['label' =>Yii::t('modules/module', 'Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="module-update">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
