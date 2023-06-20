<?php

namespace App\Controllers\Types;


class CommonTypesController
{
    static function listType()
    {
        return array(
            'Corriente' => 'Corriente',
            'Ahorros' => 'Ahorros',
            'Inversion' => 'Inversion',
            'Tarjeta de credito' => 'Tarjeta de credito',
            'Credito' => 'Credito',
            'Efectivo' => 'Efectivo',
        );
    }
    
    static function listPeriodicity()
    {
        return [
            'Weekly' => 'Semanal',
            'Biweekly' => 'Quincenal',
            'Monthly' => 'Mensual',
            'Bimonthly' => 'Bimensual',
            'Quarterly' => 'Trimestral',
            'quarterly' => 'Cuatrimestral',
            'Biannual' => 'Semestral',
            'Annual' => 'Anual'
        ];
    }

}