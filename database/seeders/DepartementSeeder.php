<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $departements = [
            // Aktif
            ['name' => 'Human Resources', 'is_active' => true],
            ['name' => 'Finance', 'is_active' => true],
            ['name' => 'Information Technology', 'is_active' => true],
            ['name' => 'Marketing', 'is_active' => true],
            ['name' => 'Sales', 'is_active' => true],
            ['name' => 'Operations', 'is_active' => true],
            ['name' => 'Customer Support', 'is_active' => true],
            ['name' => 'Research and Development', 'is_active' => true],
            ['name' => 'Legal', 'is_active' => true],
            ['name' => 'Procurement', 'is_active' => true],
            ['name' => 'Logistics', 'is_active' => true],
            ['name' => 'Quality Assurance', 'is_active' => true],
            ['name' => 'Product Management', 'is_active' => true],
            ['name' => 'Public Relations', 'is_active' => true],
            ['name' => 'Administration', 'is_active' => true],
            ['name' => 'Engineering', 'is_active' => true],
            ['name' => 'Design', 'is_active' => true],
            ['name' => 'Business Development', 'is_active' => true],
            ['name' => 'Compliance', 'is_active' => true],
            ['name' => 'Corporate Strategy', 'is_active' => true],
            // Tidak Aktif
            ['name' => 'Legacy Systems', 'is_active' => false],
            ['name' => 'Old Production', 'is_active' => false],
            ['name' => 'Discontinued Project', 'is_active' => false],
            ['name' => 'Merged Division', 'is_active' => false],
            ['name' => 'Outsourced Unit', 'is_active' => false],
        ];

        foreach ($departements as $dept) {
            Departement::firstOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }
    }
}
