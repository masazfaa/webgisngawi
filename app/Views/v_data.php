<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
    /* Styling Container Utama */
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
    .tab-content { padding: 20px; background: #fff; min-height: 400px; }

    /* Map Preview & Drawing Styles */
    #map_style_preview, #map_draw_polygon {
        height: 300px; width: 100%; border: 1px solid #ccc; border-radius: 4px;
    }
    .attr-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .btn-xs {
    padding: 0.15rem 0.5rem; /* Padding lebih tipis */
    font-size: 0.75rem;      /* Font lebih kecil */
    line-height: 1.5;
    border-radius: 0.2rem;
    }

    /* Opsional: Agar ikon dan teks di tombol rata tengah */
    .btn-xs i {
        font-size: 0.7rem;
        margin-right: 2px;
    }
    .scrollable-area {
    max-height: 65vh; /* Tinggi maksimal 65% dari layar */
    overflow-y: auto; /* Munculkan scrollbar jika konten melebihi */
    padding-right: 5px; /* Jarak agar scrollbar tidak menempel konten */
    }

    /* Mempercantik Scrollbar (Chrome/Safari/Edge) */
    .scrollable-area::-webkit-scrollbar {
        width: 6px;
    }
    .scrollable-area::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .scrollable-area::-webkit-scrollbar-thumb {
        background: #ccc; 
        border-radius: 3px;
    }
    .scrollable-area::-webkit-scrollbar-thumb:hover {
        background: #aaa; 
    }
</style>

