<div class="news-list">

    <?php if ($this->paginator) { ?>

        <?php if ($this->category) { ?>
            <h2><?= $this->category->getName(); ?></h2>
        <?php } ?>

        <div>

            <?php foreach ($this->paginator as $news) { ?>

                <?php

                $lang = $this->language;

                $path = str_replace("/$lang/", '', $this->document->getFullPath());

                $href = $this->url([
                    'lang'    => $this->language,
                    'path' => $path,
                    'name' => $news->getName(),
                    'news' => $news->getId()
                ], 'news_detail');
                ?>

                <div class="row item">
                    <div class="image col-xs-12 col-md-5">
                        <?php
                        if ($news->getImage() instanceof \Pimcore\Model\Asset\Image) { ?>
                            <a href="<?= $href ?>">
                                <?= $news->getImage()->getThumbnail("content")->getHtml(['class' => 'img-responsive']); ?>
                            </a>
                        <?php } else { ?>
                            <a href="<?= $href ?>">
                                <div class="image placeholder"></div>
                            </a>
                        <?php } ?>
                    </div>
                    <div class="col-xs-12 col-md-7">
                        <p class="date"><?= $news->getDate()->get('dd.MM.YYYY'); ?></p>

                        <h2><?= $news->getName(); ?></h2>

                        <p><?= $news->getLead(); ?></p>

                        <a href="<?= $href ?>">
                            <span class="more">
                                <?= $this->translate('Read more'); ?>
                            </span>
                        </a>

                    </div>
                </div>

            <?php } ?>

        </div>

        <?php if ($this->itemsPerPage) { ?>
            <div class="paginator">
                <?=$this->paginationControl($this->paginator, 'Sliding', 'news/helper/paging.php', array( 'appendQueryString' => true)); ?>
            </div>
        <?php } ?>

    <?php } else { ?>

        <?= $this->translate('No News found')?>

    <?php } ?>
</div>