<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Empresa;
use Faker\Generator as Faker;

$factory->define(Empresa::class, function (Faker $faker) {
    return [
        'nome' => 'ELSoft Sistemas',//$faker->name,
        'cnpjcpf' => '11663518000121',//$faker->unique()->safeEmail,
        'inativo' => false,
        'email' => 'elsoft@gmail.com',
        'cidade_id' => 1
    ];
});
