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

        <div class="tab-content mt-3">
            <?php if($activeTab == 'polygon'): ?>
                <?php foreach($grupPolygon as $grup): ?><?php endforeach; ?>
            
            <?php elseif($activeTab == 'line'): ?>
                <?php foreach($grupLine as $grup): ?><?php endforeach; ?>

            <?php elseif($activeTab == 'point'): ?>
                <?php foreach($grupPoint as $grup): ?><?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-content p-4">
        <div class="tab-pane fade show active" id="polygon-pane">
            <div class="row align-items-center mb-4">
                <div class="col">
                    <button class="btn btn-primary px-4 fw-bold mb-2" onclick="openGrupModal()" style="background: var(--primary); border:none;">
                        <i class="fas fa-layer-group me-2"></i>Grup Poligon Baru
                    </button>
                    <button class="btn btn-outline-primary px-4 fw-bold mb-2" onclick="openImportGrupModal()">
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
                                    <button class="btn btn-sm btn-light border" onclick='openGrupModal(<?= json_encode($grup) ?>)'><i class="fas fa-palette me-1"></i> Style</button>
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

        <div class="tab-pane fade" id="line-pane">
            <div class="row align-items-center mb-4">
                <div class="col">
                    <button class="btn btn-primary px-4 fw-bold mb-2" onclick="openGrupModal(null, 'line')" style="background: var(--primary); border:none;">
                        <i class="fas fa-layer-group me-2"></i>Grup Line Baru
                    </button>
                </div>
            </div>
            
            <div class="accordion scrollable-area" id="accordionLine">
                <?php if(!empty($grupLine)): foreach($grupLine as $grup): ?>
                <div class="accordion-item grup-item shadow-sm">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLine<?= $grup['id_dg'] ?>">
                            <div class="d-flex align-items-center w-100">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 22px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">
                                    <div style="width: 80%; height: 0; border-top: <?= $grup['weight'] ?>px <?= !empty($grup['dashArray']) ? 'dashed' : 'solid' ?> <?= $grup['color'] ?>;"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="grup-name text-dark"><?= $grup['nama_grup'] ?></span>
                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;"><?= count($grup['items']) ?> Data Line</div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapseLine<?= $grup['id_dg'] ?>" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-light border" onclick='openGrupModal(<?= json_encode($grup) ?>, "line")'><i class="fas fa-palette me-1"></i> Style</button>
                                    <button class="btn btn-sm btn-light border text-danger" onclick="deleteGrup(<?= $grup['id_dg'] ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                                <button class="btn btn-sm btn-primary shadow-sm px-3" onclick='openAddLine(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'>
                                    <i class="fas fa-plus-circle me-1"></i> Tambah Line
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-in-group border">
                                    <thead><tr><th width="60">ID</th><th>Nama Jalur</th><th>Atribut</th><th width="120" class="text-center">Aksi</th></tr></thead>
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
                                                <button class="btn btn-xs btn-light border text-warning" onclick='openEditLine(<?= json_encode($item) ?>, <?= json_encode($grup) ?>)'><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-xs btn-light border text-danger" onclick="deleteData('line', <?= $item['id'] ?>)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="point-pane">
            <div class="row align-items-center mb-4">
                <div class="col">
                    <button class="btn btn-primary px-4 fw-bold mb-2" onclick="openGrupModal(null, 'point')" style="background: var(--primary); border:none;">
                        <i class="fas fa-layer-group me-2"></i>Grup Point Baru
                    </button>
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
                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;"><?= count($grup['items']) ?> Data Point</div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapsePoint<?= $grup['id_dg'] ?>" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-light border" onclick='openGrupModal(<?= json_encode($grup) ?>, "point")'><i class="fas fa-palette me-1"></i> Style</button>
                                    <button class="btn btn-sm btn-light border text-danger" onclick="deleteGrup(<?= $grup['id_dg'] ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                                <button class="btn btn-sm btn-primary shadow-sm px-3" onclick='openAddPoint(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'>
                                    <i class="fas fa-plus-circle me-1"></i> Tambah Point
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-in-group border">
                                    <thead><tr><th width="60">ID</th><th>Nama Lokasi</th><th>Koordinat (Lat, Lng)</th><th width="120" class="text-center">Aksi</th></tr></thead>
                                    <tbody>
                                        <?php if(!empty($grup['items'])): foreach($grup['items'] as $item): ?>
                                        <tr class="item-row">
                                            <td class="text-center text-muted small"><?= $item['id'] ?></td>
                                            <td class="fw-bold itemName"><?= $item['nama_display'] ?></td>
                                            <td class="small text-muted">
                                                <?php 
                                                    // Extract koordinat sederhana untuk preview
                                                    $geo = json_decode($item['data_geospasial'], true);
                                                    if(isset($geo['geometry']['coordinates'])) {
                                                        $c = $geo['geometry']['coordinates'];
                                                        echo number_format($c[1], 5) . ", " . number_format($c[0], 5);
                                                    } else echo "-";
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-xs btn-light border text-warning" onclick='openEditPoint(<?= json_encode($item) ?>, <?= json_encode($grup) ?>)'><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-xs btn-light border text-danger" onclick="deleteData('point', <?= $item['id'] ?>)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGrup" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('geospasial/saveGrup') ?>" method="post">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Konfigurasi Grup & Style</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_dg" id="grup_id">
                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Kategori/Grup</label>
                                <input type="text" name="nama_grup" id="grup_nama" class="form-control" required placeholder="Misal: Batas Administrasi">
                            </div>

                            <div class="mb-3 p-2 border rounded bg-light">
                                <label class="form-label fw-bold small text-primary"><i class="fas fa-tag me-1"></i> Penamaan Dinamis</label>
                                <select name="label_column" id="style_label_column" class="form-select form-select-sm shadow-sm">
                                    <option value="">-- Gunakan Nama Manual --</option>
                                    </select>
                                <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">
                                    Pilih atribut dari GeoJSON untuk dijadikan Nama Utama di tabel secara otomatis.
                                </small>
                            </div>
                            <label class="form-label fw-bold small">Template Atribut</label>
                            <div id="template_container" class="mb-3"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="addTemplateRow()">
                                <i class="fas fa-plus me-1"></i> Tambah Kolom Template
                            </button>
                        </div>
                        <div class="col-md-4 border-end">
                            <h6 class="fw-bold mb-3 small">Visualisasi Peta</h6>
                            <div class="row g-3">
                                <div class="col-6"><label class="small text-muted">Stroke</label><input type="color" name="color" id="style_color" class="form-control form-control-color w-100" value="#3388ff"></div>
                                <div class="col-6"><label class="small text-muted">Fill</label><input type="color" name="fillColor" id="style_fillColor" class="form-control form-control-color w-100" value="#3388ff"></div>
                                <div class="col-6"><label class="small text-muted">Weight</label><input type="number" name="weight" id="style_weight" class="form-control" value="3"></div>
                                <div class="col-12">
                                        <label class="small text-muted fw-bold">Pola Garis (Dash Array)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="dashArray" id="style_dashArray" class="form-control" placeholder="Contoh: 5, 10" title="Masukkan angka dipisah koma (garis, jarak)">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Presets</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="setDashPreset('')">Solid (Garis Utuh)</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="setDashPreset('5, 5')">Dashed (Putus-putus)</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="setDashPreset('1, 10')">Dotted (Titik-titik)</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="setDashPreset('10, 5, 1, 5')">Dash-Dot</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><small class="dropdown-item text-muted" style="font-size: 10px;">Format: panjang_garis, jarak</small></li>
                                            </ul>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Gunakan format angka dipisah koma untuk custom pola.</small>
                                    </div>
                                <div class="col-6"><label class="small text-muted">Op. Stroke</label><input type="number" name="opacity" id="style_opacity" class="form-control" value="1" step="0.1" max="1"></div>
                                <div class="col-6"><label class="small text-muted">Op. Fill</label><input type="number" name="fillOpacity" id="style_fillOpacity" class="form-control" value="0.2" step="0.1" max="1"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold small mb-3">Preview</label>
                            <div id="map_style_preview"></div>
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
                                <input type="text" name="nama_grup" class="form-control" required placeholder="Contoh: Batas Administrasi RT">
                            </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Pilih File GeoJSON</label>
                                    <input type="file" id="import_file_input" name="file_geojson" class="form-control" accept=".json,.geojson" required onchange="analyzeGeoJSON(this)">
                                </div>

                                <div id="column_mapping_container" class="mb-3 d-none">
                                    <label class="form-label fw-bold small text-success"><i class="fas fa-table me-1"></i> Pilih Kolom Nama Lokasi</label>
                                    <select name="column_name_map" id="column_name_map" class="form-select form-select-sm shadow-sm border-success">
                                        </select>
                                    <small class="text-muted" style="font-size: 0.7rem;">Pilih atribut dari file yang akan dijadikan nama identitas poligon.</small>
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
                            <div class="row g-3">
                                <div class="col-6"><label class="small text-muted">Stroke</label><input type="color" name="color" id="import_style_color" class="form-control form-control-color w-100" value="#4f46e5"></div>
                                <div class="col-6"><label class="small text-muted">Fill</label><input type="color" name="fillColor" id="import_style_fillColor" class="form-control form-control-color w-100" value="#4f46e5"></div>
                                <div class="col-6"><label class="small text-muted">Weight</label><input type="number" name="weight" id="import_style_weight" class="form-control" value="2"></div>
                                <div class="col-12">
                                    <label class="small text-muted fw-bold">Pola Garis</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="dashArray" id="import_style_dashArray" class="form-control" placeholder="Contoh: 5, 10">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Presets</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="setImportDashPreset('')">Solid</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="setImportDashPreset('5, 5')">Dashed</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="setImportDashPreset('1, 10')">Dotted</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="setImportDashPreset('10, 5, 1, 5')">Dash-Dot</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><small class="dropdown-item text-muted" style="font-size: 10px;">Format: panjang_garis, jarak</small></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-6"><label class="small text-muted">Op. Stroke</label><input type="number" name="opacity" id="import_style_opacity" class="form-control" value="1" step="0.1" max="1"></div>
                                <div class="col-6"><label class="small text-muted">Op. Fill</label><input type="number" name="fillOpacity" id="import_style_fillOpacity" class="form-control" value="0.2" step="0.1" max="1"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold small mb-3">Preview</label>
                            <div id="map_import_preview" style="height: 250px; width: 100%; border-radius: 12px; border: 2px solid var(--slate-100);"></div>
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
        <div class="modal-content">
            <form action="<?= base_url('geospasial/save/line') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Editor Data Line (Jalur)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="line_id">
                    <input type="hidden" name="id_dg" id="line_id_grup">
                    <textarea name="data_geospasial" id="line_geojson" class="d-none"></textarea>

                    <div class="row">
                        <div class="col-md-4">
                            <label>Nama Jalur</label>
                            <input type="text" name="nama_dg" id="line_nama" class="form-control mb-3" required>
                            
                            <label>Upload PDF</label>
                            <input type="file" name="file_pdf[]" class="form-control mb-3" multiple accept="application/pdf">
                            <div id="line_pdf_list" class="mb-3 text-sm"></div>

                            <div id="line_attr_area"></div>
                            <button type="button" class="btn btn-sm btn-info mt-2" onclick="addAttrRow('line')">+ Atribut</button>
                        </div>
                        <div class="col-md-8">
                            <div id="map_draw_line" style="height: 400px; width: 100%; border:1px solid #ccc;"></div>
                            <small class="text-muted">Klik peta untuk membuat titik jalur.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Line</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDataPoint" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('geospasial/save/point') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Editor Data Point (Titik)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="point_id">
                    <input type="hidden" name="id_dg" id="point_id_grup">
                    <textarea name="data_geospasial" id="point_geojson" class="d-none"></textarea>

                    <div class="row">
                        <div class="col-md-5">
                            <label>Nama Lokasi</label>
                            <input type="text" name="nama_dg" id="point_nama" class="form-control mb-3" required>
                            
                            <label>Upload PDF</label>
                            <input type="file" name="file_pdf[]" class="form-control mb-3" multiple accept="application/pdf">
                            <div id="point_pdf_list" class="mb-3 text-sm"></div>

                            <div id="point_attr_area"></div>
                            <button type="button" class="btn btn-sm btn-info mt-2" onclick="addAttrRow('point')">+ Atribut</button>
                        </div>
                        <div class="col-md-7">
                            <div id="map_draw_point" style="height: 350px; width: 100%; border:1px solid #ccc;"></div>
                            <small class="text-muted">Geser marker untuk menentukan lokasi.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Point</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// --- KONFIGURASI PETA ---
