<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinalGeospasialSchema extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. TABEL GRUP (MASTER STYLE & KATEGORI)
        // =========================================================
        $this->forge->addField([
            'id_dg' => [ 
                'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true
            ],
            // Nama Grup/Kategori (Misal: "Jaringan Jalan", "Batas Desa")
            'nama_grup' => [
                'type' => 'VARCHAR', 'constraint' => '255',
            ],
            'jenis_peta' => [
                'type' => 'ENUM', 'constraint' => ['Point', 'Line', 'Polygon'], 
            ],
            
            // --- GLOBAL STYLE LEAFLET (Berlaku untuk satu grup) ---
            'color'       => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'],
            'weight'      => ['type' => 'INT', 'constraint' => 5, 'default' => 3],
            'opacity'     => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 1.0],
            'dashArray'   => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'fillColor'   => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'],
            'fillOpacity' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 0.2],
            'radius'      => ['type' => 'INT', 'constraint' => 5, 'default' => 10], // Khusus Point
            
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_dg', true);
        $this->forge->createTable('geospasial_grup');


        // =========================================================
        // 2. TABEL POLIGON
        // =========================================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            
            // Penanda Masuk Grup Mana
            'id_dg' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            
            // REVISI: Menggunakan nama_dg (bukan nama_lokasi)
            'nama_dg' => ['type' => 'VARCHAR', 'constraint' => '255'], 
            
            'data_geospasial' => ['type' => 'TEXT'], // GeoJSON
            'atribut_tambahan' => ['type' => 'JSON', 'null' => true],
            
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Relasi ke tabel grup
        $this->forge->addForeignKey('id_dg', 'geospasial_grup', 'id_dg', 'CASCADE', 'CASCADE');
        $this->forge->createTable('poligon');


        // =========================================================
        // 3. TABEL LINE (POLYLINE)
        // =========================================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_dg' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            
            // REVISI: Menggunakan nama_dg
            'nama_dg' => ['type' => 'VARCHAR', 'constraint' => '255'],
            
            'data_geospasial' => ['type' => 'TEXT'], 
            'atribut_tambahan' => ['type' => 'JSON', 'null' => true],
            
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_dg', 'geospasial_grup', 'id_dg', 'CASCADE', 'CASCADE');
        $this->forge->createTable('line');


        // =========================================================
        // 4. TABEL POINT (MARKER)
        // =========================================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_dg' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            
            // REVISI: Menggunakan nama_dg
            'nama_dg' => ['type' => 'VARCHAR', 'constraint' => '255'],
            
            'data_geospasial' => ['type' => 'TEXT'], 
            'atribut_tambahan' => ['type' => 'JSON', 'null' => true],
            
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_dg', 'geospasial_grup', 'id_dg', 'CASCADE', 'CASCADE');
        $this->forge->createTable('point');


        // =========================================================
        // 5. TABEL PDF (MULTI RELASI)
        // =========================================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            
            // Relasi ke masing-masing tabel data (Nullable)
            'poligon_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'line_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'point_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            
            'judul_pdf' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('poligon_id', 'poligon', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('line_id', 'line', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('point_id', 'point', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('geospasial_pdf');
    }

    public function down()
    {
        $this->forge->dropTable('geospasial_pdf');
        $this->forge->dropTable('point');
        $this->forge->dropTable('line');
        $this->forge->dropTable('poligon');
        $this->forge->dropTable('geospasial_grup');
    }
}