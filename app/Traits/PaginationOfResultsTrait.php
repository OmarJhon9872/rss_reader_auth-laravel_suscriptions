<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

trait PaginationOfResultsTrait{

    /** Funcion para crear paginacion de resultados
     *
     * @var $currentPage Request Parametro tipo get que identifica el numero de pagina
     * @var $currentElements array Array de datos a paginar
     * @var $dataPaginated Paginator Datos paginados
     *
     * @param $arrayData array de datos a paginar
     * @param $oldValue string Cadena que se estaba buscando
     * @param $perPage int Numero de resultados por pagina
     * @param $getParamSearch string Parametro GET que indica la busqueda
     * @param $getParamPage string Nombre parametro GET que indica numero de pagina
     *
     * @return Paginator Data paginada por cantidad de elementos indicados
    */
    public function createCustomPagination($arrayData, $oldValue = '', $perPage = 15, $getParamSearch = 'buscar', $getParamPage = 'page'){

        $currentPage = Paginator::resolveCurrentPage($getParamPage);
        $currentElements = array_slice($arrayData, $perPage * ($currentPage - 1), $perPage);

        for($i = 0; $i < (count($arrayData)-$perPage); $i++){
            $currentElements[] = [];
        }

        $dataPaginated = new Paginator($currentElements, $perPage, $currentPage);
        $dataPaginated->setPath(route('home.buscar').'?buscar='.$oldValue);

        return $dataPaginated;
    }
}
