<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* Container Utama */
    .container {
      justify-content: unset;
    }
    .geo-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        padding: 0;
        overflow: hidden;
        margin-top: 10px;
    }
    
    /* Header Tabs */
    .geo-header {
        background-color: #fff;
        border-bottom: 1px solid #dee2e6;
        padding: 20px 20px 0 20px;
    }
    .nav-tabs { border-bottom: none; margin-bottom: 0; }
    .nav-tabs .nav-link {
        color: #6c757d; border: none; border-bottom: 3px solid transparent;
        padding: 10px 20px; font-weight: 500; transition: all 0.3s;
    }
    .nav-tabs .nav-link:hover { color: #0d6efd; }
    .nav-tabs .nav-link.active {
        color: #0d6efd; background: transparent; border-bottom: 3px solid #0d6efd; font-weight: bold;
    }
    
    /* Content Area */
    .tab-content { padding: 20px; background: #fff; min-height: 400px; }

    /* Map Styles */
    #map_style_preview, #map_draw_polygon {
        height: 350px; width: 100%; border: 1px solid #ccc; border-radius: 4px;
    }
    
    /* Utilitas */
    .attr-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .btn-xs { padding: 0.15rem 0.5rem; font-size: 0.75rem; line-height: 1.5; border-radius: 0.2rem; }
    .btn-xs i { font-size: 0.7rem; margin-right: 2px; }
    
    /* Scrollable Area */
    .scrollable-area {
        max-height: 65vh;
        overflow-y: auto;
        padding-right: 5px;
    }
    .scrollable-area::-webkit-scrollbar { width: 6px; }
    .scrollable-area::-webkit-scrollbar-track { background: #f1f1f1; }
    .scrollable-area::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
    .scrollable-area::-webkit-scrollbar-thumb:hover { background: #aaa; }

    /* Loading State untuk Tombol Simpan */
    .btn-loading {
        pointer-events: none;
        opacity: 0.65;
        cursor: not-allowed;
    }
</style>

<div class="geo-container">
    <div class="geo-header">
        <ul class="nav nav-tabs" id="geoTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="polygon-tab" data-bs-toggle="tab" data-bs-target="#polygon-pane">
                    <i class="fas fa-draw-polygon me-1"></i> Data Poligon
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="line-tab" data-bs-toggle="tab" data-bs-target="#line-pane">
                    <i class="fas fa-route me-1"></i> Data Line
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="point-tab" data-bs-toggle="tab" data-bs-target="#point-pane">
                    <i class="fas fa-map-marker-alt me-1"></i> Data Point
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="geoTabContent">

        <div class="tab-pane fade show active" id="polygon-pane">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" onclick="openGrupModal()">
                    <i class="fas fa-layer-group"></i> Buat Grup Baru
                </button>

                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchGroupInput" class="form-control border-start-0 ps-0" placeholder="Cari Grup Poligon...">
                </div>
            </div>

            <div class="accordion scrollable-area" id="accordionPolygon">
                <?php if(!empty($grupPolygon)): foreach($grupPolygon as $index => $grup): ?>
                
                <div class="accordion-item grup-item">
                    <h2 class="accordion-header" id="heading<?= $grup['id_dg'] ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $grup['id_dg'] ?>">
                            <span class="d-flex align-items-center w-100">
                                <span class="me-3 shadow-sm" style="display: inline-block; width: 50px; height: 25px; background-color: <?= $grup['fillColor'] ?>; border: <?= min($grup['weight'], 5) ?>px <?= !empty($grup['dashArray']) ? 'dashed' : 'solid' ?> <?= $grup['color'] ?>; opacity: 0.9; border-radius: 2px;"></span>

                                <div class="d-flex flex-column">
                                    <strong class="grup-name"><?= $grup['nama_grup'] ?></strong>
                                    <small class="text-muted" style="font-size: 0.75rem;">(Color: <?= $grup['color'] ?> | Items: <?= count($grup['items']) ?>)</small>
                                </div>
                            </span>
                        </button>
                    </h2>
                    <div id="collapse<?= $grup['id_dg'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionPolygon">
                          <div id="collapse<?= $grup['id_dg'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionPolygon">
                              <div class="accordion-body">
                                  
                                  <div class="d-flex justify-content-between align-items-end mb-3 border-bottom pb-2">
                                      
                                      <div>
                                          <button class="btn btn-sm btn-outline-primary" onclick='openGrupModal(<?= json_encode($grup) ?>)' title="Edit Style Grup">
                                              <i class="fas fa-palette"></i> Style
                                          </button>
                                          <button class="btn btn-sm btn-outline-danger" onclick="deleteGrup(<?= $grup['id_dg'] ?>)" title="Hapus Grup">
                                              <i class="fas fa-trash"></i> Hapus
                                          </button>
                                      </div>

                                      <div class="d-flex align-items-center gap-2">
                                          <div class="input-group input-group-sm" style="width: 200px;">
                                              <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                              <input type="text" class="form-control border-start-0 search-in-group" placeholder="Cari nama..." style="box-shadow: none;">
                                          </div>

                                          <button class="btn btn-sm btn-success" onclick='openAddPolygon(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'>
                                              <i class="fas fa-plus"></i> Tambah
                                          </button>
                                      </div>
                                  </div>

                                  <table class="table table-sm table-hover table-bordered mb-0 table-in-group">
                                      <thead class="table-light">
                                          <tr>
                                              <th class="text-center" width="50">ID</th>
                                              <th>Nama Poligon</th>
                                              <th>Atribut</th>
                                              <th class="text-center" width="60">PDF</th>
                                              <th class="text-center" width="130">Aksi</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php if(!empty($grup['items'])): foreach($grup['items'] as $item): ?>
                                          <tr class="item-row"> <td class="text-center align-middle"><?= $item['id'] ?></td>
                                              <td class="align-middle fw-bold itemName"><?= $item['nama_dg'] ?></td>
                                              <td class="align-middle">
                                                  <?php 
                                                      $attrs = json_decode($item['atribut_tambahan'], true);
                                                      if($attrs) {
                                                          echo '<div style="font-size: 0.8rem; line-height: 1.2;">';
                                                          foreach($attrs as $a) echo "<span class='badge bg-light text-dark border me-1'>{$a['label']}: {$a['value']}</span>";
                                                          echo '</div>';
                                                      } else { echo "-"; }
                                                  ?>
                                              </td>
                                                    <td class="text-center align-middle">
                                                        <?php if(!empty($item['daftar_pdf']) && is_array($item['daftar_pdf'])): ?>
                                                            
                                                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                                                
                                                                <?php foreach($item['daftar_pdf'] as $pdf): ?>
                                                                    <a href="<?= base_url('uploads/pdf/'.$pdf['file_path']) ?>" 
                                                                    target="_blank" 
                                                                    class="btn btn-sm btn-outline-danger btn-xs" 
                                                                    title="<?= htmlspecialchars($pdf['judul_pdf']) ?>" 
                                                                    data-bs-toggle="tooltip">
                                                                        <i class="fas fa-file-pdf"></i>
                                                                    </a>
                                                                <?php endforeach; ?>

                                                            </div>

                                                        <?php else: ?>
                                                            <span class="text-muted small">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                              <td class="text-center align-middle">
                                                  <div class="btn-group" role="group">
                                                      <button class="btn btn-warning btn-xs text-white" onclick='openEditPolygon(<?= json_encode($item) ?>, <?= json_encode($grup) ?>)'><i class="fas fa-edit"></i></button>
                                                      <button class="btn btn-danger btn-xs" onclick="deleteData('polygon', <?= $item['id'] ?>)"><i class="fas fa-trash"></i></button>
                                                  </div>
                                              </td>
                                          </tr>
                                          <?php endforeach; else: ?>
                                              <tr class="no-data"><td colspan="5" class="text-center text-muted small py-3">Belum ada data.</td></tr>
                                          <?php endif; ?>
                                          <tr class="search-no-result" style="display: none;">
                                              <td colspan="5" class="text-center text-muted small py-3">Data tidak ditemukan.</td>
                                          </tr>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <div class="alert alert-info text-center mt-3">Belum ada Grup Poligon.</div>
                <?php endif; ?>
                
                <div id="noGroupFound" class="alert alert-warning text-center mt-3" style="display: none;">Grup poligon tidak ditemukan.</div>
            </div>
        </div>

        <div class="tab-pane fade" id="line-pane"><div class="alert alert-secondary">Fitur Line akan dikembangkan nanti.</div></div>
        <div class="tab-pane fade" id="point-pane"><div class="alert alert-secondary">Fitur Point akan dikembangkan nanti.</div></div>
    </div>
</div>

<div class="modal fade" id="modalGrup" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="<?= base_url('geospasial/saveGrup') ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Grup Poligon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_dg" id="grup_id">
                    <input type="hidden" name="jenis_peta" value="Polygon">

                    <div class="row">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="fw-bold">Nama Grup</label>
                                <input type="text" name="nama_grup" id="grup_nama" class="form-control" required placeholder="Contoh: Lahan Pertanian">
                            </div>
                            <hr>
                            <label class="fw-bold mb-2">Template Atribut (Default)</label>
                            <div id="template_container" class="mb-2" style="max-height: 200px; overflow-y: auto;"></div>
                            <button type="button" class="btn btn-sm btn-outline-success w-100" onclick="addTemplateRow()">
                                <i class="fas fa-plus"></i> Tambah Kolom Atribut
                            </button>
                        </div>

                        <div class="col-md-4 border-end">
                            <h6 class="border-bottom pb-2 fw-bold">Pengaturan Style</h6>
                            <div class="row g-2">
                                <div class="col-6"><label class="small">Warna Garis</label><input type="color" name="color" id="style_color" class="form-control form-control-color w-100" value="#3388ff"></div>
                                <div class="col-6"><label class="small">Warna Isi</label><input type="color" name="fillColor" id="style_fillColor" class="form-control form-control-color w-100" value="#3388ff"></div>
                                <div class="col-6"><label class="small">Tebal Garis</label><input type="number" name="weight" id="style_weight" class="form-control form-control-sm" value="3"></div>
                                <div class="col-6"><label class="small">Tipe Garis</label><select name="dashArray" id="style_dashArray" class="form-select form-select-sm"><option value="">Solid</option><option value="5, 5">Putus-putus</option><option value="1, 5">Titik-titik</option></select></div>
                                <div class="col-6"><label class="small">Opacity Garis</label><input type="number" name="opacity" id="style_opacity" class="form-control form-control-sm" value="1.0" step="0.1" max="1"></div>
                                <div class="col-6"><label class="small">Opacity Isi</label><input type="number" name="fillOpacity" id="style_fillOpacity" class="form-control form-control-sm" value="0.2" step="0.1" max="1"></div>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex flex-column">
                            <label class="mb-2 fw-bold">Live Preview</label>
                            <div id="map_style_preview" style="flex-grow: 1; min-height: 250px; border: 2px solid #eee; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan Grup & Template</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDataPolygon" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="<?= base_url('geospasial/save/polygon') ?>" method="post" id="formPolygonData" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Editor Poligon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="poly_id">
                    <input type="hidden" name="id_dg" id="poly_id_grup">
                    <textarea name="data_geospasial" id="poly_geojson" class="d-none"></textarea>

                    <div class="row">
                        <div class="col-md-4" style="border-right: 1px solid #eee;">
                            <div class="mb-3">
                                <label>Nama Poligon</label>
                                <input type="text" name="nama_dg" id="poly_nama" class="form-control" required>
                            </div>
                            
                                <div class="mb-3 p-3 bg-light border rounded">
                                    <label class="small fw-bold mb-2"><i class="fas fa-file-pdf text-danger"></i> Dokumen PDF</label>
                                    
                                    <div id="existing_files_container" class="list-group mb-2"></div>

                                    <label class="form-label small text-muted">Tambah File Baru:</label>
                                    <input type="file" name="file_pdf[]" id="poly_pdf" class="form-control form-control-sm" accept="application/pdf" multiple>
                                    <small class="text-muted" style="font-size:0.7rem">*Bisa pilih banyak file sekaligus. Tahan tombol Ctrl saat memilih.</small>
                                </div>
                            
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="m-0">Atribut Tambahan</h6>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addAttributeRow()"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="attribute_container" style="max-height: 300px; overflow-y: auto;"></div>
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fas fa-map"></i> Gambar Area</span>
                                <div>
                                    <input type="file" id="fileGeoJSON" accept=".json,.geojson" class="d-none" onchange="handleFileUpload(this)">
                                    <button type="button" class="btn btn-sm btn-info text-white" onclick="document.getElementById('fileGeoJSON').click()">Upload GeoJSON</button>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="clearMapDraw()">Reset Gambar</button>
                                </div>
                            </div>
                            <div id="map_draw_polygon"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="btnSimpanPoly" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// --- GLOBAL VARS ---
const DEFAULT_COORD = [-7.408019826354289, 111.4428818182571]; 
let styleMap = null, styleLayer = null;
let drawMap = null, drawLayer = null, markers = [];
let currentGroupStyle = {}; 

document.addEventListener('DOMContentLoaded', function () {
    // 1. Table Manager Class (Search & Pagination)
    class TableManager {
        constructor(tableId, paginationId, searchId) {
            this.table = document.getElementById(tableId);
            if (!this.table) return;
            this.paginationNav = document.querySelector(`#${paginationId} ul`);
            this.searchInput = document.getElementById(searchId);
            this.rowsPerPage = 10;
            this.rows = Array.from(this.table.querySelectorAll('tbody tr'));
            this.filteredRows = this.rows; 
            this.currentPage = 1;
            this.init();
        }
        init() {
            if(this.searchInput) {
                this.searchInput.addEventListener('keyup', (e) => {
                    const term = e.target.value.toLowerCase();
                    this.filteredRows = this.rows.filter(row => row.innerText.toLowerCase().includes(term));
                    this.currentPage = 1;
                    this.render();
                });
            }
            this.render();
        }
        render() {
            if (!this.table) return;
            const totalPages = Math.ceil(this.filteredRows.length / this.rowsPerPage);
            if (this.currentPage > totalPages) this.currentPage = totalPages || 1;
            this.rows.forEach(r => r.style.display = 'none');
            const start = (this.currentPage - 1) * this.rowsPerPage;
            const end = start + this.rowsPerPage;
            this.filteredRows.slice(start, end).forEach(r => r.style.display = '');
            this.updatePagination(totalPages);
        }
        updatePagination(totalPages) {
            if (!this.paginationNav) return;
            this.paginationNav.innerHTML = '';
            if(this.filteredRows.length === 0) return;
            const prev = document.createElement('li');
            prev.className = `page-item ${this.currentPage === 1 ? 'disabled' : ''}`;
            prev.innerHTML = `<a class="page-link" href="#">&laquo;</a>`;
            prev.onclick = (e) => { e.preventDefault(); if(this.currentPage > 1) { this.currentPage--; this.render(); } };
            this.paginationNav.appendChild(prev);
            for(let i=1; i<=totalPages; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === this.currentPage ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                li.onclick = (e) => { e.preventDefault(); this.currentPage = i; this.render(); };
                this.paginationNav.appendChild(li);
            }
            const next = document.createElement('li');
            next.className = `page-item ${this.currentPage === totalPages ? 'disabled' : ''}`;
            next.innerHTML = `<a class="page-link" href="#">&raquo;</a>`;
            next.onclick = (e) => { e.preventDefault(); if(this.currentPage < totalPages) { this.currentPage++; this.render(); } };
            this.paginationNav.appendChild(next);
        }
    }

    new TableManager('tablePolygon', 'pagPolygon', 'searchGroupInput');
    
    // 2. Logic Pencarian Grup (Accordion)
    const searchInput = document.getElementById('searchGroupInput');
    if(searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            const searchText = e.target.value.toLowerCase();
            const groups = document.querySelectorAll('.grup-item');
            let hasVisibleGroup = false;
            groups.forEach(group => {
                const groupName = group.querySelector('.grup-name').textContent.toLowerCase();
                if(groupName.includes(searchText)) {
                    group.style.display = ''; hasVisibleGroup = true;
                } else {
                    group.style.display = 'none'; 
                }
            });
            const noResult = document.getElementById('noGroupFound');
            if(noResult) noResult.style.display = hasVisibleGroup ? 'none' : 'block';
        });
    }

    // 3. Logic Loading Tombol Simpan
    const formPoly = document.getElementById('formPolygonData');
    const btnSimpan = document.getElementById('btnSimpanPoly');
    if(formPoly) {
        formPoly.addEventListener('submit', function() {
            btnSimpan.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengupload...';
            btnSimpan.classList.add('btn-loading');
            // Catatan: Jangan disable tombol via JS jika menggunakan form submit standar karena POST data bisa tidak terkirim di beberapa browser/versi. 
            // Kita gunakan pointer-events:none dari CSS .btn-loading.
        });
    }
});

// ===========================================
// 1. LOGIC GRUP
// ===========================================
function openGrupModal(data = null) {
    const modalEl = document.getElementById('modalGrup');
    modalEl.querySelector('form').reset();
    document.getElementById('grup_id').value = '';
    document.getElementById('template_container').innerHTML = '';

    let style = { color: '#3388ff', weight: 3, opacity: 1, fillColor: '#3388ff', fillOpacity: 0.2, dashArray: '' };

    if (data) {
        document.getElementById('grup_id').value = data.id_dg;
        document.getElementById('grup_nama').value = data.nama_grup;
        style = { color: data.color, weight: data.weight, opacity: data.opacity, fillColor: data.fillColor, fillOpacity: data.fillOpacity, dashArray: data.dashArray || '' };
        try { const t = JSON.parse(data.atribut_default); if(Array.isArray(t)) t.forEach(x => addTemplateRow(x.label)); } catch(e){}
    } else {
        addTemplateRow();
    }

    document.getElementById('style_color').value = style.color;
    document.getElementById('style_weight').value = style.weight;
    document.getElementById('style_opacity').value = style.opacity;
    document.getElementById('style_fillColor').value = style.fillColor;
    document.getElementById('style_fillOpacity').value = style.fillOpacity;
    document.getElementById('style_dashArray').value = style.dashArray;

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    modalEl.addEventListener('shown.bs.modal', () => setTimeout(() => initStyleMap(style), 200), { once: true });
}

function addTemplateRow(value = '') {
    const div = document.createElement('div'); div.className = 'input-group mb-2';
    div.innerHTML = `<span class="input-group-text bg-light"><i class="fas fa-tag"></i></span><input type="text" name="template_attr[]" class="form-control form-control-sm" value="${value}" placeholder="Nama Kolom"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>`;
    document.getElementById('template_container').appendChild(div);
}

function initStyleMap(style) {
    if (styleMap) { styleMap.remove(); styleMap = null; }
    styleMap = L.map('map_style_preview', { zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false }).setView(DEFAULT_COORD, 13);
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}').addTo(styleMap);
    styleLayer = L.polygon([[DEFAULT_COORD[0]+0.01, DEFAULT_COORD[1]-0.01], [DEFAULT_COORD[0]-0.01, DEFAULT_COORD[1]+0.01], [DEFAULT_COORD[0]-0.01, DEFAULT_COORD[1]-0.02]], style).addTo(styleMap);
    styleMap.fitBounds(styleLayer.getBounds(), { padding: [20, 20] });

    ['style_color', 'style_weight', 'style_opacity', 'style_fillColor', 'style_fillOpacity', 'style_dashArray'].forEach(id => {
        const el = document.getElementById(id); const n = el.cloneNode(true); el.parentNode.replaceChild(n, el);
        n.addEventListener('input', updateStylePreview); n.addEventListener('change', updateStylePreview);
    });
}

