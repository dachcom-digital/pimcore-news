<div class="<?= $this->mainClasses; ?>">

    <?php if ($this->paginator) { ?>

        <div class="news-list-content">
            <?= $this->template('news/partial/entries.php'); ?>
        </div>

        <?php if ($this->showPagination) { ?>

            <div class="preloader"></div>
            <div class="paginator">
                <?= $this->paginationControl($this->paginator, 'Sliding', 'news/helper/paging.php', []); ?>
            </div>

        <?php } ?>

    <?php } else { ?>

        <?= $this->translate('No News found') ?>

    <?php } ?>
</div>