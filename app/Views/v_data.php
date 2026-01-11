<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    :root {
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-600: #475569;
        --slate-700: #334155;
        --danger: #ef4444;
    }

    /* Container SaaS Style */
    .container {
        justify-content: unset;
    }
    .geo-container {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--slate-200);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-top: 10px;
    }
    
    .geo-header {
        background-color: var(--slate-50);
        padding: 0 24px;
        border-bottom: 1px solid var(--slate-200);
    }

    .nav-tabs { border: none; }
    .nav-tabs .nav-link {
        padding: 18px 20px;
        font-weight: 600;
        font-size: 0.85rem;
        border: none;
        color: #64748b;
        border-bottom: 3px solid transparent;
        transition: 0.2s;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: transparent;
    }

    /* Professional Accordion */
    .accordion-item { 
        border: 1px solid var(--slate-200); 
        margin-bottom: 10px; 
        border-radius: 10px !important; 
        overflow: hidden; 
    }
    .accordion-button { padding: 16px 20px; font-weight: 600; }
    .accordion-button:not(.collapsed) { 
        background-color: var(--slate-50); 
        color: var(--primary); 
        box-shadow: none; 
    }

    /* Table & UI Elements */
    .table-in-group thead th {
        background: var(--slate-50);
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        color: #64748b;
        padding: 12px;
    }
    
    .btn-xs { 
        width: 30px; height: 30px; padding: 0; 
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; transition: 0.2s;
    }
    .btn-xs:hover { transform: translateY(-2px); }

    .scrollable-area {
        max-height: 70vh;
        overflow-y: auto;
        padding: 10px;
    }

    #map_style_preview, #map_draw_polygon {
        height: 400px; width: 100%; 
        border: 2px solid var(--slate-100); 
        border-radius: 12px;
    }

    .btn-loading { pointer-events: none; opacity: 0.6; }
</style>

<div class="geo-container">
    <div class="geo-header">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?= ($activeTab == 'polygon') ? 'active' : '' ?>" href="<?= base_url('geospasial?tab=polygon') ?>">
                    <i class="fas fa-draw-polygon me-2"></i> Data Poligon
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($activeTab == 'line') ? 'active' : '' ?>" href="<?= base_url('geospasial?tab=line') ?>">
                    <i class="fas fa-route me-2"></i> Data Line
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($activeTab == 'point') ? 'active' : '' ?>" href="<?= base_url('geospasial?tab=point') ?>">
                    <i class="fas fa-map-marker-alt me-2"></i> Data Point
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content p-4">
        <div class="tab-pane fade <?= ($activeTab == 'polygon') ? 'show active' : '' ?>" id="polygon-pane">
            <div class="row align-items-center mb-4">
                <div class="col">
                    <button class="btn btn-primary px-4 fw-bold mb-2" onclick="openGrupModal()" style="background: var(--primary); border:none;">
                        <i class="fas fa-layer-group me-2"></i>Grup Poligon Baru
                    </button>
                    <button class="btn btn-outline-primary px-4 fw-bold mb-2" onclick="openImportGrupModal('Polygon')">
                        <i class="fas fa-file-import me-2"></i>Import GeoJSON Grup
                    </button>
                </div>
                <div class="col-auto">
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchGroupInput" class="form-control border-start-0 ps-0" placeholder="Cari kategori...">
                    </div>
                </div>
            </div>

            <div class="accordion scrollable-area" id="accordionPolygon">
                <?php if(!empty($grupPolygon)): foreach($grupPolygon as $grup): ?>
                <div class="accordion-item grup-item shadow-sm">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $grup['id_dg'] ?>">
                            <div class="d-flex align-items-center w-100">
                                <div class="me-3" style="width: 45px; height: 22px; background: <?= $grup['fillColor'] ?>; border: 2px <?= !empty($grup['dashArray']) ? 'dashed' : 'solid' ?> <?= $grup['color'] ?>; border-radius: 4px;"></div>
                                <div class="flex-grow-1">
                                    <span class="grup-name text-dark"><?= $grup['nama_grup'] ?></span>
                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;"><?= count($grup['items']) ?> Entitas Terdata</div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?= $grup['id_dg'] ?>" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-light border" onclick='openGrupModal(<?= json_encode($grup) ?>, "Polygon")'><i class="fas fa-palette me-1"></i> Style</button>
                                    <button class="btn btn-sm btn-light border text-danger" onclick="deleteGrup(<?= $grup['id_dg'] ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="position-relative">
                                            <i class="fas fa-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                                            <input type="text" class="form-control form-control-sm search-in-group" 
                                                placeholder="Cari..." 
                                                style="width: 140px; padding-left: 30px; border-radius: 6px;">
                                        </div>

                                        <button class="btn btn-sm btn-outline-dark shadow-sm px-2" 
                                                onclick="doExportAJAX(<?= $grup['id_dg'] ?>, '<?= $grup['nama_grup'] ?>', this)"
                                                title="Export ke GeoJSON">
                                            <i class="fas fa-file-export text-success"></i>
                                        </button>

                                        <button class="btn btn-sm btn-primary shadow-sm px-3 d-flex align-items-center" 
                                                onclick='openAddPolygon(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'
                                                style="border-radius: 6px; font-weight: 500;">
                                            <i class="fas fa-plus-circle me-1"></i> Tambah
                                        </button>
                                    </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-in-group border">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="60">ID</th>
                                            <th>Nama Poligon</th>
                                            <th>Atribut Tambahan</th>
                                            <th class="text-center">Lampiran PDF</th>
                                            <th class="text-center" width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($grup['items'])): foreach($grup['items'] as $item): ?>
                                        <tr class="item-row">
                                            <td class="text-center text-muted small"><?= $item['id'] ?></td>
                                            <td class="fw-bold itemName"><?= $item['nama_display'] ?></td>
                                            <td>
                                                <?php 
                                                    $attrs = json_decode($item['atribut_tambahan'], true);
                                                    if($attrs) {
                                                        foreach($attrs as $a) echo "<span class='badge bg-light text-dark border me-1 fw-normal'>{$a['label']}: {$a['value']}</span>";
                                                    } else { echo "-"; }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if(!empty($item['daftar_pdf'])): foreach($item['daftar_pdf'] as $pdf): ?>
                                                        <a href="<?= base_url('uploads/pdf/'.$pdf['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-danger" title="<?= $pdf['judul_pdf'] ?>">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    <?php endforeach; else: echo "-"; endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-xs btn-light border text-warning" 
                                                        onclick='openEditPolygon(<?= json_encode(['id' => $item['id']]) ?>, <?= json_encode($grup) ?>, this)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-xs btn-light border text-danger" onclick="deleteData('polygon', <?= $item['id'] ?>)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; else: ?>
                                            <tr class="no-data"><td colspan="5" class="text-center py-4 text-muted small">Belum ada data di grup ini.</td></tr>
                                        <?php endif; ?>
                                        <tr class="search-no-result" style="display: none;"><td colspan="5" class="text-center py-3 text-muted">Data tidak ditemukan.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <div class="alert alert-light border text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                        <span class="text-muted">Belum ada kategori data yang dibuat.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade <?= ($activeTab == 'line') ? 'show active' : '' ?>" id="line-pane">
            <div class="row align-items-center mb-4">
                <div class="col">
                    <button class="btn btn-primary px-4 fw-bold mb-2" onclick="openGrupModal(null, 'Line')" style="background: var(--primary); border:none;">
                        <i class="fas fa-layer-group me-2"></i>Grup Line Baru
                    </button>
                    <button class="btn btn-outline-primary px-4 fw-bold mb-2" onclick="openImportGrupModal('Line')">
                        <i class="fas fa-file-import me-2"></i>Import GeoJSON Grup
                    </button>
                </div>
                <div class="col-auto">
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchGroupInputLine" class="form-control border-start-0 ps-0" placeholder="Cari kategori line...">
                    </div>
                </div>
            </div>
            
            <div class="accordion scrollable-area" id="accordionLine">
                <?php if(!empty($grupLine)): foreach($grupLine as $grup): ?>
                <div class="accordion-item grup-item shadow-sm">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLine<?= $grup['id_dg'] ?>">
                            <div class="d-flex align-items-center w-100">
                                <div class="me-3 d-flex align-items-center justify-content-center" 
                                    style="width: 45px; height: 22px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">
                                    <div style="width: 80%; height: 0; border-top: <?= $grup['weight'] ?>px <?= !empty($grup['dashArray']) ? 'dashed' : 'solid' ?> <?= $grup['color'] ?>;"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="grup-name text-dark"><?= $grup['nama_grup'] ?></span>
                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;"><?= count($grup['items']) ?> Jalur Terdata</div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapseLine<?= $grup['id_dg'] ?>" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-light border" onclick='openGrupModal(<?= json_encode($grup) ?>, "Line")'><i class="fas fa-palette me-1"></i> Style</button>
                                    <button class="btn btn-sm btn-light border text-danger" onclick="deleteGrup(<?= $grup['id_dg'] ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        <i class="fas fa-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                                        <input type="text" class="form-control form-control-sm search-in-group" 
                                            placeholder="Cari..." style="width: 140px; padding-left: 30px; border-radius: 6px;">
                                    </div>
                                    <button class="btn btn-sm btn-outline-dark shadow-sm px-2" 
                                            onclick="doExportAJAX(<?= $grup['id_dg'] ?>, '<?= $grup['nama_grup'] ?>', this)" title="Export ke GeoJSON">
                                        <i class="fas fa-file-export text-success"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary shadow-sm px-3" onclick='openAddLine(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'>
                                        <i class="fas fa-plus-circle me-1"></i> Tambah
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-in-group border">
                                    <thead><tr><th width="60">ID</th><th>Nama Jalur</th><th>Atribut</th><th class="text-center">PDF</th><th width="120" class="text-center">Aksi</th></tr></thead>
                                    <tbody>
                                        <?php if(!empty($grup['items'])): foreach($grup['items'] as $item): ?>
                                        <tr class="item-row">
                                            <td class="text-center text-muted small"><?= $item['id'] ?></td>
                                            <td class="fw-bold itemName"><?= $item['nama_display'] ?></td>
                                            <td>
                                                <?php 
                                                    $attrs = json_decode($item['atribut_tambahan'], true);
                                                    if($attrs) { foreach($attrs as $a) echo "<span class='badge bg-light text-dark border me-1 fw-normal'>{$a['label']}: {$a['value']}</span>"; } 
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if(!empty($item['daftar_pdf'])): foreach($item['daftar_pdf'] as $pdf): ?>
                                                    <a href="<?= base_url('uploads/pdf/'.$pdf['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-danger" title="<?= $pdf['judul_pdf'] ?>"><i class="fas fa-file-pdf"></i></a>
                                                <?php endforeach; else: echo "-"; endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-xs btn-light border text-warning" onclick='openEditLine(<?= json_encode($item) ?>, <?= json_encode($grup) ?>)'><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-xs btn-light border text-danger" onclick="deleteData('line', <?= $item['id'] ?>)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; else: ?>
                                            <tr class="no-data"><td colspan="5" class="text-center py-4 text-muted small">Belum ada data di grup ini.</td></tr>
                                        <?php endif; ?>
                                        <tr class="search-no-result" style="display: none;"><td colspan="5" class="text-center py-3 text-muted">Data tidak ditemukan.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <div class="alert alert-light border text-center py-5">
                        <i class="fas fa-route fa-3x text-muted mb-3 d-block"></i>
                        <span class="text-muted">Belum ada grup Line yang dibuat.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

            <div class="tab-pane fade <?= ($activeTab == 'point') ? 'show active' : '' ?>" id="point-pane">
        <div class="row align-items-center mb-4">
            <div class="col">
                <button class="btn btn-primary px-4 fw-bold mb-2" onclick="openGrupModal(null, 'Point')" style="background: var(--primary); border:none;">
                    <i class="fas fa-layer-group me-2"></i>Grup Point Baru
                </button>
                <button class="btn btn-outline-primary px-4 fw-bold mb-2" onclick="openImportGrupModal('Point')">
                    <i class="fas fa-file-import me-2"></i>Import GeoJSON Grup
                </button>
            </div>
            <div class="col-auto">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchGroupInputPoint" class="form-control border-start-0 ps-0" placeholder="Cari kategori point...">
                </div>
            </div>
        </div>

        <div class="accordion scrollable-area" id="accordionPoint">
            <?php if(!empty($grupPoint)): foreach($grupPoint as $grup): ?>
            <div class="accordion-item grup-item shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePoint<?= $grup['id_dg'] ?>">
                        <div class="d-flex align-items-center w-100">
                            <div class="me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 22px;">
                                <i class="fas fa-map-marker-alt" style="color: <?= $grup['color'] ?>;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <span class="grup-name text-dark"><?= $grup['nama_grup'] ?></span>
                                <div class="text-muted fw-normal" style="font-size: 0.7rem;"><?= count($grup['items']) ?> Lokasi Terdata</div>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapsePoint<?= $grup['id_dg'] ?>" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-sm btn-light border" onclick='openGrupModal(<?= json_encode($grup) ?>, "Point")'><i class="fas fa-palette me-1"></i> Style</button>
                                <button class="btn btn-sm btn-light border text-danger" onclick="deleteGrup(<?= $grup['id_dg'] ?>)"><i class="fas fa-trash"></i></button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="position-relative">
                                    <i class="fas fa-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                                    <input type="text" class="form-control form-control-sm search-in-group" placeholder="Cari..." style="width: 140px; padding-left: 30px; border-radius: 6px;">
                                </div>
                                <button class="btn btn-sm btn-outline-dark shadow-sm px-2" onclick="doExportAJAX(<?= $grup['id_dg'] ?>, '<?= $grup['nama_grup'] ?>', this)" title="Export"><i class="fas fa-file-export text-success"></i></button>
                                <button class="btn btn-sm btn-primary shadow-sm px-3" onclick='openAddPoint(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'><i class="fas fa-plus-circle me-1"></i> Tambah</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-in-group border">
                                <thead>
                                    <tr>
                                        <th width="60" class="text-center">ID</th>
                                        <th>Nama Lokasi</th>
                                        <th>Atribut Tambahan</th> <th class="text-center">Lampiran PDF</th>
                                        <th width="120" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($grup['items'])): foreach($grup['items'] as $item): ?>
                                    <tr class="item-row">
                                        <td class="text-center text-muted small"><?= $item['id'] ?></td>
                                        <td class="fw-bold itemName"><?= $item['nama_display'] ?></td>
                                        
                                        <td>
                                            <?php 
                                                $attrs = json_decode($item['atribut_tambahan'], true);
                                                if($attrs) {
                                                    foreach($attrs as $a) {
                                                        // Tampilkan Label: Value dalam badge
                                                        echo "<span class='badge bg-light text-dark border me-1 fw-normal'>{$a['label']}: {$a['value']}</span>";
                                                    }
                                                } else {
                                                    echo "-";
                                                }
                                            ?>
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <?php if(!empty($item['daftar_pdf'])): foreach($item['daftar_pdf'] as $pdf): ?>
                                                    <a href="<?= base_url('uploads/pdf/'.$pdf['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-danger" title="<?= $pdf['judul_pdf'] ?>">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                <?php endforeach; else: echo "-"; endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-xs btn-light border text-warning" onclick='openEditPoint(<?= json_encode($item) ?>, <?= json_encode($grup) ?>)'><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-xs btn-light border text-danger" onclick="deleteData('point', <?= $item['id'] ?>)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                        <tr class="no-data"><td colspan="5" class="text-center py-4 text-muted small">Belum ada data di grup ini.</td></tr>
                                    <?php endif; ?>
                                    <tr class="search-no-result" style="display: none;"><td colspan="5" class="text-center py-3 text-muted">Data tidak ditemukan.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
                <div class="alert alert-light border text-center py-5"><i class="fas fa-map-marker-alt fa-3x text-muted mb-3 d-block"></i><span class="text-muted">Belum ada grup Point.</span></div>
            <?php endif; ?>
        </div>
    </div>

