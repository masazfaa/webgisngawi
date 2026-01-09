<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGeospasialTables extends Migration
{
    public function up()
    {
        // ==========================================
        // 1. TABEL POLIGON (Area)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'data_geospasial' => ['type' => 'TEXT'], // GeoJSON Polygon
            'atribut_tambahan' => ['type' => 'JSON', 'null' => true],
            
            // Style Leaflet untuk Polygon (Path options)
            'color'       => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'], // Stroke color
            'weight'      => ['type' => 'INT', 'constraint' => 5, 'default' => 3], // Stroke width
            'opacity'     => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 1.0], // Stroke opacity
            'dashArray'   => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true], // Putus-putus
            'fillColor'   => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'], // Isi color
            'fillOpacity' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 0.2], // Isi opacity
            
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('poligon');

        // ==========================================
        // 2. TABEL LINE (Garis / Polyline)
        // ==========================================
        // Catatan: Polyline di Leaflet tidak punya properti 'fill'
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'data_geospasial' => ['type' => 'TEXT'], // GeoJSON LineString
            'atribut_tambahan' => ['type' => 'JSON', 'null' => true],

            // Style Leaflet untuk Polyline (Hanya Stroke)
            'color'     => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#ff7800'],
            'weight'    => ['type' => 'INT', 'constraint' => 5, 'default' => 5],
            'opacity'   => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 1.0],
            'dashArray' => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'lineCap'   => ['type' => 'VARCHAR', 'constraint' => '20', 'default' => 'round'], // round, butt, square
            'lineJoin'  => ['type' => 'VARCHAR', 'constraint' => '20', 'default' => 'round'], // round, bevel, miter
            
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('line');

        // ==========================================
        // 3. TABEL POINT (Titik / CircleMarker)
        // ==========================================
        // Kita gunakan style L.circleMarker agar bisa dicustom warna & ukurannya
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'data_geospasial' => ['type' => 'TEXT'], // GeoJSON Point
            'atribut_tambahan' => ['type' => 'JSON', 'null' => true],

            // Style Leaflet untuk CircleMarker
            'radius'      => ['type' => 'INT', 'constraint' => 5, 'default' => 10], // Ukuran lingkaran
            'color'       => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'],
            'weight'      => ['type' => 'INT', 'constraint' => 5, 'default' => 1], // Border width
            'opacity'     => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 1.0],
            'fillColor'   => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'],
            'fillOpacity' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 0.8],
            
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('point');

        // ==========================================
        // 4. TABEL GEOSPASIAL PDF (Relasi Campuran)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            
            // Foreign Keys (Boleh NULL semua, tapi nanti divalidasi di aplikasi minimal satu terisi)
            'poligon_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'line_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'point_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            
            'judul_pdf' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);

        // Menambahkan Relasi Foreign Key dengan CASCADE
        // Artinya: Hapus Poligon -> PDF terkait ikut terhapus
        $this->forge->addForeignKey('poligon_id', 'poligon', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('line_id', 'line', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('point_id', 'point', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('geospasial_pdf');
    }

    public function down()
    {
        // Hapus tabel PDF dulu karena memiliki Foreign Key
        $this->forge->dropTable('geospasial_pdf');
        
        // Baru hapus tabel master
        $this->forge->dropTable('point');
        $this->forge->dropTable('line');
        $this->forge->dropTable('poligon');
    }
}