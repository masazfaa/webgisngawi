<div class="d-flex justify-content-between align-items-center mb-3">
  <button type="button" class="button" data-bs-toggle="modal" data-bs-target="#addDataModal">+ Add New Data</button>
  <input type="text" id="searchInput" class="form-control w-25" placeholder="Search...">
</div>

<div class="table-wrapper">
  <table id="dataTable" class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Laporan</th>
        <th>Tingkat Kerusakan</th>
        <th>Progress Perbaikan</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dataLaporan as $data): ?>
        <tr>
          <td><?= $data['id']; ?></td>
          <td><?= $data['laporan']; ?></td>
            <td>
              <div class="fw-bold" style="color: <?= getKerusakanColor($data['tingkat_kerusakan']) ?>">
                <?= $data['tingkat_kerusakan']; ?>%
              </div>
            </td>
            <td>
              <div class="fw-bold" style="color: <?= getProgressColor($data['progress_perbaikan']) ?>">
                <?= $data['progress_perbaikan']; ?>%
              </div>
            </td>
          <td>
            <div class="btn-group">
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editDataModal" 
                onclick="populateEditModal(<?= htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8') ?>)">
                Edit
              </button>
              <button class="btn btn-sm btn-danger" onclick="showDeleteModal(<?= $data['id']; ?>)">Delete</button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<nav id="pagination" class="mt-3">
  <ul class="pagination justify-content-center"></ul>
</nav>

<!-- Leaflet Styles -->
<link rel="stylesheet" href="<?= base_url() ?>leaflet/1.3.0/leaflet.css" />
<script src="<?= base_url() ?>leaflet/1.3.0/leaflet.js"></script>

<!-- Modal Add with Map -->
<div class="modal fade" id="addDataModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data" action="<?= base_url('home/save'); ?>">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Laporan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Laporan</label>
            <textarea class="form-control" name="laporan" required></textarea>
          </div>
        <div class="mb-3">
          <label>Tingkat Kerusakan</label>
          <input type="range" min="0" max="100" value="0" class="form-range" name="tingkat_kerusakan" id="add-kerusakan-range" oninput="updateKerusakanColor('add')">
          <div id="add-kerusakan-label" class="text-center mt-1 fw-bold"></div>
        </div>
        <div class="mb-3">
          <label>Progress Perbaikan</label>
          <input type="range" min="0" max="100" value="0" class="form-range" name="progress_perbaikan" id="add-progress-range" oninput="updateProgressColor('add')">
          <div id="add-progress-label" class="text-center mt-1 fw-bold"></div>
        </div>
          <div class="mb-3">
            <label>Koordinat</label>
            <input type="text" id="add-koordinat" name="data_koordinat" class="form-control" required>
          </div>
          <div class="mb-3">
            <div id="add-map" style="height:300px;"></div>
          </div>
          <div class="mb-3">
            <label>Upload Foto</label>
            <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit with Map -->
<div class="modal fade" id="editDataModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data" action="<?= base_url('home/updateData'); ?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit Laporan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit-id" name="id">
          <div class="mb-3">
            <label>Laporan</label>
            <textarea id="edit-laporan" name="laporan" class="form-control" required></textarea>
          </div>
            <div class="mb-3">
              <label>Tingkat Kerusakan</label>
              <input type="range" min="0" max="100" name="tingkat_kerusakan" class="form-range" id="edit-kerusakan-range" oninput="updateKerusakanColor('edit')">
              <div id="edit-kerusakan-label" class="text-center mt-1 fw-bold"></div>
            </div>
            <div class="mb-3">
              <label>Progress Perbaikan</label>
              <input type="range" min="0" max="100" name="progress_perbaikan" class="form-range" id="edit-progress-range" oninput="updateProgressColor('edit')">
              <div id="edit-progress-label" class="text-center mt-1 fw-bold"></div>
            </div>
          <div class="mb-3">
            <label>Koordinat</label>
            <input type="text" id="edit-koordinat" name="data_koordinat" class="form-control" required>
          </div>
          <div class="mb-3">
            <div id="edit-map" style="height:300px;"></div>
          </div>
          <div class="mb-3">
            <label>Foto Saat Ini</label>
            <div id="existing-photos" class="d-flex flex-wrap gap-2"></div>
          </div>
          <div class="mb-3">
            <label>Tambah Foto</label>
            <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    
