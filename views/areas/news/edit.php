<?php
$settings = \News\Model\Configuration::get('news_latest_settings');
?>

<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Top news') ?></label>
        <?= $this->checkbox('latest'); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Max records displayed') ?></label><br/>
        <?php
        if ($this->editmode) {
            if ($this->numeric('limit')->isEmpty()) {
                $this->numeric('limit')->setDataFromResource($settings['maxItems']);
            }
        }
        echo $this->numeric('limit', [
            'decimalPrecision' => 0,
            'minValue'         => 0
        ]); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Category') ?></label><br/>
        <?php
        echo $this->href('category', [
            'types'   => ['object'],
            'classes' => ['NewsCategory'],
        ]); ?>
    </div>
</div>
