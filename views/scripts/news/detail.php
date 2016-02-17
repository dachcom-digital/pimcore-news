<?php $href = $this->url(['lang' => $this->language, 'name' => $this->news->getName(), 'news' => $this->news->getId()], 'news_detail'); ?>

<div class="news-detail">
    <h1><?= $this->news->getName(); ?></h1>

<?php if (count($this->news->getImages()) > 0) { ?>
    <div class="media-gallery">
    <?php foreach ($this->news->getImages() as $image) { ?>
        <?php if ($image instanceof \Pimcore\Model\Asset\Image) { ?>
        <div class="image">
            <?php echo $image->getThumbnail('content'); ?>
        </div>
        <?php } ?>
    <?php } ?>
    </div>
<?php } ?>

    <div class="date">
        <?= $this->news->getDate()->get('dd.MM.YYYY'); ?>
    </div>

    <div class="lead">
        <?= $this->news->getLead(); ?>
    </div>

<?php if (count($this->news->getDescription()) > 0) { ?>
    <div class="description">
        <?= $this->news->getDescription(); ?>
    </div>
<?php } ?>

<?php if ($this->news->getVideo()) { ?>
    <div class="video row">
        <div class="col-xs-12 col-sm-3">
            <?php
            $video = new \Pimcore\Model\Document\Tag\Video();
            $video->setOptions([
                "thumbnail" => "content", // specify your thumbnail here - IMPORTANT!
                "width" => "100%",
                "attributes" => ["class" => "video-js", "preload" => "auto", "controls" => "", "data-custom-attr" => "my-test"]
            ]);

            $video->type = $this->news->getVideo()->getType();
            $video->id = $this->news->getVideo()->getData();
            $video->title = $this->news->getVideo()->getTitle();

            if ($this->news->getVideo()->getPoster()) {
                $video->poster = $this->news->getVideo()->getPoster()->getId();
            }
            echo $video->frontend();

            ?>
        </div>
        <div class="col-xs-12 col-sm-9">
            <h3><?= $this->news->getVideo()->getTitle();?></h3>
            <span class="descripton">
                <?= $this->news->getVideo()->getDescription();?>
            </span>
        </div>
    </div>
<?php } ?>

<?php if (count($this->news->getDownloads()) > 0) { ?>
    <div class="download-list">
        <?php foreach ($this->news->getDownloads() as $download) { ?>
            <?php if ($download instanceof \Pimcore\Model\Asset\Document) {

                $dPath = $download->getFullPath();
                $dSize = $download->getFileSize('MB', 2);
                $dType = Pimcore\File::getFileExtension( $download->getFilename() );

                if ($download->getMetadata('name')) {
                    $dName = $download->getMetadata('name');
                }
                else {
                    $dName = 'Download';
                }
                ?>

                <a href="<?=$dPath;?>" target="_blank">
                    <?=$dName;?> <?=$dSize;?> <?=$dType;?>
                </a>


            <?php } ?>
        <?php } ?>
    </div>
<?php } ?>

</div>