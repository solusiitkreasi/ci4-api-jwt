
<!DOCTYPE html>
<html lang="en">
<head>
    
    <title>TOL API | <?= esc($title ?? 'Dashboard') ?></title>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="<?= esc($title ?? 'Dashboard') ?>" />

    <!-- Favicon Tags Start -->
    <link rel="shortcut icon" href="https://place-hold.it/100x100/00362b/fff/fff?text=TOL&fontsize=35&bold" type="image/x-icon" >
    <link rel="icon" type="image/png" href="https://place-hold.it/128x128/00362b/fff/fff?text=TOL&fontsize=35&bold" sizes="128x128" />
    <meta name="application-name" content="TOL API" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <!-- Favicon Tags End -->

    <!-- Font Tags Start -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>font/CS-Interface/style.css" />
    <!-- Font Tags End -->

    <?= $this->include('backend/layouts/partials/_styles') ?>
    
</head>

<body>

    <div id="root">

        <!-- Start Loading -->
        <div class="load-wrapper">
            <div class="loader">
            </div>
        </div>
        <!-- END Loadning -->

        <?= $this->include('backend/layouts/partials/_sidebar') ?>

        <main>
            <div class="container">
                <?= $this->renderSection('content') ?>
            </div>
	    
            <?= $this->include('backend/layouts/partials/_footer') ?>

        </main>

	</div>

    <?= $this->include('backend/layouts/partials/_scripts') ?>

</body>
</html>