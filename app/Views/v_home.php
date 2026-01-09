<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?= base_url() ?>favicon.ico" type="image/x-icon">
    <title>WebGIS Kabupaten Ngawi - <?= $title ?></title>

    <link rel="stylesheet" href="<?= base_url() ?>leaflet/1.3.0/leaflet.css" />
    <link rel="stylesheet" href="<?= base_url() ?>leaflet/leafletsearchmaster/src/leaflet-search.css" />
    <link rel="stylesheet" href="<?= base_url() ?>leaflet/locateme/dist/L.Control.Locate.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>leaflet/leaflet-panel-layers-master/src/leaflet-panel-layerss.css" />
    <link rel="stylesheet" href="<?= base_url() ?>leaflet/leaflet-panel-layers-master/examples/icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>leaflet/leaflet-bmswitcher-main/src/leaflet-bmswitcher.css" />
    <link rel="stylesheet" href="<?= base_url() ?>leaflet/leaflet-betterscale-master/L.Control.BetterScale.css" />
    <link rel="stylesheet" href="<?= base_url() ?>leaflet/leaflet-measure.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="<?= base_url() ?>leaflet/1.3.0/leaflet.js"></script>
    <script src="<?= base_url() ?>leaflet/leafletsearchmaster/src/leaflet-search.js"></script>
    <script src="<?= base_url() ?>leaflet/locateme/dist/L.Control.Locate.min.js"></script>
    <script src="<?= base_url() ?>leaflet/rbush.min.js"></script>
    <script src="<?= base_url() ?>leaflet/labelgun.min.js"></script>
    <script src="<?= base_url() ?>leaflet/labels.js"></script>
    <script src="<?= base_url() ?>leaflet/leaflet-panel-layers-master/src/leaflet-panel-layers.js"></script>
    <script src="<?= base_url() ?>leaflet/leaflet-bmswitcher-main/src/leaflet-bmswitcher.js"></script>
    <script src="<?= base_url() ?>leaflet/leaflet-betterscale-master/L.Control.BetterScale.js"></script>
    <script src="<?= base_url() ?>leaflet/leaflet-measure.js"></script>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif !important;
            overflow: hidden;
        }

        #map {
            width: 100%;
            height: 100vh;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }

        /* --- Logo Container --- */
        #logo-container {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: white;
            border-radius: 10px;
            width: 300px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        #inner-container {
            background-color: white;
            border: 2px solid gray;
            border-radius: 10px;
            width: 90%;
            height: 80%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 10px;
        }

        #arrow {
            cursor: pointer;
            margin-left: 10px;
            user-select: none;
        }

        /* --- Popup Menu --- */
        #popup-logo-container {
            position: absolute;
            top: 80px;
            left: 20px;
            background-color: white;
            border-radius: 8px;
            padding: 10px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            z-index: 1001;
            display: none; /* Hidden by default */
        }

        #popup-logo-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #popup-logo-container ul li a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            display: block;
        }

        #popup-logo-container ul li:hover {
            background-color: #f0f0f0;
            border-radius: 4px;
        }

        /* --- Search Container --- */
        #search-filter-container {
            position: absolute;
            top: 20px;
            left: 330px; /* Sebelah kanan logo */
            z-index: 1000;
            width: 300px;
        }

        #search-container input {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* --- Coordinate Box --- */
        #coordinate-container {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: white;
            border-radius: 5px;
            padding: 5px;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        #coordinate-container a {
            padding: 0 10px;
            font-size: 0.8em;
            color: #333;
            text-decoration: none;
        }

        /* --- Custom Controls --- */
        .leaflet-control-custom {
            background-color: white;
            width: 34px;
            height: 34px;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 1px 5px rgba(0,0,0,0.4);
            border-radius: 2px;
        }

        .leaflet-control-custom:hover {
            background-color: #f4f4f4;
        }

        /* --- Panel Layers Override --- */
        .leaflet-panel-layers.expanded {
            display: none; /* Dikontrol oleh tombol custom */
            top: 50px;
        }
        
        .leaflet-panel-layers-list {
            max-height: 50vh;
            overflow-y: auto;
        }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            #logo-container {
                position: relative;
                top: 10px;
                left: 10px;
                width: calc(100% - 20px);
            }

            #search-filter-container {
                position: relative;
                top: 20px;
                left: 10px;
                width: calc(100% - 20px);
                margin-left: 0;
            }

            .leaflet-top.leaflet-left {
                top: 140px; /* Geser control leaflet ke bawah agar tidak tertutup */
            }
        }
    </style>
</head>