<div class="geo-container">
    <div class="geo-header">
        <ul class="nav nav-tabs" id="geoTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="polygon-tab" data-bs-toggle="tab" data-bs-target="#polygon-pane" type="button">
                    <i class="fas fa-draw-polygon me-1"></i> Data Poligon
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="line-tab" data-bs-toggle="tab" data-bs-target="#line-pane" type="button">
                    <i class="fas fa-route me-1"></i> Data Line
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="point-tab" data-bs-toggle="tab" data-bs-target="#point-pane" type="button">
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
                          <span class="input-group-text bg-white text-muted border-end-0">
                              <i class="fas fa-search"></i>
                          </span>
                          <input type="text" id="searchGroupInput" class="form-control border-start-0 ps-0" placeholder="Cari Grup Poligon...">
                      </div>
                  </div>

                  <div class="accordion scrollable-area" id="accordionPolygon">
                      <?php if(!empty($grupPolygon)): foreach($grupPolygon as $index => $grup): ?>
                      
                      <div class="accordion-item grup-item">
                          <h2 class="accordion-header" id="heading<?= $grup['id_dg'] ?>">
                              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $grup['id_dg'] ?>">
                                  <span class="d-flex align-items-center w-100">
                                      
                                      <span class="me-3 shadow-sm" 
                                            style="
                                              display: inline-block;
                                              width: 50px; height: 25px;
                                              background-color: <?= $grup['fillColor'] ?>;
                                              border: <?= min($grup['weight'], 5) ?>px <?= !empty($grup['dashArray']) ? 'dashed' : 'solid' ?> <?= $grup['color'] ?>;
                                              opacity: 0.9; border-radius: 2px;
                                            ">
                                      </span>

                                      <div class="d-flex flex-column">
                                          <strong class="grup-name"><?= $grup['nama_grup'] ?></strong>
                                          <small class="text-muted" style="font-size: 0.75rem;">
                                              (Color: <?= $grup['color'] ?> | Items: <?= count($grup['items']) ?>)
                                          </small>
                                      </div>

                                  </span>
                              </button>
                          </h2>
                          <div id="collapse<?= $grup['id_dg'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionPolygon">
                              <div class="accordion-body">
                                  
                                  <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                      <div>
                                          <button class="btn btn-sm btn-outline-primary" onclick='openGrupModal(<?= json_encode($grup) ?>)'>
                                              <i class="fas fa-palette"></i> Edit Style Grup
                                          </button>
                                          <button class="btn btn-sm btn-outline-danger" onclick="deleteGrup(<?= $grup['id_dg'] ?>)">
                                              <i class="fas fa-trash"></i> Hapus Grup
                                          </button>
                                      </div>
                                      <button class="btn btn-sm btn-success" onclick='openAddPolygon(<?= $grup["id_dg"] ?>, <?= json_encode($grup) ?>)'>
                                          <i class="fas fa-plus"></i> Tambah Poligon
                                      </button>
                                  </div>

                                  <table class="table table-sm table-hover table-bordered mb-0">
                                      <thead class="table-light">
                                          <tr>
                                              <th class="text-center" style="width: 50px;">ID</th>
                                              <th>Nama Poligon</th>
                                              <th>Atribut</th>
                                              <th class="text-center" style="width: 140px;">Aksi</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php if(!empty($grup['items'])): foreach($grup['items'] as $item): ?>
                                          <tr>
                                              <td class="text-center align-middle"><?= $item['id'] ?></td>
                                              <td class="align-middle fw-bold"><?= $item['nama_dg'] ?></td>
                                              <td class="align-middle">
                                                  <?php 
                                                      $attrs = json_decode($item['atribut_tambahan'], true);
                                                      if($attrs) {
                                                          echo '<div style="font-size: 0.8rem; line-height: 1.2;">';
                                                          foreach($attrs as $a) {
                                                              echo "<span class='badge bg-light text-dark border me-1'>{$a['label']}: {$a['value']}</span>";
                                                          }
                                                          echo '</div>';
                                                      } else { echo "-"; }
                                                  ?>
                                              </td>
                                              <td class="text-center align-middle">
                                                  <div class="btn-group" role="group">
                                                      <button type="button" class="btn btn-warning btn-xs text-white" 
                                                              onclick='openEditPolygon(<?= json_encode($item) ?>, <?= json_encode($grup) ?>)'>
                                                          <i class="fas fa-edit"></i> Edit
                                                      </button>
                                                      <button type="button" class="btn btn-danger btn-xs" 
                                                              onclick="deleteData('polygon', <?= $item['id'] ?>)">
                                                          <i class="fas fa-trash"></i> Hapus
                                                      </button>
                                                  </div>
                                              </td>
                                          </tr>
                                          <?php endforeach; else: ?>
                                              <tr><td colspan="4" class="text-center text-muted py-3 small">Belum ada data poligon di grup ini.</td></tr>
                                          <?php endif; ?>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                      </div>
                      <?php endforeach; else: ?>
                          <div class="alert alert-info text-center mt-3">Belum ada Grup Poligon.</div>
                      <?php endif; ?>
                      
                      <div id="noGroupFound" class="alert alert-warning text-center mt-3" style="display: none;">
                          Grup poligon tidak ditemukan.
                      </div>
                  </div>
              </div>

        <div class="tab-pane fade" id="line-pane">
            <div class="alert alert-secondary">Fitur Line akan dikembangkan nanti (Fokus Poligon dulu).</div>
        </div>

        <div class="tab-pane fade" id="point-pane">
             <div class="alert alert-secondary">Fitur Point akan dikembangkan nanti (Fokus Poligon dulu).</div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGrup" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('geospasial/saveGrup') ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Grup & Style Poligon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_dg" id="grup_id">
                    <input type="hidden" name="jenis_peta" value="Polygon">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Nama Grup</label>
                                <input type="text" name="nama_grup" id="grup_nama" class="form-control" required>
                            </div>
                            
                            <h6 class="border-bottom pb-1 mb-2">Konfigurasi Style</h6>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small">Warna Garis</label>
                                    <input type="color" name="color" id="style_color" class="form-control form-control-color w-100" value="#3388ff">
                                </div>
                                <div class="col-6">
                                    <label class="small">Warna Isi</label>
                                    <input type="color" name="fillColor" id="style_fillColor" class="form-control form-control-color w-100" value="#3388ff">
                                </div>
                                
                                <div class="col-6">
                                    <label class="small">Tebal Garis</label>
                                    <input type="number" name="weight" id="style_weight" class="form-control form-control-sm" value="3" min="1">
                                </div>
                                <div class="col-6">
                                    <label class="small">Tipe Garis</label>
                                    <select name="dashArray" id="style_dashArray" class="form-select form-select-sm">
                                        <option value="">Solid (Lurus)</option>
                                        <option value="5, 5">Dashed (Putus)</option>
                                        <option value="1, 5">Dotted (Titik)</option>
                                        <option value="10, 5">Long Dash</option>
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="small">Opacity Garis (0-1)</label>
                                    <input type="number" name="opacity" id="style_opacity" class="form-control form-control-sm" value="1.0" step="0.1" min="0" max="1">
                                </div>
                                <div class="col-6">
                                    <label class="small">Opacity Isi (0-1)</label>
                                    <input type="number" name="fillOpacity" id="style_fillOpacity" class="form-control form-control-sm" value="0.2" step="0.1" min="0" max="1">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 d-flex flex-column">
                            <label class="mb-2 fw-bold">Real-time Preview</label>
                            <div id="map_style_preview" style="flex-grow: 1; min-height: 300px; border: 2px solid #ddd; border-radius: 4px;"></div>
                            <small class="text-muted mt-1 fst-italic">Geser/ubah nilai di kiri untuk melihat hasil.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Grup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDataPolygon" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="<?= base_url('geospasial/save/polygon') ?>" method="post" id="formPolygonData">
                <div class="modal-header">
                    <h5 class="modal-title">Data Poligon</h5>
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
                                <input type="text" name="nama_dg" id="poly_nama" class="form-control" placeholder="Contoh: Sawah Pak Budi" required>
                            </div>
                            
                            <hr>
                            <h6>Atribut Tambahan</h6>
                            <div id="attribute_container">
                                </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2 w-100" onclick="addAttributeRow()">
                                <i class="fas fa-plus"></i> Tambah Kolom Atribut
                            </button>
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex justify-content-between mb-2">
                                <label>Gambar Area / Upload GeoJSON</label>
                                <div>
                                    <input type="file" id="fileGeoJSON" accept=".json,.geojson" class="d-none" onchange="handleFileUpload(this)">
                                    <button type="button" class="btn btn-sm btn-info text-white" onclick="document.getElementById('fileGeoJSON').click()">
                                        <i class="fas fa-upload"></i> Upload GeoJSON
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="clearMapDraw()">
                                        <i class="fas fa-undo"></i> Reset Gambar
                                    </button>
                                </div>
                            </div>
                            
                            <div id="map_draw_polygon"></div>
                            
                            <div class="alert alert-light mt-2 small">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Manual:</strong> Klik peta untuk tambah titik (min 3). Geser marker untuk edit. Klik marker untuk hapus titik.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Poligon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variabel Global
