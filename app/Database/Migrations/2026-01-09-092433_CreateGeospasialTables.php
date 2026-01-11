<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinalGeospasialSchema extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. TABEL GRUP (MASTER STYLE, KATEGORI & LABEL)
        // =========================================================
        $this->forge->addField([
            'id_dg' => [ 
                'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true
            ],
            'nama_grup' => [
                'type' => 'VARCHAR', 'constraint' => '255',
            ],
            // --- KOLOM: PENAMAAN DINAMIS ---
            'label_column' => [
                'type' => 'VARCHAR', 'constraint' => '100', 'null' => true,
                'comment' => 'Nama key atribut GeoJSON yang dijadikan label utama'
            ],
            'jenis_peta' => [
                'type' => 'ENUM', 'constraint' => ['Point', 'Line', 'Polygon'], 
            ],

            // --- KOLOM BARU: CUSTOM MARKER (POINT) ---
            'marker_type' => [
                'type'       => 'ENUM',
                'constraint' => ['circle', 'pin', 'icon_url', 'icon_file'],
                'default'    => 'circle',
                'comment'    => 'Jenis visualisasi marker khusus Point'
            ],
            'marker_icon' => [
                'type'       => 'VARCHAR', 
                'constraint' => '255', 
                'null'       => true,
                'comment'    => 'Nama file (jika upload) atau URL penuh (jika link)'
            ],
            
            // --- GLOBAL STYLE LEAFLET ---
            'color'       => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'],
            'weight'      => ['type' => 'INT', 'constraint' => 5, 'default' => 3],
            'opacity'     => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 1.0],
            'dashArray'   => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'fillColor'   => ['type' => 'VARCHAR', 'constraint' => '7', 'default' => '#3388ff'],
            'fillOpacity' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 0.2],
            'radius'      => ['type' => 'INT', 'constraint' => 5, 'default' => 10], 
            
            'atribut_default' => [
                'type' => 'TEXT', 
                'null' => true,
                'comment' => 'Template atribut default untuk grup ini (JSON)'
            ],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_dg', true);
        $this->forge->createTable('geospasial_grup');


        // =========================================================
        // 2. TABEL DATA (POLIGON, LINE, POINT)
        // =========================================================
        // Kita gunakan loop untuk field yang identik agar kode bersih
        $tables = ['poligon', 'line', 'point'];
        foreach ($tables as $table) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_dg' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'nama_dg' => ['type' => 'VARCHAR', 'constraint' => '255'], 
                'data_geospasial' => ['type' => 'LONGTEXT'], // LONGTEXT agar aman untuk koordinat ribuan desa
                'atribut_tambahan' => ['type' => 'JSON', 'null' => true], 
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('id_dg', 'geospasial_grup', 'id_dg', 'CASCADE', 'CASCADE');
            $this->forge->createTable($table);
        }


        // =========================================================
        // 3. TABEL PDF (MULTI RELASI)
        // =========================================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'poligon_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'line_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'point_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'judul_pdf'  => ['type' => 'VARCHAR', 'constraint' => '255'],
            'file_path'  => ['type' => 'VARCHAR', 'constraint' => '255'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
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