const DEFAULT_COORD = [-7.408019826354289, 111.4428818182571]; 
let styleMap, styleLayer, drawMap, drawLayer, markers = [], currentGroupStyle = {};

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', function () {
    // Tooltip init
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(t => new bootstrap.Tooltip(t));

    // Global Search Group
    document.getElementById('searchGroupInput')?.addEventListener('keyup', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.grup-item').forEach(g => {
            const name = g.querySelector('.grup-name').textContent.toLowerCase();
            g.style.display = name.includes(term) ? '' : 'none';
        });
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
});

// --- LOGIC GRUP ---
function openGrupModal(data = null) {
    const modalEl = document.getElementById('modalGrup');
    const form = modalEl.querySelector('form');
    form.reset();
    document.getElementById('template_container').innerHTML = '';
    
    // Reset dropdown label
    const labelSelect = document.getElementById('style_label_column');
    labelSelect.innerHTML = '<option value="">-- Gunakan Nama Manual --</option>';

    let style = { color:'#3388ff', weight:3, opacity:1, fillColor:'#3388ff', fillOpacity:0.2, dashArray:'' };

    if(data) {
        document.getElementById('grup_id').value = data.id_dg;
        document.getElementById('grup_nama').value = data.nama_grup;
        style = { color:data.color, weight:data.weight, opacity:data.opacity, fillColor:data.fillColor, fillOpacity:data.fillOpacity, dashArray:data.dashArray || '' };
        
        // --- MODIFIKASI DI SINI: MENAMPILKAN CONTOH DATA ---
        if(data.items && data.items.length > 0) {
            try {
                // Ambil atribut dari item pertama sebagai sampel kolom
                const sampleAttrs = JSON.parse(data.items[0].atribut_tambahan);
                
                sampleAttrs.forEach(attr => {
                    const opt = document.createElement('option');
                    opt.value = attr.label;
                    
                    // Tampilkan Label + Contoh Nilainya
                    // Contoh: "KECAMATAN (Contoh: NGAWI)"
                    let contohValue = attr.value ? ` (Contoh: ${attr.value})` : ' (Kosong)';
                    opt.text = attr.label + contohValue;
                    
                    if(data.label_column === attr.label) opt.selected = true;
                    labelSelect.appendChild(opt);
                });
            } catch(e) { 
                console.error("Gagal parse atribut untuk label", e); 
            }
        }
        // ---------------------------------------------------

        try { JSON.parse(data.atribut_default).forEach(x => addTemplateRow(x.label)); } catch(e){}
    } else {
        document.getElementById('grup_id').value = '';
        addTemplateRow();
    }

    // Terapkan style ke input
    Object.keys(style).forEach(key => { 
        if(document.getElementById('style_'+key)) document.getElementById('style_'+key).value = style[key]; 
    });

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    modalEl.addEventListener('shown.bs.modal', () => initStyleMap(style), { once: true });
}

