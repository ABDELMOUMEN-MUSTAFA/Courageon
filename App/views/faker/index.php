<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Generator | <?= SITENAME ?></title>
    <link rel="icon" type="image/x-icon" href="<?= IMAGEROOT ?>/favicon.ico" />
    <link href="<?= CSSROOT ?>/icons/all.min.css" rel="stylesheet" />
    <link href="<?= CSSROOT ?>/plugins/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', 'sans-serif';
            height: 100vh;
            overflow: hidden;
            background-color: #610C9F;
        }

        a {
            text-decoration: none;
            padding: .5rem 3rem;
            border-radius: 999px;
            font-weight: 600;
            background-color: #ffc107;
            color: #333;
            transition: .3s background-color;
        }

        a:hover{
            background-color: #940B92;
            color: white;
        }

        .card {
            background-color: #662d91;
            color: wheat;
            transition: 0.3s transform;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-20px);
            background-color: #ffc107;
            color: #000;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="loader d-flex flex-column justify-content-center">
        <div class="d-flex justify-content-center text-light">
            <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">75</span>
            </div>
        </div>
        <p class="mt-3 text-white">Generating Data (<span id="loading-pourcent">0</span>%)</p>
    </div>
    
    <div class="container" style="display: none;">
        <h1 class="text-center mb-5 text-white">Statistics Of Generated Data</h1>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <div class="card shadow border-0" style="width: 10rem;">
                <div class="card-body">
                    <div class="text-center mb-3"><i class="fa-solid fa-graduation-cap fs-1"></i></div>
                    <p class="card-text text-center">
                        <strong id="etudiants">0</strong> Etudiants
                    </p>
                </div>
            </div>
            <div class="card shadow border-0" style="width: 10rem;">
                <div class="card-body">
                    <div class="text-center mb-3"><i class="fa-solid fa-person-chalkboard fs-1"></i></div>
                    <p class="card-text text-center">
                        <strong id="formateurs">0</strong> Formateurs
                    </p>
                </div>
            </div>
            <div class="card shadow border-0" style="width: 10rem;">
                <div class="card-body">
                    <div class="text-center mb-3"><i class="fa-solid fa-list fs-1"></i></div>
                    <p class="card-text text-center">
                        <strong id="categories">0</strong> Categories
                    </p>
                </div>
            </div>
            <div class="card shadow border-0" style="width: 10rem;">
                <div class="card-body">
                    <div class="text-center mb-3"><i class="fa-solid fa-desktop fs-1"></i></div>
                    <p class="card-text text-center">
                        <strong id="formations">0</strong> Formations
                    </p>
                </div>
            </div>
            <div class="card shadow border-0" style="width: 10rem;">
                <div class="card-body">
                    <div class="text-center mb-3"><i class="fa-solid fa-video fs-1"></i></div>
                    <p class="card-text text-center">
                        <strong id="videos">0</strong> Videos
                    </p>
                </div>
            </div>
            <div class="card shadow border-0" style="width: 10rem;">
                <div class="card-body">
                    <div class="text-center mb-3"><i class="fa-solid fa-money-check fs-1"></i></div>
                    <p class="card-text text-center">
                        <strong id="inscriptions">0</strong> Inscriptions
                    </p>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
            <a href="<?= URLROOT ?>">Go to Home</a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= JSROOT ?>/plugins/jquery-3.6.3.min.js"></script>
    <script>
        $.ajax({
            url: `<?= URLROOT ?>/faker`,
            type: 'POST',
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = $.ajaxSettings.xhr();
                xhr.addEventListener('progress', function(event) {
                    if (event.lengthComputable) {
                        const percentComplete = parseInt((event.loaded / event.total) * 100);
                        $("#loading-pourcent").text(percentComplete);
                    }
                });
                return xhr;
            },
            success: function({data}) {
                $('.loader').remove();
                for(let record in data){
                    $(`#${record}`).text(data[record]);
                }
                $('.container').fadeIn('slow');
            },
            error: function({responseJSON}) {
                console.log(responseJSON)              
            },
        });
    </script>
</body>
</html>