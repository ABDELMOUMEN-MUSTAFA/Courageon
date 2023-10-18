$(function(){
    // Refresh tooltips after setting a video to preview
    function refreshTooltips(){
        $('.tooltip.show').remove();
        $('[data-toggle="tooltip"]').tooltip('dispose');
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // ================ Player ===========================
    const players = {};

    Array.from(document.querySelectorAll('video')).forEach(video => {
        players[video.id] = new Plyr(video, {captions: {active: true, update: true}});
    });

    // ================= Custom validations =================
    // Add a custom method for validating accepted file types
    $.validator.addMethod('allowedTypes', function(value, element, allowedTypes) {
        const file = element.files[0];
        if (!file) {
            return true;
        }

        return allowedTypes.indexOf(file.type) !== -1;
    }, function(allowedTypes){return 'Please select a valid image file (' + allowedTypes.join(', ') + ').'});

    // Add a custom method for maximum file size validation
    $.validator.addMethod('maxFileSize', function(value, element, maxSizeMB) {
        const file = element.files[0];

        if (!file) {
          return true;
        }

        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        return file.size <= maxSizeBytes;
    }, function (maxSizeMB){ return 'File size exceeds ' + maxSizeMB + 'MB limit.'});

    // Add a custom method for validating time in HH:MM format
    $.validator.addMethod("maxTime", function(value, element, time) {
        if (element.files && element.files.length > 0) {
            const prefix = element.id.replace('-video', '');
            const duration = $(`#${prefix} video`)[0].duration.toFixed(2) ?? 0;
            return duration < 60 * time;
        }

        return true; 
    }, function(time){return `La durée maximale est de ${time} minutes.`});

    // Extension validator
    $.validator.addMethod("extension", function(value, element, param) {
        param = typeof param === "string" ? param.replace(/ /g, "") : "vtt"; // Change this to the allowed subtitle file extension
        return this.optional(element) || new RegExp(".(" + param + ")$", "i").test(value);
      }, "Please upload a file with a valid extension.");

    // ====================== END =======================================

    // ================== input type file ====================
    $('.video-input').change(function (event) {
        const prefix = '#' + $(this).attr('id').replace('-video', '');

        const file = event.target.files[0];
        const fileName = file.name;
        const blobURL = URL.createObjectURL(file);
        $(`${prefix} video`).prop('src', blobURL);

        setTimeout(() => {
            $(`${prefix} label[for="add-lesson-video"]`).html('<div class="loader loader-secondary"></div>');
            if($(this).valid()){
                $(`${prefix} .preview-video`).removeClass('d-none');
                $(`${prefix} label[for="add-lesson-video"]`).text(fileName);
                $(`${prefix} [id*=v-title]`).val(fileName.substring(0, fileName.length - 4)).valid();
            }else{
                $(`${prefix} video`).prop('src', '#');
                $(`${prefix} .preview-video`).addClass('d-none');
                $(`${prefix} label[for="add-lesson-video"]`).text('Choose video');
            }
        }, 300);
    });

    // ==================================== Handle create lesson ===================
    // Create lesson form validation
    const $createLessonForm = $("#create-lesson-form");

    // create lesson Button
    const $createLessonBtn = $('#create-lesson-btn');

    $createLessonForm.validate({
        // debug: true,
        errorElement: "div",
        // onfocusout: false,
        // focusInvalid: false,
        rules: {
            lesson_video: {
                required: true,
                maxFileSize: 1024, // Maximum file size in bytes (1GB)
                allowedTypes: ['video/mp4', 'video/mov', 'video/avi', 'video/x-matroska'],
                maxTime: 50
            },
            v_title: {
                required: true,
                minlength: 3,
                maxlength: 80,
            },
            v_description: {
                required: true,
                minlength: 3,
                maxlength: 800,
            },
        },
        messages: {
            lesson_video: {
                required: "Veuillez sélectionner un lesson vidéo.",
            },
            v_title: {
                required: "Le titre est requis.",
                minlength: "Le titre doit comporter au moins {0} caractères.",
                maxlength: "Le titre ne doit pas dépasser {0} caractères.",
            },
            v_description: {
                required: "La description est requise.",
                minlength: "La description doit comporter au moins {0} caractères.",
                maxlength: "La description ne doit pas dépasser {0} caractères.",
            },
        },
        errorPlacement: function(error, element){
            error.addClass('invalid-feedback').appendTo(element.parent());
        },
        highlight: function(element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function(element) {
            $(element).removeClass("is-invalid");
        },
        submitHandler: function(form){
            const formData = new FormData();
            formData.append('lesson_video', $('#add-lesson-video')[0].files[0]);

            $(form).serializeArray().forEach((field) => {
                formData.append(field.name, field.value);
            });

            $('.subtitle-input').each((i, element) => {
                formData.append('subtitles[]', element.files[0]);
            });

            $('#create-lesson-form :input').prop('disabled', true);
            $createLessonBtn.addClass('is-loading is-loading-sm').prop('disabled', true);
            
            $.ajax({
              url: `${URLROOT}/api/videos`, 
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              success: function({status, data: video, messages}) {
                if(status === 201){
                    AddVideoToUI(video);
                    refreshTooltips();

                    // Hide and clear add Modal
                    const addModal = $("#add-lesson");
                    addModal.modal('toggle');
                    addModal.find('.preview-video').addClass('d-none');
                    addModal.find('#player-1').removeAttr('src').empty();
                    addModal.find('.subtitles-add').empty();
                    addModal.find('#add-lesson-video').val("");
                    addModal.find('label[for="add-lesson-video"]').text("Choose video");
                    addModal.find('#v-title-add').val("");
                    addModal.find('#v-description-add').val("");

                    showMessage('success', messages, 'Success');
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
              },
              error: function(response) {
                console.log(response);
                alert('Failed to submit the form.');
              },
              complete: function(){
                $createLessonBtn
                .removeClass('is-loading is-loading-sm')
                .prop('disabled', false);

                $('#create-lesson-form :input').prop('disabled', false);
              }
            });
        }
    });

    // ==================================== Handle Edit lesson =========================
    // edit lesson form validation
    const $editLessonForm = $('#edit-lesson-form');

    // edit lesson Button
    const $editLessonBtn = $('#edit-lesson-btn');

    $editLessonForm.validate({
        // debug: true,
        errorElement: "div",
        // onfocusout: false,
        // focusInvalid: false,
        rules: {
            lesson_video: {
                required : {
                    depends: function(element) {
                        return $(element).val().trim().length > 0;
                    }
                },
                maxFileSize: 1024, // Maximum file size in bytes (1GB)
                allowedTypes: ['video/mp4', 'video/mov', 'video/avi', 'video/x-matroska'],
                maxTime: 50
            },
            v_title: {
                required: true,
                minlength: 3,
                maxlength: 80,
            },
            v_description: {
                required: true,
                minlength: 3,
                maxlength: 800,
            },
        },
        messages: {
            v_title: {
                required: "Le titre est requis.",
                minlength: "Le titre doit comporter au moins {0} caractères.",
                maxlength: "Le titre ne doit pas dépasser {0} caractères.",
            },
            v_description: {
                required: "La description est requise.",
                minlength: "La description doit comporter au moins {0} caractères.",
                maxlength: "La description ne doit pas dépasser {0} caractères.",
            },
        },
        errorPlacement: function(error, element){
            error.addClass('invalid-feedback').appendTo(element.parent());
        },
        highlight: function(element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function(element) {
            $(element).removeClass("is-invalid");
        },
        submitHandler: function(form){
            const formData = new FormData();
            const lessonVideo = $('#edit-lesson-video')[0].files[0];
            
            if(lessonVideo){
                formData.append('lesson_video', lessonVideo);
            }

            $(form).serializeArray().forEach((field) => formData.append(field.name, field.value));

            $('.subtitle-input').each((i, element) => {
                formData.append('subtitles[]', element.files[0]);
            });
            
            $('#edit-lesson-form :input').prop('disabled', true);
            $editLessonBtn.addClass('is-loading is-loading-sm').prop('disabled', true);

            $.ajax({
              url: `${URLROOT}/api/videos/${$('[name="id_video"]').val()}`, 
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              success: function({messages, data: video}) {
                updateVideoInUI(video);
                // Hide Modal
                $('.close').click();
                showMessage('success', messages, 'Success');
                $('html, body').animate({ scrollTop: 0 }, 'slow');
              },
              error: function(response) {
                console.log(response);
                alert('Failed to submit the form.');
              },
              complete: function(){
                $editLessonBtn
                .removeClass('is-loading is-loading-sm')
                .prop('disabled', false);

                $('#edit-lesson-form :input').prop('disabled', false);
              }
            });
        }
    });

    // show Alert
    function showMessage(bgColor, message, strongMessage){
        $('.mb-headings').after(`
            <div class="alert alert-dismissible bg-${bgColor} text-white border-0 fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>${strongMessage}</strong> ${message}
            </div>
        `);
    }

    // Add video to The UI after create
    function AddVideoToUI(video){
        $('.nestable-list').append(`
            <li class="nestable-item nestable-item-handle"
                data-id="${video.id_video}">
                <div class="nestable-handle"><i class="material-icons">menu</i></div>
                <div class="nestable-content">
                    <div class="media align-items-center">
                        <div class="media-left">
                            <img src="${URLROOT}/public/images/${video.thumbnail}"
                                 alt="thumbnail video"
                                 width="100"
                                 class="rounded">
                        </div>
                        <div class="media-body">
                            <h5 class="card-title h6 mb-0">
                                <a data-id-video="${video.id_video}" data-url="${URLROOT}/public/videos/${video.url}" href="javascript:void(0)" class="video-name">${video.nomVideo}</a>
                            </h5>
                            <small class="text-muted created-at">created ${video.created_at}</small>
                        </div>
                        <div class="media-right d-flex gap-3 align-items-center">
                            <button data-id-formation="${video.id_formation}" data-id-video="${video.id_video}" class="btn btn-white btn-sm set-preview-btn" data-toggle="tooltip" data-placement="top" title="Make it Preview"><i class="material-icons">play_circle_outline</i></button>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle text-muted" data-caret="false" data-toggle="dropdown" aria-expanded="false">
                                    <i class="material-icons">more_vert</i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="javascript:void(0)" data-video='${JSON.stringify(video)}' data-target="#edit-lesson" data-toggle="modal" class="dropdown-item edit-lesson"><i class="material-icons">edit</i> Edit</a>
                                    <div class="dropdown-divider"></div>
                                    <a data-id="${video.id_video}" class="dropdown-item text-danger delete-video" href="javascript:void(0)"><i class="material-icons">delete</i> Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        `);
    }

    // Update UI video after update
    function updateVideoInUI(video){
        const videoItem = $(`.nestable-list li[data-id="${video.id_video}"]`);

        videoItem.find('img').attr('src', `${URLROOT}/public/images/${video.thumbnail}`);
        videoItem.find('.video-name').text(video.nomVideo);
        videoItem.find('.created-at').text(`created ${video.created_at}`);
        videoItem.find('.edit-lesson').data('video', video);
    }

    // Fill in the edit modal after clicking on the video.
    $(document).on('click', '.edit-lesson', function() {
        const video = $(this).data('video');

        console.log(video)
        $('#edit-lesson-form [name="v_title"]').val(video.nomVideo);
        $('#edit-lesson-form [name="v_description"]').val(video.description);
        $('#edit-lesson-form [name="id_video"]').val(video.id_video);
        $('#edit-lesson .preview-video').removeClass('d-none');
        $('#edit-lesson label[for="edit-lesson-video"]').text(video.nomVideo);
        getSubtitlesOfVideo("#edit-lesson video", video.id_video, `${URLROOT}/videos/${video.url}`);
    });

    // Sort Videos
    let initialOrder = $('.nestable').nestable('serialize');
    $('.nestable').change(function(){
        const updatedOrder = $(this).nestable('serialize');
        if(JSON.stringify(initialOrder) !== JSON.stringify(updatedOrder)){
            initialOrder = updatedOrder;
            $.post(`${URLROOT}/courses/sortVideos/${$(this).data('id-formation')}`, { order: updatedOrder }, function(response) {
                console.log('Order successfully:', response);
            }).fail(function(xhr, status, error) {
                console.log('Error sending order:', xhr);
            });
        }
    });

    $(document).on('click', '.set-preview-btn', function(){
        const currentButton = $(this);
        $.post(`${URLROOT}/courses/setVideoToPreview/${currentButton.data('id-formation')}`, 
        { id_video: currentButton.data('id-video') }, 
        function({data}) {
            const previousPreview = $('.preview-badge');
            previousPreview.replaceWith(`<button data-id-formation="${previousPreview.data('id-formation')}" data-id-video="${previousPreview.data('id-video')}" class="btn btn-white btn-sm set-preview-btn" data-toggle="tooltip" data-placement="top" title="Make it Preview"><i class="material-icons">play_circle_outline</i></button>`);     
            currentButton.replaceWith(`<span data-id-formation="${currentButton.data('id-formation')}" data-id-video="${currentButton.data('id-video')}" class="badge badge-pill badge-light preview-badge">Preview</span>`);
            refreshTooltips();
        }).fail(function(xhr, status, error) {
            console.log('Error sending order:', xhr);
        });
    });

    // Handle delete video
    $(document).on('click', '.delete-video', function(){
        const currentVideo = $(this);

        swal({
            title: "Are you sure?",
            text: "You are about to delete this video",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: `${URLROOT}/api/videos/${currentVideo.data('id')}`, 
                    type: 'DELETE',
                    data: {method: 'DELETE'},
                    success: function({messages}) {
                        // Remove from DOM
                        $(`.nestable-item[data-id="${currentVideo.data('id')}"]`).remove();

                        swal(messages, {
                            icon: "success",
                        });
                    },
                    error: function(response) {
                        swal('Failed to delete the video.', {
                            icon: "danger",
                        });
                        console.log(response);
                    },
                });
            }
        });
    });

    //=========== to top button ===========
    const $toTopBtn = $('.to-top-btn');

    $(window).scroll(function(){
        if (document.body.scrollTop > 500 || document.documentElement.scrollTop > 500) {
            $toTopBtn.fadeIn('slow');
        } else {
            $toTopBtn.fadeOut('slow');
        }
    });

    $toTopBtn.click(function() {
        $("html, body").animate({ scrollTop: 0 }, 'slow');
        return false;
    })
    //=========== end top button ===========

    // Open the overlay when the button is clicked
    $(document).on('click', '.video-name', function() {
        const currentVideo = $(this);
        $("#overlay").fadeIn();
        $(".overlay-content").css({ transform: "translate(-50%, -50%)" });
        $("body").css({
            overflow : 'hidden'
        });
  
        getSubtitlesOfVideo('#player-show', currentVideo.data('idVideo'), currentVideo.data('url'));
    });

    // Fetch Subtitles of a video from the server
    function getSubtitlesOfVideo(videoElement, idVideo, urlVideo){
        // Get Subtitles From DataBase
        $.get(`${URLROOT}/api/subtitles`, {id_video: idVideo}, function({data: subtitles}){
            $(videoElement).prop('src', urlVideo);

            for(let subtitle of subtitles){
                const track = document.createElement('track');
                Object.assign(track, {
                    label: subtitle.nom,
                    srclang: subtitle.nom.slice(0, 2).toLowerCase(),
                    default: true,
                    src: `${URLROOT}/public/${subtitle.source}`
                });

                $(videoElement).append(track);
            }

            createSubtitles(subtitles);
        });
    }

    // Add subtiles to the DOM (from Database)
    function createSubtitles(subtitles){
        subtitles.forEach((subtitle) => {
            $('.subtitles-edit').append(`
                <div data-id="${subtitle.id_sous_titre}" class="subtitle mb-2 p-3">
                    <span class="delete-subtitle d-flex align-items-center justify-content-center" data-id-subtitle="${subtitle.id_sous_titre}" data-name="${subtitle.nom}">
                        <i class="material-icons md-18">delete</i>
                    </span>

                    <div class="form-group row mb-0">
                        <label for="v-description-add"
                                class="col-form-label form-label col-md-3">Langue:</label>
                        <div class="col-md-9">
                            <input
                                type="text"
                                class="form-control"
                                value="${subtitle.nom}" disabled />
                        </div>
                    </div>
                </div>
            `);
        })
    }

    function hideOverlay() {
        $("#overlay").fadeOut();
        $(".overlay-content").css({ transform: "translate(-50%, 1000%)" });
        $("body").css({
            overflow : 'auto'
        });
        
        $('#player-show').prop('src', '#');
        $('#player-show track').remove();
    }

    $("#closeBtn, #overlay").click(hideOverlay);

    let i = 1;
    // Add subtiles to the DOM
    $(document).on('click', '.add-subtitle', function(){
        const id = $(this).attr('id');
        const subtitlesParent = id === 'subtitle-edit' ? '.subtitles-edit' : '.subtitles-add';

        let langueOptions = '';
        for(let langue of langues){
            langueOptions += `<option value="${langue.id_langue}">${langue.nom}</option>`
        }

        $(subtitlesParent).append(`
            <div data-id="sub-${i}" class="subtitle mb-2 p-3">
                <span class="delete-subtitle d-flex align-items-center justify-content-center" id="sub-${i}">
                    <i class="material-icons md-18">delete</i>
                </span>

                <div class="form-group row">
                    <label for="v-description-add"
                            class="col-form-label form-label col-md-3">Langue:</label>
                    <div class="col-md-9">
                        <select data-id="${i}" class="form-control" name="langues[]">
                            ${langueOptions}
                        </select>
                    </div>
                </div>

                <div class="form-group mb-0 row">
                    <label for="v-description-add"
                            class="col-form-label form-label col-md-3">Subtitle:</label>
                    <div class="col-md-9">
                        <div class="custom-file">
                            <input type="file"
                                name="subtitle_${i}"
                                data-id="${i}"
                                class="custom-file-input subtitle-input ${id === 'subtitle-edit' && 'edit'}">
                            <label
                                class="custom-file-label">Choose Subtitle</label>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $(`input[name="subtitle_${i}"]`).rules('add', {
            required: true,
            extension: "vtt",
            messages : {
                required: "Veuillez sélectionner une sous-titre.",
                extension: "Please upload a valid VTT file."
            }
        });

        i++;
    })

    $(document).on('click', '.delete-subtitle', function(){
        let langueName;
        let id = $(this).attr('id');

        if (id === undefined) {
            id = $(this).data('idSubtitle');
            
            langueName = $(this).data('name');

            $.ajax({
                url: `${URLROOT}/api/subtitles/${id}`,
                type: 'DELETE',
                success: function(res){
                    // remove subtitle track
                    $(`track[label="${langueName}"]`).remove()

                    // remove subtitle element (input + select)
                    $(`div[data-id="${id}"]`).remove();
                },
                error: function(res){
                    alert("Something went wrong, Please try again later.")
                    console.log(res);
                }
            });

            return;
        }
        
        // get the language from the array
        langueName = langues[$(`select[data-id="${id.slice(4)}"]`).val() - 1].nom;

        // remove subtitle track
        $(`track[label="${langueName}"]`).remove()

        // remove subtitle element (input + select)
        $(`div[data-id="${id}"]`).remove();
    });

    // Handle Subtitle (Attach subtitle to the video to preview it)
    $(document).on('change', '.subtitle-input', function(e){
        const id = $(this).data('id');
        const file = e.target.files[0];
        if(!file) return;

        const fileName = file.name;
        $(this).next('label').text(fileName);
        const blobURL = URL.createObjectURL(file);
        const langueName = langues[$(`select[data-id="${id}"]`).val() - 1].nom;
        const oldTrackElement = $(`track[id="${id}"]`);

        if(oldTrackElement.length !== 0){
           oldTrackElement.remove();
        }

        const track = document.createElement('track');
        Object.assign(track, {
            label: langueName,
            srclang: langueName.slice(0, 2).toLowerCase(),
            default: true,
            src: blobURL,
            id: id
        });

        if($(this).hasClass('edit')) {
            $('video#player-2').append(track);
        }else{
            $('video#player-1').append(track);
        }
    });

    // Clear edit modal
    $("#edit-lesson").on('hide.bs.modal', function(){
        $(this).find('#player-2').attr('src', '').empty();
        $(this).find('.subtitles-edit').empty();
    });

    // Fetch videos from the server
    function fetchVideos(page = 1){
        $.get(`${URLROOT}/api/courses/${courseID}/videos?page=${page}`, function({data: { videos, totalPages, nextPage }}){
            const videosWrapper = $('#wrapper-videos');
            $('#nom-formation').text(videos[0].nomFormation);
            videos.forEach((video) => {
                videosWrapper.append(`
                    <li class="nestable-item nestable-item-handle video-item"
                        data-id="${video.id_video}">
                        <div class="nestable-handle"><i class="material-icons">menu</i></div>
                        <div class="nestable-content">
                            <div class="media align-items-center">
                                <div class="media-left">
                                    <img src="${URLROOT}/public/images/${video.thumbnail}"
                                            alt="thumbnail video"
                                            width="100"
                                            class="rounded" />
                                </div>
                                <div class="media-body">
                                    <h5 class="card-title h6 mb-0">
                                        <a data-id-video="${video.id_video}" data-url="${URLROOT}/public/videos/${video.url}" href="javascript:void(0)" class="video-name">${video.nomVideo}</a>
                                    </h5>
                                    <small class="text-muted created-at">created ${video.created_at}</small>
                                </div>
                                <div class="media-right d-flex gap-3 align-items-center">
                                    ${!video.is_preview ? `<button data-id-formation="${video.id_formation}" data-id-video="${video.id_video}" class="btn btn-white btn-sm set-preview-btn" data-toggle="tooltip" data-placement="top" title="Make it Preview"><i class="material-icons">play_circle_outline</i></button>` : `<span data-id-formation="${video.id_formation}" data-id-video="${video.id_video}" class="badge badge-pill badge-light preview-badge">Preview</span>`}
                                    
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle text-muted" data-caret="false" data-toggle="dropdown" aria-expanded="false">
                                            <i class="material-icons">more_vert</i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="javascript:void(0)" data-video='${JSON.stringify(video)}' data-target="#edit-lesson" data-toggle="modal" class="dropdown-item edit-lesson"><i class="material-icons">edit</i> Edit</a>
                                            <div class="dropdown-divider"></div>
                                            <a data-id="${video.id_video}" class="dropdown-item text-danger delete-video" href="javascript:void(0)"><i class="material-icons">delete</i> Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li> 
                `);
            });
            $('[data-toggle="tooltip"]').tooltip();

            const lastVideoItem = $('li.video-item:last-child')[0];
            const observer = new IntersectionObserver((entries) => {
                if(!nextPage) return
                
                if(entries[0].isIntersecting){
                    observer.unobserve(entries[0].target);
                    fetchVideos(nextPage);
                }
            },{
                threshold: 1
            });

            observer.observe(lastVideoItem)
        });

    }
    
    // First Fetch
    fetchVideos();
});
