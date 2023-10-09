<!DOCTYPE html>
<html lang="fr" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" type="image/x-icon" href="<?= IMAGEROOT ?>/favicon.ico" />
        <title>Private Course | <?= SITENAME ?></title>

        <!-- Custom Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Oswald:400,500,700%7CRoboto:400,500%7CRoboto:400,500&display=swap" />

        <!-- Perfect Scrollbar -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/plugins/perfect-scrollbar.css" />

        <!-- Material Design Icons -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/icons/material-icons.css" />

        <!-- Preloader -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/plugins/spinkit.css" />

        <!-- App CSS -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/plugins/app.css" />

        <!-- Nestable CSS -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/plugins/nestable.css" />
    </head>

    <body class=" layout-fluid">

        <div class="preloader">
            <div class="sk-chase">
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
            </div>

            <!-- <div class="sk-bounce">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div> -->

            <!-- More spinner examples at https://github.com/tobiasahlin/SpinKit/blob/master/examples.html -->
        </div>

        <!-- Header Layout -->
        <div class="mdk-header-layout js-mdk-header-layout">
            <!-- require navbar header -->
            <?php require_once APPROOT . "/views/includes/formateur/navbar.php" ?>

            <!-- Header Layout Content -->
            <div class="mdk-header-layout__content">
                <div data-push
                     data-responsive-width="992px"
                     class="mdk-drawer-layout js-mdk-drawer-layout">
                    <div class="mdk-drawer-layout__content page">
                    <div class="container-fluid page__container">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/formateur">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/courses">Courses</a></li>
                                <li class="breadcrumb-item active">Private Courses</li>
                            </ol>
                            <div class="media align-items-center mb-headings">
                                <div class="media-body">
                                    <h1 class="h2">Manage Private Courses</h1>
                                </div>
                                <a href="<?= URLROOT ?>/courses/add" class="btn btn-success">Add course</a>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="nestable" id="nestable-handles-primary">
                                        <ul class="nestable-list">
                                            <?php foreach($privateCourses as $course): ?>
                                            <li class="nestable-item nestable-item-handle">
                                                <div style="border-width: 2px;" class="nestable-content mx-0 <?= $course->can_join ? 'border-success' : 'border-danger' ?>">
                                                    <div class="media align-items-center">
                                                        <div class="media-left">
                                                            <img src="<?= IMAGEROOT ?>/<?= $course->image ?>"
                                                                    alt="thumbnail formation"
                                                                    width="100"
                                                                    class="rounded">
                                                        </div>
                                                        <div class="media-body">
                                                            <h5 class="card-title h6 mb-0">
                                                                <a href="<?= URLROOT ?>/courses/edit/<?= $course->id_formation ?>"><?= $course->nom ?></a>
                                                            </h5>
                                                            <small class="text-muted"><?= $course->mass_horaire ?></small>
                                                        </div>
                                                        <div class="media-right">
                                                            <button <?= $course->can_join ? 'disabled' : '' ?> data-id-formation="<?= $course->id_formation ?>" class="btn btn-success btn-sm toggle-join" data-toggle="tooltip" data-placement="top" title="Student can join this course by Code"><i class="material-icons">check_circle</i></button>
                                                            <button <?= !$course->can_join ? 'disabled' : '' ?> data-id-formation="<?= $course->id_formation ?>" class="btn btn-danger btn-sm toggle-join" data-toggle="tooltip" data-placement="top" title="Student can't join this course by Code"><i class="material-icons">cancel</i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- require sidebar -->
                    <?php require_once APPROOT . "/views/includes/formateur/sideNavbar.php" ?>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <!-- jQuery -->
        <script src="<?= JSROOT ?>/plugins/jquery-3.6.3.min.js"></script>

        <!-- Bootstrap -->
        <script src="<?= JSROOT ?>/plugins/popper.min.js"></script>

        <!-- Perfect Scrollbar -->
        <script src="<?= JSROOT ?>/plugins/perfect-scrollbar.min.js"></script>

        <!-- MDK -->
        <script src="<?= JSROOT ?>/plugins/dom-factory.js"></script>
        <script src="<?= JSROOT ?>/plugins/material-design-kit.js"></script>

        <!-- Bootstrap -->
        <script src="<?= JSROOT ?>/plugins/bootstrap-4.min.js"></script>

        <!-- App JS -->
        <script src="<?= JSROOT ?>/plugins/app.js"></script>

        <script>
            const URLROOT = `<?= URLROOT ?>`;
            $('.toggle-join').click(function(event){
                const currentButton = $(this);

                $.ajax({
                    url: `${URLROOT}/courses/toggleCanJoin/${currentButton.data('idFormation')}`,
                    type: 'PUT',
                    success: function(){
                        currentButton.prop('disabled', true);
                        currentButton.siblings().prop('disabled', false);
                    },
                    error: function({responseJSON: {messages}}){
                        alert(messages);
                    }
                });


            })
        </script>
    </body>
</html>