<?php

use yii\helpers\Html;
use Yii;

/**
 * @var yii\web\View $this
 * @var bariew\moduleModule\models\Item $model
 */

$this->title =  Yii::t('modules/module', 'Create Item');
$this->params['breadcrumbs'][] = ['label' => 'Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-create">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
