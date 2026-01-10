<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    
    <title>WebGIS Kabupaten Ngawi - <?= $title ?></title>
    <link rel="shortcut icon" href="<?= base_url() ?>favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-search@2.9.8/dist/leaflet-search.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-panel-layers@1.3.0/dist/leaflet-panel-layers.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-search@2.9.8/dist/leaflet-search.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js"></script>
    <script src="https://unpkg.com/leaflet-panel-layers@1.3.0/dist/leaflet-panel-layers.min.js"></script>

    <style>
        /* =======================================================
           3. CSS VARIABLES & GLOBAL STYLES
        ======================================================== */
        :root {
            --primary-color: #007bff;
            --shadow-soft: 0 4px 12px rgba(0, 0, 0, 0.1);
            --bg-glass: rgba(255, 255, 255, 0.95);
        }

        body { 
            margin: 0; padding: 0; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            overflow: hidden; 
        }
        
        #map { 
            width: 100%; height: 100vh; 
            position: absolute; top: 0; left: 0; z-index: 1; 
        }

        /* =======================================================
           4. HEADER & NAVIGATION STYLES
        ======================================================== */
        /* Kotak Header (Kiri Atas) */
        #header-box {
            position: absolute; top: 15px; left: 15px; z-index: 1000;
            background: var(--bg-glass); backdrop-filter: blur(5px);
            padding: 12px 20px; border-radius: 12px;
            box-shadow: var(--shadow-soft);
            display: flex; align-items: center; gap: 12px;
            cursor: pointer; transition: transform 0.2s;
        }
        #header-box:hover { transform: translateY(-2px); }

        .logo-img {
            height: 40px; width: auto; 
            object-fit: contain; margin-right: 10px;
        }
        .app-title { font-weight: 700; font-size: 1.1rem; color: #333; line-height: 1.2; }
        .app-subtitle { font-size: 0.75rem; color: #666; font-weight: 500; display: block; }
        
        .dropdown-arrow { font-size: 0.8rem; color: #999; margin-left: 5px; transition: transform 0.3s; }
        .dropdown-arrow.open { transform: rotate(180deg); }

        /* Menu Dropdown */
        #menu-dropdown {
            position: absolute; top: 80px; left: 15px; z-index: 1001;
            background: white; border-radius: 10px; padding: 8px 0;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            min-width: 220px; display: none; transform-origin: top left;
            animation: fadeIn 0.2s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        
        .menu-item {
            display: flex; align-items: center; padding: 12px 20px;
            text-decoration: none; color: #444; font-size: 0.95rem;
            transition: background 0.2s;
        }
        .menu-item:hover { background: #f8f9fa; color: var(--primary-color); }
        .menu-item i { width: 24px; text-align: center; margin-right: 10px; }

        /* =======================================================
           5. SEARCH BAR & COORDINATE BOX
        ======================================================== */
        #search-wrapper {
            position: absolute; top: 15px; left: 300px; z-index: 1000;
            width: 25vw; transition: all 0.3s;
        }
        .search-input {
            width: 100%; padding: 12px 20px; padding-left: 45px;
            border: none; border-radius: 50px;
            background: var(--bg-glass); backdrop-filter: blur(5px);
            font-size: 0.95rem; box-shadow: var(--shadow-soft);
            outline: none; transition: box-shadow 0.2s;
        }
        .search-input:focus { box-shadow: 0 6px 16px rgba(0,0,0,0.15); }
        .search-icon {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: #999; font-size: 1rem; pointer-events: none;
        }

        #coord-box {
            position: absolute; bottom: 25px; left: 50%; transform: translateX(-50%);
            z-index: 1000; background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px); padding: 6px 16px;
            border-radius: 20px; font-size: 0.85rem; font-weight: 500; color: #555;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); pointer-events: none;
            display: flex; gap: 15px;
        }

        /* =======================================================
           6. LEAFLET CONTROLS CUSTOMIZATION
        ======================================================== */
        .leaflet-control-container .leaflet-top { top: 80px; } /* Geser control ke bawah */
        .leaflet-bar { border: none !important; box-shadow: var(--shadow-soft) !important; border-radius: 8px !important; overflow: hidden; }
        .leaflet-bar a { width: 36px !important; height: 36px !important; line-height: 36px !important; background: white; color: #444; border-bottom: 1px solid #f0f0f0; transition: 0.2s; }
        .leaflet-bar a:hover { background: #f8f9fa; color: var(--primary-color); }
        .leaflet-bar a:last-child { border-bottom: none; }
        
        /* Panel Layers Styling */
        .leaflet-panel-layers { 
            background: white !important; border-radius: 12px !important; 
            box-shadow: var(--shadow-soft) !important; padding: 5px !important; 
        }
        .leaflet-panel-layers-item { padding: 8px 10px; border-radius: 6px; transition: 0.2s; }
        .leaflet-panel-layers-item:hover { background-color: #f8f9fa; }
        
        /* Inject Checkbox Label Style */
        .leaflet-panel-layers-item-inject {
            display: flex; align-items: center; gap: 6px; margin-top: 2px;
        }
        .label-checkbox-input {
            cursor: pointer; margin: 0 !important; width: 13px; height: 13px;
        }

        /* Custom Reset Button */
        .custom-btn {
            background: white; width: 36px; height: 36px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: var(--shadow-soft); margin-bottom: 10px;
            color: #555; transition: 0.2s;
        }
        .custom-btn:hover { color: var(--primary-color); background: #fff; }

        
        /* =======================================================
           7. PROFESSIONAL POPUP STYLE
        ======================================================== */
        /* Hapus margin bawaan */
        .leaflet-popup-content { margin: 0 !important; padding: 0 !important; width: auto !important; }

        /* Wrapper Utama */
        .professional-popup .leaflet-popup-content-wrapper {
            background: #fff; border-radius: 8px; padding: 0;
            overflow: hidden; box-shadow: 0 3px 14px rgba(0,0,0,0.4);
        }

        /* Tombol Close (X) */
        .professional-popup .leaflet-popup-close-button {
            color: #ffffff !important; top: 12px !important; right: 12px !important;
            font-size: 18px !important; text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            transition: all 0.2s;
        }
        .professional-popup .leaflet-popup-close-button:hover { color: #f0f0f0 !important; }
        .professional-popup .leaflet-popup-tip { background: #fff; }

        /* =======================================================
           8. LABEL STYLE (TOOLTIP)
        ======================================================== */
        .polygon-label {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 4px; padding: 2px 8px;
            font-size: 11px; font-weight: 600; color: #333;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            white-space: nowrap; pointer-events: none;
        }
        .leaflet-tooltip-top:before, .leaflet-tooltip-bottom:before, .leaflet-tooltip-left:before, .leaflet-tooltip-right:before {
            display: none !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #search-wrapper { left: 15px; top: 90px; width: 40vw; }
            .leaflet-control-container .leaflet-top.leaflet-left { top: 150px; }
            #header-box { width: auto; max-width: 80%; }
            #coord-box { bottom: 30px; font-size: 0.75rem; width: 45%; justify-content: center; }
        }

    </style>
</head>

<body>

    <div id="map"></div>

    <div id="header-box" onclick="toggleMenu()">
        <a href="<?= base_url() ?>" class="text-decoration-none">
            <img src="<?= base_url('GAS.png') ?>" alt="Logo GAS" class="logo-img">
        </a>
        <div>
            <div class="app-title">WEBGIS NGAWI</div>
            <span class="app-subtitle">Sistem Informasi Geografis</span>
        </div>
        <i id="menu-arrow" class="fa-solid fa-chevron-down dropdown-arrow"></i>
    </div>

    <div id="menu-dropdown">
        <div style="padding: 10px 20px; font-size: 0.8rem; font-weight: 600; color: #aaa; letter-spacing: 0.5px;">MENU UTAMA</div>
        <a href="<?= base_url('geospasial') ?>" class="menu-item">
            <i class="fa-solid fa-user-shield"></i> Login Administrator
        </a>
    </div>

    <div id="search-wrapper">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="search-input" class="search-input" placeholder="Cari lokasi atau data poligon...">
    </div>

    <div id="coord-box">
        <span>Lat <span id="lat-val">-</span></span>
        <span>Lon <span id="lng-val">-</span></span>
    </div>

    <script>
        // =======================================================
        // A. INISIALISASI PETA DASAR
        // =======================================================
        var map = L.map('map', { zoomControl: false, attributionControl: false })
                   .setView([-7.408019826354289, 111.4428818182571], 12);
        
        L.control.zoom({ position: 'bottomright' }).addTo(map);
        
        // =======================================================
        // B. KONTROL TAMBAHAN (LOCATE & RESET)
        // =======================================================
        // 1. Locate Control (Lokasi Saya)
        L.control.locate({ 
            position: 'topleft', 
            strings: { title: "Lokasi Saya" },
            flyTo: true,
            icon: 'fa-solid fa-crosshairs',
            iconLoading: 'fa-solid fa-spinner fa-spin'
        }).addTo(map);

        // 2. Custom Reset Control (Tombol Home)
        var ResetControl = L.Control.extend({
            options: { position: 'topleft' },
            onAdd: function(map) {
                var btn = L.DomUtil.create('div', 'custom-btn');
                btn.innerHTML = '<i class="fa-solid fa-house"></i>';
                btn.title = "Reset Tampilan";
                btn.onclick = function(){ map.setView([-7.408019826354289, 111.4428818182571], 12); }
                return btn;
            }
        });
        map.addControl(new ResetControl());

        // 3. Event Mousemove (Update Koordinat UI)
        map.on('mousemove', function(e) {
            document.getElementById('lat-val').innerText = e.latlng.lat.toFixed(5);
            document.getElementById('lng-val').innerText = e.latlng.lng.toFixed(5);
        });

        // =======================================================
        // C. BASEMAPS LAYERS
        // =======================================================
        var baseLayers = [
            {
                name: "Open Street Map",
                layer: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
                icon: '<i class="fa-solid fa-map text-success"></i>'
            },
            {
                name: "Google Satellite",
                layer: L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {maxZoom: 22}),
                icon: '<i class="fa-solid fa-satellite text-primary"></i>'
            },
            {
                name: "Google Terrain",
                layer: L.tileLayer('https://mt1.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {maxZoom: 22}),
                icon: '<i class="fa-solid fa-mountain text-secondary"></i>'
            }
        ];
        map.addLayer(baseLayers[1].layer); // Default ke Satellite

        // =======================================================
        // D. DATA LAYERS & PHP INJECTION
        // =======================================================
        var overLayers = [];
        var searchGroup = new L.LayerGroup().addTo(map); // Layer khusus untuk fitur search

        <?php if(!empty($layers)): ?>
            <?php foreach($layers as $grup): ?>
                
                // --- Cek apakah grup memiliki data fitur ---
                <?php if(!empty($grup['final_geojson']['features'])): ?>
                    
                    // 1. Ambil GeoJSON dan Config Style dari PHP
                    var dataGrup_<?= $grup['id_dg'] ?> = <?= json_encode($grup['final_geojson']) ?>;
                    
                    var style_<?= $grup['id_dg'] ?> = {
                        color: "<?= $grup['color'] ?>",
                        weight: <?= $grup['weight'] ?>,
                        opacity: 1,
                        fillColor: "<?= $grup['fillColor'] ?>",
                        fillOpacity: <?= $grup['fillOpacity'] ?>,
                        dashArray: "<?= $grup['dashArray'] ?? '' ?>"
                    };

                    // 2. Tentukan Icon & Nama Grup untuk Panel Control
                    <?php
                        $jenis = $grup['jenis_peta'];
                        $panelGroupName = "Layer Lainnya";
                        $cssIcon = "";
                        // Logika Styling Icon Panel
                        if ($jenis == 'Polygon') {
                            $panelGroupName = " 🗺️ Data Area (Poligon)";
                            $borderStyle = (!empty($grup['dashArray'])) ? ((strpos($grup['dashArray'], '1') !== false) ? 'dotted' : 'dashed') : 'solid';
                            $cssIcon = "display:inline-block; width:18px; height:14px; background:{$grup['fillColor']}; border:2px {$borderStyle} {$grup['color']}; border-radius:2px; opacity:0.8;";
                        } else if ($jenis == 'Line') {
                            $panelGroupName = " 🛤️ Data Jalur (Garis)";
                            $cssIcon = "display:inline-block; width:20px; height:4px; background:{$grup['color']}; margin-top:6px;";
                        } else if ($jenis == 'Point') {
                            $panelGroupName = " 📍 Data Lokasi (Titik)";
                            $cssIcon = "display:inline-block; width:12px; height:12px; background:{$grup['color']}; border:2px solid #fff; border-radius:50%; box-shadow:0 0 2px #000;";
                        }
                    ?>

                    // 3. Buat GeoJSON Layer
                    var layer_<?= $grup['id_dg'] ?> = L.geoJSON(dataGrup_<?= $grup['id_dg'] ?>, {
                        style: style_<?= $grup['id_dg'] ?>,
                        onEachFeature: function(feature, layer) {
                            var props = feature.properties;
                            var layerColor = style_<?= $grup['id_dg'] ?>.color;

                            // --- BUILD POPUP CONTENT (HTML) ---
                            // a. Header Popup
                            var content = `
                            <div style="font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; min-width: 280px; max-width: 320px;">
                                <div style="background-color: ${layerColor}; color: #fff; padding: 12px 40px 12px 15px; display: flex; align-items: center; justify-content: space-between;">
                                    <h3 style="margin: 0; font-size: 15px; font-weight: 600; line-height: 1.4;">
                                        ${props.nama || 'Tanpa Nama'}
                                    </h3>
                                </div>
                                <div style="max-height: 250px; overflow-y: auto; background: #fff;">
                            `;

                            // b. Table Info
                            if(props.info && props.info.length > 0) {
                                content += `<table style="width: 100%; border-collapse: collapse; font-size: 13px;">`;
                                props.info.forEach((attr, idx) => {
                                    var bg = idx % 2 === 0 ? '#f9f9f9' : '#ffffff'; 
                                    content += `
                                        <tr style="background: ${bg}; border-bottom: 1px solid #eee;">
                                            <td style="padding: 10px 15px; color: #666; width: 40%; vertical-align: top;">${attr.label}</td>
                                            <td style="padding: 10px 15px; font-weight: 500; color: #333; text-align: right;">${attr.value}</td>
                                        </tr>`;
                                });
                                content += `</table>`;
                            } else {
                                content += `<div style="padding: 20px; text-align: center; color: #999; font-style: italic; font-size: 12px;">Tidak ada atribut data.</div>`;
                            }

                            // c. PDF Attachment
                            if (props.daftar_pdf && props.daftar_pdf.length > 0) {
                                content += `
                                    <div style="padding: 15px; background: #f8f9fa; border-top: 1px solid #e9ecef;">
                                        <div style="font-size: 11px; font-weight: 700; color: #8898aa; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <i class="fa-solid fa-paperclip"></i> Dokumen Terkait
                                        </div>
                                `;
                                props.daftar_pdf.forEach((doc) => {
                                    var linkPdf = "<?= base_url('uploads/pdf/') ?>/" + doc.file_path;
                                    content += `
                                        <a href="${linkPdf}" target="_blank" style="display: flex; align-items: center; background: #fff; border: 1px solid #e0e0e0; padding: 10px; border-radius: 6px; text-decoration: none; color: #333; margin-bottom: 8px; transition: all 0.2s ease; font-size: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                                           onmouseover="this.style.borderColor='${layerColor}'; this.style.color='${layerColor}';"
                                           onmouseout="this.style.borderColor='#e0e0e0'; this.style.color='#333';">
                                            <div style="width: 32px; height: 32px; background: #fff0f0; color: #d32f2f; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-right: 12px;">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${doc.judul_pdf}</div>
                                                <div style="font-size: 10px; color: #888; margin-top:2px;">Klik untuk unduh</div>
                                            </div>
                                        </a>`;
                                });
                                content += `</div>`;
                            }
                            content += `</div></div>`; // Tutup Wrapper

                            // Bind Popup Profesional
                            layer.bindPopup(content, { maxWidth: 320, className: 'professional-popup' });
                            searchGroup.addLayer(layer); // Tambahkan ke Search Group
                        }
                    });

                    // 4. Siapkan Layer Label (Tooltip)
                    var labelGroup_<?= $grup['id_dg'] ?> = L.layerGroup();
                    layer_<?= $grup['id_dg'] ?>.eachLayer(function(l) {
                        if (l.getBounds) {
                            var center = l.getBounds().getCenter();
                            var labelContent = l.feature.properties.nama || 'Tanpa Nama';
                            var label = L.tooltip({
                                permanent: true, direction: 'center', className: 'polygon-label'
                            }).setContent(labelContent).setLatLng(center);
                            labelGroup_<?= $grup['id_dg'] ?>.addLayer(label);
                        }
                    });

                    // 5. Konfigurasi Panel Layer
                    <?php $isAdministrasi = (stripos($grup['nama_grup'], 'administrasi') !== false) ? 'true' : 'false'; ?>
                    var labelActive_<?= $grup['id_dg'] ?> = <?= $isAdministrasi ?>;

                    // Push ke array overLayers untuk Panel Control
                    overLayers.push({
                        active: true,
                        group: "<?= $panelGroupName ?>",
                        name: `<span style="<?= $cssIcon ?> margin-right:8px; vertical-align:middle;"></span> <?= $grup['nama_grup'] ?>`,
                        layer: layer_<?= $grup['id_dg'] ?>,
                        injectParam: {
                            label: "Tampilkan Label",
                            active: labelActive_<?= $grup['id_dg'] ?>,
                            layer: labelGroup_<?= $grup['id_dg'] ?>
                        }
                    });

                    // Auto-show Label jika Administrasi
                    if (labelActive_<?= $grup['id_dg'] ?>) {
                        labelGroup_<?= $grup['id_dg'] ?>.addTo(map);
                    }

                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        // =======================================================
        // E. PANEL LAYERS CONTROL (SIDEBAR)
        // =======================================================
        var panelLayers = new L.Control.PanelLayers(baseLayers, overLayers, {
            collapsibleGroups: true, collapsed: false, position: 'topright', compact: true,
            // Custom Builder untuk menyisipkan Checkbox Label
            buildItem: function(item) {
                var container = L.DomUtil.create('div', 'leaflet-panel-layers-inject-container');
                container.style.marginLeft = "25px"; container.style.marginTop = "2px";

                if (item.injectParam) {
                    var node = L.DomUtil.create('div', 'leaflet-panel-layers-item-inject');
                    var input = L.DomUtil.create('input', 'label-checkbox-input');
                    input.type = 'checkbox';
                    input.checked = item.injectParam.active;

                    // Event Click Checkbox Label
                    L.DomEvent.on(input, 'click', function(e) {
                        L.DomEvent.stopPropagation(e);
                        item.injectParam.active = e.target.checked;
                        if (e.target.checked) {
                            if (map.hasLayer(item.layer)) item.injectParam.layer.addTo(map);
                        } else {
                            if (map.hasLayer(item.injectParam.layer)) map.removeLayer(item.injectParam.layer);
                        }
                    });

                    var labelSpan = L.DomUtil.create('span', '');
                    labelSpan.innerHTML = ' ' + item.injectParam.label;
                    labelSpan.style.fontSize = '10px'; labelSpan.style.color = '#666'; labelSpan.style.cursor = 'pointer';

                    // Event Click Label Teks
                    L.DomEvent.on(labelSpan, 'click', function(e) {
                        L.DomEvent.stopPropagation(e);
                        input.click();
                    });

                    node.appendChild(input);
                    node.appendChild(labelSpan);
                    container.appendChild(node);
                    return container;
                }
                return L.DomUtil.create('div', ''); 
            }
        });
        map.addControl(panelLayers);

        // Event Sync: Hapus label jika layer induk dimatikan
        map.on('layerremove', function(e) {
            overLayers.forEach(function(item) {
                if (e.layer === item.layer && item.injectParam && map.hasLayer(item.injectParam.layer)) {
                    map.removeLayer(item.injectParam.layer);
                }
            });
        });

        // Event Sync: Munculkan label jika layer induk dinyalakan (dan zoom cukup)
        map.on('layeradd', function(e) {
            overLayers.forEach(function(item) {
                if (e.layer === item.layer && item.injectParam && item.injectParam.active && map.getZoom() >= 13) {
                    item.injectParam.layer.addTo(map);
                }
            });
        });

        // =======================================================
        // F. FITUR PENCARIAN (SEARCH)
        // =======================================================
        var searchControl = new L.Control.Search({
            layer: searchGroup, propertyName: 'nama', marker: false,
            initial: false, zoom: 16,
            moveToLocation: function(latlng, title, map) {
                map.setView(latlng, 16);
                if(latlng.layer.openPopup) latlng.layer.openPopup();
            }
        });
        document.getElementById('search-input').addEventListener('keyup', function(e) {
            searchControl.searchText(this.value);
        });

        // =======================================================
        // G. INTERAKSI UI & RESPONSIVE
        // =======================================================
        function toggleMenu() {
            var menu = document.getElementById('menu-dropdown');
            var arrow = document.getElementById('menu-arrow');
            if (menu.style.display === 'block') {
                menu.style.display = 'none'; arrow.classList.remove('open');
            } else {
                menu.style.display = 'block'; arrow.classList.add('open');
            }
        }

        // Tutup menu jika klik di luar area
        document.addEventListener('click', function(event) {
            var header = document.getElementById('header-box');
            var menu = document.getElementById('menu-dropdown');
            if (!header.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = 'none';
                document.getElementById('menu-arrow').classList.remove('open');
            }
        });

        // Fix Render Leaflet saat load awal
        setTimeout(() => { map.invalidateSize(); }, 500);

        // Kontrol Zoom Level untuk Label (Hanya muncul di zoom detail)
        map.on('zoomend', function() {
            var currentZoom = map.getZoom();
            overLayers.forEach(function(item) {
                if (item.injectParam && item.injectParam.layer) {
                    if (currentZoom < 13) {
                        if (map.hasLayer(item.injectParam.layer)) map.removeLayer(item.injectParam.layer);
                    } else {
                        if (map.hasLayer(item.layer) && item.injectParam.active) item.injectParam.layer.addTo(map);
                    }
                }
            });
        });
    </script>
</body>
</html>