let styleMap = null, styleLayer = null;
let drawMap = null, drawLayer = null, markers = [];
let currentGroupStyle = {}; 

// Koordinat Default (Ngawi)
const defaultLat = -7.408019826354289;
const defaultLng = 111.4428818182571;

document.addEventListener('DOMContentLoaded', function () {
    // --- CLASS MANAGER PAGINATION & SEARCH (FIXED) ---
    class TableManager {
        constructor(tableId, paginationId, searchId) {
            this.table = document.getElementById(tableId);
            
            // --- SAFETY CHECK (PENGAMAN) ---
            // Jika tabel tidak ditemukan di HTML (misal tab Line/Point sedang "Coming Soon")
            // Maka hentikan proses agar tidak error.
            if (!this.table) {
                return; 
            }
            // -------------------------------

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
            // Cek lagi table ada atau tidak (double safety)
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
            if (!this.paginationNav) return; // Safety check untuk navigasi

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

    // Inisialisasi tetap sama, tapi sekarang aman walau tabelnya tidak ada
    new TableManager('tablePolygon', 'pagPolygon', 'searchPolygon');
    new TableManager('tableLine', 'pagLine', 'searchLine');
    new TableManager('tablePoint', 'pagPoint', 'searchPoint');
});

// ==========================================
// 1. LOGIC GRUP & STYLE PREVIEW
// ==========================================
function openGrupModal(data = null) {
    const modalEl = document.getElementById('modalGrup');
    const form = modalEl.querySelector('form');
    form.reset();
    document.getElementById('grup_id').value = '';

    // Default Style
    let style = { 
        color: '#3388ff', weight: 3, opacity: 1, 
        fillColor: '#3388ff', fillOpacity: 0.2, dashArray: '' 
    };

    if (data) {
        document.getElementById('grup_id').value = data.id_dg;
        document.getElementById('grup_nama').value = data.nama_grup;
        
        style = {
            color: data.color, weight: data.weight, opacity: data.opacity,
            fillColor: data.fillColor, fillOpacity: data.fillOpacity, 
            dashArray: data.dashArray || ''
        };
    }

    // Set Input Values
    document.getElementById('style_color').value = style.color;
    document.getElementById('style_weight').value = style.weight;
    document.getElementById('style_opacity').value = style.opacity;
    document.getElementById('style_fillColor').value = style.fillColor;
    document.getElementById('style_fillOpacity').value = style.fillOpacity;
    document.getElementById('style_dashArray').value = style.dashArray;

    modalEl.removeAttribute('aria-hidden');
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();

    // TRIGGER PETA SAAT MODAL MUNCUL
    modalEl.addEventListener('shown.bs.modal', function () {
        setTimeout(() => {
            initStyleMap(style);
        }, 100);
    }, { once: true });
}

function initStyleMap(initialStyle) {
    if (styleMap) { 
        styleMap.off();
        styleMap.remove(); 
        styleMap = null;
    }

    // Init Map Baru (Titik Ngawi)
    styleMap = L.map('map_style_preview', { 
        attributionControl: false,
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false
    }).setView([defaultLat, defaultLng], 13);
    
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}').addTo(styleMap);

    // Buat Dummy Polygon (Segitiga Sederhana di Ngawi)
    // Offset sedikit dari titik tengah agar terlihat bagus
    const latlngs = [
        [defaultLat + 0.01, defaultLng - 0.01], 
        [defaultLat - 0.01, defaultLng + 0.01], 
        [defaultLat - 0.01, defaultLng - 0.02]
    ];
    
    styleLayer = L.polygon(latlngs, initialStyle).addTo(styleMap);
    styleMap.fitBounds(styleLayer.getBounds(), { padding: [20, 20] });

    styleMap.invalidateSize();

    // Event Listener Real-time
    const inputIds = ['style_color', 'style_weight', 'style_opacity', 'style_fillColor', 'style_fillOpacity', 'style_dashArray'];
    
    inputIds.forEach(id => {
        const el = document.getElementById(id);
        const newEl = el.cloneNode(true);
        el.parentNode.replaceChild(newEl, el);
        
        newEl.addEventListener('input', updateStylePreview);
        newEl.addEventListener('change', updateStylePreview); 
    });
}

function updateStylePreview() {
    if (!styleLayer) return;

    const newStyle = {
        color: document.getElementById('style_color').value,
        weight: parseInt(document.getElementById('style_weight').value) || 1,
        opacity: parseFloat(document.getElementById('style_opacity').value) || 1,
        fillColor: document.getElementById('style_fillColor').value,
        fillOpacity: parseFloat(document.getElementById('style_fillOpacity').value) || 0.2,
        dashArray: document.getElementById('style_dashArray').value
    };

    styleLayer.setStyle(newStyle);
}

function deleteGrup(id) {
    if(confirm('Hapus Grup ini? Semua data poligon di dalamnya akan ikut terhapus!')) {
        window.location.href = `<?= base_url('geospasial/deleteGrup') ?>/${id}`;
    }
}

// ==========================================
// 2. LOGIC DATA POLIGON (DRAW & ATTRIB)
// ==========================================

function openAddPolygon(grupId, grupStyle) {
    preparePolygonModal(grupId, grupStyle);
}

function openEditPolygon(data, grupStyle) {
    preparePolygonModal(data.id_dg, grupStyle, data);
}

function preparePolygonModal(grupId, grupStyle, data = null) {
    const modalEl = document.getElementById('modalDataPolygon');
    document.getElementById('formPolygonData').reset();
    document.getElementById('attribute_container').innerHTML = ''; 
    
    document.getElementById('poly_id_grup').value = grupId;
    
    currentGroupStyle = {
        color: grupStyle.color, 
        weight: grupStyle.weight, 
        opacity: grupStyle.opacity, 
        fillColor: grupStyle.fillColor, 
        fillOpacity: grupStyle.fillOpacity,
        dashArray: grupStyle.dashArray
    };

    if (data) {
        document.getElementById('poly_id').value = data.id;
        document.getElementById('poly_nama').value = data.nama_dg;
        document.getElementById('poly_geojson').value = data.data_geospasial;

        try {
            const attrs = JSON.parse(data.atribut_tambahan);
            if(Array.isArray(attrs)) {
                attrs.forEach(a => addAttributeRow(a.label, a.value));
            }
        } catch(e) {}
    } else {
        document.getElementById('poly_id').value = '';
        document.getElementById('poly_geojson').value = '';
        addAttributeRow(); 
    }

    modalEl.removeAttribute('aria-hidden');
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();

    modalEl.addEventListener('shown.bs.modal', function () {
        setTimeout(() => {
            initDrawMap(data ? data.data_geospasial : null);
        }, 100);
    }, { once: true });
}

// Atribut Dinamis
function addAttributeRow(label = '', value = '') {
    const container = document.getElementById('attribute_container');
    const div = document.createElement('div');
    div.className = 'attr-row';
    div.innerHTML = `
        <input type="text" name="attr_key[]" class="form-control form-control-sm" placeholder="Label (mis: Luas)" value="${label}" required>
        <input type="text" name="attr_val[]" class="form-control form-control-sm" placeholder="Nilai" value="${value}">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>
    `;
    container.appendChild(div);
}

// Logic Drawing
function initDrawMap(initialGeoJSON = null) {
    if (drawMap) { 
        drawMap.off();
        drawMap.remove(); 
        drawMap = null;
    }
    
    // Set View ke Ngawi
    drawMap = L.map('map_draw_polygon').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}').addTo(drawMap);
    
    drawMap.invalidateSize();

    markers = [];
    drawLayer = null;

    if (initialGeoJSON) {
        loadGeoJSONToMap(initialGeoJSON);
    }

    drawMap.on('click', function(e) {
        addMarker(e.latlng);
    });
}