<div class="modal fade" id="modalGrup" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('geospasial/saveGrup') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Konfigurasi Grup & Style</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_dg" id="grup_id">
                    <input type="hidden" name="jenis_peta" id="grup_jenis_peta">
                    
                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Kategori/Grup</label>
                                <input type="text" name="nama_grup" id="grup_nama" class="form-control" required placeholder="Misal: Kantor Pemerintahan">
                            </div>

                            <div class="mb-3 p-2 border rounded bg-light">
                                <label class="form-label fw-bold small text-primary"><i class="fas fa-tag me-1"></i> Penamaan Dinamis</label>
                                <select name="label_column" id="style_label_column" class="form-select form-select-sm shadow-sm">
                                    <option value="">-- Gunakan Nama Manual --</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Template Atribut</label>
                                <div id="template_container" class="mb-2"></div>
                                <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="addTemplateRow()">
                                    <i class="fas fa-plus me-1"></i> Tambah Kolom Template
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4 border-end">
                            <h6 class="fw-bold mb-3 small">Visualisasi Peta</h6>
                            
                            <div id="point_style_options" class="mb-3 p-2 border rounded bg-light d-none">
                                <label class="small fw-bold mb-2">Tipe Marker Point</label>
                                <select name="marker_type" id="style_marker_type" class="form-select form-select-sm mb-2" onchange="toggleMarkerOptions()">
                                    <option value="circle">Circle Marker (Bulat Warna)</option>
                                    <option value="pin">Standard Pin (Leaflet Default)</option>
                                    <option value="icon_url">Custom Icon (URL)</option>
                                    <option value="icon_file">Custom Icon (Upload Gambar)</option>
                                </select>

                                <div id="input_icon_url" class="d-none animate__animated animate__fadeIn">
                                    <input type="text" name="icon_url_input" id="style_icon_url" class="form-control form-control-sm" placeholder="https://example.com/icon.png">
                                    <small class="text-muted" style="font-size: 10px;">Paste URL gambar (.png/.svg)</small>
                                </div>

                                <div id="input_icon_file" class="d-none animate__animated animate__fadeIn">
                                    <input type="file" name="icon_file_input" id="style_icon_file" class="form-control form-control-sm" accept="image/*">
                                    <small class="text-muted" style="font-size: 10px;">Upload icon (Max 2MB)</small>
                                </div>
                            </div>

                            <div id="standard_style_container">
                                <div class="row g-3">
                                    <div class="col-6"><label class="small text-muted">Stroke</label><input type="color" name="color" id="style_color" class="form-control form-control-color w-100" value="#3388ff"></div>
                                    <div class="col-6" id="cont_fillColor"><label class="small text-muted">Fill</label><input type="color" name="fillColor" id="style_fillColor" class="form-control form-control-color w-100" value="#3388ff"></div>
                                    <div class="col-6"><label class="small text-muted">Weight/Size</label><input type="number" name="weight" id="style_weight" class="form-control" value="3"></div>
                                    <div class="col-12" id="cont_dashArray">
                                        <label class="small text-muted fw-bold">Pola Garis</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="dashArray" id="style_dashArray" class="form-control" placeholder="5, 10">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Presets</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" onclick="setDashPreset('')">Solid</a></li>
                                                <li><a class="dropdown-item" onclick="setDashPreset('5, 5')">Dashed</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-6"><label class="small text-muted">Op. Stroke</label><input type="number" name="opacity" id="style_opacity" class="form-control" value="1" step="0.1" max="1"></div>
                                    <div class="col-6" id="cont_fillOpacity"><label class="small text-muted">Op. Fill</label><input type="number" name="fillOpacity" id="style_fillOpacity" class="form-control" value="0.2" step="0.1" max="1"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold small mb-3">Preview Style</label>
                            <div id="map_style_preview"></div>
                            <div class="alert alert-info mt-2 p-2" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i> Perubahan style akan diterapkan ke semua data dalam grup ini.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDataPolygon" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('geospasial/save/polygon') ?>" method="post" id="formPolygonData" enctype="multipart/form-data">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Editor Entitas Poligon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="poly_id">
                    <input type="hidden" name="id_dg" id="poly_id_grup">
                    <textarea name="data_geospasial" id="poly_geojson" class="d-none"></textarea>

                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Identitas</label>
                                <input type="text" name="nama_dg" id="poly_nama" class="form-control" required>
                            </div>
                            
                            <div class="mb-3 p-3 bg-light border rounded">
                                <label class="small fw-bold mb-2 text-primary"><i class="fas fa-paperclip me-1"></i> Manajemen Dokumen</label>
                                <div id="existing_files_container" class="list-group mb-3 shadow-sm"></div>
                                <label class="form-label small text-muted">Upload PDF Baru (Bisa banyak):</label>
                                <input type="file" name="file_pdf[]" class="form-control form-control-sm" accept="application/pdf" multiple>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="m-0 fw-bold small">Atribut Detail</h6>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addAttributeRow()"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="attribute_container" class="scrollable-area p-0" style="max-height: 200px;"></div>
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold small"><i class="fas fa-vector-square me-1"></i> Gambar Geometri</span>
                                <div class="btn-group shadow-sm">
                                    <input type="file" id="fileGeoJSON" accept=".json,.geojson" class="d-none" onchange="handleFileUpload(this)">
                                    <button type="button" class="btn btn-xs btn-info text-white" onclick="document.getElementById('fileGeoJSON').click()" title="Import GeoJSON"><i class="fas fa-file-import"></i></button>
                                    <button type="button" class="btn btn-xs btn-warning text-white" onclick="clearMapDraw()" title="Reset Gambar"><i class="fas fa-redo"></i></button>
                                </div>
                            </div>
                            <div id="map_draw_polygon"></div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">*Klik pada peta untuk membuat titik, tarik titik untuk menggeser, klik titik untuk menghapus.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" id="btnSimpanPoly" class="btn btn-primary px-5 fw-bold"><i class="fas fa-save me-2"></i>Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportGrup" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <form id="formImportGrup" enctype="multipart/form-data">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Import Grup dari GeoJSON</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Grup Baru</label>
                                <input type="text" name="nama_grup" class="form-control" required placeholder="Contoh: Batas Wilayah">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Pilih File GeoJSON</label>
                                <input type="file" id="import_file_input" name="file_geojson" class="form-control" accept=".json,.geojson" required onchange="analyzeGeoJSON(this)">
                            </div>
                            <div id="column_mapping_container" class="mb-3 d-none">
                                <label class="form-label fw-bold small text-success"><i class="fas fa-table me-1"></i> Pilih Kolom Nama Lokasi</label>
                                <select name="column_name_map" id="column_name_map" class="form-select form-select-sm shadow-sm border-success"></select>
                            </div>
                            <div id="importProgressContainer" class="d-none mt-4 p-3 border rounded bg-light">
                                <label class="small fw-bold mb-1 d-block text-primary"><i class="fas fa-spinner fa-spin me-1"></i> Memproses Data...</label>
                                <div class="progress" style="height: 15px;">
                                    <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                                </div>
                                <small id="importStatusText" class="text-muted mt-2 d-block" style="font-size: 0.7rem;">Menunggu...</small>
                            </div>
                        </div>

                        <div class="col-md-4 border-end">
                            <h6 class="fw-bold mb-3 small">Visualisasi Peta</h6>
                            
                            <div id="import_point_options" class="mb-3 p-2 border rounded bg-light d-none">
                                <label class="small fw-bold mb-2">Tipe Marker</label>
                                <select name="marker_type" id="import_marker_type" class="form-select form-select-sm mb-2" onchange="refreshImportPreview(true)">
                                    <option value="circle">Circle Marker (Bulat Warna)</option>
                                    <option value="pin">Standard Pin (Leaflet Default)</option>
                                    <option value="icon_url">Custom Icon (URL)</option>
                                    <option value="icon_file">Custom Icon (Upload Gambar)</option>
                                </select>

                                <div id="cont_import_icon_url" class="d-none">
                                    <input type="text" name="icon_url_input" id="import_icon_url" class="form-control form-control-sm" placeholder="https://example.com/icon.png">
                                </div>
                                <div id="cont_import_icon_file" class="d-none">
                                    <input type="file" name="icon_file_input" id="import_icon_file" class="form-control form-control-sm" accept="image/*">
                                </div>
                            </div>

                            <div id="import_standard_style">
                                <div class="row g-3">
                                    <div class="col-6"><label class="small text-muted">Stroke</label><input type="color" name="color" id="import_style_color" class="form-control form-control-color w-100" value="#4f46e5"></div>
                                    <div class="col-6" id="cont_imp_fill"><label class="small text-muted">Fill</label><input type="color" name="fillColor" id="import_style_fillColor" class="form-control form-control-color w-100" value="#4f46e5"></div>
                                    <div class="col-6"><label class="small text-muted">Weight</label><input type="number" name="weight" id="import_style_weight" class="form-control" value="2"></div>
                                    <div class="col-12" id="cont_imp_dash">
                                        <label class="small text-muted fw-bold">Pola Garis</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="dashArray" id="import_style_dashArray" class="form-control" placeholder="5, 10">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Presets</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" onclick="setImportDashPreset('')">Solid</a></li>
                                                <li><a class="dropdown-item" onclick="setImportDashPreset('5, 5')">Dashed</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-6"><label class="small text-muted">Op. Stroke</label><input type="number" name="opacity" id="import_style_opacity" class="form-control" value="1" step="0.1" max="1"></div>
                                    <div class="col-6" id="cont_imp_fillOp"><label class="small text-muted">Op. Fill</label><input type="number" name="fillOpacity" id="import_style_fillOpacity" class="form-control" value="0.2" step="0.1" max="1"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold small mb-3">Preview Style</label>
                            <div id="map_import_preview" style="height: 250px; width: 100%; border-radius: 12px; border: 2px solid var(--slate-100);"></div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">Style ini akan diterapkan ke grup baru.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" id="btnSubmitImport" class="btn btn-primary px-5 fw-bold"><i class="fas fa-cloud-upload-alt me-2"></i>Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDataLine" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('geospasial/save/line') ?>" method="post" id="formLineData" enctype="multipart/form-data">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Editor Entitas Line (Jalur)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="line_id">
                    <input type="hidden" name="id_dg" id="line_id_grup">
                    <textarea name="data_geospasial" id="line_geojson" class="d-none"></textarea>

                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Jalur</label>
                                <input type="text" name="nama_dg" id="line_nama" class="form-control" required>
                            </div>
                            
                            <div class="mb-3 p-3 bg-light border rounded">
                                <label class="small fw-bold mb-2 text-primary"><i class="fas fa-paperclip me-1"></i> Manajemen Dokumen</label>
                                <div id="line_pdf_list" class="list-group mb-3 shadow-sm"></div>
                                <label class="form-label small text-muted">Upload PDF Baru (Bisa banyak):</label>
                                <input type="file" name="file_pdf[]" class="form-control form-control-sm" accept="application/pdf" multiple>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="m-0 fw-bold small">Atribut Detail</h6>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addAttrRow('line')"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="line_attr_area" class="scrollable-area p-0" style="max-height: 200px;"></div>
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold small"><i class="fas fa-route me-1"></i> Gambar Jalur</span>
                                <div class="btn-group shadow-sm">
                                    <input type="file" id="fileGeoJSONLine" accept=".json,.geojson" class="d-none" onchange="handleFileUploadLine(this)">
                                    <button type="button" class="btn btn-xs btn-info text-white" onclick="document.getElementById('fileGeoJSONLine').click()" title="Import GeoJSON"><i class="fas fa-file-import"></i></button>
                                    
                                    <button type="button" class="btn btn-xs btn-warning text-white" onclick="resetMapLine()" title="Reset Gambar"><i class="fas fa-redo"></i></button>
                                </div>
                            </div>
                            <div id="map_draw_line" style="height: 400px; width: 100%; border: 2px solid var(--slate-100); border-radius: 12px;"></div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">*Klik pada peta untuk membuat titik jalur, tarik titik untuk menggeser, klik titik untuk menghapus.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary px-5 fw-bold"><i class="fas fa-save me-2"></i>Simpan Line</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDataPoint" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('geospasial/save/point') ?>" method="post" id="formPointData" enctype="multipart/form-data">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Editor Entitas Point (Titik)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="point_id">
                    <input type="hidden" name="id_dg" id="point_id_grup">
                    <textarea name="data_geospasial" id="point_geojson" class="d-none"></textarea>

                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Lokasi</label>
                                <input type="text" name="nama_dg" id="point_nama" class="form-control" required>
                            </div>
                            
                            <div class="mb-3 p-3 bg-light border rounded">
                                <label class="small fw-bold mb-2 text-primary"><i class="fas fa-paperclip me-1"></i> Manajemen Dokumen</label>
                                <div id="point_pdf_list" class="list-group mb-3 shadow-sm"></div>
                                <label class="form-label small text-muted">Upload PDF Baru (Bisa banyak):</label>
                                <input type="file" name="file_pdf[]" class="form-control form-control-sm" accept="application/pdf" multiple>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="m-0 fw-bold small">Atribut Detail</h6>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addAttrRow('point')"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="point_attr_area" class="scrollable-area p-0" style="max-height: 200px;"></div>
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold small"><i class="fas fa-map-marker-alt me-1"></i> Tentukan Lokasi</span>
                                <div class="btn-group shadow-sm">
                                    <input type="file" id="fileGeoJSONPoint" accept=".json,.geojson" class="d-none" onchange="handleFileUploadPoint(this)">
                                    <button type="button" class="btn btn-xs btn-info text-white" onclick="document.getElementById('fileGeoJSONPoint').click()" title="Import GeoJSON"><i class="fas fa-file-import"></i></button>
                                </div>
                            </div>
                            <div id="map_draw_point" style="height: 400px; width: 100%; border: 2px solid var(--slate-100); border-radius: 12px;"></div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">*Klik pada peta atau geser marker untuk mengubah posisi lokasi.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary px-5 fw-bold"><i class="fas fa-save me-2"></i>Simpan Point</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// --- KONFIGURASI PETA (GOOGLE SATELLITE) ---
