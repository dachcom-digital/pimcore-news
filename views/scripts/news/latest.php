
<?php if ($this->news) { ?>

    <?php   foreach($this->news as $news) { ?>
        <?php $href = $this->url(['lang' => $this->language, 'name' => $news->getName(), 'news' => $news->getId()], 'news_detail'); ?>
        <div class="row">
            <div class="col-xs-12 col-md-3">
                <?php
                if ($news->getImage() instanceof \Pimcore\Model\Asset\Image) { ?>
                    <a href="<?=$href?>">
                        <?php echo $news->getImage()->getThumbnail('content'); ?>
                    </a>

                <?php } ?>
            </div>
            <div class="col-xs-12 col-md-9">
                <h3><a href="<?=$href?>"><?= $news->getName(); ?></a></h3>
                <span class="lead"><?= $news->getLead(); ?></span>
            </div>
        </div>
    <?php   }
}
else {
    echo $this->translate('No News found');
}
?>