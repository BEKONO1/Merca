<!DOCTYPE html>
<html>

<?php $this->load->view('admin/include-css.php'); ?>
<div id="loading">
    <div class="lds-ring">
        <div></div>
    </div>
</div>

<body class="hold-transition sidebar-mini layout-fixed ">
    <div class="page">
        <?php $this->load->view('admin/include-sidebar.php'); ?>
        <?php $this->load->view('layouts/toast.php'); ?>
        <?php $this->load->view('layouts/filter_offcanvas.php'); ?>
        <?php $this->load->view('admin/include-navbar.php') ?>

        <!-- Setup Wizard Banner -->
        <?php 
        $wizard_hidden = isset($_COOKIE['setup_wizard_hidden']) && $_COOKIE['setup_wizard_hidden'] === 'true';
        $wizard_dismissed = isset($_COOKIE['setup_wizard_dismissed']) && $_COOKIE['setup_wizard_dismissed'] === 'true';
        
        if (!$wizard_hidden && !$wizard_dismissed) {
            $next_module = get_next_incomplete_module();
            $setup_progress = get_setup_wizard_progress();
            
            if ($next_module): 
        ?>
        <div class="page-wrapper mt-3 px-3" id="setupWizardBanner">
            <div class="container-fluid alert alert-info mb-0 d-flex align-items-center" role="alert">
                <div class="d-flex align-items-center me-4">
                    <i class="ti ti-settings-automation fs-1 me-3"></i>
                    <div>
                        <h4 class="alert-title mb-1">System Setup in Progress</h4>
                        <div class="text-muted small">Next: <strong><?= $next_module['name'] ?></strong> - <?= $next_module['description'] ?></div>
                    </div>
                </div>
                <div class="me-4 setup-wizard-progress-container">
                    <div class="mb-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted small">Setup Progress</div>
                            <div>
                                <span class="badge badge-primary bg-primary-lt"><?= $setup_progress['percentage'] ?>%</span>
                                <span class="text-muted small ms-2"><?= $setup_progress['completed_count'] ?>/<?= $setup_progress['total_modules'] ?> completed</span>
                            </div>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated setup-wizard-progress-bar" 
                             role="progressbar" 
                             aria-valuenow="<?= $setup_progress['percentage'] ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"
                             data-width="<?= $setup_progress['percentage'] ?>">
                        </div>
                    </div>
                </div>
                <div class="ms-auto">
                    <div class="btn-list d-flex flex-nowrap">
                        <button onclick="hideSetupWizardForever()" class="btn btn-outline-danger" title="Hide permanently">
                            <i class="ti ti-x"></i>
                            Don't show again
                        </button>
                        <button onclick="dismissSetupWizard()" class="btn" title="Hide until refresh">
                            <i class="ti ti-eye-off"></i>
                            Dismiss
                        </button>
                        <a href="<?= base_url($next_module['link']) ?>" class="btn btn-primary">
                            <i class="ti ti-arrow-right me-1"></i>
                            Configure Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endif;
        }
        ?>
        <!-- End Setup Wizard Banner -->

        <button id="filterOffcanvasTrigger" type="button" class="d-none" data-bs-toggle="offcanvas"
            data-bs-target="#filterOffcanvas"></button>


        <?php if (isset($_GET['error']) && $_GET['error'] === "true"): ?>
            <?php
            $api = isset($_GET['api']) ? htmlspecialchars($_GET['api']) : "unknown";
            $httpStatus = isset($_GET['http_status']) ? (int) $_GET['http_status'] : 0;
            ?>

            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded" role="alert">
                    <h5 class="alert-heading">⚠️ CSRF Token Missing</h5>
                    <p>
                        The request to <code><?= $api ?></code> failed because the CSRF token was not provided or expired.
                    </p>
                    <p>
                        <strong>HTTP Status:</strong> <?= $httpStatus ?>
                    </p>

                    <hr>
                    <p class="mb-0">
                        Please refresh the page and try again.
                    </p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <div id="errorText">

            </div>

            <script>
                (function () {
                    const params = new URLSearchParams(window.location.search);
                    let errorResponse = localStorage.getItem("errorResponse", "");
                    if (errorResponse !== "") {
                        localStorage.setItem("errorResponse", "");
                        console.log(errorResponse)
                        document.getElementById(errorText).innerText = errorResponse
                    }
                    // Remove known query parameters
                    params.delete("error");
                    params.delete("api");
                    params.delete("http_status");

                    const newUrl =
                        window.location.pathname + (params.toString() ? "?" + params.toString() : "");

                    // Update URL without reloading
                    window.history.replaceState({}, document.title, newUrl);
                })();
            </script>
        <?php endif; ?>


        <?php $this->load->view('admin/pages/' . $main_page); ?>
        <?php $this->load->view('admin/include-footer.php'); ?>
    </div>
    <?php $this->load->view('admin/include-script.php'); ?>
</body>

</html>