<div class="nav-backdrop" id="navBackdrop"></div>
    
    <nav class="nav-panel" id="navPanel">
        <div class="nav-header">
            <span class="fw-bold text-dark"><i class="fas fa-compass text-primary me-2"></i>Navigasi</span>
            <button class="btn btn-sm btn-light border" id="closeNav">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <ul class="nav-links">
            <li>
                <a href="<?= base_url() ?>">
                    <i class="fas fa-home"></i> Home Public
                </a>
            </li>
            <li>
                <a href="<?= base_url('geospasial') ?>" class="<?= uri_string() == 'geospasial' ? 'active' : '' ?>">
                    <i class="fas fa-map-marked-alt"></i> Data Management
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
            </li>
        </ul>

        <div class="nav-footer">
                    <div class="d-flex align-items-center mb-3 px-1">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                            style="width: 40px; height: 40px; margin-right: 12px; font-weight: 700; font-size: 1.1rem;">
                            <?= strtoupper(substr(user()->username, 0, 1)) ?>
                        </div>
                        
                        <div style="line-height: 1.2;">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                <?= user()->username ?>
                            </div>
                            <div class="text-muted text-truncate" style="font-size: 0.75rem; max-width: 160px;" title="<?= user()->email ?>">
                                <?= user()->email ?>
                            </div>
                        </div>
                    </div>

                    <a href="<?= base_url('logout') ?>" class="btn-logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
    </nav>

    <main class="main-wrapper">
        <div class="container-xxl"> <div class="page-header d-flex align-items-center justify-content-between">
                <h1 class="page-title"><?= $title; ?></h1>
                
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
                    </ol>
                </nav>
            </div>