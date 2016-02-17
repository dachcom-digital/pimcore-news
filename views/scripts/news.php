<!doctype html>
<html lang="<?= $this->language; ?>" class="<?=$this->editmode?'edit-m':''?>">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php

    if ($this->news->getMetaTitle()) {
        $this->headTitle()->set($this->news->getMetaTitle());
    }

    if ($this->news->getMetaDescription()) {
        $this->headMeta()->appendName('description', $this->news->getMetaDescription());
    }

    if ($this->news->getMetaKeywords()) {
        $this->headMeta()->appendName('keywords', $this->news->getMetaKeywords());
    }

    ?>

    <?= $this->headTitle();?>
    <?= $this->headMeta(); ?>

    <?= $this->headLink(); ?>
    <?= $this->headScript(); ?>

    <?php if( $this->editmode ) { ?>

        <script>var _PIMCORE_EDITMODE = true;</script>

    <?php } else { ?>

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    <?php } ?>
</head>

<body class="lang-<?=$this->language?>">

<?= $this->layout()->content; ?>

</body>
</html>