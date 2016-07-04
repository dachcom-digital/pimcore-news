<?php
$settings = \News\Model\Configuration::get('news_latest_settings');
?>
<div class="toolbox-edit-overlay">

    <div class="t-row">
        <label><?= $this->translateAdmin('Show only top news') ?></label>
        <?= $this->checkbox('latest'); ?>
    </div>

    <div class="t-row">
        <label><?= $this->translateAdmin('Show Pagination') ?></label>
        <?= $this->checkbox('showPagination'); ?>
    </div>

    <div class="t-row">
        <label><?= $this->translateAdmin('Max records displayed') ?></label>
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

    <div class="t-row">
        <label><?= $this->translateAdmin('Category') ?></label>
        <?= $this->href('category', [
            'types'   => ['object'],
            'classes' => ['NewsCategory'],
        ]); ?>
    </div>

</div>