const DEFAULT_COORD = [-7.408019826354289, 111.4428818182571]; 
const GOOGLE_SAT_URL = 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}'; // URL Google Satellite

let styleMap, styleLayer;
let mapLine, drawLineLayer, lineMarkers = [];
let mapPoint, drawPointLayer;
let drawMap, drawLayer, markers = []; // Untuk Poligon
let currentGroupStyle = {};
let currentGrupType = 'Polygon'; 

// --- TAMBAHAN DARI KODE ATAS ---
let importStyleMap, importStyleLayer; // Variabel untuk preview import

// Listener untuk efek Loading pada tombol Submit (UX Improvement)
document.addEventListener('DOMContentLoaded', function() {
    // Loading saat simpan Polygon
    document.getElementById('formPolygonData')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSimpanPoly');
        if(btn) {
            btn.classList.add('btn-loading');
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;
            btn.disabled = true;
        }
    });

    // Loading saat simpan Grup
    document.querySelector('#modalGrup form')?.addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        if(btn) {
            btn.classList.add('btn-loading');
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memproses...`;
            btn.disabled = true;
        }
    });
});

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', function () {
    // Tooltip init
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(t => new bootstrap.Tooltip(t));

    // Fungsi Search Universal
    function setupSearch(inputId, itemClass) {
        const input = document.getElementById(inputId);
        if(!input) return;
        
        input.addEventListener('keyup', function(e) {
            const term = e.target.value.toLowerCase();
            const paneId = input.closest('.tab-pane').id; 
            const items = document.querySelectorAll(`#${paneId} .grup-item`);
            
            items.forEach(g => {
                const name = g.querySelector('.grup-name').textContent.toLowerCase();
                g.style.display = name.includes(term) ? '' : 'none';
            });
        });
    }

    setupSearch('searchGroupInput', '.grup-item');      // Polygon
    setupSearch('searchGroupInputLine', '.grup-item');  // Line
    setupSearch('searchGroupInputPoint', '.grup-item'); // Point
});

    // In-Group Search
    document.addEventListener('keyup', function(e) {
        if (e.target.classList.contains('search-in-group')) {
            const term = e.target.value.toLowerCase();
            const body = e.target.closest('.accordion-body');
            const rows = body.querySelectorAll('.item-row');
            let found = false;
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(term) ? '' : 'none';
                if(text.includes(term)) found = true;
            });
            body.querySelector('.search-no-result').style.display = found ? 'none' : 'table-row';
        }
    });

