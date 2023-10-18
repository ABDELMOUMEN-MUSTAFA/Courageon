<!DOCTYPE html>
<html lang="fr" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" type="image/x-icon" href="<?= IMAGEROOT ?>/favicon.ico" />
        <title>Lessons | <?= SITENAME ?></title>

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

        <!-- Quill Theme -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/plugins/quill.css" />

        <!-- Nestable -->
        <link rel="stylesheet"  href="<?= CSSROOT ?>/plugins/nestable.css">

        <!-- Plyr CSS -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/plugins/plyr.min.css" /> 

        <!-- Custom Style -->
        <link rel="stylesheet" href="<?= CSSROOT ?>/videos/index.css" />
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
                    <div class="mdk-drawer-layout__content page ">
                        <div class="container-fluid page__container">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/formateur">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/courses">Formations</a></li>
                                <li class="breadcrumb-item active">Videos</li>
                            </ol>
                            <div class="media align-items-center mb-headings">
                                <div class="media-body">
                                    <h1 class="h2" id="nom-formation"></h1>
                                </div>
                                <div class="media-right">
                                    <button id="add-course" data-target="#add-lesson" data-toggle="modal" class="btn btn-success">AJOUTER</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="nestable" data-id-formation="<?= $search ?>"
                                         id="nestable-handles-primary">
                                        <ul id="wrapper-videos" class="nestable-list">
                                            <!-- Videos -->
                                        </ul>
                                    </div>
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

        <!-- Add Lesson Modal -->
        <div class="modal fade" id="add-lesson">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title text-white">Add Lesson</h4>
                        <button type="button"
                                class="close text-white"
                                data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- video -->
                        <div class="row mb-3 d-none preview-video">
                            <div class="col">
                                <video style="--plyr-color-main: #662d91;" id="player-1" playsinline controls>
                                      Your browser does not support the video tag.
                                </video>
                            </div>
                          </div>  
                        <!-- endvideo -->
                        <form id="create-lesson-form" method="POST" enctype="multipart/form-data">
                            <div class="form-group row">
                                <label
                                       class="col-form-label form-label col-md-3">Lesson:</label>
                                <div class="col-md-9">
                                    <div class="custom-file">
                                        <input type="file"
                                            name="lesson_video" 
                                            id="add-lesson-video"
                                            accept="video/*" 
                                            class="custom-file-input video-input">
                                        <label for="add-lesson-video"
                                                class="custom-file-label">Choose video</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="v-title-add"
                                       class="col-form-label form-label col-md-3">Titre:</label>
                                <div class="col-md-9">
                                    <input id="v-title-add"
                                           type="text"
                                           name="v_title" 
                                           class="form-control"
                                           placeholder="Entrez titre" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="v-description-add"
                                       class="col-form-label form-label col-md-3">Description:</label>
                                <div class="col-md-9">
                                   <div class="form-group">
                                        <textarea class="form-control" name="v_description" id="v-description-add" rows="3" placeholder="Description"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mb-3">
                                <button id="subtitle-add" class="d-flex align-items-center justify-content-center add-subtitle" type="button"><i class="material-icons">add</i></button>
                            </div>
                            
                            <div class="subtitles-add">
                                <!-- subtitles inputs -->
                            </div>

                            <div class="form-group row mb-0 mt-3">
                                <div class="col-12 d-grid">
                                    <button type="submit"
                                            class="btn btn-success btn-block"
                                            id="create-lesson-btn">Add Video</button>
                                </div>
                            </div>

                            <input type="hidden" name="_token" value="<?= csrf_token() ?>" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add lesson EndModal -->

        <!-- Edit Lesson Modal -->
       <div class="modal fade" id="edit-lesson">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title text-white">Edit Lesson</h4>
                        <button type="button"
                                class="close text-white"
                                data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- video -->
                        <div class="row mb-3 d-none preview-video">
                            <div class="col">
                                <video style="--plyr-color-main: #662d91;" id="player-2" playsinline controls>
                                      Your browser does not support the video tag.
                                </video>
                            </div>
                          </div>  
                        <!-- endvideo -->
                        <form id="edit-lesson-form" method="POST" enctype="multipart/form-data">
                            <div class="form-group row">
                                <label
                                       class="col-form-label form-label col-md-3">Lesson:</label>
                                <div class="col-md-9">
                                    <div class="custom-file">
                                        <input type="file"
                                                name="lesson_video" 
                                                id="edit-lesson-video"
                                                accept="video/*"
                                                class="custom-file-input video-input">
                                        <label for="edit-lesson-video"
                                            class="custom-file-label">Choose video</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="v-title-edit"
                                       class="col-form-label form-label col-md-3">Titre:</label>
                                <div class="col-md-9">
                                    <input id="v-title-edit"
                                           type="text"
                                           name="v_title" 
                                           class="form-control"
                                           placeholder="Entrez titre" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="v-description-edit"
                                       class="col-form-label form-label col-md-3">Description:</label>
                                <div class="col-md-9">
                                   <div class="form-group">
                                        <textarea class="form-control" name="v_description" id="v-description-edit" rows="3" placeholder="Description ..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mb-3">
                                <button id="subtitle-edit" class="d-flex align-items-center justify-content-center add-subtitle" type="button"><i class="material-icons">add</i></button>
                            </div>
                            
                            <div class="subtitles-edit">
                                <!-- subtitles inputs -->
                            </div>
                                                        
                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-3">
                                    <button type="submit"
                                            class="btn btn-success"
                                            id="edit-lesson-btn">Save</button>
                                </div>
                            </div>
                            <!-- input hidden ID Lesson -->
                            <input type="hidden" name="id_video" />
                            <!-- PUT method -->
                            <input type="hidden" name="method" value="PUT" />

                            <input type="hidden" name="_token" value="<?= csrf_token() ?>" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit lesson EndModal -->

        <!-- Popup Show Video -->
        <div class="overlay-content">
            <video style="--plyr-color-main: #662d91;" class="object-fit-cover" id="player-show" playsinline controls src="#">
            </video>
            <button id="closeBtn" class="d-flex align-items-center justify-content-center"><i class="material-icons">cancel</i></button>
        </div>
        <div id="overlay" class="hidden"></div>
        <!-- End Popup -->

        <!-- to-top Button -->
        <span class="to-top-btn"><i class="material-icons">expand_less</i></span>

        <!-- Scripts -->
        <!-- jQuery -->
        <script src="<?= JSROOT ?>/plugins/jquery-3.6.3.min.js"></script>

        <!-- Plyr -->
        <script src="<?= JSROOT ?>/plugins/plyr.min.js"></script>

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

        <!-- Highlight.js -->
        <script src="<?= JSROOT ?>/plugins/hljs.js"></script>

        <!-- Nestable -->
        <script src="<?= JSROOT ?>/plugins/jquery.nestable.js"></script>
        <script src="<?= JSROOT ?>/plugins/nestable.js"></script>

        <!-- Quill -->
        <script src="<?= JSROOT ?>/plugins/quill.min.js"></script>

        <!-- Jquery Validation -->
        <script src="<?= JSROOT ?>/plugins/jquery.validate.min.js"></script>

        <!-- SweetAlert -->
        <script src="<?= JSROOT ?>/plugins/sweetalert.min.js"></script>
        
        <script>
            const URLROOT = `<?= URLROOT ?>`;
            const langues = JSON.parse(`<?= json_encode($langues) ?>`);
            const courseID = `<?= $search ?>`;            
        </script>

        <script src="<?= JSROOT ?>/videos/index.js"></script>
    </body>
</html>