function updateStylePreview() {
    if (!styleLayer) return;
    styleLayer.setStyle({
        color: document.getElementById('style_color').value, weight: document.getElementById('style_weight').value, opacity: document.getElementById('style_opacity').value,
        fillColor: document.getElementById('style_fillColor').value, fillOpacity: document.getElementById('style_fillOpacity').value, dashArray: document.getElementById('style_dashArray').value
    });
}

function deleteGrup(id) { if(confirm('Hapus grup ini?')) window.location.href = `<?= base_url('geospasial/deleteGrup') ?>/${id}`; }

// ===========================================
// 2. LOGIC EDITOR POLIGON (AUTO TEMPLATE)
// ===========================================
function openAddPolygon(grupId, grupData) { preparePolygonModal(grupId, grupData); }
function openEditPolygon(data, grupData) { preparePolygonModal(data.id_dg, grupData, data); }

// ===========================================
// UPDATE FUNGSI INI DI DALAM CODE ANDA
// ===========================================

function preparePolygonModal(grupId, grupData, data = null) {
    const modalEl = document.getElementById('modalDataPolygon');
    document.getElementById('formPolygonData').reset();
    document.getElementById('attribute_container').innerHTML = '';
    document.getElementById('poly_id_grup').value = grupId;
    
    // Reset Container PDF Lama
    const filesContainer = document.getElementById('existing_files_container');
    filesContainer.innerHTML = ''; 

    currentGroupStyle = { 
        color: grupData.color, 
        weight: grupData.weight, 
        opacity: grupData.opacity, 
        fillColor: grupData.fillColor, 
        fillOpacity: grupData.fillOpacity, 
        dashArray: grupData.dashArray 
    };

    // Reset tombol simpan
    const btnSimpan = document.getElementById('btnSimpanPoly');
    if(btnSimpan){
        btnSimpan.innerHTML = '<i class="fas fa-save"></i> Simpan Data';
        btnSimpan.classList.remove('btn-loading');
    }

    if (data) {
        // --- MODE EDIT ---
        document.getElementById('poly_id').value = data.id;
        document.getElementById('poly_nama').value = data.nama_dg;
        document.getElementById('poly_geojson').value = data.data_geospasial;
        
        // --- LOGIKA BARU: MENAMPILKAN LIST PDF ---
        // Asumsi: data.daftar_pdf berisi array [{id: 1, judul_pdf: '...', file_path: '...'}]
        // Data ini harus dikirim dari Controller melalui variabel $item
        
        if (data.daftar_pdf && data.daftar_pdf.length > 0) {
            data.daftar_pdf.forEach(pdf => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2';
                itemDiv.id = `pdf-item-${pdf.id}`;
                itemDiv.innerHTML = `
                    <div class="d-flex align-items-center overflow-hidden">
                        <i class="fas fa-file-pdf text-danger me-2"></i>
                        <a href="<?= base_url('uploads/pdf/') ?>/${pdf.file_path}" target="_blank" class="text-decoration-none text-dark small text-truncate" style="max-width: 200px;" title="${pdf.judul_pdf}">
                            ${pdf.judul_pdf}
                        </a>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-danger ms-2" onclick="deletePdfItem(${pdf.id})" title="Hapus File Ini">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                filesContainer.appendChild(itemDiv);
            });
        } else {
            filesContainer.innerHTML = '<div class="text-muted small text-center fst-italic py-1 border rounded bg-white">Belum ada dokumen PDF.</div>';
        }

        try { 
            const a = JSON.parse(data.atribut_tambahan); 
            if(Array.isArray(a)) a.forEach(x => addAttributeRow(x.label, x.value)); 
        } catch(e){}

    } else {
        // --- MODE TAMBAH ---
        document.getElementById('poly_id').value = '';
        document.getElementById('poly_geojson').value = '';
        filesContainer.innerHTML = ''; // Kosongkan list
        
        try { 
            const t = JSON.parse(grupData.atribut_default); 
            if(Array.isArray(t) && t.length > 0) t.forEach(x => addAttributeRow(x.label, '')); 
            else addAttributeRow(); 
        } catch(e) { addAttributeRow(); }
    }

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    modalEl.addEventListener('shown.bs.modal', () => setTimeout(() => initDrawMap(data ? data.data_geospasial : null), 200), { once: true });
}

// --- FUNGSI BARU: HAPUS PDF VIA AJAX ---
function deletePdfItem(idPdf) {
    if(!confirm('Apakah Anda yakin ingin menghapus file PDF ini secara permanen?')) return;

    // Ganti URL sesuai route Anda
    fetch(`<?= base_url('geospasial/deletePdf') ?>/${idPdf}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            // Hapus elemen dari tampilan modal
            const el = document.getElementById(`pdf-item-${idPdf}`);
            if(el) el.remove();
            
            // Cek jika kosong, tampilkan placeholder
            const container = document.getElementById('existing_files_container');
            if(container.children.length === 0) {
                container.innerHTML = '<div class="text-muted small text-center fst-italic py-1 border rounded bg-white">Belum ada dokumen PDF.</div>';
            }
        } else {
            alert('Gagal menghapus: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus file.');
    });
}

function addAttributeRow(label = '', value = '') {
    const div = document.createElement('div'); div.className = 'attr-row';
    div.innerHTML = `<input type="text" name="attr_key[]" class="form-control form-control-sm" placeholder="Label" value="${label}" required><input type="text" name="attr_val[]" class="form-control form-control-sm" placeholder="Isi Data" value="${value}"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>`;
    document.getElementById('attribute_container').appendChild(div);
}

// Map Drawing
function initDrawMap(geoJsonString = null) {
    if (drawMap) { drawMap.remove(); drawMap = null; }
    drawMap = L.map('map_draw_polygon').setView(DEFAULT_COORD, 14);
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}').addTo(drawMap);
    markers = []; drawLayer = null;
    if (geoJsonString) loadGeoJSON(geoJsonString);
    drawMap.on('click', function(e) { addMarker(e.latlng); });
}

