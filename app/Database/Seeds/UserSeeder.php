<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $jsonData = '[
            {
                "type": "table",
                "name": "users",
                "database": "database",
                "data": [
                    {
                        "id": "1",
                        "email": "azfaahmaddzulvikar@gmail.com",
                        "username": "adminmeong",
                        "password_hash": "$2y$10$Xm2zRXmHtq5Dg6PeCbvH5.FyocoarSBR8SyV5ojbxK5cUc93AzUAa",
                        "reset_hash": null,
                        "reset_at": null,
                        "reset_expires": null,
                        "activate_hash": null,
                        "status": null,
                        "status_message": null,
                        "active": "1",
                        "force_pass_reset": "0",
                        "created_at": "2026-01-09 19:58:46",
                        "updated_at": "2026-01-09 19:59:04",
                        "deleted_at": null
                    }
                ]
            }
        ]';

        // Decode JSON menjadi array
        $rows = json_decode($jsonData, true);

        // Cari elemen yang memiliki tipe "table" dan nama "users"
        foreach ($rows as $row) {
            if (isset($row['type']) && $row['type'] === 'table' && $row['name'] === 'users') {
                $userData = $row['data'];
                
                // Melakukan insert batch ke tabel users
                $this->db->table('users')->insertBatch($userData);
            }
        }
    }
}