// --- HELPER UMUM ---
function deletePdfItem(id) {
    if(!confirm('Hapus file ini?')) return;
    fetch(`<?= base_url('geospasial/deletePdf') ?>/${id}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json()).then(res => { if(res.status==='success') document.getElementById(`pdf-item-${id}`).remove(); });
}

function addAttrRow(type = 'polygon', label = '', value = '') {
    let containerId = 'attribute_container'; 
    if(type === 'line') containerId = 'line_attr_area';
    if(type === 'point') containerId = 'point_attr_area';

    const div = document.createElement('div');
    div.className = 'input-group mb-2 shadow-sm';
    div.innerHTML = `
        <input type="text" name="attr_key[]" class="form-control form-control-sm" placeholder="Label" value="${label}" required>
        <input type="text" name="attr_val[]" class="form-control form-control-sm" placeholder="Nilai" value="${value}">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>
    `;
    const container = document.getElementById(containerId);
    if(container) container.appendChild(div);
}
// Alias legacy
function addAttributeRow(l, v) { addAttrRow('polygon', l, v); }

function deleteGrup(id) { if(confirm('Hapus seluruh grup dan datanya?')) window.location.href = `<?= base_url('geospasial/deleteGrup') ?>/${id}`; }
function deleteData(t, id) { if(confirm('Hapus entitas ini?')) window.location.href = `<?= base_url('geospasial/delete') ?>/${t}/${id}`; }

// --- BAGIAN 1: LOGIKA GRUP & PREVIEW ---
// --- VARIABEL GLOBAL ---
let markerMap;      // Map KHUSUS untuk Marker/Point (Baru)
let previewLayer;   // Layer object (untuk update style ringan)
let tempIconUrl = null; 

// --- 1. MODAL OPENER (ROUTER UTAMA) ---
function openGrupModal(data = null, type = 'Polygon') {
    const modalEl = document.getElementById('modalGrup');
    const form = modalEl.querySelector('form');
    
    // Reset Form
    form.reset();
    document.getElementById('template_container').innerHTML = '';
    tempIconUrl = null;

    // Set Tipe & ID
    currentGrupType = (data && data.jenis_peta) ? data.jenis_peta : type;
    document.getElementById('grup_jenis_peta').value = currentGrupType;
    document.getElementById('grup_id').value = data ? data.id_dg : '';
    if(data) document.getElementById('grup_nama').value = data.nama_grup;

    // Toggle Input UI (Fungsi Helper)
    toggleMarkerOptions(); 

    // Default Style
    let style = { 
        color: '#3388ff', weight: 3, opacity: 1, 
        fillColor: '#3388ff', fillOpacity: 0.2, 
        dashArray: '', radius: 10,
        marker_type: 'circle', marker_icon: ''
    };

    // Override jika Edit Data
    if(data) {
        style.color = data.color;
        style.weight = data.weight;
        style.opacity = data.opacity;
        style.fillColor = data.fillColor;
        style.fillOpacity = data.fillOpacity;
        style.dashArray = data.dashArray;
        style.radius = data.radius || 10;
        style.marker_type = data.marker_type || 'circle';
        style.marker_icon = data.marker_icon || '';
        
        // Load Template Atribut
        try { JSON.parse(data.atribut_default).forEach(x => addTemplateRow(x.label)); } catch(e){}
    } else {
        addTemplateRow(); // Default row
    }

    // Set Nilai Form
    const fields = ['color', 'fillColor', 'weight', 'opacity', 'fillOpacity', 'dashArray'];
    fields.forEach(f => {
        if(document.getElementById('style_' + f)) document.getElementById('style_' + f).value = style[f];
    });

    // Set Nilai Marker Khusus
    if(document.getElementById('style_marker_type')) {
        document.getElementById('style_marker_type').value = style.marker_type;
    }
    if (style.marker_type === 'icon_url') {
        document.getElementById('style_icon_url').value = style.marker_icon;
    }

    // Tampilkan Modal
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    // --- LOGIKA PEMISAH (ROUTER) ---
    modalEl.addEventListener('shown.bs.modal', () => {
        if (currentGrupType === 'Point') {
            // Panggil Fungsi KHUSUS Marker
            initMarkerPreviewMap(style); 
        } else {
            // Panggil Fungsi LAMA (Poly/Line)
            initStyleMap(style, currentGrupType);
        }
    }, { once: true });
}

// --- 2. FUNGSI KHUSUS PREVIEW MARKER (BARU) ---
function initMarkerPreviewMap(style) {
    // Bersihkan container map dari instance leaflet manapun
    cleanMapContainer();

    // Init Peta Baru
    markerMap = L.map('map_style_preview', { zoomControl: false, attributionControl: false }).setView(DEFAULT_COORD, 15);
    L.tileLayer(GOOGLE_SAT_URL).addTo(markerMap);

    const mType = document.getElementById('style_marker_type').value;

    // A. LOGIKA CIRCLE
    if (mType === 'circle') {
        previewLayer = L.circleMarker(DEFAULT_COORD, { 
            color: style.color, 
            weight: parseInt(style.weight),
            opacity: parseFloat(style.opacity),
            fillColor: style.fillColor,
            fillOpacity: parseFloat(style.fillOpacity),
            radius: 10 
        }).addTo(markerMap);
    } 
    // B. LOGIKA PIN STANDAR
    else if (mType === 'pin') {
        previewLayer = L.marker(DEFAULT_COORD).addTo(markerMap);
    } 
    // C. LOGIKA CUSTOM ICON (URL/FILE)
    else {
        let iconSrc = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
        
        if (tempIconUrl) {
            iconSrc = tempIconUrl; // Dari upload sementara
        } else if (mType === 'icon_url' && document.getElementById('style_icon_url').value) {
            iconSrc = document.getElementById('style_icon_url').value;
        } else if (style.marker_icon) {
            // Dari database (saat edit)
            iconSrc = style.marker_icon.startsWith('http') ? style.marker_icon : `<?= base_url('uploads/icons') ?>/${style.marker_icon}`;
        }

        const customIcon = L.icon({
            iconUrl: iconSrc, 
            iconSize: [32, 32], 
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
        previewLayer = L.marker(DEFAULT_COORD, { icon: customIcon }).addTo(markerMap);
    }

    // Setup Listeners khusus Marker
    setupStyleListeners();
    
    // Fix Blank Map
    setTimeout(() => { markerMap.invalidateSize(); }, 300);
}

// --- 4. HELPER MEMBERSIHKAN PETA (PENTING) ---
function cleanMapContainer() {
    // Hapus map instance global manapun yang sedang aktif di div 'map_style_preview'
    if (styleMap) { styleMap.off(); styleMap.remove(); styleMap = null; }
    if (markerMap) { markerMap.off(); markerMap.remove(); markerMap = null; }
}

// --- 5. REFRESHER & LISTENERS ---
function refreshPreview() {
    const style = {
        color: document.getElementById('style_color').value,
        weight: parseInt(document.getElementById('style_weight').value),
        opacity: parseFloat(document.getElementById('style_opacity').value),
        fillColor: document.getElementById('style_fillColor').value,
        fillOpacity: parseFloat(document.getElementById('style_fillOpacity').value),
        dashArray: document.getElementById('style_dashArray').value,
        marker_type: document.getElementById('style_marker_type').value,
        marker_icon: document.getElementById('style_icon_url') ? document.getElementById('style_icon_url').value : ''
    };

    // Router lagi saat ada perubahan input
    if (currentGrupType === 'Point') {
        initMarkerPreviewMap(style);
    } else {
        initStyleMap(style, currentGrupType);
    }
}

function updateMapStyle() {
    // Fungsi ringan: Hanya update CSS style tanpa gambar ulang peta
    // Cocok untuk Slider Opacity / Color Picker
    if(previewLayer && typeof previewLayer.setStyle === 'function') {
        previewLayer.setStyle({
            color: document.getElementById('style_color').value,
            weight: parseInt(document.getElementById('style_weight').value),
            opacity: parseFloat(document.getElementById('style_opacity').value),
            fillColor: document.getElementById('style_fillColor').value,
            fillOpacity: parseFloat(document.getElementById('style_fillOpacity').value),
            dashArray: document.getElementById('style_dashArray').value
        });
    }
}

function setupStyleListeners() {
    // 1. Listener Input Standar
    ['style_color', 'style_weight', 'style_opacity', 'style_fillColor', 'style_fillOpacity', 'style_dashArray'].forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            const newEl = el.cloneNode(true); // Bersihkan listener lama
            el.parentNode.replaceChild(newEl, el);
            
            newEl.addEventListener('input', () => {
                // Jika Circle atau Poly/Line, pakai update ringan
                const mType = document.getElementById('style_marker_type').value;
                if (currentGrupType !== 'Point' || mType === 'circle') {
                    updateMapStyle();
                } else {
                    refreshPreview(); // Icon butuh refresh total
                }
            });
        }
    });

    // 2. Listener Khusus Point (Marker Type)
    const elType = document.getElementById('style_marker_type');
    if(elType) {
        const newElType = elType.cloneNode(true);
        elType.parentNode.replaceChild(newElType, elType);
        newElType.addEventListener('change', () => {
            toggleMarkerOptions();
            refreshPreview();
        });
    }

    // 3. Listener File Upload
    const elFile = document.getElementById('style_icon_file');
    if(elFile) {
        const newElFile = elFile.cloneNode(true);
        elFile.parentNode.replaceChild(newElFile, elFile);
        newElFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                tempIconUrl = URL.createObjectURL(file);
                refreshPreview();
            }
        });
    }
    
    // 4. Listener URL
    const elUrl = document.getElementById('style_icon_url');
    if(elUrl) {
        const newElUrl = elUrl.cloneNode(true);
        elUrl.parentNode.replaceChild(newElUrl, elUrl);
        newElUrl.addEventListener('input', refreshPreview);
    }
}

// UI HELPER
function toggleMarkerOptions() {
    const type = currentGrupType;
    const markerType = document.getElementById('style_marker_type').value;

    const contStandard = document.getElementById('standard_style_container');
    const inputUrl = document.getElementById('input_icon_url');
    const inputFile = document.getElementById('input_icon_file');
    const elFill = document.getElementById('cont_fillColor');
    const elFillOp = document.getElementById('cont_fillOpacity');
    const elDash = document.getElementById('cont_dashArray');
    const pointOptions = document.getElementById('point_style_options');

    // Reset visibility
    inputUrl.classList.add('d-none');
    inputFile.classList.add('d-none');
    contStandard.classList.remove('d-none');
    pointOptions.classList.add('d-none');

    if (type === 'Point') {
        pointOptions.classList.remove('d-none');
        elDash.classList.add('d-none');

        if (markerType === 'circle') {
            elFill.classList.remove('d-none');
            elFillOp.classList.remove('d-none');
        } else if (markerType === 'pin') {
            contStandard.classList.add('d-none');
        } else if (markerType === 'icon_url') {
            inputUrl.classList.remove('d-none');
            contStandard.classList.add('d-none');
        } else if (markerType === 'icon_file') {
            inputFile.classList.remove('d-none');
            contStandard.classList.add('d-none');
        }
    } else if (type === 'Line') {
        elFill.classList.add('d-none');
        elFillOp.classList.add('d-none');
        elDash.classList.remove('d-none');
    } else {
        // Polygon
        elFill.classList.remove('d-none');
        elFillOp.classList.remove('d-none');
        elDash.classList.remove('d-none');
    }
}

function addTemplateRow(val = '') {
    const div = document.createElement('div');
    div.className = 'input-group mb-2 shadow-sm';
    div.innerHTML = `<span class="input-group-text bg-white"><i class="fas fa-tag text-muted"></i></span><input type="text" name="template_attr[]" class="form-control form-control-sm" value="${val}"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>`;
    document.getElementById('template_container').appendChild(div);
}

function initStyleMap(style, type) {
    if (typeof styleMap !== 'undefined') { styleMap.remove(); }
    styleMap = L.map('map_style_preview', { zoomControl: false, attributionControl: false }).setView(DEFAULT_COORD, 13);
    L.tileLayer(GOOGLE_SAT_URL).addTo(styleMap);
    
    if (type === 'Line') {
        const coords = [[DEFAULT_COORD[0], DEFAULT_COORD[1]-0.02], [DEFAULT_COORD[0]+0.01, DEFAULT_COORD[1]], [DEFAULT_COORD[0], DEFAULT_COORD[1]+0.02]];
        styleLayer = L.polyline(coords, style).addTo(styleMap);
    } else if (type === 'Point') {
        styleLayer = L.circleMarker(DEFAULT_COORD, { ...style, radius: 10 }).addTo(styleMap);
    } else {
        const coords = [[DEFAULT_COORD[0]+0.01, DEFAULT_COORD[1]-0.01], [DEFAULT_COORD[0]-0.01, DEFAULT_COORD[1]+0.01], [DEFAULT_COORD[0]-0.01, DEFAULT_COORD[1]-0.02]];
        styleLayer = L.polygon(coords, style).addTo(styleMap);
    }
    if(type !== 'Point') styleMap.fitBounds(styleLayer.getBounds(), { padding: [20, 20] });

    ['style_color', 'style_weight', 'style_opacity', 'style_fillColor', 'style_fillOpacity', 'style_dashArray'].forEach(id => {
        document.getElementById(id).oninput = function() {
            styleLayer.setStyle({
                color: document.getElementById('style_color').value,
                weight: document.getElementById('style_weight').value,
                opacity: document.getElementById('style_opacity').value,
                fillColor: document.getElementById('style_fillColor').value,
                fillOpacity: document.getElementById('style_fillOpacity').value,
                dashArray: document.getElementById('style_dashArray').value
            });
        };
    });
}

// --- BAGIAN 2: LOGIKA POLIGON ---
function openAddPolygon(grupId, grupData) { 
    preparePolygonModal(grupId, grupData); 
}
function openEditPolygon(item, grupData, btn) {
    if(btn) btn.style.pointerEvents = 'none';
    fetch(`<?= base_url('geospasial/getDetail/polygon') ?>/${item.id}`, { headers: { "X-Requested-With": "XMLHttpRequest" } })
    .then(r => r.json())
    .then(data => { preparePolygonModal(data.id_dg, grupData, data); })
    .finally(() => { if(btn) btn.style.pointerEvents = 'auto'; });
}

function preparePolygonModal(grupId, grupData, data = null) {
    const modalEl = document.getElementById('modalDataPolygon');
    document.getElementById('formPolygonData').reset();
    document.getElementById('attribute_container').innerHTML = '';
    document.getElementById('existing_files_container').innerHTML = '';
    document.getElementById('poly_id_grup').value = grupId;
    currentGroupStyle = { color:grupData.color, weight:grupData.weight, opacity:grupData.opacity, fillColor:grupData.fillColor, fillOpacity:grupData.fillOpacity, dashArray:grupData.dashArray };

    if (data) {
        document.getElementById('poly_id').value = data.id;
        document.getElementById('poly_nama').value = data.nama_dg;
        document.getElementById('poly_geojson').value = data.data_geospasial;
        if (data.daftar_pdf) {
            data.daftar_pdf.forEach(pdf => {
                const d = document.createElement('div');
                d.className = 'list-group-item d-flex justify-content-between align-items-center py-2';
                d.id = `pdf-item-${pdf.id}`;
                d.innerHTML = `<div class="text-truncate" style="max-width:80%"><i class="fas fa-file-pdf text-danger me-2"></i><small>${pdf.judul_pdf}</small></div><button type="button" class="btn btn-xs btn-link text-danger" onclick="deletePdfItem(${pdf.id})"><i class="fas fa-times-circle"></i></button>`;
                document.getElementById('existing_files_container').appendChild(d);
            });
        }
        try { JSON.parse(data.atribut_tambahan).forEach(x => addAttributeRow(x.label, x.value)); } catch(e){}
    } else {
        document.getElementById('poly_id').value = '';
        try { JSON.parse(grupData.atribut_default).forEach(x => addAttributeRow(x.label, '')); } catch(e){ addAttributeRow(); }
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    modalEl.addEventListener('shown.bs.modal', () => initDrawMap(data ? data.data_geospasial : null), { once: true });
}

function initDrawMap(jsonStr) {
    if (drawMap) drawMap.remove();
    drawMap = L.map('map_draw_polygon').setView(DEFAULT_COORD, 14);
    L.tileLayer(GOOGLE_SAT_URL).addTo(drawMap); // Menggunakan konstanta yang sudah ada
    
    markers = []; 
    drawLayer = null;
    
    // Pisahkan logic load data agar lebih rapi
    if (jsonStr) loadGeoJSON(jsonStr);
    
    drawMap.on('click', e => addMarker(e.latlng));
}

function loadGeoJSON(str) {
    if (!str || str === "null" || str === "") return;

    try {
        const geojsonData = JSON.parse(str);
        const l = L.geoJSON(geojsonData);
        
        // Bersihkan marker lama
        markers.forEach(m => drawMap.removeLayer(m));
        markers = [];

        const allLayers = l.getLayers();
        if (allLayers.length > 0) {
            const firstLayer = allLayers[0];
            let coords = [];

            // Logic ekstraksi koordinat yang mampu menangani Nested Array GeoJSON
            if (typeof firstLayer.getLatLngs === 'function') {
                let latlngs = firstLayer.getLatLngs();
                // Ratakan array sampai ketemu object LatLng (penting untuk MultiPolygon)
                while (Array.isArray(latlngs) && latlngs.length > 0 && Array.isArray(latlngs[0])) {
                    latlngs = latlngs[0];
                }
                coords = latlngs;
            } else if (typeof firstLayer.getLatLng === 'function') {
                coords = [firstLayer.getLatLng()];
            }

            // Render Marker Edit
            if (Array.isArray(coords)) {
                coords.forEach(c => {
                    if (c && typeof c === 'object') addMarker(c);
                });
                
                // Zoom ke area data
                if (markers.length > 0) {
                    drawMap.fitBounds(l.getBounds(), { padding: [30, 30] });
                }
            }
        }
    } catch (e) {
        console.error("GeoJSON Parse Error:", e);
    }
}

function addMarker(latlng) {
    if (!latlng) return;
    const m = L.marker(latlng, { draggable: true }).addTo(drawMap);
    m.on('drag', updatePoly);
    m.on('click', () => { 
        drawMap.removeLayer(m); 
        markers = markers.filter(x => x !== m); 
        updatePoly(); 
    });
    markers.push(m); 
    updatePoly();
}

function updatePoly() {
    const pts = markers.map(m => m.getLatLng());
    if (drawLayer) drawMap.removeLayer(drawLayer);
    
    if (pts.length >= 3) {
        // Render polygon baru berdasarkan posisi marker
        drawLayer = L.polygon(pts, currentGroupStyle).addTo(drawMap);
        document.getElementById('poly_geojson').value = JSON.stringify(drawLayer.toGeoJSON());
    } else {
        document.getElementById('poly_geojson').value = '';
    }
}

function clearMapDraw() { 
    markers.forEach(m => drawMap.removeLayer(m)); 
    markers = []; 
    if(drawLayer) drawMap.removeLayer(drawLayer); 
    document.getElementById('poly_geojson').value = ''; 
}

// --- BAGIAN 3: LOGIKA LINE (FIXED) ---

function openAddLine(grupId, grupData) {
    document.getElementById('line_id').value = '';
    document.getElementById('line_id_grup').value = grupId;
    document.getElementById('line_nama').value = '';
    document.getElementById('line_geojson').value = '';
    document.getElementById('line_pdf_list').innerHTML = '';
    document.getElementById('line_attr_area').innerHTML = '';

    // FIX: Load Template Atribut
    try {
        if(grupData.atribut_default) {
            JSON.parse(grupData.atribut_default).forEach(x => addAttrRow('line', x.label, ''));
        } else { addAttrRow('line'); }
    } catch(e) { addAttrRow('line'); }

    // Style
    currentGroupStyle = { color: grupData.color, weight: grupData.weight, dashArray: grupData.dashArray };

    var myModal = new bootstrap.Modal(document.getElementById('modalDataLine'));
    myModal.show();
    
    // Resize map saat modal muncul
    document.getElementById('modalDataLine').addEventListener('shown.bs.modal', function(){
        initDrawLineMap(); // Panggil init map khusus line
    }, {once:true});
}

function openEditLine(item, grupData) {
    // Ambil data detail via AJAX
    fetch('<?= base_url("geospasial/getDetail/line") ?>/' + item.id, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(r => r.json())
    .then(data => {
        // 1. Isi Form Input
        document.getElementById('line_id').value = data.id;
        document.getElementById('line_id_grup').value = grupData.id_dg;
        document.getElementById('line_nama').value = data.nama_dg;
        
        // 2. Load Daftar PDF
        const pdfContainer = document.getElementById('line_pdf_list');
        pdfContainer.innerHTML = '';
        if(data.daftar_pdf) {
            data.daftar_pdf.forEach(pdf => {
                pdfContainer.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1" id="pdf-item-${pdf.id}">
                        <a href="<?= base_url('uploads/pdf') ?>/${pdf.file_path}" target="_blank" class="small text-decoration-none">
                            <i class="fas fa-file-pdf text-danger"></i> ${pdf.judul_pdf}
                        </a>
                        <button type="button" class="btn btn-xs text-danger" onclick="deletePdfItem(${pdf.id})">&times;</button>
                    </div>`;
            });
        }

        // 3. Load Atribut Tambahan
        document.getElementById('line_attr_area').innerHTML = '';
        try { JSON.parse(data.atribut_tambahan).forEach(x => addAttrRow('line', x.label, x.value)); } catch(e) {}

        // 4. Set Style Global
        currentGroupStyle = { color: grupData.color, weight: grupData.weight, dashArray: grupData.dashArray };

        // 5. TAMPILKAN MODAL DULU
        var myModal = new bootstrap.Modal(document.getElementById('modalDataLine'));
        myModal.show();
        
        // 6. SETELAH MODAL MUNCUL, BARU INIT PETA & GAMBAR DATA
        // Ini solusi error "undefined reading addLayer"
        document.getElementById('modalDataLine').addEventListener('shown.bs.modal', function(){
            // Kirim data GeoJSON ke fungsi init map
            initDrawLineMap(data.data_geospasial); 
        }, {once:true});
    });
}

