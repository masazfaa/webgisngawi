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
        <ul class="nav nav-tabs" id="geoTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#polygon-pane">
                    <i class="fas fa-draw-polygon me-2"></i>Data Poligon
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#line-pane">
                    <i class="fas fa-route me-2"></i>Data Line
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#point-pane">
                    <i class="fas fa-map-marker-alt me-2"></i>Data Point
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content p-4">
        <div class="tab-pane fade show active" id="polygon-pane">
            <div class="row align-items-center mb-4">
                <div class="col">
                    <button class="btn btn-primary px-4 fw-bold" onclick="openGrupModal()" style="background: var(--primary); border:none;">
                        <i class="fas fa-layer-group me-2"></i>Grup Baru
                    </button>
                    <button class="btn btn-outline-primary px-4 fw-bold ms-2" onclick="openImportGrupModal()">
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
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control form-control-sm search-in-group" placeholder="Cari nama..." style="width: 150px;">
                                    <button class="btn btn-sm btn-success px-3" onclick='openAddPolygon(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'>
                                        <i class="fas fa-plus me-1"></i>Tambah
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-in-group border">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="60">ID</th>
                                            <th>Nama Lokasi</th>
                                            <th>Atribut Tambahan</th>
                                            <th class="text-center">Lampiran PDF</th>
                                            <th class="text-center" width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($grup['items'])): foreach($grup['items'] as $item): ?>
                                        <tr class="item-row">
                                            <td class="text-center text-muted small"><?= $item['id'] ?></td>
                                            <td class="fw-bold itemName"><?= $item['nama_dg'] ?></td>
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

        <div class="tab-pane fade" id="line-pane"><div class="text-center py-5 text-muted">Fitur Line segera hadir.</div></div>
        <div class="tab-pane fade" id="point-pane"><div class="text-center py-5 text-muted">Fitur Point segera hadir.</div></div>
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
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Nama Kategori/Grup</label>
                                <input type="text" name="nama_grup" id="grup_nama" class="form-control" required placeholder="Misal: Batas Administrasi">
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
                                <input type="file" name="file_geojson" class="form-control" accept=".json,.geojson" required>
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
    
    let style = { color:'#3388ff', weight:3, opacity:1, fillColor:'#3388ff', fillOpacity:0.2, dashArray:'' };

    if(data) {
        document.getElementById('grup_id').value = data.id_dg;
        document.getElementById('grup_nama').value = data.nama_grup;
        style = { color:data.color, weight:data.weight, opacity:data.opacity, fillColor:data.fillColor, fillOpacity:data.fillOpacity, dashArray:data.dashArray || '' };
        try { JSON.parse(data.atribut_default).forEach(x => addTemplateRow(x.label)); } catch(e){}
    } else {
        document.getElementById('grup_id').value = '';
        addTemplateRow();
    }

    Object.keys(style).forEach(key => { if(document.getElementById('style_'+key)) document.getElementById('style_'+key).value = style[key]; });

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
    // 1. Beri proteksi agar tidak klik ganda saat loading
    if(btn) btn.style.pointerEvents = 'none';
    
    try {
        const response = await fetch(`<?= base_url('geospasial/getPolygonDetail') ?>/${item.id}`);
        const fullData = await response.json();
        
        // 2. Cek apakah data_geospasial benar-benar ada isinya dari database
        if (!fullData.data_geospasial) {
            console.error("Data geospasial kosong di database!");
            return;
        }

        // 3. Jalankan modal
        preparePolygonModal(fullData.id_dg, grupData, fullData);
    } catch (error) {
        console.error("Fetch error:", error);
    } finally {
        if(btn) btn.style.pointerEvents = 'auto';
    }
}
</script>