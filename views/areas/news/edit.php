
<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Top news') ?></label>
        <?= $this->checkbox('latest'); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-6">
        <label><?= $this->translate('Max records displayed') ?></label><br/>
        <?php
        if ($this->editmode) {
            if ($this->numeric('limit')->isEmpty()) {
                $this->numeric('limit')->setDataFromResource('0');
            }
        }
        echo $this->numeric('limit', [
            'decimalPrecision' => 0,
            'minValue'         => 0
        ]); ?>
    </div>
    <div class="col-xs-6">
        <label><?= $this->translate('What to display') ?></label><br/>
        <?php
        if ($this->editmode) {
            if ($this->select('view')->isEmpty()) {
                $this->select('view')->setDataFromResource('list');
            }
        }
        echo $this->select('view', [
            'store'  => [
                [
                    'list',
                    $this->translate('List')
                ],
                [
                    'latest',
                    $this->translate('Latest')
                ]
            ],
            'reload' => true
        ]); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Category') ?></label><br/>
        <?php
        echo $this->href('category', [
            'types' => ['object'],
            'classes' => ['NewsCategory'],
        ]); ?>
    </div>
</div>