function addTemplateRow(val = '') {
    const div = document.createElement('div');
    div.className = 'input-group mb-2 shadow-sm';
    div.innerHTML = `<span class="input-group-text bg-white"><i class="fas fa-tag text-muted"></i></span><input type="text" name="template_attr[]" class="form-control form-control-sm" value="${val}"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>`;
    document.getElementById('template_container').appendChild(div);
}

function initStyleMap(style) {
    if (styleMap) styleMap.remove();
    styleMap = L.map('map_style_preview', { zoomControl: false }).setView(DEFAULT_COORD, 13);
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}').addTo(styleMap);
    
    styleLayer = L.polygon([[DEFAULT_COORD[0]+0.01, DEFAULT_COORD[1]-0.01], [DEFAULT_COORD[0]-0.01, DEFAULT_COORD[1]+0.01], [DEFAULT_COORD[0]-0.01, DEFAULT_COORD[1]-0.02]], style).addTo(styleMap);
    styleMap.fitBounds(styleLayer.getBounds(), { padding: [10,10] });

    // Daftar ID input yang akan dipantau perubahannya
    const styleInputs = ['style_color', 'style_weight', 'style_opacity', 'style_fillColor', 'style_fillOpacity', 'style_dashArray'];
    
    styleInputs.forEach(id => {
        const el = document.getElementById(id);
        if(!el) return;

        // Gunakan event 'input' agar perubahan teks langsung terlihat di peta tanpa nunggu blur/lose focus
        el.addEventListener('input', () => {
            styleLayer.setStyle({
                color: document.getElementById('style_color').value,
                weight: document.getElementById('style_weight').value,
                opacity: document.getElementById('style_opacity').value,
                fillColor: document.getElementById('style_fillColor').value,
                fillOpacity: document.getElementById('style_fillOpacity').value,
                dashArray: document.getElementById('style_dashArray').value // Mengambil string dari input teks
            });
        });
    });
}