function loadExistingPhotos(dataId) {
  const photoContainer = document.getElementById('existing-photos');
  photoContainer.innerHTML = '<div class="text-muted">Memuat foto...</div>';

  fetch(`<?= base_url('home/get_fotos'); ?>/${dataId}`)
    .then(res => res.json())
    .then(fotos => {
      photoContainer.innerHTML = '';

      if (!Array.isArray(fotos) || fotos.length === 0) {
        photoContainer.innerHTML = '<div class="text-muted">Tidak ada foto tersedia.</div>';
        return;
      }

      fotos.forEach(foto => {
        const wrapper = document.createElement('div');
        wrapper.className = 'photo-box border p-2 rounded position-relative';
        wrapper.style.width = '150px';
        wrapper.style.margin = '5px';
        wrapper.style.flex = '0 0 auto';

        // Gambar
        const img = document.createElement('img');
        img.src = `<?= base_url(); ?>/${foto.url_foto}`;
        img.alt = 'Foto laporan';
        img.className = 'img-fluid rounded';
        img.style.width = '100%';
        img.style.height = '100px';
        img.style.objectFit = 'cover';

        // Tombol hapus
        const btnDelete = document.createElement('button');
        btnDelete.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
        btnDelete.innerHTML = '&times;';
        btnDelete.type = 'button';
        btnDelete.onclick = () => {
          if (confirm('Hapus foto ini?')) {
            fetch(`<?= base_url('home/delete_foto'); ?>/${foto.id}`)
              .then(() => {
                wrapper.remove();
                if (!photoContainer.querySelector('.photo-box')) {
                  photoContainer.innerHTML = '<div class="text-muted">Tidak ada foto tersedia.</div>';
                }
              });
          }
        };

        // Label + Input replace
        const label = document.createElement('small');
        label.className = 'd-block text-center mt-1';
        label.innerText = 'Ganti Foto:';

        const inputReplace = document.createElement('input');
        inputReplace.type = 'file';
        inputReplace.name = `replace_foto[${foto.id}]`;
        inputReplace.className = 'form-control form-control-sm mt-1';
        inputReplace.accept = 'image/*';

        wrapper.appendChild(img);
        wrapper.appendChild(btnDelete);
        wrapper.appendChild(label);
        wrapper.appendChild(inputReplace);
        photoContainer.appendChild(wrapper);
      });

      // Grid layout
      photoContainer.style.display = 'flex';
      photoContainer.style.flexWrap = 'wrap';
      photoContainer.style.gap = '10px';
    })
    .catch(err => {
      console.error('Gagal memuat foto:', err);
      photoContainer.innerHTML = '<div class="text-danger">Gagal memuat foto.</div>';
    });
}

let latlngs = [];
let markers = [];
let shape = null;
let mapAdd = null, mapEdit = null;

