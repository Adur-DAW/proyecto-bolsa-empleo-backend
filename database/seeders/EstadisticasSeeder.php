<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Demandante;
use App\Models\Oferta;
use Carbon\Carbon;

class EstadisticasSeeder extends Seeder
{
    public function run()
    {
        // Limpiar tablas (orden inverso por FKs)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('demandantes_oferta')->truncate();
        DB::table('titulos_oferta')->truncate();
        DB::table('titulos_demandante')->truncate();
        DB::table('ofertas')->truncate();
        DB::table('demandantes')->truncate();
        DB::table('empresas')->truncate();
        DB::table('titulos')->truncate();
        DB::table('usuarios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $faker = \Faker\Factory::create('es_ES');

        // 1. Títulos y Familias
        $familias = ['Informática', 'Administración', 'Hostelería', 'Sanidad', 'Comercio'];
        $titulosIds = [];

        foreach ($familias as $familia) {
            for ($i = 0; $i < 5; $i++) {
                $titulosIds[$familia][] = DB::table('titulos')->insertGetId([
                    'nombre' => $familia . ' - Grado ' . ($i + 1),
                    'familia_profesional' => $familia,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Empresas y Usuarios (200 Empresas)
        $empresasIds = [];
        for ($i = 0; $i < 200; $i++) {
            $fechaRegistro = Carbon::now()->subDays(rand(1, 240));

            $userUrl = Usuario::create([
                'email' => "empresa$i@test.com",
                'password' => Hash::make('password'),
                'rol' => 'empresa',
                'created_at' => $fechaRegistro,
                'updated_at' => $fechaRegistro
            ]);

            $empresa = Empresa::create([
                'id_empresa' => $userUrl->id,
                'nombre' => $faker->company,
                'cif' => $faker->vat,
                'localidad' => $faker->randomElement(['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Bilbao', 'Zaragoza', 'Malaga', 'Murcia']), 
                'telefono' => substr($faker->phoneNumber, 0, 9),
                'familia_profesional' => $faker->randomElement($familias),
                'validado' => 1,
                'created_at' => $fechaRegistro,
                'updated_at' => $fechaRegistro
            ]);
            $empresasIds[] = $empresa->id_empresa;
        }
        
        // Admin
        Usuario::create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'rol' => 'centro',
            'created_at' => now(), 
            'updated_at' => now()
        ]);

        // 3. Demandantes (500 Demandantes)
        $demandantesIds = [];
        for ($i = 0; $i < 500; $i++) {
            $fechaRegistro = Carbon::now()->subDays(rand(1, 240));

            $userD = Usuario::create([
                'email' => "demandante$i@test.com",
                'password' => Hash::make('password'),
                'rol' => 'demandante',
                'created_at' => $fechaRegistro,
                'updated_at' => $fechaRegistro
            ]);

            $familia = $faker->randomElement($familias);
            
            $demandante = Demandante::create([
                'id_demandante' => $userD->id,
                'dni' => $faker->dni,
                'nombre' => $faker->firstName,
                'apellido1' => $faker->lastName,
                'apellido2' => $faker->lastName,
                'email' => $userD->email,
                'telefono_movil' => substr($faker->phoneNumber, 0, 9),
                'familia_profesional' => $familia,
                'situacion' => 1,
                'created_at' => $fechaRegistro,
                'updated_at' => $fechaRegistro
            ]);
            $demandantesIds[] = $demandante->id_demandante;

            // Asignar Título
            DB::table('titulos_demandante')->insert([
                'id_demandante' => $demandante->id_demandante,
                'id_titulo' => $faker->randomElement($titulosIds[$familia]),
                'año' => $faker->year(),
                'created_at' => $fechaRegistro,
                'updated_at' => $fechaRegistro
            ]);
        }

        // 4. Ofertas (600 Ofertas en 8 meses)
        for ($i = 0; $i < 600; $i++) {
            $fechaPub = Carbon::now()->subDays(rand(1, 240));
            $empresaId = $faker->randomElement($empresasIds);
            
            $familiaOferta = $faker->randomElement($familias); 
            $tituloRequerido = $faker->randomElement($titulosIds[$familiaOferta]);

            $oferta = Oferta::create([
                'nombre' => 'Oferta de ' . $familiaOferta . ' #' . $i,
                'fecha_publicacion' => $fechaPub,
                'numero_puestos' => rand(1, 4),
                'tipo_contrato' => 'Indefinido',
                'abierta' => 1,
                'id_empresa' => $empresaId,
                'created_at' => $fechaPub,
                'updated_at' => $fechaPub,
            ]);

            DB::table('titulos_oferta')->insert([
                'id_oferta' => $oferta->id,
                'id_titulo' => $tituloRequerido,
                'created_at' => $fechaPub,
                'updated_at' => $fechaPub
            ]);

            // Generar inscripciones
            $numInscritos = rand(0, 15); // Más competencia
            $seAdjudica = rand(0, 10) > 3 && $numInscritos > 0; // 70% probabilidad adjudicar

            $candidatos = $faker->randomElements($demandantesIds, $numInscritos);

            foreach ($candidatos as $idx => $candId) {
                $esElElegido = $seAdjudica && $idx === 0; 

                $adjudicada = $esElElegido ? 1 : 0;
                $fechaAdj = $esElElegido ? (clone $fechaPub)->addDays(rand(2, 20)) : null;

                DB::table('demandantes_oferta')->insert([
                    'id_oferta' => $oferta->id,
                    'id_demandante' => $candId,
                    'adjudicada' => $adjudicada,
                    'fecha' => $fechaAdj ? $fechaAdj : null, 
                    'created_at' => (clone $fechaPub)->addDays(rand(0, 2)), 
                    'updated_at' => now()
                ]);

                if ($esElElegido) {
                    $oferta->abierta = 0; 
                    $oferta->save();
                }
            }

            if ($i % 10 === 0 && $oferta->abierta === 1) {
                $oferta->abierta = 0;
                $oferta->save();
            }
        }
    }
}
