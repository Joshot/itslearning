<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123'),
            'is_admin' => true,
        ]);



        // Sample Student
        Student::factory()->create([
            'name' => 'Joshua Hotama',
            'email' => 'joshuaho@student.edu',
            'nim' => '00000056899',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);


        // Sample Lecturer
        Lecturer::factory()->create([
            'name' => 'Hamzah Unto',
            'email' => 'hamzahunto@lecturer.edu',
            'nidn' => '00000001526',
            'major' => 'Informatika',
            'mata_kuliah' => 'Pengenalan Internet, Machine Learning',
            'password' => bcrypt('123'),
        ]);

        //Pertanian
        // Lecturer Record (Pertanian)
        Lecturer::factory()->create([
            'name' => 'Dr. Hasan Basri',
            'email' => 'hasanbasri1@lecturer.edu',
            'nidn' => '00000001701',
            'major' => 'Pertanian',
            'mata_kuliah' => 'Dasar Genetik Tanaman, Agroteknologi',
            'password' => bcrypt('123'),
        ]);

        // Student Records (Pertanian)
        Student::factory()->create([
            'name' => 'Ahmad Saiful',
            'email' => 'ahmads1@student.edu',
            'nim' => '00000058001',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Siti Rahmah',
            'email' => 'sitir2@student.edu',
            'nim' => '00000058002',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budis3@student.edu',
            'nim' => '00000058003',
            'major' => 'Pertanian',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rina Amelia',
            'email' => 'rinaa4@student.edu',
            'nim' => '00000058004',
            'major' => 'Pertanian',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Dedi Pratama',
            'email' => 'dedip5@student.edu',
            'nim' => '00000058005',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Lina Sari',
            'email' => 'linas6@student.edu',
            'nim' => '00000058006',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fajar Nugroho',
            'email' => 'fajarn7@student.edu',
            'nim' => '00000058007',
            'major' => 'Pertanian',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Maya Putri',
            'email' => 'mayap8@student.edu',
            'nim' => '00000058008',
            'major' => 'Pertanian',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rudi Hartono',
            'email' => 'rudih9@student.edu',
            'nim' => '00000058009',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Anita Dewi',
            'email' => 'anitad10@student.edu',
            'nim' => '00000058010',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Hendra Wijaya',
            'email' => 'hendraw11@student.edu',
            'nim' => '00000058011',
            'major' => 'Pertanian',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sari Indah',
            'email' => 'sarii12@student.edu',
            'nim' => '00000058012',
            'major' => 'Pertanian',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Eko Saputra',
            'email' => 'ekos13@student.edu',
            'nim' => '00000058013',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nia Ramadhani',
            'email' => 'niar14@student.edu',
            'nim' => '00000058014',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Yusuf Maulana',
            'email' => 'yusufm15@student.edu',
            'nim' => '00000058015',
            'major' => 'Pertanian',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Tina Lestari',
            'email' => 'tinat16@student.edu',
            'nim' => '00000058016',
            'major' => 'Pertanian',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Agus Setiawan',
            'email' => 'aguss17@student.edu',
            'nim' => '00000058017',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Dewi Puspita',
            'email' => 'dewip18@student.edu',
            'nim' => '00000058018',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rizky Pratama',
            'email' => 'rizkyp19@student.edu',
            'nim' => '00000058019',
            'major' => 'Pertanian',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Laila Sari',
            'email' => 'lailas20@student.edu',
            'nim' => '00000058020',
            'major' => 'Pertanian',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Adi Nugroho',
            'email' => 'adin21@student.edu',
            'nim' => '00000058021',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Mira Aulia',
            'email' => 'miram22@student.edu',
            'nim' => '00000058022',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Bima Sakti',
            'email' => 'bimas23@student.edu',
            'nim' => '00000058023',
            'major' => 'Pertanian',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sinta Dewi',
            'email' => 'sintad24@student.edu',
            'nim' => '00000058024',
            'major' => 'Pertanian',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Arif Rahman',
            'email' => 'arifr25@student.edu',
            'nim' => '00000058025',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nadia Putri',
            'email' => 'nadiap26@student.edu',
            'nim' => '00000058026',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Doni Setiawan',
            'email' => 'donis27@student.edu',
            'nim' => '00000058027',
            'major' => 'Pertanian',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rani Amelia',
            'email' => 'ranir28@student.edu',
            'nim' => '00000058028',
            'major' => 'Pertanian',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fandi Ahmad',
            'email' => 'fandia29@student.edu',
            'nim' => '00000058029',
            'major' => 'Pertanian',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Aisyah Lestari',
            'email' => 'aisyah30@student.edu',
            'nim' => '00000058030',
            'major' => 'Pertanian',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        //Sample Dummy

        // Student Records
        Student::factory()->create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmadfauzi1@student.edu',
            'nim' => '00000057001',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Siti Nurhaliza',
            'email' => 'sitinurhaliza2@student.edu',
            'nim' => '00000057002',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budisantoso3@student.edu',
            'nim' => '00000057003',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rina Amelia',
            'email' => 'rinaamelia4@student.edu',
            'nim' => '00000057004',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Dedi Pratama',
            'email' => 'dedipratama5@student.edu',
            'nim' => '00000057005',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Lina Sari',
            'email' => 'linasari6@student.edu',
            'nim' => '00000057006',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fajar Nugroho',
            'email' => 'fajarnugroho7@student.edu',
            'nim' => '00000057007',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Maya Putri',
            'email' => 'mayaputri8@student.edu',
            'nim' => '00000057008',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rudi Hartono',
            'email' => 'rudihartono9@student.edu',
            'nim' => '00000057009',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Anita Dewi',
            'email' => 'anitadewi10@student.edu',
            'nim' => '00000057010',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Hendra Wijaya',
            'email' => 'hendrawijaya11@student.edu',
            'nim' => '00000057011',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sari Indah',
            'email' => 'sariindah12@student.edu',
            'nim' => '00000057012',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Eko Saputra',
            'email' => 'ekosaputra13@student.edu',
            'nim' => '00000057013',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nia Ramadhani',
            'email' => 'niaramadhani14@student.edu',
            'nim' => '00000057014',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Yusuf Maulana',
            'email' => 'yusufmaulana15@student.edu',
            'nim' => '00000057015',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Tina Lestari',
            'email' => 'tinalestari16@student.edu',
            'nim' => '00000057016',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Agus Setiawan',
            'email' => 'agussetiawan17@student.edu',
            'nim' => '00000057017',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Dewi Puspita',
            'email' => 'dewipuspita18@student.edu',
            'nim' => '00000057018',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rizky Pratama',
            'email' => 'rizkypratama19@student.edu',
            'nim' => '00000057019',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Laila Sari',
            'email' => 'lailasari20@student.edu',
            'nim' => '00000057020',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Adi Nugroho',
            'email' => 'adinugroho21@student.edu',
            'nim' => '00000057021',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Mira Aulia',
            'email' => 'miraaulia22@student.edu',
            'nim' => '00000057022',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Bima Sakti',
            'email' => 'bimasakti23@student.edu',
            'nim' => '00000057023',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sinta Dewi',
            'email' => 'sintadewi24@student.edu',
            'nim' => '00000057024',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Arif Rahman',
            'email' => 'arifrahman25@student.edu',
            'nim' => '00000057025',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nadia Putri',
            'email' => 'nadiaputri26@student.edu',
            'nim' => '00000057026',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Doni Setiawan',
            'email' => 'donisetiawan27@student.edu',
            'nim' => '00000057027',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rani Amelia',
            'email' => 'raniamelia28@student.edu',
            'nim' => '00000057028',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fandi Ahmad',
            'email' => 'fandiahmad29@student.edu',
            'nim' => '00000057029',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Aisyah Lestari',
            'email' => 'aisyahlestari30@student.edu',
            'nim' => '00000057030',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Irfan Hakim',
            'email' => 'irfanhakim31@student.edu',
            'nim' => '00000057031',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fitriani Sari',
            'email' => 'fitrianisari32@student.edu',
            'nim' => '00000057032',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Hadi Santoso',
            'email' => 'hadisantoso33@student.edu',
            'nim' => '00000057033',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rina Putri',
            'email' => 'rinaputri34@student.edu',
            'nim' => '00000057034',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Toni Wijaya',
            'email' => 'toniwijaya35@student.edu',
            'nim' => '00000057035',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Vina Lestari',
            'email' => 'vinalestari36@student.edu',
            'nim' => '00000057036',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Andi Pratama',
            'email' => 'andipratama37@student.edu',
            'nim' => '00000057037',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sari Aulia',
            'email' => 'sariaulia38@student.edu',
            'nim' => '00000057038',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rudi Maulana',
            'email' => 'rudimaulana39@student.edu',
            'nim' => '00000057039',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Lina Putri',
            'email' => 'linaputri40@student.edu',
            'nim' => '00000057040',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Eko Nugroho',
            'email' => 'ekonugroho41@student.edu',
            'nim' => '00000057041',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Mira Sari',
            'email' => 'mirasari42@student.edu',
            'nim' => '00000057042',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Budi Pratama',
            'email' => 'budipratama43@student.edu',
            'nim' => '00000057043',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nia Aulia',
            'email' => 'niaaulia44@student.edu',
            'nim' => '00000057044',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Yusuf Santoso',
            'email' => 'yusufsantoso45@student.edu',
            'nim' => '00000057045',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Tina Amelia',
            'email' => 'tinaamelia46@student.edu',
            'nim' => '00000057046',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Agus Wijaya',
            'email' => 'aguswijaya47@student.edu',
            'nim' => '00000057047',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Dewi Lestari',
            'email' => 'dewilestari48@student.edu',
            'nim' => '00000057048',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rizky Nugroho',
            'email' => 'rizkynugroho49@student.edu',
            'nim' => '00000057049',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Laila Aulia',
            'email' => 'lailaaulia50@student.edu',
            'nim' => '00000057050',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Adi Santoso',
            'email' => 'adisantoso51@student.edu',
            'nim' => '00000057051',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Mira Pratama',
            'email' => 'mirapratama52@student.edu',
            'nim' => '00000057052',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Bima Wijaya',
            'email' => 'bimawijaya53@student.edu',
            'nim' => '00000057053',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sinta Lestari',
            'email' => 'sintalestari54@student.edu',
            'nim' => '00000057054',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Arif Nugroho',
            'email' => 'arifnugroho55@student.edu',
            'nim' => '00000057055',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nadia Sari',
            'email' => 'nadiasari56@student.edu',
            'nim' => '00000057056',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Doni Pratama',
            'email' => 'donipratama57@student.edu',
            'nim' => '00000057057',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rani Putri',
            'email' => 'raniputri58@student.edu',
            'nim' => '00000057058',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fandi Wijaya',
            'email' => 'fandiwijaya59@student.edu',
            'nim' => '00000057059',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Aisyah Aulia',
            'email' => 'aisyahaulia60@student.edu',
            'nim' => '00000057060',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Irfan Santoso',
            'email' => 'irfansantoso61@student.edu',
            'nim' => '00000057061',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fitriani Lestari',
            'email' => 'fitrianilestari62@student.edu',
            'nim' => '00000057062',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Hadi Nugroho',
            'email' => 'hadinugroho63@student.edu',
            'nim' => '00000057063',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rina Aulia',
            'email' => 'rinaaulia64@student.edu',
            'nim' => '00000057064',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Toni Pratama',
            'email' => 'tonipratama65@student.edu',
            'nim' => '00000057065',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Vina Sari',
            'email' => 'vinasari66@student.edu',
            'nim' => '00000057066',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Andi Nugroho',
            'email' => 'andinugroho67@student.edu',
            'nim' => '00000057067',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sari Pratama',
            'email' => 'saripratama68@student.edu',
            'nim' => '00000057068',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rudi Santoso',
            'email' => 'rudisantoso69@student.edu',
            'nim' => '00000057069',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Lina Aulia',
            'email' => 'linaaulia70@student.edu',
            'nim' => '00000057070',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Eko Wijaya',
            'email' => 'ekowijaya71@student.edu',
            'nim' => '00000057071',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Mira Lestari',
            'email' => 'miralestari72@student.edu',
            'nim' => '00000057072',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Budi Nugroho',
            'email' => 'budinugroho73@student.edu',
            'nim' => '00000057073',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nia Pratama',
            'email' => 'niapratama74@student.edu',
            'nim' => '00000057074',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Yusuf Aulia',
            'email' => 'yusufaulia75@student.edu',
            'nim' => '00000057075',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Tina Sari',
            'email' => 'tinasari76@student.edu',
            'nim' => '00000057076',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Agus Pratama',
            'email' => 'aguspratama77@student.edu',
            'nim' => '00000057077',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Dewi Aulia',
            'email' => 'dewiaulia78@student.edu',
            'nim' => '00000057078',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rizky Santoso',
            'email' => 'rizkysantoso79@student.edu',
            'nim' => '00000057079',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Laila Nugroho',
            'email' => 'lailanugroho80@student.edu',
            'nim' => '00000057080',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Adi Pratama',
            'email' => 'adipratama81@student.edu',
            'nim' => '00000057081',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Mira Wijaya',
            'email' => 'mirawijaya82@student.edu',
            'nim' => '00000057082',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Bima Lestari',
            'email' => 'bimalestari83@student.edu',
            'nim' => '00000057083',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sinta Pratama',
            'email' => 'sintapratama84@student.edu',
            'nim' => '00000057084',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Arif Aulia',
            'email' => 'arifaulia85@student.edu',
            'nim' => '00000057085',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Nadia Lestari',
            'email' => 'nadialestari86@student.edu',
            'nim' => '00000057086',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Doni Nugroho',
            'email' => 'doninugroho87@student.edu',
            'nim' => '00000057087',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rani Sari',
            'email' => 'ranisari88@student.edu',
            'nim' => '00000057088',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fandi Pratama',
            'email' => 'fandipratama89@student.edu',
            'nim' => '00000057089',
            'major' => 'Sistem Informasi',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Aisyah Nugroho',
            'email' => 'aisyahnugroho90@student.edu',
            'nim' => '00000057090',
            'major' => 'Teknik Komputer',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Irfan Wijaya',
            'email' => 'irfanwijaya91@student.edu',
            'nim' => '00000057091',
            'major' => 'Informatika',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Fitriani Pratama',
            'email' => 'fitrianipratama92@student.edu',
            'nim' => '00000057092',
            'major' => 'Sistem Informasi',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Hadi Aulia',
            'email' => 'hadiaulia93@student.edu',
            'nim' => '00000057093',
            'major' => 'Teknik Elektro',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rina Lestari',
            'email' => 'rinalestari94@student.edu',
            'nim' => '00000057094',
            'major' => 'Informatika',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Toni Sari',
            'email' => 'tonisari95@student.edu',
            'nim' => '00000057095',
            'major' => 'Sistem Informasi',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Vina Pratama',
            'email' => 'vinapratama96@student.edu',
            'nim' => '00000057096',
            'major' => 'Teknik Komputer',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Andi Lestari',
            'email' => 'andilestari97@student.edu',
            'nim' => '00000057097',
            'major' => 'Informatika',
            'angkatan' => '2021',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Sari Nugroho',
            'email' => 'sarinugroho98@student.edu',
            'nim' => '00000057098',
            'major' => 'Sistem Informasi',
            'angkatan' => '2022',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Rudi Pratama',
            'email' => 'rudipratama99@student.edu',
            'nim' => '00000057099',
            'major' => 'Teknik Elektro',
            'angkatan' => '2020',
            'password' => bcrypt('123'),
        ]);

        Student::factory()->create([
            'name' => 'Lina Sari',
            'email' => 'linasari100@student.edu',
            'nim' => '00000057100',
            'major' => 'Informatika',
            'angkatan' => '2023',
            'password' => bcrypt('123'),
        ]);

        // Lecturer Records
        Lecturer::factory()->create([
            'name' => 'Dr. Ahmad Yani',
            'email' => 'ahmadyani1@lecturer.edu',
            'nidn' => '00000001601',
            'major' => 'Informatika',
            'mata_kuliah' => 'Pemrograman Dasar, Struktur Data',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Siti Aminah',
            'email' => 'sitiaminah2@lecturer.edu',
            'nidn' => '00000001602',
            'major' => 'Sistem Informasi',
            'mata_kuliah' => 'Manajemen Basis Data, Analisis Sistem',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budisantoso3@lecturer.edu',
            'nidn' => '00000001603',
            'major' => 'Teknik Elektro',
            'mata_kuliah' => 'Sistem Digital, Elektronika',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Rina Lestari',
            'email' => 'rinalestari4@lecturer.edu',
            'nidn' => '00000001604',
            'major' => 'Informatika',
            'mata_kuliah' => 'Algoritma, Kecerdasan Buatan',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Dedi Pratama',
            'email' => 'dedipratama5@lecturer.edu',
            'nidn' => '00000001605',
            'major' => 'Sistem Informasi',
            'mata_kuliah' => 'Sistem Informasi Manajemen, E-Commerce',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Lina Sari',
            'email' => 'linasari6@lecturer.edu',
            'nidn' => '00000001606',
            'major' => 'Teknik Komputer',
            'mata_kuliah' => 'Jaringan Komputer, Sistem Operasi',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Fajar Nugroho',
            'email' => 'fajarnugroho7@lecturer.edu',
            'nidn' => '00000001607',
            'major' => 'Informatika',
            'mata_kuliah' => 'Pemrograman Web, Keamanan Sistem',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Maya Putri',
            'email' => 'mayaputri8@lecturer.edu',
            'nidn' => '00000001608',
            'major' => 'Sistem Informasi',
            'mata_kuliah' => 'Analisis Data, Data Warehouse',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Rudi Hartono',
            'email' => 'rudihartono9@lecturer.edu',
            'nidn' => '00000001609',
            'major' => 'Teknik Elektro',
            'mata_kuliah' => 'Rangkaian Listrik, Sistem Kontrol',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Anita Dewi',
            'email' => 'anitadewi10@lecturer.edu',
            'nidn' => '00000001610',
            'major' => 'Informatika',
            'mata_kuliah' => 'Pemrograman Mobile, Interaksi Manusia Komputer',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Hendra Wijaya',
            'email' => 'hendrawijaya11@lecturer.edu',
            'nidn' => '00000001611',
            'major' => 'Sistem Informasi',
            'mata_kuliah' => 'Sistem Pendukung Keputusan, Manajemen Proyek',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Sari Indah',
            'email' => 'sariindah12@lecturer.edu',
            'nidn' => '00000001612',
            'major' => 'Teknik Komputer',
            'mata_kuliah' => 'Arsitektur Komputer, Sistem Tertanam',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Eko Saputra',
            'email' => 'ekosaputra13@lecturer.edu',
            'nidn' => '00000001613',
            'major' => 'Informatika',
            'mata_kuliah' => 'Pemrograman Berorientasi Objek, Desain UI/UX',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Nia Ramadhani',
            'email' => 'niaramadhani14@lecturer.edu',
            'nidn' => '00000001614',
            'major' => 'Sistem Informasi',
            'mata_kuliah' => 'Basis Data Terdistribusi, Big Data',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Yusuf Maulana',
            'email' => 'yusufmaulana15@lecturer.edu',
            'nidn' => '00000001615',
            'major' => 'Teknik Elektro',
            'mata_kuliah' => 'Elektronika Daya, Sistem Tenaga Listrik',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Tina Lestari',
            'email' => 'tinalestari16@lecturer.edu',
            'nidn' => '00000001616',
            'major' => 'Informatika',
            'mata_kuliah' => 'Kecerdasan Buatan, Machine Learning',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Agus Setiawan',
            'email' => 'agussetiawan17@lecturer.edu',
            'nidn' => '00000001617',
            'major' => 'Sistem Informasi',
            'mata_kuliah' => 'Manajemen Basis Data, Sistem Informasi Geografis',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Dewi Puspita',
            'email' => 'dewipuspita18@lecturer.edu',
            'nidn' => '00000001618',
            'major' => 'Teknik Komputer',
            'mata_kuliah' => 'Jaringan Nirkabel, Internet of Things',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Dr. Rizky Pratama',
            'email' => 'rizkypratama19@lecturer.edu',
            'nidn' => '00000001619',
            'major' => 'Informatika',
            'mata_kuliah' => 'Pemrograman Lanjut, Komputasi Awan',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Prof. Laila Sari',
            'email' => 'lailasari20@lecturer.edu',
            'nidn' => '00000001620',
            'major' => 'Sistem Informasi',
            'mata_kuliah' => 'Analisis Bisnis, Sistem ERP',
            'password' => bcrypt('123'),
        ]);




    }
}