function initializeMap(containerId, inputId, initialData = '') {
  const map = L.map(containerId).setView([-7.76537, 110.35884], 18);
  const tile = L.tileLayer("http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", {
    maxZoom: 22,
    subdomains: ['mt0','mt1','mt2','mt3']
  }).addTo(map);

  let isEditing = (containerId === 'edit-map');
  if (isEditing) mapEdit = map;
  else mapAdd = map;

  latlngs = [];
  markers = [];

  // If initialData is available, load the map with the existing data
  if (initialData) {
    try {
      const coords = JSON.parse(initialData);
      coords.forEach(latlng => {
        addPoint(latlng.lat, latlng.lng, map, inputId);
      });
    } catch (e) {
      console.warn('Koordinat awal tidak valid');
    }
  }

  // When the map is clicked, add a point
  map.on('click', function (e) {
    if (L.DomUtil.hasClass(document.body, 'suspend-map-click')) return;
    addPoint(e.latlng.lat, e.latlng.lng, map, inputId);
  });

  // Add Undo + Reset control
  L.control.customUndoReset = L.Control.extend({
    onAdd: function () {
      const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control bg-white p-1');
      L.DomEvent.disableClickPropagation(div);
      L.DomEvent.disableScrollPropagation(div);

      div.innerHTML = `
        <button title="Undo" class="btn btn-light btn-sm">↶</button>
        <button title="Reset" class="btn btn-light btn-sm">⟳</button>
      `;

      div.children[0].addEventListener('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        if (latlngs.length > 0) {
          map.removeLayer(markers.pop());
          latlngs.pop();
          updateShape(map, inputId);
        }
      });

      div.children[1].addEventListener('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        markers.forEach(m => map.removeLayer(m));
        markers.length = 0;
        latlngs.length = 0;
        updateShape(map, inputId);
      });

      return div;
    }
  });

  L.control.customUndoReset = new L.control.customUndoReset({ position: 'topright' });
  map.addControl(L.control.customUndoReset);

  // Manual Input Panel
  const inputBox = L.control({ position: 'topright' });
  inputBox.onAdd = function () {
    const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control bg-white p-2');
    div.innerHTML = `
      <details>
        <summary><strong>➕ Titik Manual</strong></summary>
        <input type="number" step="any" class="form-control form-control-sm mt-2" placeholder="Lat" id="manual-lat">
        <input type="number" step="any" class="form-control form-control-sm mt-1" placeholder="Lon" id="manual-lon">
        <button type="button" class="btn btn-sm btn-primary mt-2 w-100" id="btnAddManual">Tambah Titik</button>
      </details>
    `;
    L.DomEvent.disableClickPropagation(div);
    L.DomEvent.disableScrollPropagation(div);

    setTimeout(() => {
      div.querySelector('#btnAddManual').onclick = () => {
        const lat = parseFloat(div.querySelector('#manual-lat').value);
        const lng = parseFloat(div.querySelector('#manual-lon').value);
        if (!isNaN(lat) && !isNaN(lng)) {
          addPoint(lat, lng, map, inputId);
        }
      };
    }, 100);

    return div;
  };
  inputBox.addTo(map);

  setTimeout(() => map.invalidateSize(), 200);
}

// Function to add points to the map
function addPoint(lat, lng, map, inputId) {
  const latlng = L.latLng(lat, lng);
  const marker = L.marker(latlng, { draggable: false }).addTo(map);

  // Popup with delete and move buttons
  const popup = L.DomUtil.create('div');
  popup.innerHTML = `
    <button class="btn btn-sm btn-danger w-100 mb-1" id="deleteBtn">Hapus</button>
    <button class="btn btn-sm btn-secondary w-100" id="moveBtn">Pindahkan</button>
  `;

  const deleteBtn = popup.querySelector('#deleteBtn');
  const moveBtn = popup.querySelector('#moveBtn');

  // Prevent the modal from closing when "Hapus" is clicked
  deleteBtn.onclick = (e) => {
    e.stopPropagation(); // Prevent modal from closing
    e.preventDefault(); // Prevent default button behavior
    
    // Remove the marker and update the coordinates
    map.removeLayer(marker);
    const idx = markers.indexOf(marker);
    if (idx > -1) {
      latlngs.splice(idx, 1);
      markers.splice(idx, 1);
    }
    updateShape(map, inputId);
  };

  moveBtn.onclick = () => {
    marker.dragging.enable();
    marker.setPopupContent('<em>Geser marker ke lokasi baru lalu klik di luar popup</em>');
    marker.once('dragend', function () {
      const idx = markers.indexOf(marker);
      latlngs[idx] = marker.getLatLng();
      marker.dragging.disable();
      marker.setPopupContent(popup);
      updateShape(map, inputId);
    });
  };

  marker.bindPopup(popup);
  latlngs.push(latlng);
  markers.push(marker);
  updateShape(map, inputId);
}


