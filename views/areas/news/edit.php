<?php
$settings = \News\Model\Configuration::get('news_list_settings');
?>
<div class="toolbox-edit-overlay">

    <div class="t-row">

        <div class="t-col-half">
            <label><?= $this->translateAdmin('show only top news') ?></label>
            <?= $this->checkbox('latest'); ?>
        </div>

        <div class="t-col-half">
            <label><?= $this->translateAdmin('layout') ?></label>
            <?php
            if ($this->editmode) {
                if ($this->select('layout')->isEmpty()) {
                    $this->select('layout')->setDataFromResource($settings['layouts']['default']);
                }
            }
            echo $this->select("layout", [
                "store" => $settings['layouts']['items']
            ]); ?>

        </div>


    </div>

    <div class="t-row">

        <div class="t-col-half">
            <label><?= $this->translateAdmin('show pagination') ?></label>
            <?= $this->checkbox('showPagination'); ?>
        </div>
        <div class="t-col-half">
            <label><?= $this->translateAdmin('items per page') ?></label>
            <?php
            if ($this->editmode) {
                if ($this->numeric('itemsPerPage')->isEmpty()) {
                    $this->numeric('itemsPerPage')->setDataFromResource($settings['paginate']['itemsPerPage']);
                }
            }
            echo $this->numeric('itemsPerPage', [
                'decimalPrecision' => 0,
                'minValue'         => 0
            ]); ?>
        </div>

    </div>

    <div class="t-row">

        <label><?= $this->translateAdmin('max records displayed') ?></label>
        <?php
        if ($this->editmode) {
            if ($this->numeric('limit')->getData() == NULL || $this->numeric('limit')->getData() < 0) {
                $this->numeric('limit')->setDataFromResource($settings['maxItems']);
            }
        }
        echo $this->numeric('limit', [
            'decimalPrecision' => 0,
            'minValue'         => 0
        ]); ?>

    </div>

    <div class="t-row">

        <div class="t-col-half">
            <label><?= $this->translateAdmin('sort by') ?></label>
            <?php
            if ($this->editmode) {
                if ($this->select('sortby')->isEmpty()) {
                    $this->select('sortby')->setDataFromResource($settings['sortby']);
                }
            }
            echo $this->select("sortby", [
                "store" => [
                    ['date', $this->translateAdmin('date') ],
                    ['name', $this->translateAdmin('name') ]
                ]
            ]); ?>
        </div>

        <div class="t-col-half">
            <label><?= $this->translateAdmin('sort direction') ?></label>
            <?php
            if ($this->editmode) {
                if ($this->select('orderby')->isEmpty()) {
                    $this->select('orderby')->setDataFromResource($settings['orderby']);
                }
            }
            echo $this->select("orderby", [
                "store" => [
                    ['desc', $this->translateAdmin('descending') ],
                    ['asc', $this->translateAdmin('ascending') ]
                ]
            ]); ?>
        </div>

    </div>

    <div class="t-row">
        <label><?= $this->translateAdmin('category') ?></label>
        <?= $this->href('category', [
            'types'   => ['object'],
            'subtypes' => [
                'object' => ['object']
            ],
            'classes' => ['NewsCategory'],
            'width'   => '100%'
        ]); ?>

    </div>

</div>