function addMarker(latlng) {
    const marker = L.marker(latlng, { draggable: true }).addTo(drawMap);
    
    marker.on('drag', updatePolygonFromMarkers);
    marker.on('click', function() {
        drawMap.removeLayer(marker);
        markers = markers.filter(m => m !== marker);
        updatePolygonFromMarkers();
    });

    markers.push(marker);
    updatePolygonFromMarkers();
}

function updatePolygonFromMarkers() {
    const latlngs = markers.map(m => m.getLatLng());

    if (drawLayer) drawMap.removeLayer(drawLayer);

    if (latlngs.length >= 3) {
        drawLayer = L.polygon(latlngs, currentGroupStyle).addTo(drawMap);
        
        const geojson = drawLayer.toGeoJSON();
        document.getElementById('poly_geojson').value = JSON.stringify(geojson);
    } else {
        document.getElementById('poly_geojson').value = '';
    }
}

function clearMapDraw() {
    markers.forEach(m => drawMap.removeLayer(m));
    markers = [];
    if (drawLayer) drawMap.removeLayer(drawLayer);
    document.getElementById('poly_geojson').value = '';
}

function handleFileUpload(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const geojson = JSON.parse(e.target.result);
            clearMapDraw();
            loadGeoJSONToMap(JSON.stringify(geojson));
        } catch (err) {
            alert('File GeoJSON tidak valid!');
        }
    };
    reader.readAsText(file);
    input.value = '';
}