// Function to update the shape on the map
function updateShape(map, inputId) {
  if (shape) map.removeLayer(shape);
  if (latlngs.length === 1) {
    shape = L.circleMarker(latlngs[0], { radius: 6, color: 'blue' }).addTo(map);
  } else if (latlngs.length === 2) {
    shape = L.polyline(latlngs, { color: 'blue' }).addTo(map);
  } else if (latlngs.length >= 3) {
    shape = L.polygon(latlngs, { color: 'green' }).addTo(map);
  }

  // Update input with latest coordinates
  document.getElementById(inputId).value = JSON.stringify(latlngs);
}

// Reset the map when closing the modal
$('#addDataModal').on('hidden.bs.modal', () => {
  if (mapAdd) {
    mapAdd.remove();  // Remove the map instance to reset it
    mapAdd = null;  // Set it to null to prevent issues with re-initialization
  }
});

$('#editDataModal').on('hidden.bs.modal', () => {
  if (mapEdit) {
    mapEdit.remove();  // Remove the map instance to reset it
    mapEdit = null;  // Set it to null to prevent issues with re-initialization
  }
});

// Modal Trigger for Add
$('#addDataModal').on('shown.bs.modal', () => {
  setTimeout(() => initializeMap('add-map', 'add-koordinat'), 300);
});

// Modal Trigger for Edit
$('#editDataModal').on('shown.bs.modal', () => {
  setTimeout(() => initializeMap('edit-map', 'edit-koordinat', document.getElementById('edit-koordinat').value), 300);
});

</script>


<!-- Modal Delete -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Apakah Anda yakin ingin menghapus laporan ini?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Hapus</button>
      </div>
    </div>
  </div>
</div>

<script>
function populateEditModal(data) {
  document.getElementById('edit-id').value = data.id;
  document.getElementById('edit-laporan').value = data.laporan;
  document.getElementById('edit-kerusakan-range').value = data.tingkat_kerusakan;
  document.getElementById('edit-progress-range').value = data.progress_perbaikan;
  updateKerusakanColor('edit');
  updateProgressColor('edit');
  document.getElementById('edit-koordinat').value = data.data_koordinat;
  
  loadExistingPhotos(data.id);

  const photoContainer = document.getElementById('existing-photos');
  photoContainer.innerHTML = '<div class="text-muted">Memuat foto...</div>';

  fetch(`<?= base_url('home/get_fotos'); ?>/${data.id}`)
    .then(res => res.json())
    .then(fotos => {
      photoContainer.innerHTML = '';

      if (!Array.isArray(fotos) || fotos.length === 0) {
        photoContainer.innerHTML = '<div class="text-muted">Tidak ada foto tersedia.</div>';
        return;
      }

      fotos.forEach(f => {
        const wrapper = document.createElement('div');
        wrapper.className = 'photo-box border p-2 rounded position-relative';
        wrapper.style.width = '140px';
        wrapper.style.margin = '5px';

        const img = document.createElement('img');
        img.src = `<?= base_url(); ?>/${f.url_foto}`;
        img.alt = 'Foto laporan';
        img.className = 'img-fluid rounded';
        img.style.width = '100%';
        img.style.height = '100px';
        img.style.objectFit = 'cover';
        img.title = f.url_foto;

        const btnDelete = document.createElement('button');
        btnDelete.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
        btnDelete.innerHTML = '&times;';
        btnDelete.type = 'button';
        btnDelete.onclick = () => {
          if (confirm('Hapus foto ini?')) {
            fetch(`<?= base_url('home/delete_foto'); ?>/${f.id}`)
              .then(() => {
                wrapper.remove();
                if (!photoContainer.querySelector('.photo-box')) {
                  photoContainer.innerHTML = '<div class="text-muted">Tidak ada foto tersedia.</div>';
                }
              });
          }
        };

        const label = document.createElement('small');
        label.className = 'd-block text-center mt-1';
        label.innerText = 'Ganti Foto:';

        const inputReplace = document.createElement('input');
        inputReplace.type = 'file';
        inputReplace.name = `replace_foto[${f.id}]`;
        inputReplace.className = 'form-control form-control-sm mt-1';
        inputReplace.accept = 'image/*';

        wrapper.appendChild(img);
        wrapper.appendChild(btnDelete);
        wrapper.appendChild(label);
        wrapper.appendChild(inputReplace);
        photoContainer.appendChild(wrapper);
      });

      photoContainer.style.display = 'flex';
      photoContainer.style.flexWrap = 'wrap';
      photoContainer.style.gap = '10px';
    })
    .catch(err => {
      console.error('Gagal memuat foto:', err);
      photoContainer.innerHTML = '<div class="text-danger">Gagal memuat foto.</div>';
    });
}


