<!DOCTYPE html>
<html lang="fr" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" type="image/x-icon" href="<?= IMAGEROOT ?>/favicon.ico" />
        <title>Promotions | <?= SITENAME ?></title>

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

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                                <li class="breadcrumb-item active">Promotions</li>
                            </ol>
                            <div class="media align-items-center mb-headings">
                                <div class="media-body">
                                    <h1 class="h2">Manage Promotions</h1>
                                </div>
                                <button class="btn btn-success" data-target="#add-promotion" data-toggle="modal">Add promotion</button>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="nestable" id="nestable-handles-primary">
                                        <ul class="nestable-list">
                                            <?php foreach($promotions as $promotion): ?>
                                            <li class="nestable-item nestable-item-handle">
                                                <div style="border-width: 2px;" class="nestable-content mx-0">
                                                    <div class="media align-items-center">
                                                        <div class="media-left">
                                                            <img src="<?= IMAGEROOT ?>/<?= $promotion->imgFormation ?>"
                                                                    alt="thumbnail formation"
                                                                    width="100"
                                                                    class="rounded">
                                                        </div>
                                                        <div class="media-body">
                                                            <h5 class="card-title h6 mb-0">
                                                                <a href="<?= URLROOT ?>/courses/<?= $promotion->id_formation ?>/videos"><?= $promotion->nomFormation ?></a>
                                                            </h5>
                                                            <small class="text-muted">
                                                                Start At: <?= $promotion->date_start ?> <?= isset($promotion->date_end) ? " | End At: {$promotion->date_end}" : '' ?>
                                                            </small>
                                                        </div>
                                                        <div class="media-right text-center">
                                                            <div class="mb-2">
                                                                <strong>$<?= $promotion->prix * $promotion->reduction / 100 ?></strong>
                                                            </div>
                                                            <span class="badge bg-danger p-2 text-white">
                                                                -<?= $promotion->reduction ?>%
                                                            </span>
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

        <!-- Add Promotion Modal -->
        <div class="modal fade" id="add-promotion">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title text-white">Add Promotion</h4>
                        <button type="button"
                                class="close text-white"
                                data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="create-promotion-form" method="POST">
                            <div class="form-group row">
                                <label
                                       class="col-form-label form-label col-md-3">Products:</label>
                                <div class="col-md-9">
                                    <select style="width: 100%" class="js-example-basic-single" name="product">
                                        <option></option>
                                        <option value="AL">Alabama</option>
                                        <option value="WY">Wyoming</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label form-label col-md-3">Discount (%):</label>
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <small class="text-center d-block discount-pourcent">1%</small>
                                        <input type="range" class="form-control-range" min="1" max="100" id="discount" value="1" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label form-label col-md-3">Start Date:</label>
                                <div class="col-md-9">
                                    <input type="date" placeholder="dd-mm-yyyy" min="1997-01-01" max="2030-12-31" class="form-control" name="" id="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label form-label col-md-3">End Date:</label>
                                <div class="col-md-9">
                                    <input type="date" placeholder="dd-mm-yyyy" min="1997-01-01" max="2030-12-31" class="form-control" name="" id="">
                                </div>
                            </div>
                            <div class="form-group row mb-0 mt-3">
                                <div class="col-12 d-grid">
                                    <button type="submit"
                                            class="btn btn-success btn-block"
                                            id="create-lesson-btn">Add Promotion</button>
                                </div>
                            </div>

                            <input type="hidden" name="_token" value="<?= csrf_token() ?>" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add Promotion Modal -->

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
        
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            // In your Javascript (external .js resource or <script> tag)
$(document).ready(function() {
    
    $('.js-example-basic-single').select2({
        placeholder: 'Select a product',
        theme: "classic"
    });

    $('#discount').on('input', function(){
        const value = $(this).val();
        $('.discount-pourcent').text(value + '%');
    });
});
        </script>
    </body>
</html>