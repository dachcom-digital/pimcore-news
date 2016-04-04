<?php
$news_detail_page = \News\Model\Configuration::get('news_detail_page');
$detailPage = \Pimcore\Model\Document::getById($news_detail_page[$this->language]);
?>

<div class="news-list">

    <?php if ($this->paginator) { ?>

        <?php if ($this->category) { ?>
            <h2><?= $this->category->getName(); ?></h2>
        <?php } ?>

        <div>

            <?php foreach ($this->paginator as $news) { ?>

                <?php

                if ($detailPage instanceof \Pimcore\Model\Document ) {

                    $href = $this->url([
                        'document' => $detailPage,
                        'name' => $news->getName(),
                        'news' => $news->getId()
                    ], 'news_detail');

                }
                else {
                    $href = null;
                }
                ?>

                <div class="row item">
                    <div class="image col-xs-12 col-md-5">
                        <?php if ($this->news->getImage() instanceof \Pimcore\Model\Asset\Image) { ?>

                            <?php if ($href) { ?>
                                <a href="<?= $href ?>">
                                    <?= $this->news->getImage()->getThumbnail("newsList")->getHtml(['class' => 'img-responsive']); ?>
                                </a>
                            <?php } else { ?>
                                <?= $this->news->getImage()->getThumbnail("newsList")->getHtml(['class' => 'img-responsive']); ?>
                            <?php } ?>

                        <?php } ?>

                    </div>
                    <div class="col-xs-12 col-md-7">
                        <p class="date"><?= $news->getDate()->get('dd.MM.YYYY'); ?></p>

                        <h2><?= $news->getName(); ?></h2>

                        <p><?= $news->getLead(); ?></p>

                        <?php if ($href) { ?>
                            <a href="<?= $href ?>" class="more">
                                <?= $this->translate('Read more'); ?>
                            </a>
                        <?php } ?>

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