// Map Engine Khusus Line (Google Sat)
function initDrawLineMap(jsonStr = null) {
    // 1. Hapus map lama jika ada agar tidak memory leak/double
    if (mapLine) {
        mapLine.off();
        mapLine.remove();
    }

    // 2. Buat Instance Peta Baru
    mapLine = L.map('map_draw_line').setView(DEFAULT_COORD, 13);
    L.tileLayer(GOOGLE_SAT_URL).addTo(mapLine); 

    // 3. Reset Variabel Marker Global
    lineMarkers = [];
    drawLineLayer = null;

    // 4. Jika ada data GeoJSON (Mode Edit), Parsing disini
    if(jsonStr) {
        try {
            const geo = JSON.parse(jsonStr);
            const layer = L.geoJSON(geo).getLayers()[0];
            const latlngs = layer.getLatLngs();
            
            // --- LOGIKA FLAT MARKER (Support MultiLineString) ---
            // Cek apakah array bersarang (MultiLineString: [[lat,lng], [lat,lng]])
            if (Array.isArray(latlngs) && latlngs.length > 0 && Array.isArray(latlngs[0])) {
                // Ratakan arraynya
                latlngs.forEach(segment => {
                    segment.forEach(ll => addLineMarker(ll)); // Reuse fungsi addLineMarker
                });
            } else {
                // LineString Biasa: [lat,lng]
                latlngs.forEach(ll => addLineMarker(ll));
            }

            // Zoom ke area data
            mapLine.fitBounds(layer.getBounds());
        } catch(e){ console.error("Gagal load geojson line", e); }
    }

    // 5. Aktifkan Klik Peta untuk nambah titik baru
    mapLine.on('click', function(e) { addLineMarker(e.latlng); });
}