function addMarker(latlng) {
    const m = L.marker(latlng, { draggable: true }).addTo(drawMap);
    m.on('drag', updatePolyFromMarkers);
    m.on('click', function() { drawMap.removeLayer(m); markers = markers.filter(x => x !== m); updatePolyFromMarkers(); });
    markers.push(m); updatePolyFromMarkers();
}

function updatePolyFromMarkers() {
    const latlngs = markers.map(m => m.getLatLng());
    if (drawLayer) drawMap.removeLayer(drawLayer);
    if (latlngs.length >= 3) {
        drawLayer = L.polygon(latlngs, currentGroupStyle).addTo(drawMap);
        document.getElementById('poly_geojson').value = JSON.stringify(drawLayer.toGeoJSON());
    } else document.getElementById('poly_geojson').value = '';
}

function clearMapDraw() {
    markers.forEach(m => drawMap.removeLayer(m));
    markers = [];
    if (drawLayer) drawMap.removeLayer(drawLayer);
    document.getElementById('poly_geojson').value = '';
}

function handleFileUpload(input) {
    const file = input.files[0]; if(!file) return;
    const r = new FileReader();
    r.onload = function(e) { try { const json = JSON.parse(e.target.result); clearMapDraw(); loadGeoJSON(JSON.stringify(json)); } catch(err) { alert('GeoJSON Invalid'); } };
    r.readAsText(file); input.value = '';
}

