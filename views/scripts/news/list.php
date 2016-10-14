<div class="news-list area">

    <?php if ($this->paginator) { ?>

        <div class="news-list-content">

            <?php foreach ($this->paginator as $news) { ?>

                <?php

                $href = $this->url([
                    'lang' => $this->language,
                    'name' => \Pimcore\File::getValidFilename($news->getName()),
                    'news' => $news->getId()
                ], 'news_detail', TRUE);

                ?>

                <div class="row item">

                    <div class="col-xs-12">

                        <div class="news-item">

                            <div class="row">

                                <div class="image col-xs-12 col-md-5">
                                    <?php if ($news->getImage() instanceof \Pimcore\Model\Asset\Image) { ?>
                                        <a href="<?= $href ?>">
                                            <?= $news->getImage()->getThumbnail('contentImage')->getHtml(['class' => 'img-responsive']); ?>
                                        </a>
                                    <?php } ?>
                                </div>

                                <div class="col-xs-12 col-md-7">
                                    <p class="date"><?= $news->getDate()->format('d.m.Y'); ?></p>

                                    <h2><?= $news->getName(); ?></h2>

                                    <p><?= $news->getLead(); ?></p>

                                    <a href="<?= $href ?>" class="col-xs-12">
                                        <span class="more">
                                            <?= $this->translate('Read more'); ?>
                                        </span>
                                    </a>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

        <?php if ($this->showPagination) { ?>

            <div class="preloader"></div>

            <div class="paginator">
                <?= $this->paginationControl($this->paginator, 'Sliding', 'news/helper/paging.php', array( )); ?>
            </div>

        <?php } ?>

    <?php } else { ?>

        <?= $this->translate('No News found') ?>

    <?php } ?>
</div>