function addLineMarker(latlng) {
    // Pastikan mapLine sudah ada
    if(!mapLine) return; 

    var m = L.marker(latlng, {draggable: true}).addTo(mapLine);
    lineMarkers.push(m);
    
    // --- FITUR BARU: KLIK KANAN UNTUK MENUTUP LINE ---
    m.on('contextmenu', function(e) {
        // 1. Ambil koordinat marker yang diklik kanan
        var targetLatLng = this.getLatLng();
        
        // 2. Tambahkan titik baru di posisi tersebut (Menyambung garis ke titik ini)
        addLineMarker(targetLatLng);
        
        // 3. Opsional: Beri notifikasi kecil atau console log
        console.log("Line ditutup/disambung ke titik ini.");
    });
    // --------------------------------------------------

    updateLineGeoJSON();
    
    // Event Drag (Geser Titik)
    m.on('drag', updateLineGeoJSON);
    
    // Event Klik Kiri (Hapus Titik)
    m.on('click', function() { 
        mapLine.removeLayer(this);
        lineMarkers = lineMarkers.filter(item => item !== this);
        updateLineGeoJSON();
    });
}

function updateLineGeoJSON() {
    if (drawLineLayer) mapLine.removeLayer(drawLineLayer);
    
    var latlngs = lineMarkers.map(m => m.getLatLng());
    if (latlngs.length > 1) {
        drawLineLayer = L.polyline(latlngs, currentGroupStyle).addTo(mapLine);
        document.getElementById('line_geojson').value = JSON.stringify(drawLineLayer.toGeoJSON());
    } else {
        document.getElementById('line_geojson').value = '';
    }
}

function updateLineGeoJSON() {
    if (drawLineLayer) mapLine.removeLayer(drawLineLayer);
    var latlngs = lineMarkers.map(m => m.getLatLng());
    if (latlngs.length > 1) {
        drawLineLayer = L.polyline(latlngs, currentGroupStyle).addTo(mapLine);
        document.getElementById('line_geojson').value = JSON.stringify(drawLineLayer.toGeoJSON());
    } else {
        document.getElementById('line_geojson').value = '';
    }
}


// --- BAGIAN 4: LOGIKA POINT (FIXED) ---

function openAddPoint(grupId, grupData) {
    document.getElementById('point_id').value = '';
    document.getElementById('point_id_grup').value = grupId;
    document.getElementById('point_nama').value = '';
    document.getElementById('point_geojson').value = '';
    document.getElementById('point_pdf_list').innerHTML = '';
    document.getElementById('point_attr_area').innerHTML = '';

    // FIX: Load Template Atribut
    try {
        if(grupData.atribut_default) {
            JSON.parse(grupData.atribut_default).forEach(x => addAttrRow('point', x.label, ''));
        } else { addAttrRow('point'); }
    } catch(e) { addAttrRow('point'); }

    var myModal = new bootstrap.Modal(document.getElementById('modalDataPoint'));
    myModal.show();

    document.getElementById('modalDataPoint').addEventListener('shown.bs.modal', function(){
        initDrawPointMap();
    }, {once:true});
}