// --- LOGIC EDITOR POLIGON ---
function openAddPolygon(grupId, grupData) { preparePolygonModal(grupId, grupData); }
function openEditPolygon(data, grupData) { preparePolygonModal(data.id_dg, grupData, data); }

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

function deletePdfItem(id) {
    if(!confirm('Hapus file ini?')) return;
    fetch(`<?= base_url('geospasial/deletePdf') ?>/${id}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json()).then(res => { if(res.status==='success') document.getElementById(`pdf-item-${id}`).remove(); });
}

function addAttributeRow(label = '', value = '') {
    const div = document.createElement('div');
    div.className = 'input-group mb-2 shadow-sm';
    div.innerHTML = `<input type="text" name="attr_key[]" class="form-control form-control-sm" placeholder="Label" value="${label}" required><input type="text" name="attr_val[]" class="form-control form-control-sm" placeholder="Nilai" value="${value}"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>`;
    document.getElementById('attribute_container').appendChild(div);
}

// --- DRAWING ENGINE ---
function initDrawMap(jsonStr) {
    if (drawMap) drawMap.remove();
    drawMap = L.map('map_draw_polygon').setView(DEFAULT_COORD, 14);
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}').addTo(drawMap);
    markers = []; drawLayer = null;
    if (jsonStr) loadGeoJSON(jsonStr);
    drawMap.on('click', e => addMarker(e.latlng));
}

function addMarker(latlng) {
    if (!latlng) return;
    const m = L.marker(latlng, { draggable: true }).addTo(drawMap);
    m.on('drag', updatePoly);
    m.on('click', () => { drawMap.removeLayer(m); markers = markers.filter(x => x !== m); updatePoly(); });
    markers.push(m); updatePoly();
}

function updatePoly() {
    const pts = markers.map(m => m.getLatLng());
    if (drawLayer) drawMap.removeLayer(drawLayer);
    if (pts.length >= 3) {
        drawLayer = L.polygon(pts, currentGroupStyle).addTo(drawMap);
        document.getElementById('poly_geojson').value = JSON.stringify(drawLayer.toGeoJSON());
    } else document.getElementById('poly_geojson').value = '';
}

function clearMapDraw() { markers.forEach(m => drawMap.removeLayer(m)); markers = []; if(drawLayer) drawMap.removeLayer(drawLayer); document.getElementById('poly_geojson').value = ''; }

function loadGeoJSON(str) {
    if (!str || str === "null" || str === "") return;

    try {
        const geojsonData = JSON.parse(str);
        const l = L.geoJSON(geojsonData);
        
        // Bersihkan marker lama sebelum memproses yang baru
        markers.forEach(m => drawMap.removeLayer(m));
        markers = [];

        // Ambil semua layer yang berhasil di-parse oleh Leaflet
        const allLayers = l.getLayers();
        
        if (allLayers.length > 0) {
            // Kita fokus pada layer pertama hasil parse
            const firstLayer = allLayers[0];
            let coords = [];

            // LOGIKA EKSTRAKSI KOORDINAT YANG LEBIH KUAT
            if (typeof firstLayer.getLatLngs === 'function') {
                let latlngs = firstLayer.getLatLngs();
                
                // GeoJSON Polygon biasanya punya struktur array bertingkat: [[ [lat,lng], [lat,lng] ]]
                // Kita harus "meratakan" array-nya sampai ketemu objek LatLng
                while (Array.isArray(latlngs) && latlngs.length > 0 && Array.isArray(latlngs[0])) {
                    latlngs = latlngs[0];
                }
                coords = latlngs;
            } else if (typeof firstLayer.getLatLng === 'function') {
                // Jika ternyata itu Point/Marker
                coords = [firstLayer.getLatLng()];
            }

            // PROSES PEMBUATAN MARKER (TITIK EDIT)
            if (Array.isArray(coords)) {
                coords.forEach(c => {
                    // Validasi: Pastikan 'c' adalah objek koordinat yang valid sebelum addMarker
                    if (c && typeof c === 'object' && ( (c.lat && c.lng) || (Array.isArray(c) && c.length >= 2) )) {
                        addMarker(c);
                    }
                });

                // Zoom ke lokasi data agar terlihat
                if (markers.length > 0) {
                    drawMap.fitBounds(l.getBounds(), { padding: [30, 30] });
                }
            }
        }
    } catch (e) {
        console.error("GeoJSON Parse Error:", e);
    }
}

function deleteGrup(id) { if(confirm('Hapus seluruh grup dan datanya?')) window.location.href = `<?= base_url('geospasial/deleteGrup') ?>/${id}`; }
function deleteData(t, id) { if(confirm('Hapus entitas ini?')) window.location.href = `<?= base_url('geospasial/delete') ?>/${t}/${id}`; }
function handleFileUpload(input) {
    const f = input.files[0]; if(!f) return;
    const r = new FileReader();
    r.onload = e => { try { clearMapDraw(); loadGeoJSON(e.target.result); } catch(err){ alert('GeoJSON Tidak Valid'); } };
    r.readAsText(f); input.value = '';
}

function setDashPreset(val) {
    const input = document.getElementById('style_dashArray');
    input.value = val;
    // Trigger event 'input' secara manual agar preview terupdate
    input.dispatchEvent(new Event('input'));
}

// Tambahkan ini di bagian script
document.getElementById('formPolygonData')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnSimpanPoly');
    
    // 1. Tambahkan class loading (dari CSS Anda)
    btn.classList.add('btn-loading');
    
    // 2. Ubah teks tombol agar user tahu proses sedang berjalan
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;
    
    // 3. (Opsional) Matikan tombol agar tidak diklik dua kali
    btn.disabled = true;
});

document.querySelector('#modalGrup form')?.addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.classList.add('btn-loading');
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memproses...`;
    btn.disabled = true;
});

