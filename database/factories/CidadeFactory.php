<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Cidade;
use Faker\Generator as Faker;

$factory->define(Cidade::class, function (Faker $faker) {
    return [
        'cidade' => 'SAO JOSE DO RIO PRETO',
        'uf' => 'SP'
    ];
});
