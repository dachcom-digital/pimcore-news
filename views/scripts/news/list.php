<div class="news-list">

    <?php if ($this->paginator) { ?>

        <?php if ($this->category) { ?>
            <h2><?= $this->category->getName(); ?></h2>
        <?php } ?>

        <div>

            <?php foreach($this->paginator as $news) { ?>

                <?php

                $href = $this->url([
                    'lang'    => $this->language,
                    'name'    => \Pimcore\File::getValidFilename($news->getName()),
                    'news'    => $news->getId()
                ], 'news_detail');

                ?>

                <div class="row item">
                    <div class="image col-xs-12 col-md-5">
                        <?php if ($news->getImage() instanceof \Pimcore\Model\Asset\Image) { ?>

                            <?php if ($href) { ?>
                                <a href="<?= $href ?>">
                                    <?= $news->getImage()->getThumbnail('newsList')->getHtml(['class' => 'img-responsive']); ?>
                                </a>
                            <?php } else { ?>
                                <?= $news->getImage()->getThumbnail('newsList')->getHtml(['class' => 'img-responsive']); ?>
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

        <div class="paginator">
            <?=$this->paginationControl($this->paginator, 'Sliding', 'news/helper/paging.php', array( 'appendQueryString' => true)); ?>
        </div>

    <?php } else { ?>

        <?= $this->translate('No News found')?>

    <?php } ?>
</div>