// Deklarasi global agar tidak undefined
let importStyleMap, importStyleLayer;

function openImportGrupModal() {
    const modalEl = document.getElementById('modalImportGrup');
    const modal = new bootstrap.Modal(modalEl);
    
    document.getElementById('formImportGrup').reset();
    document.getElementById('importProgressContainer').classList.add('d-none');
    
    modal.show();

    // Jalankan preview peta saat modal terbuka
    modalEl.addEventListener('shown.bs.modal', function () {
        const initialStyle = {
            color: '#4f46e5', weight: 2, opacity: 1,
            fillColor: '#4f46e5', fillOpacity: 0.2, dashArray: ''
        };
        initImportStyleMap(initialStyle); 
    }, { once: true });
}

function initImportStyleMap(style) {
    if (importStyleMap) importStyleMap.remove();
    
    importStyleMap = L.map('map_import_preview', { zoomControl: false, attributionControl: false }).setView(DEFAULT_COORD, 13);
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}').addTo(importStyleMap);
    
    // Gunakan segitiga sederhana untuk preview
    importStyleLayer = L.polygon([
        [DEFAULT_COORD[0] + 0.005, DEFAULT_COORD[1] - 0.005],
        [DEFAULT_COORD[0] - 0.005, DEFAULT_COORD[1] + 0.005],
        [DEFAULT_COORD[0] - 0.005, DEFAULT_COORD[1] - 0.005]
    ], style).addTo(importStyleMap);
    
    importStyleMap.fitBounds(importStyleLayer.getBounds(), { padding: [20, 20] });

    // Listener Input (Tiru cara kamu)
    const ids = ['import_style_color', 'import_style_weight', 'import_style_opacity', 'import_style_fillColor', 'import_style_fillOpacity', 'import_style_dashArray'];
    ids.forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => {
            importStyleLayer.setStyle({
                color: document.getElementById('import_style_color').value,
                weight: document.getElementById('import_style_weight').value,
                opacity: document.getElementById('import_style_opacity').value,
                fillColor: document.getElementById('import_style_fillColor').value,
                fillOpacity: document.getElementById('import_style_fillOpacity').value,
                dashArray: document.getElementById('import_style_dashArray').value
            });
        });
    });
}

