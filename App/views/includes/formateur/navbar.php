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
                <form action="<?= URLROOT ?>/courses" class="search-form d-none d-md-flex">
                    <input type="text"
                           class="form-control"
                           placeholder="Search"
                           name="q">
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
                            <span id="notification-counter" class="badge badge-notifications badge-danger">
                                <?= array_reduce($notifications, fn($acc, $n) => !$n->is_read ? $acc + 1 : $acc, 0) ?>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div data-perfect-scrollbar
                                 class="position-relative">
                                <div class="dropdown-header d-flex justify-content-between">
                                    <strong>Notifications</strong>
                                    <a id="btn-clear-notifications" href="javascript:void(0)">clear seen</a>
                                </div>
                                <div class="notifications list-group list-group-flush mb-0">
                                    <?php foreach($notifications as $n): ?>
                                    <a data-is-read="<?= $n->is_read ?>" data-id="<?= $n->id_notification ?>" class="notification list-group-item list-group-item-action unread d-block" href="<?= URLROOT ?>/formateur/messages/<?= $n->slug ?>"
                                       class="list-group-item list-group-item-action unread">
                                        <div class="d-flex align-items-center mb-1">
                                            <small class="text-muted"><?= $n->created_at ?></small>
                                            <?php if(!$n->is_read): ?>
                                                <span class="ml-auto unread-indicator bg-primary"></span>
                                            <?php endif ?>
                                        </div>
                                        <div class="d-flex">
                                            <div class="avatar avatar-xs mr-2">
                                                <img src="<?= IMAGEROOT ?>/<?= $n->img ?>" alt="etudiant avatar" class="avatar-img rounded-circle">
                                            </div>
                                            <div class="flex d-flex flex-column">
                                                <strong><?= $n->prenom.' '.$n->nom ?></strong>
                                                <span class="text-black-70"><?= $n->content ?></span>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach ?>
                                    <input type="hidden" name="last_notification" value="<?= $notifications[0]->unix_timestamp ?? '0000000000' ?>" />
                                    <button id="btn-play-notify-sound" class="d-none">Play Sound</button>
                                </div>
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
                                 alt="Avatar Formateur"
                                 class="avatar rounded-circle"
                                 width="40"></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item"
                               href="<?= URLROOT ?>/formateur/edit">
                                <i class="material-icons">edit</i> Edit Account
                            </a>
                            <a class="dropdown-item"
                                target="_blank"
                               href="<?= URLROOT ?>/user/<?= session('user')->get()->slug ?>">
                                <i class="material-icons">person</i> Public Profile
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