let deleteId = null;
function showDeleteModal(id) {
  deleteId = id;
  $('#deleteConfirmationModal').modal('show');
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('confirmDeleteButton').addEventListener('click', () => {
    if (deleteId) {
      window.location.href = `<?= base_url('home/delete'); ?>/${deleteId}`;
    }
  });

  document.getElementById('searchInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#dataTable tbody tr').forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(filter) ? '' : 'none';
    });
  });
});


function getGradientColor(percent, startColor, endColor) {
  const r = Math.round(startColor[0] + (endColor[0] - startColor[0]) * percent);
  const g = Math.round(startColor[1] + (endColor[1] - startColor[1]) * percent);
  const b = Math.round(startColor[2] + (endColor[2] - startColor[2]) * percent);
  return `rgb(${r}, ${g}, ${b})`;
}

function updateKerusakanColor(prefix) {
  const range = document.getElementById(`${prefix}-kerusakan-range`);
  const label = document.getElementById(`${prefix}-kerusakan-label`);
  const val = parseInt(range.value);
  const color = getGradientColor(val / 100, [0, 200, 0], [255, 0, 0]); // green to red
  label.innerText = `${val}%`;
  label.style.color = color;
}

function updateProgressColor(prefix) {
  const range = document.getElementById(`${prefix}-progress-range`);
  const label = document.getElementById(`${prefix}-progress-label`);
  const val = parseInt(range.value);
  const color = getGradientColor(val / 100, [255, 0, 0], [0, 200, 0]); // red to green
  label.innerText = `${val}%`;
  label.style.color = color;
}

document.addEventListener('DOMContentLoaded', function () {
  const rowsPerPage = 10;
  const rows = document.querySelectorAll('#dataTable tbody tr');
  const totalPages = Math.ceil(rows.length / rowsPerPage);

  // Function to update the table rows based on the current page
  function updateTablePage(page) {
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    
    // Hide all rows
    rows.forEach((row, index) => {
      row.style.display = 'none'; // Hide all rows
    });

    // Show the selected range of rows
    for (let i = start; i < end; i++) {
      if (rows[i]) {
        rows[i].style.display = ''; // Show row
      }
    }

    // Update pagination buttons
    updatePagination(page);

    // Store current page in localStorage
    localStorage.setItem('currentPage', page);
  }

  // Function to update pagination buttons
  function updatePagination(currentPage) {
    const pagination = document.querySelector('#pagination ul');
    pagination.innerHTML = ''; // Clear current pagination buttons

    // Create previous button
    const prevButton = document.createElement('li');
    prevButton.classList.add('page-item');
    prevButton.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
    prevButton.addEventListener('click', () => {
      if (currentPage > 1) {
        updateTablePage(currentPage - 1);
      }
    });
    pagination.appendChild(prevButton);

    // Create page buttons
    for (let i = 1; i <= totalPages; i++) {
      const pageButton = document.createElement('li');
      pageButton.classList.add('page-item');
      if (i === currentPage) {
        pageButton.classList.add('active');
      }
      pageButton.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      pageButton.addEventListener('click', () => {
        updateTablePage(i);
      });
      pagination.appendChild(pageButton);
    }

    // Create next button
    const nextButton = document.createElement('li');
    nextButton.classList.add('page-item');
    nextButton.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
    nextButton.addEventListener('click', () => {
      if (currentPage < totalPages) {
        updateTablePage(currentPage + 1);
      }
    });
    pagination.appendChild(nextButton);
  }

  // Check if there's a saved page in localStorage
  const savedPage = parseInt(localStorage.getItem('currentPage')) || 1;

  // Initialize pagination to saved page
  updateTablePage(savedPage);
});


</script>