function openEditPoint(item, grupData) {
    const btn = document.activeElement; 
    if(btn && btn.tagName === 'BUTTON') btn.style.pointerEvents = 'none';

    fetch('<?= base_url("geospasial/getDetail/point") ?>/' + item.id, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(r => r.json())
    .then(data => {
        // --- 1. ISI FORM ---
        document.getElementById('point_id').value = data.id;
        document.getElementById('point_id_grup').value = grupData.id_dg;
        document.getElementById('point_nama').value = data.nama_dg;

        // --- 2. ISI PDF ---
        const pdfContainer = document.getElementById('point_pdf_list');
        pdfContainer.innerHTML = '';
        if(data.daftar_pdf) {
            data.daftar_pdf.forEach(pdf => {
                pdfContainer.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1" id="pdf-item-${pdf.id}">
                        <a href="<?= base_url('uploads/pdf') ?>/${pdf.file_path}" target="_blank" class="small text-decoration-none">
                            <i class="fas fa-file-pdf text-danger"></i> ${pdf.judul_pdf}
                        </a>
                        <button type="button" class="btn btn-xs text-danger" onclick="deletePdfItem(${pdf.id})">&times;</button>
                    </div>`;
            });
        }

        // --- 3. ISI ATRIBUT ---
        document.getElementById('point_attr_area').innerHTML = '';
        try { JSON.parse(data.atribut_tambahan).forEach(x => addAttrRow('point', x.label, x.value)); } catch(e) {}

        // --- 4. TAMPILKAN MODAL ---
        const modalEl = document.getElementById('modalDataPoint');
        const myModal = new bootstrap.Modal(modalEl);
        myModal.show();

        // --- 5. RENDER PETA (SOLUSI FIX) ---
        // Definisi fungsi handler agar bisa dihapus
        const handleShown = function() {
            initDrawPointMap(data.data_geospasial); // Init Peta
            modalEl.removeEventListener('shown.bs.modal', handleShown); // Hapus listener segera
        };
        
        // Pasang listener
        modalEl.addEventListener('shown.bs.modal', handleShown);

    })
    .catch(err => {
        console.error("Error:", err);
        alert("Gagal memuat data titik.");
    })
    .finally(() => {
        if(btn && btn.tagName === 'BUTTON') btn.style.pointerEvents = 'auto';
    });
}

// Map Engine Khusus Point (Google Sat)
function initDrawPointMap(jsonStr = null) {
    // 1. Reset Peta Lama (PENTING)
    if (typeof mapPoint !== 'undefined' && mapPoint !== null) {
        mapPoint.off();
        mapPoint.remove();
        mapPoint = null;
    }

    // 2. Cek Container
    const container = document.getElementById('map_draw_point');
    if(!container) return;

    // 3. Buat Instance Peta Baru
    mapPoint = L.map('map_draw_point', {
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer(GOOGLE_SAT_URL).addTo(mapPoint);
    
    // 4. Reset Marker Global
    drawPointLayer = null;

    // 5. Parsing Data GeoJSON (Jika Edit Mode)
    let initialLat = DEFAULT_COORD[0];
    let initialLng = DEFAULT_COORD[1];
    let hasData = false;

    if(jsonStr && jsonStr !== "null" && jsonStr !== "") {
        try {
            const geo = JSON.parse(jsonStr);
            // Handle struktur: FeatureCollection, Feature, atau Geometry
            let coords = null;

            if (geo.type === 'FeatureCollection' && geo.features.length > 0) {
                const ft = geo.features.find(f => f.geometry.type === 'Point');
                if(ft) coords = ft.geometry.coordinates;
            } 
            else if (geo.type === 'Feature' && geo.geometry.type === 'Point') {
                coords = geo.geometry.coordinates;
            }
            else if (geo.type === 'Point') {
                coords = geo.coordinates; // Raw geometry
            }
            else if (geo.geometry && geo.geometry.coordinates) {
                coords = geo.geometry.coordinates; // Struktur simple
            }

            if(coords) {
                // GeoJSON = [Lng, Lat]
                initialLng = coords[0];
                initialLat = coords[1];
                hasData = true;
            }
        } catch(e) { console.error("Error GeoJSON Parsing", e); }
    }

    // 6. FIX TAMPILAN (ANTI BLANK)
    setTimeout(() => {
        if(mapPoint) {
            mapPoint.invalidateSize(); // Paksa hitung ulang ukuran

            if (hasData) {
                mapPoint.setView([initialLat, initialLng], 18);
                setPointMarker([initialLat, initialLng]);
            } else {
                mapPoint.setView(DEFAULT_COORD, 13);
            }
        }
    }, 300); // Jeda 300ms agar modal stabil

    // 7. Listener Klik Peta
    mapPoint.on('click', function(e) { 
        setPointMarker(e.latlng); 
    });
}

// --- HELPER IMPORT/RESET KHUSUS LINE & POINT ---

function resetMapLine() {
    if(drawLineLayer) mapLine.removeLayer(drawLineLayer);
    lineMarkers.forEach(m => mapLine.removeLayer(m));
    lineMarkers = [];
    document.getElementById('line_geojson').value = '';
}

function handleFileUploadLine(input) {
    const f = input.files[0]; if(!f) return;
    const r = new FileReader();
    
    r.onload = e => { 
        try { 
            resetMapLine(); // Hapus marker lama
            
            const geo = JSON.parse(e.target.result);
            const layer = L.geoJSON(geo);
            const allLayers = layer.getLayers();

            if (allLayers.length > 0) {
                // Ambil layer pertama
                const firstLayer = allLayers[0];
                let latlngs = firstLayer.getLatLngs();

                // --- LOGIKA PENANGANAN MULTILINESTRING ---
                // Cek apakah ini array bersarang (MultiLineString) atau array datar (LineString)
                if (Array.isArray(latlngs) && latlngs.length > 0 && Array.isArray(latlngs[0])) {
                    // Jika MultiLineString: [[lat,lng], [lat,lng]], kita ratakan jadi satu garis panjang
                    latlngs.forEach(segment => {
                        segment.forEach(coord => addLineMarker(coord));
                    });
                } else {
                    // Jika LineString biasa: [lat,lng]
                    latlngs.forEach(coord => addLineMarker(coord));
                }
                
                updateLineGeoJSON();
                mapLine.fitBounds(layer.getBounds());
            }
        } catch(err) { 
            console.error(err);
            alert('GeoJSON Line Tidak Valid atau Format Tidak Didukung'); 
        } 
    };
    r.readAsText(f); input.value = '';
}

function setPointMarker(latlng) {
    if (!mapPoint) return;

    // Hapus marker lama
    if (drawPointLayer) {
        mapPoint.removeLayer(drawPointLayer);
    }

    // Pasang marker baru (latlng bisa berupa array [lat, lng] atau object {lat:.., lng:..})
    drawPointLayer = L.marker(latlng, {draggable: true}).addTo(mapPoint);
    
    // Simpan ke Input Hidden (Format GeoJSON standar)
    const geoJsonData = drawPointLayer.toGeoJSON();
    document.getElementById('point_geojson').value = JSON.stringify(geoJsonData);
    
    // Listener Drag
    drawPointLayer.on('dragend', function(e) {
        document.getElementById('point_geojson').value = JSON.stringify(e.target.toGeoJSON());
    });
}

function handleFileUploadPoint(input) {
    const f = input.files[0]; 
    if(!f) return;

    const r = new FileReader();
    r.onload = e => { 
        try { 
            const geo = JSON.parse(e.target.result);
            let lat, lng;

            // --- LOGIKA DETEKSI POINT (FeatureCollection Support) ---
            
            // Kasus 1: FeatureCollection (Data Anda masuk sini)
            if (geo.type === 'FeatureCollection' && geo.features && geo.features.length > 0) {
                // Cari feature pertama yg tipenya Point
                const pointFeature = geo.features.find(ft => ft.geometry && ft.geometry.type === 'Point');
                if (pointFeature) {
                    lng = pointFeature.geometry.coordinates[0];
                    lat = pointFeature.geometry.coordinates[1];
                }
            } 
            // Kasus 2: Single Feature
            else if (geo.type === 'Feature' && geo.geometry && geo.geometry.type === 'Point') {
                lng = geo.geometry.coordinates[0];
                lat = geo.geometry.coordinates[1];
            }
            // Kasus 3: Raw Geometry
            else if (geo.type === 'Point' && geo.coordinates) {
                lng = geo.coordinates[0];
                lat = geo.coordinates[1];
            }

            // --- EKSEKUSI ---
            if (lat !== undefined && lng !== undefined) {
                if (!mapPoint) {
                    alert("Peta belum siap. Coba ulangi setelah peta muncul.");
                    return;
                }

                // Leaflet butuh [Lat, Lng]
                const newLatLng = [lat, lng];
                
                // Update Marker & Zoom
                setPointMarker(newLatLng); 
                mapPoint.setView(newLatLng, 18);
                
                // Opsional: Ambil properti nama jika ada
                // (Anda bisa mengembangkan ini untuk auto-fill input nama)
                
            } else {
                alert('Format GeoJSON valid, tapi tidak ditemukan koordinat Point di dalamnya.');
            }

        } catch(err) { 
            console.error(err);
            alert('File GeoJSON rusak atau format tidak dikenali.'); 
        } 
    };
    
    r.readAsText(f); 
    input.value = ''; // Reset input file
}

// --- FUNGSI EXPORT GEOJSON (FIXED) ---
function doExportAJAX(idGrup, namaGrup, btn) {
    // 1. Simpan state tombol asli (Text dan Title)
    const originalContent = btn.innerHTML;
    const originalTitle = btn.title || ''; // Handle jika title kosong
    
    // 2. Ubah tampilan tombol jadi loading
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin text-success"></i>`;
    btn.title = "Sedang memproses data...";

    // 3. Request ke Controller
    // Pastikan URL base_url benar
    fetch(`<?= base_url('geospasial/exportGeoJSON') ?>/${idGrup}`)
        .then(response => {
            // Cek status HTTP
            if (response.ok) { // response.ok mencakup status 200-299
                return response.blob();
            } else {
                // Jika server error (500) atau not found (404)
                throw new Error(`Gagal mengambil data. Status: ${response.status}`);
            }
        })
        .then(blob => {
            // Cek ukuran file, jika 0 bytes berarti gagal generate
            if (blob.size === 0) {
                throw new Error("File GeoJSON kosong. Mungkin tidak ada data dalam grup ini.");
            }

            // 4. Buat link download virtual
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            
            // Format nama file agar aman dari karakter aneh
            const cleanName = namaGrup.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            a.download = `${cleanName}_export.geojson`;
            
            document.body.appendChild(a);
            a.click();
            
            // Cleanup
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Export Error:', error);
            // Tampilkan pesan error yang lebih spesifik
            alert("Gagal export data! Silakan cek Console (F12) untuk detail error.\n\nKemungkinan: Data Poligon terlalu besar atau Server Timeout.");
        })
        .finally(() => {
            // 5. Kembalikan tombol seperti semula
            btn.disabled = false;
            btn.innerHTML = originalContent;
            btn.title = originalTitle;
        });
}

// --- FILE UPLOAD HANDLER ---
document.getElementById('formImportGrup')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitImport');
    const progressBar = document.getElementById('importProgressBar');
    const statusText = document.getElementById('importStatusText');
    const progressCont = document.getElementById('importProgressContainer');

    btn.disabled = true;
    progressCont.classList.remove('d-none');

    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerHTML = percent + '%';
            statusText.innerText = percent < 100 ? 'Mengunggah...' : 'Memproses database...';
        }
    });
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            const res = JSON.parse(xhr.responseText);
            if(res.status === 'success') {
                alert('Berhasil!'); window.location.reload();
            } else {
                alert('Gagal: ' + res.message); btn.disabled = false;
            }
        }
    };
    xhr.open('POST', '<?= base_url('geospasial/importGeoJSONGrup') ?>', true);
    xhr.send(new FormData(this));
});

function handleFileUpload(input) {
    const f = input.files[0]; if(!f) return;
    const r = new FileReader();
    r.onload = e => { 
        try { 
            clearMapDraw(); 
            loadGeoJSON(e.target.result); // Menggunakan fungsi loadGeoJSON yang baru
        } catch(err){ 
            alert('GeoJSON Tidak Valid'); 
        } 
    };
    r.readAsText(f); 
    input.value = '';
}

function analyzeGeoJSON(input) {
    const f = input.files[0]; if (!f) return;
    const r = new FileReader();
    r.onload = function(e) {
        try {
            const json = JSON.parse(e.target.result);
            const selectMap = document.getElementById('column_name_map');
            if (json.features && json.features.length > 0) {
                const keys = Object.keys(json.features[0].properties);
                selectMap.innerHTML = '<option value="">-- Gunakan Nama Default --</option>';
                keys.forEach(k => {
                    selectMap.innerHTML += `<option value="${k}">${k} (Contoh: ${json.features[0].properties[k]})</option>`;
                });
                document.getElementById('column_mapping_container').classList.remove('d-none');
            }
        } catch (err) {}
    };
    r.readAsText(f);
}

// ==========================================================
// LOGIKA IMPORT GRUP (UPDATE SUPPORT LINE & POINT)
// ==========================================================

// Menerima parameter 'type' dari tombol HTML
function openImportGrupModal(type = 'Polygon') {
    const modalEl = document.getElementById('modalImportGrup');
    const modal = new bootstrap.Modal(modalEl);
    
    // Simpan tipe yang sedang aktif
    currentImportType = type;
    
    // Update Judul Modal agar user yakin
    const titleEl = modalEl.querySelector('.modal-title');
    if(titleEl) titleEl.innerText = `Import GeoJSON Grup (${type})`;

    // Reset Form
    document.getElementById('formImportGrup').reset();
    document.getElementById('importProgressContainer').classList.add('d-none');
    document.getElementById('column_mapping_container').classList.add('d-none');
    
    // Tambahkan input hidden untuk mengirim tipe ke controller (Opsional, untuk memaksa tipe)
    // Tapi Controller Anda sudah auto-detect, jadi kita fokus ke Visual Preview saja.
    
    modal.show();

    // Jalankan preview peta saat modal terbuka
    modalEl.addEventListener('shown.bs.modal', function () {
        const initialStyle = {
            color: '#4f46e5', weight: 2, opacity: 1,
            fillColor: '#4f46e5', fillOpacity: 0.2, dashArray: ''
        };
        // Kirim tipe ke fungsi map
        initImportStyleMap(initialStyle, currentImportType); 
    }, { once: true });
}

function initImportStyleMap(style, type) {
    if (importStyleMap) {
        importStyleMap.off();
        importStyleMap.remove();
    }
    
    importStyleMap = L.map('map_import_preview', { zoomControl: false, attributionControl: false }).setView(DEFAULT_COORD, 13);
    L.tileLayer(GOOGLE_SAT_URL).addTo(importStyleMap);
    
    // GAMBAR PREVIEW SESUAI TIPE
    if (type === 'Line') {
        // Gambar GARIS Zig-zag
        const coords = [
            [DEFAULT_COORD[0], DEFAULT_COORD[1]-0.02], 
            [DEFAULT_COORD[0]+0.01, DEFAULT_COORD[1]], 
            [DEFAULT_COORD[0], DEFAULT_COORD[1]+0.02]
        ];
        importStyleLayer = L.polyline(coords, style).addTo(importStyleMap);
        
    } else if (type === 'Point') {
        // Gambar TITIK
        importStyleLayer = L.circleMarker(DEFAULT_COORD, { ...style, radius: 10 }).addTo(importStyleMap);
        
    } else {
        // Gambar POLIGON (Segitiga)
        const coords = [
            [DEFAULT_COORD[0] + 0.005, DEFAULT_COORD[1] - 0.005],
            [DEFAULT_COORD[0] - 0.005, DEFAULT_COORD[1] + 0.005],
            [DEFAULT_COORD[0] - 0.005, DEFAULT_COORD[1] - 0.005]
        ];
        importStyleLayer = L.polygon(coords, style).addTo(importStyleMap);
    }
    
    if (type !== 'Point') {
        importStyleMap.fitBounds(importStyleLayer.getBounds(), { padding: [20, 20] });
    } else {
        importStyleMap.setView(DEFAULT_COORD, 15);
    }

    // Listener Input (Sama seperti sebelumnya)
    const ids = ['import_style_color', 'import_style_weight', 'import_style_opacity', 'import_style_fillColor', 'import_style_fillOpacity', 'import_style_dashArray'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            const newEl = el.cloneNode(true);
            el.parentNode.replaceChild(newEl, el);
            newEl.addEventListener('input', () => {
                importStyleLayer.setStyle({
                    color: document.getElementById('import_style_color').value,
                    weight: document.getElementById('import_style_weight').value,
                    opacity: document.getElementById('import_style_opacity').value,
                    fillColor: document.getElementById('import_style_fillColor').value,
                    fillOpacity: document.getElementById('import_style_fillOpacity').value,
                    dashArray: document.getElementById('import_style_dashArray').value
                });
            });
        }
    });
    
    // Sembunyikan Style Fill jika Line
    const elFill = document.getElementById('import_style_fillColor');
    const elOpac = document.getElementById('import_style_fillOpacity');
    if (elFill && elOpac) {
        const fc = elFill.closest('.col-6');
        const foc = elOpac.closest('.col-6');
        if(fc) fc.style.display = (type === 'Line') ? 'none' : 'block';
        if(foc) foc.style.display = (type === 'Line') ? 'none' : 'block';
    }
}

