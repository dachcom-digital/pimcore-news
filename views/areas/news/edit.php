<?php $this->editSettings['editSettings'] = \News\Model\Configuration::get('news_list_settings'); ?>
<div class="toolbox-edit-overlay">

    <div class="t-row">

        <div class="t-col-half">
            <label class="clearfix"><?= $this->translateAdmin('top news') ?></label><br>
            <?= $this->checkbox('latest'); ?> <?= $this->translateAdmin('show only top news') ?>
        </div>

        <div class="t-col-half">
            <label><?= $this->translateAdmin('layout') ?></label>
            <?php if ($this->editmode && $this->select('layout')->isEmpty()) {
                $this->select('layout')->setDataFromResource($this->editSettings['listSettings']['layouts']['default']);
            } ?>

            <?= $this->select('layout', ['store' => $this->editSettings['listSettings']['layouts']['items']]); ?>
        </div>

    </div>

    <div class="t-row">

        <div class="t-col-half">
            <label class="clearfix"><?= $this->translateAdmin('pagination') ?></label><br>
            <?= $this->checkbox('showPagination'); ?> <?= $this->translateAdmin('show pagination') ?>
        </div>
        <div class="t-col-half">
            <label><?= $this->translateAdmin('items per page') ?></label>
            <?php if ($this->editmode && $this->numeric('itemsPerPage')->isEmpty()) {
                $this->numeric('itemsPerPage')->setDataFromResource($this->editSettings['listSettings']['paginate']['itemsPerPage']);
            } ?>

            <?= $this->numeric('itemsPerPage', [
                'decimalPrecision' => 0,
                'minValue'         => 0
            ]); ?>
        </div>

    </div>

    <div class="t-row">

        <label><?= $this->translateAdmin('max records displayed') ?></label>
        <?php if ($this->editmode && $this->numeric('limit')->getData() == NULL || $this->numeric('limit')->getData() < 0) {
            $this->numeric('limit')->setDataFromResource($this->editSettings['listSettings']['maxItems']);
        } ?>

        <?= $this->numeric('limit', [
            'decimalPrecision' => 0,
            'minValue'         => 0
        ]); ?>

    </div>

    <div class="t-row">

        <div class="t-col-half">
            <label><?= $this->translateAdmin('sort by') ?></label>
            <?php if ($this->editmode && $this->select('sortby')->isEmpty()) {
                $this->select('sortby')->setDataFromResource($this->editSettings['listSettings']['sortby']);
            } ?>

            <?= $this->select('sortby', [
                'store' => [
                    ['date', $this->translateAdmin('date') ],
                    ['name', $this->translateAdmin('name') ]
                ]
            ]); ?>
        </div>

        <div class="t-col-half">
            <label><?= $this->translateAdmin('sort direction') ?></label>
            <?php if ($this->editmode && $this->select('orderby')->isEmpty()) {
                $this->select('orderby')->setDataFromResource($this->editSettings['listSettings']['orderby']);
            } ?>

            <?= $this->select('orderby', [
                'store' => [
                    ['desc', $this->translateAdmin('descending') ],
                    ['asc', $this->translateAdmin('ascending') ]
                ]
            ]); ?>
        </div>

    </div>

    <div class="t-row">

        <div class="t-col-half">
            <label><?= $this->translateAdmin('category') ?></label>
            <?= $this->href('category', [
                'types'   => ['object'],
                'subtypes' => [
                    'object' => ['object']
                ],
                'classes' => ['NewsCategory'],
                'width'   => '95%'
            ]); ?>
        </div>

        <div class="t-col-half">
            <label class="clearfix"><?= $this->translateAdmin('subcategories') ?></label><br>
            <?= $this->checkbox('includeSubCategories'); ?> <?= $this->translateAdmin('include subcategories') ?>
        </div>

    </div>

    <div class="t-row">

        <div class="t-col-half">

            <label><?= $this->translateAdmin('entry type') ?></label>
            <?php if ($this->editmode && $this->select('entryType')->isEmpty()) {
                $this->select('entryType')->setDataFromResource($this->editSettings['entryTypes']['default']);
            } ?>

            <?= $this->select('entryType', ['store' => $this->editSettings['entryTypes']['store']]); ?>

        </div>

        <div class="t-col-half">
            <span class="description">
                <strong><?= $this->translateAdmin('entry type') ?>:</strong> <?= $this->translateAdmin('You can limit your output to a specific entry type. please note that all entries linked in the assigned category(s) also gets applied to the entry type filter.'); ?>
            </span>

        </div>

    </div>

</div>
