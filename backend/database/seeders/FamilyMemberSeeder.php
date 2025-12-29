<?php

namespace Database\Seeders;

use App\Models\FamilyMember;
use Illuminate\Database\Seeder;

class FamilyMemberSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        FamilyMember::query()->delete();

        // Generation 1 - Founders
        $ahmad = FamilyMember::create([
            'name' => 'Ahmad',
            'gender' => 'male',
            'birth_date' => '1960-05-15',
            'generation_level' => 1,
            'avatar' => 'male-1',
            'notes' => 'Pendiri keluarga, pekerja keras'
        ]);

        $fatimah = FamilyMember::create([
            'name' => 'Fatimah',
            'gender' => 'female',
            'birth_date' => '1962-08-20',
            'generation_level' => 1,
            'spouse_id' => $ahmad->id,
            'avatar' => 'female-1',
            'notes' => 'Ibu rumah tangga yang bijaksana'
        ]);

        // Update spouse reference
        $ahmad->update(['spouse_id' => $fatimah->id]);

        // Generation 2 - Children
        $hasan = FamilyMember::create([
            'name' => 'Hasan',
            'gender' => 'male',
            'birth_date' => '1985-03-10',
            'father_id' => $ahmad->id,
            'mother_id' => $fatimah->id,
            'generation_level' => 2,
            'avatar' => 'male-2',
            'notes' => 'Anak sulung, insinyur'
        ]);

        $sari = FamilyMember::create([
            'name' => 'Sari',
            'gender' => 'female',
            'birth_date' => '1987-11-25',
            'father_id' => $ahmad->id,
            'mother_id' => $fatimah->id,
            'generation_level' => 2,
            'spouse_id' => $hasan->id,
            'avatar' => 'female-2',
            'notes' => 'Anak kedua, guru'
        ]);

        // Update spouse reference
        $hasan->update(['spouse_id' => $sari->id]);

        $dani = FamilyMember::create([
            'name' => 'Dani',
            'gender' => 'male',
            'birth_date' => '1990-07-12',
            'father_id' => $ahmad->id,
            'mother_id' => $fatimah->id,
            'generation_level' => 2,
            'avatar' => 'male-3',
            'notes' => 'Anak ketiga, wirausaha'
        ]);

        $ida = FamilyMember::create([
            'name' => 'Ida',
            'gender' => 'female',
            'birth_date' => '1992-12-08',
            'father_id' => $ahmad->id,
            'mother_id' => $fatimah->id,
            'generation_level' => 2,
            'avatar' => 'female-3',
            'notes' => 'Anak bungsu, dokter'
        ]);

        $dita = FamilyMember::create([
            'name' => 'Dita',
            'gender' => 'female',
            'birth_date' => '1995-04-18',
            'father_id' => $ahmad->id,
            'mother_id' => $fatimah->id,
            'generation_level' => 2,
            'avatar' => 'female-4',
            'notes' => 'Anak termuda, mahasiswa'
        ]);

        // Generation 3 - Grandchildren
        $meira = FamilyMember::create([
            'name' => 'Meira',
            'gender' => 'female',
            'birth_date' => '2010-09-05',
            'father_id' => $hasan->id,
            'mother_id' => $sari->id,
            'generation_level' => 3,
            'avatar' => 'female-1',
            'notes' => 'Cucu pertama, pintar sekolah'
        ]);

        $gya = FamilyMember::create([
            'name' => 'Gya',
            'gender' => 'male',
            'birth_date' => '2012-01-30',
            'father_id' => $hasan->id,
            'mother_id' => $sari->id,
            'generation_level' => 3,
            'avatar' => 'male-4',
            'notes' => 'Cucu kedua, suka olahraga'
        ]);

        // Generation 4 - Great-grandchildren
        FamilyMember::create([
            'name' => 'Gen4',
            'gender' => 'male',
            'birth_date' => '2030-06-15',
            'father_id' => null, // Belum ada ayah dari generasi 3
            'mother_id' => $meira->id,
            'generation_level' => 4,
            'avatar' => 'male-1',
            'notes' => 'Cicit pertama'
        ]);
    }
}
