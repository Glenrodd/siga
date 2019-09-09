<?php

namespace siga\Http\Controllers\comercial;

use Illuminate\Http\Request;
use siga\Http\Controllers\Controller;
use siga\Modelo\insumo\insumo_recetas\Receta;

class IngresoPuntoVentaController extends Controller
{
    public function index()
    {
    	//dd("INGRESO PRODUCTOS PUNTO DE VENTA");
    	$recetas = Receta::leftjoin('insumo.sabor as sab','insumo.receta.rece_sabor_id','=','sab.sab_id')->get();
    	return view('backend.administracion.comercial.ingreso_punto_venta.index', compact('recetas'));
    }

    public function listarProductos()
    {
        $recetas = Receta::leftjoin('insumo.sabor as sab','insumo.receta.rece_sabor_id','=','sab.sab_id')->get();
        return Datatables::of($recetas)   
            ->editColumn('id', 'ID: {{$rece_id}}')
            ->addColumn('acciones', function ($recetas) {
                return '<div><button value="'.$stock_insumo->ins_id.'" id="buttonsol" class="btn btn-success insumo-get" onClick="MostrarCarrito()" data-toggle="modal" data-target="#myCreateRCA">+</button></div>';
            })->addColumn('solicitud_cantidad', function($recetas){
                return '<input class="form-control" type="number"></input>';
            })->addColumn('solicitud_costo', function($recetas){
                return '<input class="form-control" type="number"></input>';
            })->addColumn('solicitud_lote', function($recetas){
                return '<input class="form-control" type="number"></input>';
            })->addColumn('solicitud_fecha_vencimiento', function($recetas){
                return '<input class="form-control" type="number"></input>';
            })->addColumn('id_insumo', function($recetas){
                return '<input class="id_insumo form-control" type="hidden" value="'.$stock_insumo->ins_id.'"></input>';
            })
            ->make(true);
    }
}