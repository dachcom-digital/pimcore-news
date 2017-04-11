<?php if ($this->pageCount > 1) { ?>
    <div class="row">
        <div class="col-xs-12 pagination-block">
            <ul class="pagination">
                <?php if (isset($this->previous)) { ?>
                    <li><a href="<?= $this->url(['page' => $this->first]); ?>">«</a></li>
                <?php } ?>
                <?php foreach ($this->pagesInRange as $page) { ?>
                    <li class="<?= $page == $this->current ? 'active' : '' ?>"><a href="<?= $this->url(['page' => $page]); ?>"><?= $page ?></a></li>
                <?php } ?>

                <?php if (isset($this->next)) { ?>
                    <li><a href="<?= $this->url(['page' => $this->next]); ?>">»</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
<?php } ?>