function setImportDashPreset(val) {
    const el = document.getElementById('import_style_dashArray');
    el.value = val;
    el.dispatchEvent(new Event('input'));
}

// Helper untuk Dash Array di Modal Edit Grup Biasa
function setDashPreset(val) {
    const input = document.getElementById('style_dashArray');
    if(input) {
        input.value = val;
        input.dispatchEvent(new Event('input'));
    }
}

// ==========================================================
// LOGIKA IMPORT GRUP (TERPISAH & STABIL)
// ==========================================================

let importMap;       // Instance Map Leaflet
let importLayer;     // Layer Object (untuk update ringan)
let currentImportType = 'Polygon'; 
let tempImportIconUrl = null;

// 1. OPEN MODAL
function openImportGrupModal(type = 'Polygon') {
    const modalEl = document.getElementById('modalImportGrup');
    const modal = new bootstrap.Modal(modalEl);
    
    currentImportType = type;
    tempImportIconUrl = null;

    // UI Reset
    const titleEl = modalEl.querySelector('.modal-title');
    if(titleEl) titleEl.innerText = `Import GeoJSON Grup (${type})`;
    document.getElementById('formImportGrup').reset();
    document.getElementById('importProgressContainer').classList.add('d-none');
    document.getElementById('column_mapping_container').classList.add('d-none');
    
    // Set Marker Default
    if(document.getElementById('import_marker_type')) {
        document.getElementById('import_marker_type').value = 'circle';
    }

    // Toggle Input UI
    toggleImportUI();

    modal.show();

    // Render Peta saat modal muncul (Sekali saja)
    const onShown = function() {
        // Init Full Map (Reset Total)
        refreshImportPreview(true);
        modalEl.removeEventListener('shown.bs.modal', onShown);
    };
    modalEl.addEventListener('shown.bs.modal', onShown);
}

// 2. TOGGLE UI (HIDE/SHOW INPUT)
function toggleImportUI() {
    const type = currentImportType;
    const markerType = document.getElementById('import_marker_type').value;

    const contPoint     = document.getElementById('import_point_options');
    const contStd       = document.getElementById('import_standard_style');
    const inputUrl      = document.getElementById('cont_import_icon_url');
    const inputFile     = document.getElementById('cont_import_icon_file');
    
    // Style Elements
    const elFill   = document.getElementById('cont_imp_fill');
    const elFillOp = document.getElementById('cont_imp_fillOp');
    const elDash   = document.getElementById('cont_imp_dash');

    // Reset Display
    inputUrl.classList.add('d-none');
    inputFile.classList.add('d-none');
    contStd.classList.remove('d-none'); 
    contPoint.classList.add('d-none');

    if (type === 'Point') {
        contPoint.classList.remove('d-none'); 
        elDash.classList.add('d-none'); 

        if (markerType === 'circle') {
            elFill.classList.remove('d-none');
            elFillOp.classList.remove('d-none');
        } else if (markerType === 'pin') {
            contStd.classList.add('d-none'); 
        } else {
            // Icon URL/File
            contStd.classList.add('d-none'); 
            if (markerType === 'icon_url') inputUrl.classList.remove('d-none');
            if (markerType === 'icon_file') inputFile.classList.remove('d-none');
        }
    } else if (type === 'Line') {
        elFill.classList.add('d-none');
        elFillOp.classList.add('d-none');
        elDash.classList.remove('d-none');
    } else {
        // Polygon
        elFill.classList.remove('d-none');
        elFillOp.classList.remove('d-none');
        elDash.classList.remove('d-none');
    }
}

// 3. ROUTER UTAMA (VERSI CERDAS/OPTIMIZED)
function refreshImportPreview(forceReinit = false) {
    const style = getImportStyleValues(); // Helper ambil value input
    
    // --- LOGIKA POINT (MARKER) ---
    if (currentImportType === 'Point') {
        const markerType = document.getElementById('import_marker_type').value;
        
        // OPTIMASI: Jika tipe Circle dan map sudah ada, update ringan saja (biar slider smooth)
        if (!forceReinit && importMap && importLayer && markerType === 'circle' && importLayer instanceof L.CircleMarker) {
            importLayer.setStyle(style); // Update ringan (ganti warna/radius)
        } 
        else {
            // Jika ganti tipe (misal dari Circle ke Pin), atau Pin ke Icon, wajib Re-init
            initImportMarkerMap(); 
        }
    } 
    // --- LOGIKA LINE & POLYGON ---
    else {
        if (forceReinit || !importMap) {
            initImportPolyLineMap(); // Buat Baru (saat modal buka)
        } else {
            updateImportPolyLineStyle(); // Update Ringan (saat geser slider)
        }
    }
}

// 4. MAP ENGINE: POLYGON & LINE (STABIL & RINGAN)
function initImportPolyLineMap() {
    // Reset Total
    if (typeof importMap !== 'undefined' && importMap !== null) {
        importMap.off(); importMap.remove(); importMap = null;
    }
    
    if(!document.getElementById('map_import_preview')) return;

    importMap = L.map('map_import_preview', { zoomControl: false, attributionControl: false }).setView(DEFAULT_COORD, 13);
    L.tileLayer(GOOGLE_SAT_URL).addTo(importMap);
    
    // Ambil Style Awal
    const style = getImportStyleValues();

    if (currentImportType === 'Line') {
        const coords = [[DEFAULT_COORD[0], DEFAULT_COORD[1]-0.02], [DEFAULT_COORD[0]+0.01, DEFAULT_COORD[1]], [DEFAULT_COORD[0], DEFAULT_COORD[1]+0.02]];
        importLayer = L.polyline(coords, style).addTo(importMap);
    } else {
        // Polygon
        const coords = [[DEFAULT_COORD[0] + 0.005, DEFAULT_COORD[1] - 0.005], [DEFAULT_COORD[0] - 0.005, DEFAULT_COORD[1] + 0.005], [DEFAULT_COORD[0] - 0.005, DEFAULT_COORD[1] - 0.005]];
        importLayer = L.polygon(coords, style).addTo(importMap);
    }
    
    importMap.fitBounds(importLayer.getBounds(), { padding: [20, 20] });
    
    setupImportListeners(); 
    setTimeout(() => { importMap.invalidateSize(); }, 300);
}

// FUNGSI UPDATE RINGAN (FIX MASALAH SLIDER MACET)
function updateImportPolyLineStyle() {
    if(importLayer && typeof importLayer.setStyle === 'function') {
        const style = getImportStyleValues();
        importLayer.setStyle(style);
    }
}

// 5. MAP ENGINE: MARKER (LOGIKA BARU)
function initImportMarkerMap() {
    if (typeof importMap !== 'undefined' && importMap !== null) {
        importMap.off(); importMap.remove();
    }

    importMap = L.map('map_import_preview', { zoomControl: false, attributionControl: false }).setView(DEFAULT_COORD, 15);
    L.tileLayer(GOOGLE_SAT_URL).addTo(importMap);

    const style = getImportStyleValues();
    const markerType = document.getElementById('import_marker_type').value;

    if (markerType === 'circle') {
        importLayer = L.circleMarker(DEFAULT_COORD, { 
            color: style.color, weight: style.weight, opacity: style.opacity,
            fillColor: style.fillColor, fillOpacity: style.fillOpacity, radius: 10 
        }).addTo(importMap);
    } 
    else if (markerType === 'pin') {
        importLayer = L.marker(DEFAULT_COORD).addTo(importMap);
    } 
    else {
        // Custom Icon
        let iconSrc = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
        if (tempImportIconUrl) {
            iconSrc = tempImportIconUrl;
        } else if (markerType === 'icon_url' && document.getElementById('import_icon_url').value) {
            iconSrc = document.getElementById('import_icon_url').value;
        }

        const customIcon = L.icon({
            iconUrl: iconSrc, iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32]
        });
        importLayer = L.marker(DEFAULT_COORD, { icon: customIcon }).addTo(importMap);
    }

    setupImportListeners();
    setTimeout(() => { importMap.invalidateSize(); }, 300);
}

// 6. EVENT LISTENERS (NON-DESTRUCTIVE)
function setupImportListeners() {
    // Gunakan 'oninput' agar tidak menumpuk listener lama (lebih aman dari cloneNode)
    
    // Inputs Standard
    const inputs = ['import_style_color', 'import_style_weight', 'import_style_opacity', 'import_style_fillColor', 'import_style_fillOpacity', 'import_style_dashArray'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            el.oninput = function() {
                // False = Update Ringan (kecuali marker icon)
                refreshImportPreview(false); 
            };
        }
    });

    // Marker Inputs (Butuh Re-init)
    const elMarker = document.getElementById('import_marker_type');
    if(elMarker) elMarker.onchange = () => { toggleImportUI(); refreshImportPreview(true); };
    
    const elUrl = document.getElementById('import_icon_url');
    if(elUrl) elUrl.oninput = () => refreshImportPreview(true);
    
    const elFile = document.getElementById('import_icon_file');
    if(elFile) elFile.onchange = function(e) {
        const file = e.target.files[0];
        if(file) {
            tempImportIconUrl = URL.createObjectURL(file);
            refreshImportPreview(true);
        }
    };
}

// Helper Ambil Value
function getImportStyleValues() {
    return {
        color: document.getElementById('import_style_color').value,
        weight: parseInt(document.getElementById('import_style_weight').value),
        opacity: parseFloat(document.getElementById('import_style_opacity').value),
        fillColor: document.getElementById('import_style_fillColor').value,
        fillOpacity: parseFloat(document.getElementById('import_style_fillOpacity').value),
        dashArray: document.getElementById('import_style_dashArray').value
    };
}

function setImportDashPreset(val) {
    const el = document.getElementById('import_style_dashArray');
    if(el) { el.value = val; el.dispatchEvent(new Event('input')); }
}
</script>