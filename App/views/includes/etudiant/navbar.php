<!-- Header -->
<header id="header"
     data-fixed
     class="mdk-header js-mdk-header mb-0">
    <div class="mdk-header__content">

        <!-- Navbar -->
        <nav id="default-navbar"
             class="navbar navbar-expand navbar-dark m-0 bg-dark mdk-header--fixed">
            <div class="container-fluid">
                <!-- Toggle sidebar -->
                <button class="navbar-toggler d-block"
                        data-toggle="sidebar"
                        type="button">
                    <span class="material-icons">menu</span>
                </button>

                <!-- Brand -->
                <a href="<?= URLROOT ?>"
                   class="navbar-brand">
                    <img class="logo" src="<?= IMAGEROOT ?>/logos/dark-logo.png" width="149" height="42" alt="logo platform e-learning">
                </a>

                <!-- Search -->
                <form class="search-form d-none d-md-flex">
                    <input type="text"
                           class="form-control"
                           placeholder="Search">
                    <button class="btn"
                            type="button"><i class="material-icons font-size-24pt">search</i></button>
                </form>
                <!-- // END Search -->

                <div class="flex"></div>

                <!-- Menu -->
                <ul class="nav navbar-nav flex-nowrap">

                    <!-- Notifications dropdown -->
                    <li class="nav-item dropdown dropdown-notifications dropdown-menu-sm-full">
                        <button class="nav-link btn-flush dropdown-toggle"
                                type="button"
                                data-toggle="dropdown"
                                data-dropdown-disable-document-scroll
                                data-caret="false">
                            <i class="material-icons">notifications</i>
                            <span class="badge badge-notifications badge-danger">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div data-perfect-scrollbar
                                 class="position-relative">
                                <div class="dropdown-header"><strong>Notifications</strong></div>
                            </div>
                        </div>
                    </li>
                    <!-- // END Notifications dropdown -->
                    <!-- User dropdown -->
                    <li class="nav-item dropdown ml-1 ml-md-3">
                        <a class="nav-link dropdown-toggle"
                           data-toggle="dropdown"
                           href="javasript:void(0)"
                           role="button"><img src="<?= strpos(session('user')->get()->img, 'users') === 0 ? IMAGEROOT.'/'.session('user')->get()->img : session('user')->get()->img ?>"
                                 alt="Avatar Etudiant"
                                 class="rounded-circle avatar"
                                 width="40"></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item"
                               href="<?= URLROOT ?>/etudiant/edit">
                                <i class="material-icons">edit</i> Edit Account
                            </a>
                            <a class="dropdown-item"
                               href="<?= URLROOT ?>/user/logout">
                                <i class="material-icons">lock</i> Logout
                            </a>
                        </div>
                    </li>
                    <!-- // END User dropdown -->
                </ul>
            </div>
        </nav>
        <!-- // END Navbar -->

    </div>
</header>
<!-- // END Header -->