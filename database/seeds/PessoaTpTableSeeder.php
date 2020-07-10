<?php

use App\Model\PessoaTp;
use Illuminate\Database\Seeder;

class PessoaTpTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PessoaTp::create([
            'descricao' => 'Cliente'
        ]);
        PessoaTp::create([
            'descricao' => 'Fornecedor'
        ]);
    }
}