function loadGeoJSON(jsonString) {
    const json = JSON.parse(jsonString); const layer = L.geoJSON(json); const layers = layer.getLayers();
    if(layers.length > 0 && layers[0] instanceof L.Polygon) {
        let latlngs = layers[0].getLatLngs(); if(Array.isArray(latlngs[0])) latlngs = latlngs[0];
        latlngs.forEach(ll => addMarker(ll)); drawMap.fitBounds(layers[0].getBounds());
    } else alert('Data bukan poligon.');
}

function deleteData(type, id) {
    if(confirm('Hapus data ini?')) window.location.href = `<?= base_url('geospasial/delete') ?>/${type}/${id}`;
}


// ===========================================
// LOGIKA PENCARIAN DI DALAM SETIAP GRUP
// ===========================================
document.addEventListener('keyup', function(e) {
    // Cek apakah elemen yang diketik adalah input pencarian grup
    if (e.target && e.target.classList.contains('search-in-group')) {
        const input = e.target;
        const filter = input.value.toLowerCase();
        
        // Cari container accordion-body terdekat
        const body = input.closest('.accordion-body');
        
        // Cari tabel di dalam body tersebut
        const rows = body.querySelectorAll('.table-in-group tbody .item-row');
        const noResultRow = body.querySelector('.search-no-result');
        const noDataRow = body.querySelector('.no-data'); // Baris "Belum ada data" bawaan PHP
        
        let hasVisible = false;

        rows.forEach(row => {
            // Ambil teks dari kolom Nama (class itemName)
            const nameCell = row.querySelector('.itemName');
            const text = nameCell ? nameCell.textContent.toLowerCase() : '';
            
            // Cek juga atribut di dalamnya (opsional, biar pencarian lebih luas)
            const rowText = row.textContent.toLowerCase();

            if (rowText.indexOf(filter) > -1) {
                row.style.display = "";
                hasVisible = true;
            } else {
                row.style.display = "none";
            }
        });

        // Tampilkan pesan "Tidak ditemukan" jika hasil filter kosong
        // Tapi jangan tampilkan jika memang datanya kosong dari awal (noDataRow visible)
        if (noResultRow) {
            if (!hasVisible && (!noDataRow || noDataRow.style.display === 'none')) {
                noResultRow.style.display = "table-row";
            } else {
                noResultRow.style.display = "none";
            }
        }
    }
});
</script>