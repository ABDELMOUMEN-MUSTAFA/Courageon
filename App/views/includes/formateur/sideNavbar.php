<?php $sitename = strtolower(SITENAME) ?>
<aside class="mdk-drawer js-mdk-drawer" id="default-drawer" style="z-index: 999;">
    <div class="mdk-drawer__content ">
        <div class="sidebar sidebar-left sidebar-dark bg-dark o-hidden"
             data-perfect-scrollbar>
            <div class="sidebar-p-y">
                <div class="sidebar-heading">Instructor</div>
                <ul class="sidebar-menu sm-active-button-bg">
                    <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/formateur" ? 'active' : '' ?>">
                        <a class="sidebar-menu-button"
                           href="<?= URLROOT ?>/formateur">
                            <i class="sidebar-menu-icon sidebar-menu-icon--left material-icons">dashboard</i> Dashboard
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a class="sidebar-menu-button"
                           data-toggle="collapse"
                           href="#course_menu">
                            <i class="sidebar-menu-icon sidebar-menu-icon--left material-icons">desktop_mac</i>
                            Manage Courses
                            <span class="ml-auto sidebar-menu-toggle-icon"></span>
                        </a>
                        <ul class="sidebar-submenu sm-indent collapse"
                            id="course_menu">
                            <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/courses" ? 'active' : '' ?>">
                                <a class="sidebar-menu-button"
                                   href="<?= URLROOT ?>/courses">
                                    <span class="sidebar-menu-text">Courses</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/courses/add" ? 'active' : '' ?>">
                                <a class="sidebar-menu-button"
                                   href="<?= URLROOT ?>/courses/add">
                                    <span class="sidebar-menu-text">Add Course</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/formateur/promotions" ? 'active' : '' ?>">
                        <a class="sidebar-menu-button"
                           href="<?= URLROOT ?>/formateur/promotions">
                            <i class="sidebar-menu-icon sidebar-menu-icon--left material-icons">local_offer</i> Promotions
                        </a>
                    </li>
                    <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/formateur/earnings" ? 'active' : '' ?>">
                        <a class="sidebar-menu-button"
                           href="<?= URLROOT ?>/formateur/earnings">
                            <i class="sidebar-menu-icon sidebar-menu-icon--left material-icons">trending_up</i> Earnings
                        </a>
                    </li>
                    <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/formateur/transactions" ? 'active' : '' ?>">
                        <a class="sidebar-menu-button"
                           href="<?= URLROOT ?>/formateur/transactions">
                            <i class="sidebar-menu-icon sidebar-menu-icon--left material-icons">receipt</i> Transactions
                        </a>
                    </li>
                    <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/formateur/messages" ? 'active' : '' ?>">
                        <a class="sidebar-menu-button"
                           href="<?= URLROOT ?>/formateur/messages">
                            <i class="sidebar-menu-icon sidebar-menu-icon--left material-icons">message</i> Messages
                        </a>
                    </li>
                    <li class="sidebar-menu-item <?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/{$sitename}/formateur/privateCourses" ? 'active' : '' ?>">
                        <a class="sidebar-menu-button"
                           href="<?= URLROOT ?>/formateur/privateCourses">
                            <i class="sidebar-menu-icon sidebar-menu-icon--left material-icons">lock</i> Manage Private Courses
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</aside>
