<!doctype html>
<html lang="pl">
    <?php
    render('layout/head');
    ?>

    <body>
    <?php
    render('layout/menu');
    ?>
    <div id="content">
        <?php
        Controller::instance();
        ?>
    </div>

    <?php
    render('layout/footer');
    ?>
    <script src="<?= asset('lib/jquery.js') ?>"></script>
    <script src="<?= asset('lib/bootstrap/js/bootstrap.js') ?>"></script>
    <script src="<?= asset('lib/bootstrap/js/bootstrap.bundle.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    </body>
</html>
