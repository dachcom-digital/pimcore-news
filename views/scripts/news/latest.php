<?php if ($this->news) { ?>
    
    <?php
    $cols = (count($this->news) > 1) ? 'col-md-4' : '';

    $news_detail_page = \News\Model\Configuration::get('news_detail_page');
    $detailPage = \Pimcore\Model\Document::getById($news_detail_page[$this->language]);
    ?>

    <div class="news-latest row">
        <?php foreach ($this->news as $news) { ?>

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

            <div class="col-xs-12 <?= $cols ?>">
                <div class="image">
                    <?php if ($news->getImage() instanceof \Pimcore\Model\Asset\Image) { ?>
                        <?php if ($href) { ?>
                            <a href="<?= $href ?>">
                                <?= $news->getImage()->getThumbnail("content")->getHtml(['class' => 'img-responsive']); ?>
                            </a>
                        <?php } else { ?>
                            <?= $news->getImage()->getThumbnail("content")->getHtml(['class' => 'img-responsive']); ?>
                        <?php } ?>

                    <?php } ?>
                </div>

                <h3><?= $news->getName(); ?></h3>

                <p><?= $news->getLead(); ?></p>

                <?php if ($href) { ?>
                    <a href="<?= $href ?>" class="hidden-xs hidden-ty more">
                        <?= $this->translate('Read more'); ?>
                    </a>
                <?php } ?>
            </div>

        <?php } ?>
    </div>
<?php } else { ?>
    <?= $this->translate('No News found'); ?>
<?php } ?>