function loadGeoJSONToMap(jsonString) {
    try {
        const geojson = JSON.parse(jsonString);
        const layer = L.geoJSON(geojson);
        const layers = layer.getLayers();
        
        if (layers.length > 0) {
            const poly = layers[0];
            if (poly instanceof L.Polygon) {
                let latlngs = poly.getLatLngs();
                if(Array.isArray(latlngs[0])) latlngs = latlngs[0]; 

                latlngs.forEach(ll => {
                    addMarker(ll);
                });
                drawMap.fitBounds(poly.getBounds());
            } else {
                alert('GeoJSON bukan Polygon!');
            }
        }
    } catch(e) {
        console.error("Error loading GeoJSON", e);
    }
}

function deleteData(type, id) {
    if(confirm('Hapus data ini?')) {
        window.location.href = `<?= base_url('geospasial/delete') ?>/${type}/${id}`;
    }
}

// Fungsi Modal Standar (Line & Point)
function openAddModal(type) {
    if(type === 'polygon') return; 
    const modalId = 'modal' + type;
    const modalEl = document.getElementById(modalId);
    if(modalEl) {
        modalEl.querySelector('form').reset();
        modalEl.querySelector('[name="id"]').value = ''; 
        modalEl.removeAttribute('aria-hidden');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function openEditModal(type, data) {
    if(type === 'polygon') return; 
    const modalId = 'modal' + type;
    const modalEl = document.getElementById(modalId);
    if(modalEl) {
        document.getElementById('id_' + type).value = data.id;
        document.getElementById('nama_' + type).value = data.nama_dg;
        document.getElementById('grup_' + type).value = data.id_dg;
        document.getElementById('geo_' + type).value = data.data_geospasial;
        modalEl.removeAttribute('aria-hidden');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// --- LOGIKA PENCARIAN GRUP (REAL-TIME) ---
const searchInput = document.getElementById('searchGroupInput');

if(searchInput) {
    searchInput.addEventListener('keyup', function(e) {
        const searchText = e.target.value.toLowerCase();
        const groups = document.querySelectorAll('.grup-item');
        let hasVisibleGroup = false;

        groups.forEach(group => {
            // Ambil nama grup dari elemen dengan class 'grup-name'
            const groupName = group.querySelector('.grup-name').textContent.toLowerCase();
            
            if(groupName.includes(searchText)) {
                group.style.display = ''; // Tampilkan
                hasVisibleGroup = true;
            } else {
                group.style.display = 'none'; // Sembunyikan
            }
        });

        // Tampilkan pesan jika tidak ada hasil
        const noResult = document.getElementById('noGroupFound');
        if(noResult) {
            noResult.style.display = hasVisibleGroup ? 'none' : 'block';
        }
    });
}
</script>