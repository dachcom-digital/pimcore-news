<?php if ($this->news) { ?>

    <div class="news-detail">

        <h1><?= $this->news->getName(); ?></h1>

        <?php if (count($this->news->getImages()) > 0) { ?>

            <?php if (count($this->news->getImages()) > 1) { ?>
                <div class="media-gallery light-gallery">
                    <?php $i = 0;
                    foreach ($this->news->getImages() as $image) { ?>
                        <?php if ($image instanceof \Pimcore\Model\Asset\Image) { ?>
                            <a href="<?= $image->getFullPath(); ?>" class="item<?= (++$i > 1) ? ' hidden' : ''; ?>" data-src="<?= $image->getThumbnail('contentImage'); ?>">
                                <?php echo $image->getThumbnail('galleryImage')->getHtml(['class' => 'img-responsive']); ?>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="media-gallery">
                    <?php foreach ($this->news->getImages() as $image) { ?>
                        <?php if ($image instanceof \Pimcore\Model\Asset\Image) { ?>
                            <a href="<?= $image->getFullPath(); ?>" class="item" data-src="<?= $image->getThumbnail('contentImage'); ?>">
                                <?php echo $image->getThumbnail('galleryImage')->getHtml(['class' => 'img-responsive']); ?>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>

        <?php } ?>

        <p class="date">
            <?= $this->news->getDate()->format('d.m.Y'); ?>
        </p>

        <?php if ($this->news->getLead()) { ?>
            <div class="lead">
                <?= $this->news->getLead(); ?>
            </div>
        <?php } ?>

        <?php if ($this->news->getDescription()) { ?>
            <div class="description">
                <?= $this->news->getDescription(); ?>
            </div>
        <?php } ?>

        <?php if ($this->news->getVideo()) { ?>

            <div class="video row">
                <div class="col-xs-12 col-sm-6">

                    <div class="video-item responsive">

                        <?php
                        $video = new \Pimcore\Model\Document\Tag\Video();
                        $video->setOptions([
                            'thumbnail'  => 'content',
                            'width'      => '100%',
                            'height'     => 'auto',
                            'attributes' => [
                                'class'    => 'video-js',
                                'preload'  => 'auto',
                                'controls' => ''
                            ]
                        ]);

                        $video->type = $this->news->getVideo()->getType();
                        $video->id = $this->news->getVideo()->getData();
                        $video->title = $this->news->getVideo()->getTitle();

                        if ($this->news->getVideo()->getPoster() instanceof \Pimcore\Model\Asset\Image) {

                            $video->poster = $this->news->getVideo()->getPoster()->getId();
                        }
                        echo $video->frontend();

                        ?>

                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <h2><?= $this->news->getVideo()->getTitle(); ?></h2>

                    <p class="descripton">
                        <?= $this->news->getVideo()->getDescription(); ?>
                    </p>
                </div>
            </div>

        <?php } ?>

        <?php if (count($this->news->getDownloads()) > 0) { ?>

            <div class="download-list">
                <h2><?= $this->translate('Downloads'); ?></h2>
                <ul class="list-unstyled">
                    <?php foreach ($this->news->getDownloads() as $download) { ?>
                        <?php if ($download instanceof \Pimcore\Model\Asset) {

                            $dPath = $download->getFullPath();
                            $dSize = $download->getFileSize('kb', 2);
                            $dType = Pimcore\File::getFileExtension($download->getFilename());
                            $dName = ($download->getMetadata('name')) ? $download->getMetadata('name') : 'Download';

                            ?>

                            <li class="item">
                                <a href="<?= $dPath; ?>" target="_blank" class="icon-download-<?= $dType; ?>">
                                    <?= $dName; ?><span class="size"><?= $dType; ?> (<?= $dSize; ?>)</span>
                                </a>
                            </li>

                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>

        <?php } ?>

        <?php if ($this->news->getLinks()) { ?>

            <h4><?= $this->translate('Links'); ?></h4>

            <div class="link-list">

                <ul class="list-unstyled">

                    <?php foreach ($this->news->getLinks() as $link) { ?>
                        <li class="item"><?= $link['url']->getData(); ?></li>
                    <?php } ?>

                </ul>

            </div>

        <?php } ?>

        <?php
        $backLink = $this->newsHelper()->getBackUrl($this->news);
        ?>

        <?php if (!empty($backLink)) { ?>
            <a href="<?= $backLink ?>" class="back"><?= $this->translate('Back to List'); ?></a>
        <?php } ?>

    </div>

<?php } ?>