function setImportDashPreset(val) {
    const el = document.getElementById('import_style_dashArray');
    el.value = val;
    el.dispatchEvent(new Event('input'));
}

// Handler AJAX Upload dengan Progress Bar
document.getElementById('formImportGrup')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitImport');
    const progressCont = document.getElementById('importProgressContainer');
    const progressBar = document.getElementById('importProgressBar');
    const statusText = document.getElementById('importStatusText');

    btn.disabled = true;
    progressCont.classList.remove('d-none');

    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerHTML = percent + '%';
            statusText.innerText = percent < 100 ? 'Mengunggah file...' : 'Memproses database (Jangan tutup halaman)...';
        }
    });

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if(res.status === 'success') {
                alert('Berhasil mengimpor ' + res.count + ' poligon!');
                window.location.reload();
            } else {
                alert('Gagal: ' + res.message);
                btn.disabled = false;
            }
        }
    };

    xhr.open('POST', '<?= base_url('geospasial/importGeoJSONGrup') ?>', true);
    xhr.send(new FormData(this));
});

async function openEditPolygon(item, grupData, btn) {
    if(btn) btn.style.pointerEvents = 'none';

    try {
        // PERBAIKAN: Tambahkan parameter kedua untuk headers
        const response = await fetch(`<?= base_url('geospasial/getDetail/polygon') ?>/${item.id}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });
        
        if (!response.ok) throw new Error("Gagal mengambil data (Status: " + response.status + ")");

        const fullData = await response.json();
        
        preparePolygonModal(fullData.id_dg, grupData, fullData);

    } catch (error) {
        console.error("Fetch error:", error);
        alert("Gagal memuat data: " + error.message);
    } finally {
        if(btn) btn.style.pointerEvents = 'auto';
    }
}

function analyzeGeoJSON(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const json = JSON.parse(e.target.result);
            const mappingContainer = document.getElementById('column_mapping_container');
            const selectMap = document.getElementById('column_name_map');
            
            if (json.features && json.features.length > 0) {
                const properties = json.features[0].properties;
                const keys = Object.keys(properties);

                selectMap.innerHTML = '<option value="">-- Gunakan Nama Default --</option>';
                keys.forEach(key => {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.innerText = key + " (Contoh: " + properties[key] + ")";
                    selectMap.appendChild(opt);
                });

                mappingContainer.classList.remove('d-none');
            }
        } catch (err) {
            alert("File GeoJSON tidak bisa dibaca/rusak.");
        }
    };
    reader.readAsText(file);
}


// --- FUNGSI EXPORT GEOJSON ---
function doExportAJAX(idGrup, namaGrup, btn) {
    // 1. Simpan state tombol asli
    const originalContent = btn.innerHTML;
    const originalTitle = btn.title;
    
    // 2. Ubah tampilan tombol jadi loading
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin text-success"></i>`;
    btn.title = "Sedang mengunduh...";

    // 3. Request ke Controller
    fetch(`<?= base_url('geospasial/exportGeoJSON') ?>/${idGrup}`)
        .then(response => {
            if (response.status === 200) {
                return response.blob();
            } else {
                throw new Error('Gagal mengambil data');
            }
        })
        .then(blob => {
            // 4. Buat link download virtual
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            
            // Format nama file
            const cleanName = namaGrup.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            a.download = `${cleanName}_export.geojson`;
            
            document.body.appendChild(a);
            a.click();
            
            // Cleanup
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Terjadi kesalahan saat mengexport data. Pastikan data geospasial valid.");
        })
        .finally(() => {
            // 5. Kembalikan tombol seperti semula
            btn.disabled = false;
            btn.innerHTML = originalContent;
            btn.title = originalTitle;
        });
}

// --- VARIABEL GLOBAL MAP ---
var mapLine, drawLineLayer, lineMarkers = [];
var mapPoint, drawPointLayer;

// --- INIT SAAT MODAL DIBUKA (EVENT LISTENER) ---

// 1. SAAT MODAL LINE DIBUKA
document.getElementById('modalDataLine').addEventListener('shown.bs.modal', function () {
    if (!mapLine) {
        mapLine = L.map('map_draw_line').setView([-7.4, 111.4], 12); // Ganti koordinat default Ngawi
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapLine);
        
        // Klik peta untuk tambah titik garis
        mapLine.on('click', function(e) {
            var m = L.marker(e.latlng, {draggable: true}).addTo(mapLine);
            lineMarkers.push(m);
            updateLineGeoJSON();
            
            m.on('drag', updateLineGeoJSON);
            m.on('click', function() { // Klik marker untuk hapus
                mapLine.removeLayer(this);
                lineMarkers = lineMarkers.filter(item => item !== this);
                updateLineGeoJSON();
            });
        });
    }
    setTimeout(function(){ mapLine.invalidateSize(); }, 10);
});

// Fungsi Update GeoJSON Line
function updateLineGeoJSON() {
    if (drawLineLayer) mapLine.removeLayer(drawLineLayer);
    
    var latlngs = lineMarkers.map(m => m.getLatLng());
    if (latlngs.length > 1) {
        drawLineLayer = L.polyline(latlngs, {color: 'blue'}).addTo(mapLine);
        // Simpan ke textarea hidden
        document.getElementById('line_geojson').value = JSON.stringify(drawLineLayer.toGeoJSON());
    } else {
        document.getElementById('line_geojson').value = '';
    }
}

// 2. SAAT MODAL POINT DIBUKA
document.getElementById('modalDataPoint').addEventListener('shown.bs.modal', function () {
    if (!mapPoint) {
        mapPoint = L.map('map_draw_point').setView([-7.4, 111.4], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapPoint);

        // Klik untuk pindah marker
        mapPoint.on('click', function(e) {
            setPointMarker(e.latlng);
        });
    }
    setTimeout(function(){ mapPoint.invalidateSize(); }, 10);
});

function setPointMarker(latlng) {
    if (drawPointLayer) mapPoint.removeLayer(drawPointLayer);
    drawPointLayer = L.marker(latlng, {draggable: true}).addTo(mapPoint);
    
    // Simpan ke textarea hidden
    document.getElementById('point_geojson').value = JSON.stringify(drawPointLayer.toGeoJSON());
    
    drawPointLayer.on('dragend', function(e) {
        document.getElementById('point_geojson').value = JSON.stringify(e.target.toGeoJSON());
    });
}

// --- FUNGSI RESET MAP SAAT EDIT DATA ---
function editLine(id, idGrup) {
    // 1. Reset Map
    if(drawLineLayer) mapLine.removeLayer(drawLineLayer);
    lineMarkers.forEach(m => mapLine.removeLayer(m));
    lineMarkers = [];
    
    // 2. AJAX Get Detail
    fetch('<?= base_url("geospasial/getDetail/line") ?>/' + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById('line_id').value = data.id;
            document.getElementById('line_id_grup').value = idGrup;
            document.getElementById('line_nama').value = data.nama_dg;
            
            // Load GeoJSON ke Peta
            if(data.data_geospasial) {
                var geo = JSON.parse(data.data_geospasial);
                var layer = L.geoJSON(geo).getLayers()[0]; 
                var latlngs = layer.getLatLngs();
                
                latlngs.forEach(ll => {
                    var m = L.marker(ll, {draggable:true}).addTo(mapLine);
                    lineMarkers.push(m);
                    // Pasang event listener seperti di atas (drag/click)
                    // ... (copy logic event listener marker line disini)
                });
                updateLineGeoJSON();
                mapLine.fitBounds(layer.getBounds());
            }
            
            var myModal = new bootstrap.Modal(document.getElementById('modalDataLine'));
            myModal.show();
        });
}

function editPoint(id, idGrup) {
    fetch('<?= base_url("geospasial/getDetail/point") ?>/' + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById('point_id').value = data.id;
            document.getElementById('point_id_grup').value = idGrup;
            document.getElementById('point_nama').value = data.nama_dg;

            if(data.data_geospasial) {
                var geo = JSON.parse(data.data_geospasial);
                // GeoJSON Point koordinatnya [lng, lat]
                var lng = geo.geometry.coordinates[0];
                var lat = geo.geometry.coordinates[1];
                setPointMarker([lat, lng]);
                mapPoint.setView([lat, lng], 15);
            }

            var myModal = new bootstrap.Modal(document.getElementById('modalDataPoint'));
            myModal.show();
        });
}
</script>