<body>

    <div id="map"></div>

    <div id="logo-container">
        <div id="inner-container">
            <span style="font-weight: bold; font-size: 1.1rem;">DEMO WEBGIS KABUPATEN NGAWI</span>
            <div id="arrow">▼</div>
        </div>
    </div>

    <div id="popup-logo-container">
        <ul>
            <li><a href="<?= base_url('home/data') ?>">Login Admin</a></li>
        </ul>
    </div>

    <div id="search-filter-container">
        <div id="search-container">
            <input type="text" id="search-laporan" placeholder="Cari laporan..." />
        </div>
    </div>

    <div id="coordinate-container">
        <a id="lat">Lat: -</a>
        <a id="lng">Long: -</a>
    </div>

    <script>
        // 1. Inisialisasi Map
        var map = L.map('map', {
            zoom: 14,
            maxZoom: 22,
            center: L.latLng([-7.408019826354289, 111.4428818182571]),
            zoomControl: true 
        });

        // 2. Locate Control (Lokasi Saya)
        map.addControl(L.control.locate({
            locateOptions: {
                flyTo: true,
                minzoom: 15,
                initialZoomLevel: 17,
            }
        }));

        // 3. Measure Control (Pengukuran)
        L.control.measure({
            position: 'topleft',
            primaryLengthUnit: 'meters',
            primaryAreaUnit: 'sqmeters',
        }).addTo(map);

        // 4. Scale Control
        L.control.scale({ position: 'bottomright', metric: true }).addTo(map);

        // 5. Update Koordinat saat mouse bergerak
        map.on('mousemove', function(e) {
            document.getElementById('lat').textContent = 'Lat: ' + e.latlng.lat.toFixed(5);
            document.getElementById('lng').textContent = 'Long: ' + e.latlng.lng.toFixed(5);
        });

        // 6. Base Map Switcher
        const bmList = [
            {
                layer: L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { attribution: "&copy; OpenStreetMap contributors", crossOrigin: true }),
                name: "Open Street Map",
                icon: "<?= base_url() ?>leaflet/leaflet-bmswitcher-main/example/assets/osm.png"
            },
            {
                layer: L.tileLayer("http://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}.png", { attribution: "&copy; OpenStreetMap contributors", crossOrigin: true }),
                name: "ArcGIS Online",
                icon: "<?= base_url() ?>leaflet/leaflet-bmswitcher-main/example/assets/arcgis-online.png"
            },
            {
                layer: L.tileLayer("http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", { maxZoom: 22, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] }).addTo(map),
                name: "Google Satellite",
                icon: "<?= base_url() ?>leaflet/leaflet-bmswitcher-main/example/assets/google.png"
            },
        ];
        new L.bmSwitcher(bmList).addTo(map);

        // 7. Tombol Reset Lokasi (Custom Control)
        var ReturnCenterControl = L.Control.extend({
            options: { position: 'topleft' },
            onAdd: function(map) {
                var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                // Menggunakan icon SVG
                container.innerHTML = '<img src="<?= base_url() ?>pin-location-svgrepo-com.svg" width="20" height="20">';
                container.onclick = function() {
                    map.setView([-7.408019826354289, 111.4428818182571], 14);
                };
                return container;
            },
        });
        map.addControl(new ReturnCenterControl());

        // 8. Panel Layers
        var baseLayers = []; // Kosong karena sudah pakai BM Switcher
        var overLayers = []; // Isi layer overlay disini jika ada

        var panelLayers = new L.Control.PanelLayers(baseLayers, overLayers, {
            selectorGroup: true,
            collapsibleGroups: true,
            collapsed: false
        });
        map.addControl(panelLayers);

        // 9. Tombol Toggle Panel Layers (Custom Control)
        var PanelButtonControl = L.Control.extend({
            options: { position: 'topright' },
            onAdd: function(map) {
                var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                container.innerHTML = '<img src="<?= base_url() ?>assets/stack.svg" width="20" height="20">';
                container.onclick = function() {
                    var panel = $(panelLayers.getContainer());
                    if (panel.is(":visible")) {
                        panel.hide();
                    } else {
                        panel.show();
                    }
                };
                return container;
            },
        });
        map.addControl(new PanelButtonControl());

        // 10. Fix Rendering Map
        setTimeout(function() {
            map.invalidateSize();
        }, 500);

        // 11. Logic Toggle Menu Admin (Popup Logo)
        $(document).ready(function() {
            $('#arrow').click(function() {
                $('#popup-logo-container').fadeToggle('fast');
                // Ubah arah panah
                var text = $(this).text();
                $(this).text(text == "▼" ? "▲" : "▼");
            });
        });

    </script>
</body>
</html>