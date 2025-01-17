<?php

namespace siga\Http\Controllers\acopio\acopio_miel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use siga\Http\Requests;
use siga\Http\Controllers\Controller;
use siga\Modelo\admin\Usuario;
use siga\Modelo\acopio\acopio_miel\Acopio;
use siga\Modelo\acopio\acopio_miel\Proveedor;
use siga\Modelo\acopio\acopio_miel\Propiedades_Miel;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Auth;
use TCPDF;

class AcopioMielController extends Controller
{
    public function index(){
        $acopio = Acopio::getListar();
        // dd($acopio);
        $unidad = DB::table('acopio.unidad')->OrderBy('uni_id', 'desc')->pluck('uni_nombre','uni_id');
        $destino = DB::table('acopio.destino')->OrderBy('des_id','desc')->pluck('des_descripcion', 'des_id');
        $proveedor = Proveedor::OrderBy('prov_id', 'desc')->pluck('prov_nombre', 'prov_id');
        $resp_recep = DB::table('acopio.resp_recep')->OrderBy('rec_id','desc')->pluck('rec_nombre','rec_id');
        return view('backend.administracion.acopio.acopio_miel.acopio.index', compact('acopio', 'proveedor','unidad','destino','resp_recep'));
    }

    public function create()
    {
        $acopio = Acopio::getListar();
        // dd($acopio);
        return Datatables::of($acopio)->addColumn('acciones', function ($acopio) {
            return '<button value="' . $acopio->aco_id . '" class="btncirculo btn-xs btn-warning" onClick="MostrarAcopio(this);" data-toggle="modal" data-target="#UpdateFondosAvance"><i class="fa fa-pencil-square"></i></button><button value="' . $acopio->aco_id . '" class="btncirculo btn-xs btn-danger" onClick="Eliminar(this);"><i class="fa fa-trash-o"></i></button>';
        })
            ->editColumn('id', 'ID: {{$aco_id}}')->
            addColumn('nombreCompleto', function ($nombres) {
                return $nombres->prov_nombre.' '.$nombres->prov_ap.' '.$nombres->prov_am;
        })
            ->editColumn('id', 'ID: {{$aco_id}}')->
            addColumn('materiPrima', function ($materiPrima) {
                if ($materiPrima->aco_mat_prim == 1) {
                    return '<h4 class="text-center"><span class="label label-success">Aceptado</span></h4>';
                }
                return '<h4 class="text-center"><span class="label label-danger">Rechazado</span></h4>';
        })
            ->editColumn('id', 'ID: {{$aco_id}}')
            ->make(true);
    }
    public function nuevoAcopio(){
        return view('backend.administracion.acopio.acopio_miel.acopio.partials.formularioCreate');
    }
    public function store(Request $request)
    {   
        if ($request['is_pago'] == 1) {
            $this->validate(request(), [
                'peso_bruto' => 'required',
                'peso_tara' => 'required',
                'peso_neto' => 'required',
                'cantidad' => 'required',
                'total' => 'required',
                'humedad' => 'required',
                'costo' => 'required',
                'id_proveedor' => 'required',
                'aco_numaco' => 'required',
                'acta_entrega' => 'required',
                'fecha_acopio' => 'required',
                'fecha_resgistro' => 'required',
                // 'observacion' => 'required',
                'nro_recibo' => 'required',
                'responsable_recepcion' => 'required',
                'is_pago' => 'required',
                'fecha_recibo' => 'required',
                'destino' => 'required',
                'aco_mapri' => 'required',
            ]);
            $prom = Propiedades_Miel::create([
                'prom_peso_bruto'           =>$request['peso_bruto'],
                'prom_peso_tara'            =>$request['peso_tara'],
                'prom_peso_neto'            =>$request['peso_neto'],
                'prom_cantidad_baldes'      =>$request['cantidad'],
                'prom_total'                =>$request['total'],
                'prom_cod_colmenas'         =>1,
                'prom_centrifugado'         =>1,
                'prom_peso_bruto_centrif'   =>1,
                'prom_peso_bruto_filt'      =>1,
                'prom_peso_bruto_imp'       =>1,
                'prom_humedad'              =>$request['humedad'],
                'prom_cos_un'               =>$request['costo'],  
            ]);
            $prom_id = $prom->prom_id;
            Acopio::create([
                'aco_id_prov'       => $request['id_proveedor'],//MIEL//
                'aco_id_proc'       => 1,//MIEL//
                'aco_centro'        => null,//MIEL//
                'aco_peso_neto'     => null,//          
                'aco_id_tipo_cas'   => 1,//MODIFICADO
                'aco_numaco'        => $request['aco_numaco'],//
                'aco_num_act'       => $request['acta_entrega'],//MIEL//
                'aco_unidad'        => 1,//MODIFICADO
                'aco_cantidad'      => null,//MIEL//
                'aco_cos_un'        => null,//MIEL//
                'aco_cos_total'     => null,//MIEL//
                'aco_con_hig'       => null,//
                'aco_fecha_acop'    => $request['fecha_acopio'],//
                'aco_fecha_reg'     => $request['fecha_resgistro'],//MIEL//
                'aco_obs'           => $request['observacion'],//MIEL//
                'aco_estado'        => 'A',//MIEL//
                'aco_tram'          => "2018-10-01 16:00:00",//
                'aco_num_rec'       => $request['nro_recibo'],//MIEL//
                'aco_id_comunidad'  => null,//MODIFICADO
                'aco_id_recep'      => $request['responsable_recepcion'],//MIEL//
                'aco_id_linea'      => 3,//MIEL//
                'aco_pago'          => $request['is_pago'],//MIEL//
                'aco_fecha_rec'     => $request['fecha_recibo'],//MIEL//
                'aco_id_destino'    => $request['destino'],//MIEL//
                'aco_id_prom'       => $prom_id,//MIEL//
                'aco_id_usr'        => Auth::user()->usr_id,//MIEL//
                'aco_lac_tem'       => null,
                'aco_lac_aci'       => null,
                'aco_lac_ph'        => null,
                'aco_lac_sng'       => null,
                'aco_lac_den'       => null,
                'aco_lac_mgra'      => null,
                'aco_lac_palc'      => null,
                'aco_lac_pant'      => null,
                'aco_lac_asp'       => null,
                'aco_lac_col'       => null,
                'aco_lac_olo'       => null,
                'aco_lac_sab'       => null,
                'aco_id_comp'       => null,
                'aco_cert'          => null,
                'aco_tipo'          => 3,
                'aco_mat_prim'      => $request['aco_mapri'],
            ]);
            return response()->json(['Mensaje' => 'Se registro correctamente']);
        } else {
            $this->validate(request(), [
                'peso_bruto' => 'required',
                'peso_tara' => 'required',
                'peso_neto' => 'required',
                'cantidad' => 'required',
                'total' => 'required',
                'humedad' => 'required',
                'costo' => 'required',
                'id_proveedor' => 'required',
                'aco_numaco' => 'required',
                'acta_entrega' => 'required',
                'fecha_acopio' => 'required',
                'fecha_resgistro' => 'required',
                // 'observacion' => 'required',
                // 'nro_recibo' => 'required',
                'responsable_recepcion' => 'required',
                'is_pago' => 'required',
                // 'fecha_recibo' => 'required',
                'destino' => 'required',
                'aco_mapri' => 'required',
            ]);
            $prom = Propiedades_Miel::create([
                'prom_peso_bruto'           =>$request['peso_bruto'],
                'prom_peso_tara'            =>$request['peso_tara'],
                'prom_peso_neto'            =>$request['peso_neto'],
                'prom_cantidad_baldes'      =>$request['cantidad'],
                'prom_total'                =>$request['total'],
                'prom_cod_colmenas'         =>1,
                'prom_centrifugado'         =>1,
                'prom_peso_bruto_centrif'   =>1,
                'prom_peso_bruto_filt'      =>1,
                'prom_peso_bruto_imp'       =>1,
                'prom_humedad'              =>$request['humedad'],
                'prom_cos_un'               =>$request['costo'],  
            ]);
            $prom_id = $prom->prom_id;
            Acopio::create([
                'aco_id_prov'       => $request['id_proveedor'],//MIEL//
                'aco_id_proc'       => 1,//MIEL//
                'aco_centro'        => null,//MIEL//
                'aco_peso_neto'     => null,//          
                'aco_id_tipo_cas'   => 1,//MODIFICADO
                'aco_numaco'        => $request['aco_numaco'],//
                'aco_num_act'       => $request['acta_entrega'],//MIEL//
                'aco_unidad'        => 1,//MODIFICADO
                'aco_cantidad'      => null,//MIEL//
                'aco_cos_un'        => null,//MIEL//
                'aco_cos_total'     => null,//MIEL//
                'aco_con_hig'       => null,//
                'aco_fecha_acop'    => $request['fecha_acopio'],//
                'aco_fecha_reg'     => $request['fecha_resgistro'],//MIEL//
                'aco_obs'           => $request['observacion'],//MIEL//
                'aco_estado'        => 'A',//MIEL//
                'aco_tram'          => "2018-10-01 16:00:00",//
                // 'aco_num_rec'       => $request['nro_recibo'],//MIEL//
                'aco_id_comunidad'  => null,//MODIFICADO
                'aco_id_recep'      => $request['id_resp_recepcion'],//MIEL//
                'aco_id_linea'      => 3,//MIEL//
                'aco_pago'          => $request['is_pago'],//MIEL//
                // 'aco_fecha_rec'     => $request['fecha_recibo'],//MIEL//
                'aco_id_destino'    => $request['id_destino'],//MIEL//
                'aco_id_prom'       => $prom_id,//MIEL//
                'aco_id_usr'        => Auth::user()->usr_id,//MIEL//
                'aco_lac_tem'       => null,
                'aco_lac_aci'       => null,
                'aco_lac_ph'        => null,
                'aco_lac_sng'       => null,
                'aco_lac_den'       => null,
                'aco_lac_mgra'      => null,
                'aco_lac_palc'      => null,
                'aco_lac_pant'      => null,
                'aco_lac_asp'       => null,
                'aco_lac_col'       => null,
                'aco_lac_olo'       => null,
                'aco_lac_sab'       => null,
                'aco_id_comp'       => null,
                'aco_cert'          => null,
                'aco_tipo'          => 3,
                'aco_mat_prim'      => $request['aco_mapri'],
            ]); 

            return response()->json(['Mensaje' => 'Se registro correctamente']);
        }
        
    }

    public function registroAcopio(Request $request)
    {
        $planta = Usuario::join('public._bp_planta as planta','public._bp_usuarios.usr_planta_id','=','planta.id_planta')->select('planta.id_planta')->where('usr_id','=',Auth::user()->usr_id)->first(); 
        
        $array_nro = $request['nro'];
        $array_humedad_json = $request['humedad_json'];
        $array_bruto_json = $request['peso_bruto_json'];
        $array_tara_json = $request['peso_tara_json'];
        $array_neto_json = $request['peso_neto_json'];
        $array_estado_json = $request['estado_json'];

        // $json_baldes[]; 
        for ($i=0; $i <sizeof($array_nro) ; $i++) { 
            $json_baldes[] = array("nro"=>$array_nro[$i], "humedad"=>$array_humedad_json[$i],"peso_bruto"=>$array_bruto_json[$i],"peso_tara"=>$array_tara_json[$i],"peso_neto"=>$array_neto_json[$i],"estado"=>$array_estado_json[$i]); 
        }
        $baldes_json = json_encode($json_baldes);
        // dd($baldes_json);
        $aco_numaco = $request['nro_acopio'];
        $aco_id_recep = $request['id_resp_recepcion'];
        $aco_id_destino = $request['id_destino'];
        if ($request['is_pago'] == 1) {
            // $this->validate(request(), [
            //     'peso_bruto' => 'required',
            //     'peso_tara' => 'required',
            //     'peso_neto' => 'required',
            //     'cantidad' => 'required',
            //     'total' => 'required',
            //     'humedad' => 'required',
            //     'costo' => 'required',
            //     'id_proveedor' => 'required',
            //     'aco_numaco' => 'required',
            //     'acta_entrega' => 'required',
            //     'fecha_acopio' => 'required',
            //     'fecha_resgistro' => 'required',
            //     // 'observacion' => 'required',
            //     'nro_recibo' => 'required',
            //     'responsable_recepcion' => 'required',
            //     'is_pago' => 'required',
            //     'fecha_recibo' => 'required',
            //     'destino' => 'required',
            //     'aco_mapri' => 'required',
            // ]);
            $prom = Propiedades_Miel::create([
                'prom_peso_bruto'           =>$request['peso_bruto'],
                'prom_peso_tara'            =>$request['peso_tara'],
                'prom_peso_neto'            =>$request['peso_neto'],
                'prom_cantidad_baldes'      =>$request['cantidad'],
                'prom_total'                =>$request['total'],
                'prom_cod_colmenas'         =>1,
                'prom_centrifugado'         =>1,
                'prom_peso_bruto_centrif'   =>1,
                'prom_peso_bruto_filt'      =>1,
                'prom_peso_bruto_imp'       =>1,
                'prom_humedad'              =>$request['humedad'],
                'prom_cos_un'               =>$request['costo'],
                'prom_baldesjson'           =>$baldes_json,  
            ]);
            $prom_id = $prom->prom_id;
            Acopio::create([
                'aco_id_prov'       => $request['id_proveedor'],//MIEL//
                'aco_id_proc'       => 1,//MIEL//
                'aco_centro'        => null,//MIEL//
                'aco_peso_neto'     => null,//          
                'aco_id_tipo_cas'   => 1,//MODIFICADO
                'aco_numaco'        => $aco_numaco,//
                'aco_num_act'       => $request['acta_entrega'],//MIEL//
                'aco_unidad'        => 1,//MODIFICADO
                'aco_cantidad'      => null,//MIEL//
                'aco_cos_un'        => null,//MIEL//
                'aco_cos_total'     => null,//MIEL//
                'aco_con_hig'       => null,//
                'aco_fecha_acop'    => $request['fecha_acopio'],//
                'aco_fecha_reg'     => $request['fecha_resgistro'],//MIEL//
                'aco_obs'           => $request['observacion'],//MIEL//
                'aco_estado'        => 'A',//MIEL//
                'aco_tram'          => "2018-10-01 16:00:00",//
                'aco_num_rec'       => $request['nro_recibo'],//MIEL//
                'aco_id_comunidad'  => null,//MODIFICADO
                'aco_id_recep'      => $aco_id_recep,//MIEL//
                'aco_id_linea'      => 3,//MIEL//
                'aco_pago'          => $request['is_pago'],//MIEL//
                'aco_fecha_rec'     => $request['fecha_recibo'],//MIEL//
                'aco_id_destino'    => $aco_id_destino,//MIEL//
                'aco_id_prom'       => $prom_id,//MIEL//
                'aco_id_usr'        => Auth::user()->usr_id,//MIEL//
                'aco_lac_tem'       => null,
                'aco_lac_aci'       => null,
                'aco_lac_ph'        => null,
                'aco_lac_sng'       => null,
                'aco_lac_den'       => null,
                'aco_lac_mgra'      => null,
                'aco_lac_palc'      => null,
                'aco_lac_pant'      => null,
                'aco_lac_asp'       => null,
                'aco_lac_col'       => null,
                'aco_lac_olo'       => null,
                'aco_lac_sab'       => null,
                'aco_id_comp'       => null,
                'aco_cert'          => null,
                'aco_tipo'          => 3,
                'aco_mat_prim'      => $request['aco_mapri'],
                'aco_id_planta'     => $planta->id_planta
            ]);
            // return response()->json(['Mensaje' => 'Se registro correctamente']);
            return redirect('/AcopioMiel')->with('success','Registro creado satisfactoriamente');
        } else {
            // $this->validate(request(), [
            //     'peso_bruto' => 'required',
            //     'peso_tara' => 'required',
            //     'peso_neto' => 'required',
            //     'cantidad' => 'required',
            //     'total' => 'required',
            //     'humedad' => 'required',
            //     'costo' => 'required',
            //     'id_proveedor' => 'required',
            //     'aco_numaco' => 'required',
            //     'acta_entrega' => 'required',
            //     'fecha_acopio' => 'required',
            //     'fecha_resgistro' => 'required',
            //     // 'observacion' => 'required',
            //     // 'nro_recibo' => 'required',
            //     'responsable_recepcion' => 'required',
            //     'is_pago' => 'required',
            //     // 'fecha_recibo' => 'required',
            //     'destino' => 'required',
            //     'aco_mapri' => 'required',
            // ]);
            $prom = Propiedades_Miel::create([
                'prom_peso_bruto'           =>$request['peso_bruto'],
                'prom_peso_tara'            =>$request['peso_tara'],
                'prom_peso_neto'            =>$request['peso_neto'],
                'prom_cantidad_baldes'      =>$request['cantidad'],
                'prom_total'                =>$request['total'],
                'prom_cod_colmenas'         =>1,
                'prom_centrifugado'         =>1,
                'prom_peso_bruto_centrif'   =>1,
                'prom_peso_bruto_filt'      =>1,
                'prom_peso_bruto_imp'       =>1,
                'prom_humedad'              =>$request['humedad'],
                'prom_cos_un'               =>$request['costo'],
                'prom_baldesjson'           =>$baldes_json,    
            ]);
            $prom_id = $prom->prom_id;
            Acopio::create([
                'aco_id_prov'       => $request['id_proveedor'],//MIEL//
                'aco_id_proc'       => 1,//MIEL//
                'aco_centro'        => null,//MIEL//
                'aco_peso_neto'     => null,//          
                'aco_id_tipo_cas'   => 1,//MODIFICADO
                'aco_numaco'        => $aco_numaco,//
                'aco_num_act'       => $request['acta_entrega'],//MIEL//
                'aco_unidad'        => 1,//MODIFICADO
                'aco_cantidad'      => null,//MIEL//
                'aco_cos_un'        => null,//MIEL//
                'aco_cos_total'     => null,//MIEL//
                'aco_con_hig'       => null,//
                'aco_fecha_acop'    => $request['fecha_acopio'],//
                'aco_fecha_reg'     => $request['fecha_resgistro'],//MIEL//
                'aco_obs'           => $request['observacion'],//MIEL//
                'aco_estado'        => 'A',//MIEL//
                'aco_tram'          => "2018-10-01 16:00:00",//
                // 'aco_num_rec'       => $request['nro_recibo'],//MIEL//
                'aco_id_comunidad'  => null,//MODIFICADO
                'aco_id_recep'      => $aco_id_recep,//MIEL//
                'aco_id_linea'      => 3,//MIEL//
                'aco_pago'          => $request['is_pago'],//MIEL//
                // 'aco_fecha_rec'     => $request['fecha_recibo'],//MIEL//
                'aco_id_destino'    => $aco_id_destino,//MIEL//
                'aco_id_prom'       => $prom_id,//MIEL//
                'aco_id_usr'        => Auth::user()->usr_id,//MIEL//
                'aco_lac_tem'       => null,
                'aco_lac_aci'       => null,
                'aco_lac_ph'        => null,
                'aco_lac_sng'       => null,
                'aco_lac_den'       => null,
                'aco_lac_mgra'      => null,
                'aco_lac_palc'      => null,
                'aco_lac_pant'      => null,
                'aco_lac_asp'       => null,
                'aco_lac_col'       => null,
                'aco_lac_olo'       => null,
                'aco_lac_sab'       => null,
                'aco_id_comp'       => null,
                'aco_cert'          => null,
                'aco_tipo'          => 3,
                'aco_mat_prim'      => $request['aco_mapri'],
                'aco_id_planta'     => $planta->id_planta
            ]); 

            // return response()->json(['Mensaje' => 'Se registro correctamente']);
            return redirect('/AcopioMiel')->with('success','Registro creado satisfactoriamente');
        }
        
    }
    public function edit($id)
    {
        $acopio = Acopio::find($id);
        return response()->json($acopio->toArray());
    }

    public function update(Request $request, $id)
    {
        $acopio = Acopio::find($id);
        $acopio->fill($request->all());
        $acopio->save();
        return response()->json(['mensaje' => 'Se actualizo el acopio']);
    }
    public function show($id)
    {

    }
    public function destroy($id)
    {
        $acopio = Acopio::getDestroy($id);
        return response()->json(['mensaje' => 'Se elimino correctamente']);
    }

    public function convenio()
    {
        $acopio = Acopio::getListar();
        $unidad = DB::table('acopio.unidad')->OrderBy('uni_id', 'desc')->pluck('uni_nombre','uni_id');
        $destino = DB::table('acopio.destino')->OrderBy('des_id','desc')->pluck('des_descripcion', 'des_id');
        $proveedor = Proveedor::OrderBy('prov_id', 'desc')->pluck('prov_nombre', 'prov_id');
        $resp_recep = DB::table('acopio.resp_recep')->OrderBy('rec_id','desc')->pluck('rec_nombre','rec_id');
        return view('backend.administracion.acopio.acopio_miel.acopio.indexConvenio', compact('acopio', 'proveedor','unidad','destino','resp_recep'));
    }

    public function produccion()
    {
        $acopioprod = Acopio::getListarProd();
        $unidad = DB::table('acopio.unidad')->OrderBy('uni_id', 'desc')->pluck('uni_nombre','uni_id');
        $destino = DB::table('acopio.destino')->OrderBy('des_id','desc')->pluck('des_descripcion', 'des_id');
        $proveedor = Proveedor::OrderBy('prov_id', 'desc')->pluck('prov_nombre', 'prov_id');
        $resp_recep = DB::table('acopio.resp_recep')->OrderBy('rec_id','desc')->pluck('rec_nombre','rec_id');
        return view('backend.administracion.acopio.acopio_miel.acopio.indexProduccion', compact('acopioprod', 'proveedor','unidad','destino','resp_recep'));
    
    }

    // REPORTES FONDOS EN AVANCE

    public function reportes()
    {
        return view('backend.administracion.acopio.acopio_miel.reportes.index');
    }

    public function reporteFondos()
    {   
        setlocale(LC_TIME, 'es');
        $datebus = new Carbon();
        $dato= $datebus->formatLocalized('%B de %Y');
        $datoexacto = $datebus->format('y-m');
        $usuario = Usuario::join('public._bp_personas as persona','public._bp_usuarios.usr_prs_id','=','persona.prs_id')
            ->join('public._bp_planta as planta','public._bp_usuarios.usr_planta_id','=','planta.id_planta')->where('usr_id','=',Auth::user()->usr_id)->first();
     // dd($usuario);
        $acopios = Acopio::join('acopio.proveedor as p','acopio.acopio.aco_id_prov', '=', 'p.prov_id')
                         ->join('acopio.propiedades_miel as prom', 'acopio.acopio.aco_id_prom','=','prom.prom_id')
                         ->select('acopio.acopio.aco_id','p.prov_id','p.prov_nombre', 'p.prov_ap','p.prov_am', 'acopio.acopio.aco_num_act', 'acopio.acopio.aco_fecha_acop','prom.prom_total','prom.prom_peso_bruto','prom.prom_peso_neto','acopio.acopio.aco_mat_prim','acopio.acopio.aco_id_usr')
                        ->where('acopio.acopio.aco_id_linea','=',3 )
                        ->where('acopio.acopio.aco_estado', '=','A')
                        ->where('acopio.acopio.aco_tipo', '=' , 3)
                        ->where('acopio.acopio.aco_id_usr','=',Auth::user()->usr_id)
                        ->where('p.prov_id_tipo','=',10)
                        ->where('acopio.acopio.aco_mat_prim','=',1)
                        ->where('acopio.acopio.aco_fecha_acop','LIKE','%'.$datoexacto.'%')
                        ->OrderBy('acopio.acopio.aco_id', 'DESC')
                ->get();
        // dd($acopios);
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        // $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('EBA');
        $pdf->SetTitle('EBA');
        $pdf->SetSubject('ACOPIO MIEL');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, "EBA - ACOPIO", "REPORTE DE ACOPIO MIEL - FONDOS EN AVANCE");

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 9);

        // add a page
        $pdf->AddPage('L', 'Carta');

        $tituloTabla = 'ACOPIO MIEL - FONDOS EN AVANCE';
        // create some HTML content        

        

        $html = '<h3>'.$tituloTabla.' DEL MES DE '.strtoupper($dato).' - PLANTA '.$usuario->nombre_planta.'</h3>
                    <table border="1" cellspacing="0" cellpadding="1">
                        <tr>
                            <th align="center" bgcolor="#3498DB"><strong>#</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>PROVEEDOR</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>FECHA/HORA ACOPIO</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>NUMERO ACTA</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>PESO BRUTO</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>PESO NETO</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>TOTAL DE LA COMPRA</strong></th>
                            
                        </tr>';
                $num = 1;
                $totalbruto = 0;
                $totalneto = 0;
                $totalprecio = 0;
                foreach ($acopios as $acopio){
                    $totalbruto = $totalbruto+$acopio->prom_peso_bruto;
                    $totalneto = $totalneto+$acopio->prom_peso_neto;
                    $totalprecio = $totalprecio+$acopio->prom_total;
                    $html = $html.'<tr>
                            <td align="center">'.$num.'</td>
                            <td align="center">'.$acopio->prov_nombre.' '.$acopio->prov_ap.' '.$acopio->prov_am.'</td>
                            <td align="center">'.$acopio->aco_fecha_acop.'</td>
                            <td align="center">'.$acopio->aco_num_act.'</td>
                            <td align="center">'.number_format($acopio->prom_peso_bruto,2,'.',',').' Kg.</td>
                            <td align="center">'.number_format($acopio->prom_peso_neto,2,'.',',').' Kg.</td>
                            <td align="center">'.number_format($acopio->prom_total,2,'.',',').' Bs.</td>
                        </tr>';
                    $num= $num+1;
                }                       
                $html = $html.'<tr>
                        <td colspan="4" align="center" bgcolor="#3498DB">TOTALES</td>
                        <td align="center"><strong>'.number_format($totalbruto,2,'.',',').' Kg.</strong></td>
                        <td align="center"><strong>'.number_format($totalneto,2,'.',',').' Kg.</strong></td>
                        <td align="center"><strong>'.number_format($totalprecio,2,'.',',').' Bs.</strong></td>
                    </tr>';        
                             
                $html = $html.'</table>';
                $html=$html.'
                            <br>  <br>  <br>  <br> <br> <br>
                            <table>
                            <tr>
                                <td align="center">______________________</td>
                                <td align="center">______________________</td>
                                <td align="center">______________________</td>
                            </tr>
                            <tr>
                                <td align="center">Encargado de Acopio</td>
                                <td align="center">Responsable de Almacen</td>
                                <td align="center">Responsable de Planta</td>
                            </tr>
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                            </tr>
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                            </tr>
                            
                            
                            </table>

                            <br>  <br>  <br>  <br> <br> <br>
                            <table>
                            <tr>
                                <td align="center">______________________</td>
                                
                                <td align="center">______________________</td>
                            </tr>
                            <tr>
                                <td align="center">Responsable de Acopio Nacional</td>
                                
                                <td align="center">Acopiador</td>
                            </tr>
                            <tr>
                                <td align="center"></td>
                                
                                <td align="center">'.$usuario->prs_nombres.' '.$usuario->prs_paterno.' '.$usuario->prs_materno.'</td>
                            </tr>
                            
                            
                            </table>
                            
                        ';
        // output the HTML content
        $pdf->writeHTML($html, true, 0, true, 0);



        $pdf->AddPage();

        ////////////////////////////////////////////////////////////////////////////////////////////
        //                               REPORTES POR PROVEEDOR
        ////////////////////////////////////////////////////////////////////////////////////////////
        $proveedores = Proveedor::where('prov_id_linea','=',3)
                                ->where('prov_id_tipo','=',10)
                                ->where('prov_id_usr','=',Auth::user()->usr_id)->get();

        // dd($proveedores);

        $tituloTabla = 'ACOPIO MIEL -  POR PROVEEDOR - FONDOS EN AVANCE';
        // create some HTML content        
     

        $html = '<h3>'.$tituloTabla.' DEL MES DE '.strtoupper($dato).' - PLANTA '.$usuario->nombre_planta.'</h3>
                    <table border="1" cellspacing="0" cellpadding="1">
                        <tr>
                            <th align="center" bgcolor="#3498DB"><strong>#</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>PROVEEDOR</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>TOTAL NETO</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>TOTAL BRUTO</strong></th>
                            <th align="center" bgcolor="#3498DB"><strong>TOTAL COMPRA</strong></th>
                        </tr>';
                $numprov = 1;
                $totalprecioAco = 0;
                $totalbruto = 0;
                $totalneto = 0;
                foreach ($proveedores as $proveedor){
                    $html = $html.'<tr>
                            <td align="center">'.$numprov.'</td>
                            <td align="center">'.$proveedor->prov_nombre.' '.$proveedor->prov_ap.' '.$proveedor->prov_am.'</td>';
                            $totalprecioAco = $totalprecioAco + $total= $this->precioProvAco($proveedor->prov_id,$datoexacto);
                            $totalbruto = $totalbruto + $bruto = $this->brutoProvAco($proveedor->prov_id,$datoexacto);
                            $totalneto = $totalneto + $neto = $this->netoProvAco($proveedor->prov_id,$datoexacto);
                    $html= $html.'<td align="center">'.number_format($neto,2,'.',',').' Kg.</td>
                                <td align="center">'.number_format($bruto,2,'.',',').' Kg.</td>
                                  <td align="center">'.number_format($total,2,'.',',').' Bs.</td>  
                                                                   
                        </tr>';
                    $numprov= $numprov+1;
                }                       
                $html = $html.'<tr>
                        <td colspan="2" align="center" bgcolor="#3498DB">TOTALES</td>
                        <td align="center"><strong>'.number_format($totalneto,2,'.',',').' Kg.</strong></td>
                        <td align="center"><strong>'.number_format($totalbruto,2,'.',',').' Kg.</strong></td>
                        <td align="center"><strong>'.number_format($totalprecioAco,2,'.',',').' Bs.</strong></td>
                        
                    </tr>';        
                             
                $html = $html.'</table>';

                $html=$html.'
                            <br>  <br>  <br>  <br> <br> <br>
                            <table>
                            <tr>
                                <td align="center">______________________</td>
                                <td align="center">______________________</td>
                                <td align="center">______________________</td>
                            </tr>
                            <tr>
                                <td align="center">Encargado de Acopio</td>
                                <td align="center">Responsable de Almacen</td>
                                <td align="center">Responsable de Planta</td>
                            </tr>
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                            </tr>
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                            </tr>
                            
                            
                            </table>

                            <br>  <br>  <br>  <br> <br> <br>
                            <table>
                            <tr>
                                <td align="center">______________________</td>
                                
                                <td align="center">______________________</td>
                            </tr>
                            <tr>
                                <td align="center">Responsable de Acopio Nacional</td>
                                
                                <td align="center">Acopiador</td>
                            </tr>
                            <tr>
                                <td align="center"></td>
                                
                                <td align="center">'.$usuario->prs_nombres.' '.$usuario->prs_paterno.' '.$usuario->prs_materno.'</td>
                            </tr>
                            
                            
                            </table>
                            
                        ';
        // output the HTML content
        $pdf->writeHTML($html, true, 0, true, 0);

        // reset pointer to the last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('Acopio_Miel_Proveedor_Fondos_Avance.pdf', 'I');

    }

    public function precioProvAco($idprov, $fecha){
        $acopiosproveedor = Acopio::join('acopio.proveedor as p','acopio.acopio.aco_id_prov', '=', 'p.prov_id')
                                 ->join('acopio.propiedades_miel as prom', 'acopio.acopio.aco_id_prom','=','prom.prom_id')
                                 ->select('acopio.acopio.aco_id','p.prov_id','p.prov_nombre', 'p.prov_ap','p.prov_am', 'acopio.acopio.aco_num_act', 'acopio.acopio.aco_fecha_acop','prom.prom_total','prom.prom_peso_bruto','prom.prom_peso_neto','acopio.acopio.aco_mat_prim')
                                ->where('acopio.acopio.aco_id_linea','=',3 )
                                ->where('acopio.acopio.aco_estado', '=','A')
                                ->where('acopio.acopio.aco_tipo', '=' , 3)
                                ->where('p.prov_id','=',$idprov)
                                ->where('p.prov_id_tipo','=',10)
                                ->where('acopio.acopio.aco_mat_prim','=',1)
                                ->where('acopio.acopio.aco_fecha_acop','LIKE','%'.$fecha.'%')
                                ->where('acopio.acopio.aco_id_usr','=',Auth::user()->usr_id)
                                ->OrderBy('acopio.acopio.aco_id', 'DESC')->get();
        // dd($acopiosproveedor);
        $precioAco=0;
        foreach($acopiosproveedor as $acopioprov){
            $precioAco = $precioAco + $acopioprov->prom_total;
        }
        return $precioAco;
    }

    public function brutoProvAco($idprov, $fecha){
        $acopiosproveedor = Acopio::join('acopio.proveedor as p','acopio.acopio.aco_id_prov', '=', 'p.prov_id')
                                 ->join('acopio.propiedades_miel as prom', 'acopio.acopio.aco_id_prom','=','prom.prom_id')
                                 ->select('acopio.acopio.aco_id','p.prov_id','p.prov_nombre', 'p.prov_ap','p.prov_am', 'acopio.acopio.aco_num_act', 'acopio.acopio.aco_fecha_acop','prom.prom_total','prom.prom_peso_bruto','prom.prom_peso_neto','acopio.acopio.aco_mat_prim')
                                ->where('acopio.acopio.aco_id_linea','=',3 )
                                ->where('acopio.acopio.aco_estado', '=','A')
                                ->where('acopio.acopio.aco_tipo', '=' , 3)
                                ->where('p.prov_id','=',$idprov)
                                ->where('p.prov_id_tipo','=',10)
                                ->where('acopio.acopio.aco_mat_prim','=',1)
                                ->where('acopio.acopio.aco_fecha_acop','LIKE','%'.$fecha.'%')
                                ->where('acopio.acopio.aco_id_usr','=',Auth::user()->usr_id)
                                ->OrderBy('acopio.acopio.aco_id', 'DESC')->get();
        $brutoAco=0;
        foreach($acopiosproveedor as $acopioprov){
            $brutoAco = $brutoAco + $acopioprov->prom_peso_bruto;
        }
        return $brutoAco;
    }

    public function netoProvAco($idprov, $fecha){
        $acopiosproveedor = Acopio::join('acopio.proveedor as p','acopio.acopio.aco_id_prov', '=', 'p.prov_id')
                                 ->join('acopio.propiedades_miel as prom', 'acopio.acopio.aco_id_prom','=','prom.prom_id')
                                 ->select('acopio.acopio.aco_id','p.prov_id','p.prov_nombre', 'p.prov_ap','p.prov_am', 'acopio.acopio.aco_num_act', 'acopio.acopio.aco_fecha_acop','prom.prom_total','prom.prom_peso_bruto','prom.prom_peso_neto','acopio.acopio.aco_mat_prim')
                                ->where('acopio.acopio.aco_id_linea','=',3 )
                                ->where('acopio.acopio.aco_estado', '=','A')
                                ->where('acopio.acopio.aco_tipo', '=' , 3)
                                ->where('p.prov_id','=',$idprov)
                                ->where('p.prov_id_tipo','=',10)
                                ->where('acopio.acopio.aco_mat_prim','=',1)
                                ->where('acopio.acopio.aco_fecha_acop','LIKE','%'.$fecha.'%')
                                ->where('acopio.acopio.aco_id_usr','=',Auth::user()->usr_id)
                                ->OrderBy('acopio.acopio.aco_id', 'DESC')->get();
        $netoAco=0;
        foreach($acopiosproveedor as $acopioprov){
            $netoAco = $netoAco + $acopioprov->prom_peso_neto;
        }
        return $netoAco;
    }

    public function reporteFondosPlantas()
    {
        $usuario = Usuario::join('public._bp_personas as persona','public._bp_usuarios.usr_prs_id','=','persona.prs_id')->where('usr_id','=',Auth::user()->usr_id)->first();
        // dd($usuario);
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        // $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('EBA');
        $pdf->SetTitle('EBA');
        $pdf->SetSubject('ACOPIO MIEL');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData('logopeqe.png', PDF_HEADER_LOGO_WIDTH, "EBA - ACOPIO", "REPORTE DE ACOPIO MIEL - FONDOS EN AVANCE");

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        setlocale(LC_TIME, 'es');
        $datebus2 = new Carbon();
        $datoanio = $datebus2->format('y'); 
        $datoaniocom= $datebus2->formatLocalized('%Y');
        // set font
        $pdf->SetFont('helvetica', '', 9);

        // add a page
        $pdf->AddPage('L', 'Carta');

        $tituloTabla = 'ACOPIO MIEL - FONDOS EN AVANCE';
        // create some HTML content
        $html = '<h3 align="center">'.$tituloTabla.' - CAMPAÑA '.$datoaniocom.'</h3>
                <table border="1" cellspacing="0" cellpadding="1">
                <tr>
                    <th align="center" bgcolor="#3498DB"><strong>MESES DE ACOPIO</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>SAMUZABETY</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>SHINAHOTA</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>MONTEAGUDO</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>VILLAR</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>CAMARGO</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>IRUPANA</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>TOTAL ACOPIADO</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>COCHABAMBA</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>CHUQUISACA</strong></th>
                    <th align="center" bgcolor="#3498DB"><strong>LA PAZ</strong></th>
                </tr>
                <tr>
                    <td align="center">ENERO</td>
                    <td align="center">'.number_format($enero1=$this->totalAcoPlantas(1,$datoanio.'-01'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($enero2=$this->totalAcoPlantas(2,$datoanio.'-01'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($enero3=$this->totalAcoPlantas(3,$datoanio.'-01'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($enero4=$this->totalAcoPlantas(4,$datoanio.'-01'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($enero5=$this->totalAcoPlantas(5,$datoanio.'-01'),2,'.',',').' Kg</td>
                    <td align="center">'.number_format($enero6=$this->totalAcoPlantas(6,$datoanio.'-01'),2,'.',',').' Kg.</td>';
                    $totalplantasenero = $enero1+$enero2+$enero3+$enero4+$enero5+$enero6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasenero,2,'.',',').' Kg.</td>';
                    $cochabamba1 = $enero1+$enero2;
                    $html=$html.'<td align="center">'.number_format($cochabamba1,2,'.',',').' Kg.</td>';
                    $chuquisacaenero = $enero3+$enero4+$enero5;
                    $html=$html.'<td align="center">'.number_format($chuquisacaenero,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($enero6=$this->totalAcoPlantas(6,$datoanio.'-01'),2,'.',',').' Kg.</td>
                </tr>
                <tr>
                    <td align="center">FEBRERO</td>
                    <td align="center">'.number_format($febrero1=$this->totalAcoPlantas(1,$datoanio.'-02'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($febrero2=$this->totalAcoPlantas(2,$datoanio.'-02'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($febrero3=$this->totalAcoPlantas(3,$datoanio.'-02'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($febrero4=$this->totalAcoPlantas(4,$datoanio.'-02'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($febrero5=$this->totalAcoPlantas(5,$datoanio.'-02'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($febrero6=$this->totalAcoPlantas(6,$datoanio.'-02'),2,'.',',').' Kg.</td>';
                    $totalplantasfebrero = $febrero1+$febrero2+$febrero3+$febrero4+$febrero5+$febrero6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasfebrero,2,'.',',').' Kg.</td>';
                    $cochabambafeb = $febrero1+$febrero2;
                    $html=$html.'<td align="center">'.number_format($cochabambafeb,2,'.',',').' Kg.</td>';
                    $chuquisacafebrero = $febrero3+$febrero4+$febrero5;
                    $html=$html.'<td align="center">'.number_format($chuquisacafebrero,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($febrero6=$this->totalAcoPlantas(6,$datoanio.'-02'),2,'.',',').' Kg.</td>
                </tr>
                <tr>
                    <td align="center">MARZO</td>
                    <td align="center">'.number_format($marzo1=$this->totalAcoPlantas(1,$datoanio.'-03'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($marzo2=$this->totalAcoPlantas(2,$datoanio.'-03'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($marzo3=$this->totalAcoPlantas(3,$datoanio.'-03'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($marzo4=$this->totalAcoPlantas(4,$datoanio.'-03'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($marzo5=$this->totalAcoPlantas(5,$datoanio.'-03'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($marzo6=$this->totalAcoPlantas(6,$datoanio.'-03'),2,'.',',').' Kg.</td>';
                    $totalplantasmarzo = $marzo1+$marzo2+$marzo3+$marzo4+$marzo5+$marzo6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasmarzo,2,'.',',').' Kg.</td>';
                    $cochabambamar = $marzo1+$marzo2;
                    $html=$html.'<td align="center">'.number_format($cochabambamar,2,'.',',').' Kg.</td>';
                    $chuquisacamarzo = $marzo3+$marzo4+$marzo5;
                    $html=$html.'<td align="center">'.number_format($chuquisacamarzo,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($marzo6=$this->totalAcoPlantas(6,$datoanio.'-03'),2,'.',',').' Kg.</td>
                </tr>
                <tr>
                    <td align="center">ABRIL</td>
                    <td align="center">'.number_format($abril1=$this->totalAcoPlantas(1,$datoanio.'-04'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($abril2=$this->totalAcoPlantas(2,$datoanio.'-04'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($abril3=$this->totalAcoPlantas(3,$datoanio.'-04'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($abril4=$this->totalAcoPlantas(4,$datoanio.'-04'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($abril5=$this->totalAcoPlantas(5,$datoanio.'-04'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($abril6=$this->totalAcoPlantas(6,$datoanio.'-04'),2,'.',',').' Kg.</td>';
                    $totalplantasabril = $abril1+$abril2+$abril3+$abril4+$abril5+$abril6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasabril,2,'.',',').' Kg.</td>';
                    $cochabambaabril = $abril1+$abril2;
                    $html=$html.'<td align="center">'.number_format($cochabambaabril,2,'.',',').' Kg.</td>';
                    $chuquisacaabril = $abril3+$abril4+$abril5;
                    $html=$html.'<td align="center">'.number_format($chuquisacaabril,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($abril6=$this->totalAcoPlantas(6,$datoanio.'-04'),2,'.',',').' Kg.</td>
                </tr>
                <tr>
                    <td align="center">MAYO</td>
                    <td align="center">'.number_format($mayo1=$this->totalAcoPlantas(1,$datoanio.'-05'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($mayo2=$this->totalAcoPlantas(2,$datoanio.'-05'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($mayo3=$this->totalAcoPlantas(3,$datoanio.'-05'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($mayo4=$this->totalAcoPlantas(4,$datoanio.'-05'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($mayo5=$this->totalAcoPlantas(5,$datoanio.'-05'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($mayo6=$this->totalAcoPlantas(6,$datoanio.'-05'),2,'.',',').' Kg.</td>';
                    $totalplantasmayo = $mayo1+$mayo2+$mayo3+$mayo4+$mayo5+$mayo6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasmayo,2,'.',',').' Kg.</td>';
                    $cochabambamayo = $mayo1+$mayo2;
                    $html=$html.'<td align="center">'.number_format($cochabambamayo,2,'.',',').' Kg.</td>';
                    $chuquisacamayo = $mayo3+$mayo4+$mayo5;
                    $html=$html.'<td align="center">'.number_format($chuquisacamayo,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($mayo6=$this->totalAcoPlantas(6,$datoanio.'-05'),2,'.',',').' Kg.</td>
                </tr>  
                <tr>
                    <td align="center">JUNIO</td>
                    <td align="center">'.number_format($junio1=$this->totalAcoPlantas(1,$datoanio.'-06'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($junio2=$this->totalAcoPlantas(2,$datoanio.'-06'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($junio3=$this->totalAcoPlantas(3,$datoanio.'-06'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($junio4=$this->totalAcoPlantas(4,$datoanio.'-06'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($junio5=$this->totalAcoPlantas(5,$datoanio.'-06'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($junio6=$this->totalAcoPlantas(6,$datoanio.'-06'),2,'.',',').' Kg.</td>';
                    $totalplantasjunio = $junio1+$junio2+$junio3+$junio4+$junio5+$junio6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasjunio,2,'.',',').' Kg.</td>';
                    $cochabambajunio = $junio1+$junio2;
                    $html=$html.'<td align="center">'.number_format($cochabambajunio,2,'.',',').' Kg.</td>';
                    $chuquisacajunio = $junio3+$junio4+$junio5;
                    $html=$html.'<td align="center">'.number_format($chuquisacajunio,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($junio6=$this->totalAcoPlantas(6,$datoanio.'-06'),2,'.',',').' Kg.</td>
                </tr>  
                <tr>
                    <td align="center">JULIO</td>
                    <td align="center">'.number_format($julio1=$this->totalAcoPlantas(1,$datoanio.'-07'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($julio2=$this->totalAcoPlantas(2,$datoanio.'-07'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($julio3=$this->totalAcoPlantas(3,$datoanio.'-07'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($julio4=$this->totalAcoPlantas(4,$datoanio.'-07'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($julio5=$this->totalAcoPlantas(5,$datoanio.'-07'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($julio6=$this->totalAcoPlantas(6,$datoanio.'-07'),2,'.',',').' Kg.</td>';
                    $totalplantasjulio = $julio1+$julio2+$julio3+$julio4+$julio5+$julio6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasjulio,2,'.',',').' Kg.</td>';
                    $cochabambajulio = $julio1+$julio2;
                    $html=$html.'<td align="center">'.number_format($cochabambajulio,2,'.',',').' Kg.</td>';
                    $chuquisacajulio = $julio3+$julio4+$julio5;
                    $html=$html.'<td align="center">'.number_format($chuquisacajulio,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($julio6=$this->totalAcoPlantas(6,$datoanio.'-07'),2,'.',',').' Kg.</td>
                </tr>
                <tr>
                    <td align="center">AGOSTO</td>
                    <td align="center">'.number_format($agosto1=$this->totalAcoPlantas(1,$datoanio.'-08'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($agosto2=$this->totalAcoPlantas(2,$datoanio.'-08'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($agosto3=$this->totalAcoPlantas(3,$datoanio.'-08'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($agosto4=$this->totalAcoPlantas(4,$datoanio.'-08'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($agosto5=$this->totalAcoPlantas(5,$datoanio.'-08'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($agosto6=$this->totalAcoPlantas(6,$datoanio.'-08'),2,'.',',').' Kg.</td>';
                    $totalplantasagosto = $agosto1+$agosto2+$agosto3+$agosto4+$agosto5+$agosto6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasagosto,2,'.',',').' Kg.</td>';
                    $cochabambaagosto = $agosto1+$agosto2;
                    $html=$html.'<td align="center">'.number_format($cochabambaagosto,2,'.',',').' Kg.</td>';
                    $chuquisacaagosto = $agosto3+$agosto4+$agosto5;
                    $html=$html.'<td align="center">'.number_format($chuquisacaagosto,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($agosto6=$this->totalAcoPlantas(6,$datoanio.'-08'),2,'.',',').' Kg.</td>
                </tr>
                <tr>
                    <td align="center">SEPTIEMBRE</td>
                    <td align="center">'.number_format($septiembre1=$this->totalAcoPlantas(1,$datoanio.'-09'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($septiembre2=$this->totalAcoPlantas(2,$datoanio.'-09'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($septiembre3=$this->totalAcoPlantas(3,$datoanio.'-09'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($septiembre4=$this->totalAcoPlantas(4,$datoanio.'-09'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($septiembre5=$this->totalAcoPlantas(5,$datoanio.'-09'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($septiembre6=$this->totalAcoPlantas(6,$datoanio.'-09'),2,'.',',').' Kg.</td>';
                    $totalplantasseptiembre = $septiembre1+$septiembre2+$septiembre3+$septiembre4+$septiembre5+$septiembre6;
                    $html=$html.'
                    <td align="center">'.number_format($totalplantasseptiembre,2,'.',',').' Kg.</td>';
                    $cochabambaseptiembre = $septiembre1+$septiembre2;
                    $html=$html.'<td align="center">'.number_format($cochabambaseptiembre,2,'.',',').' Kg.</td>';
                    $chuquisacaseptiembre = $septiembre3+$septiembre4+$septiembre5;
                    $html=$html.'<td align="center">'.number_format($chuquisacaseptiembre,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($septiembre6=$this->totalAcoPlantas(6,$datoanio.'-09'),2,'.',',').' Kg.</td>
                </tr>  
                <tr>
                    <td align="center">OCTUBRE</td>
                    <td align="center">'.number_format($octubre1=$this->totalAcoPlantas(1,$datoanio.'-10'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($octubre2=$this->totalAcoPlantas(2,$datoanio.'-10'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($octubre3=$this->totalAcoPlantas(3,$datoanio.'-10'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($octubre4=$this->totalAcoPlantas(4,$datoanio.'-10'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($octubre5=$this->totalAcoPlantas(5,$datoanio.'-10'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($octubre6=$this->totalAcoPlantas(6,$datoanio.'-10'),2,'.',',').' Kg.</td>';
                    $totalplantasoctubre = $octubre1+$octubre2+$octubre3+$octubre4+$octubre5+$octubre6;
                    $html=$html.
                    '<td align="center">'.number_format($totalplantasoctubre,2,'.',',').' Kg.</td>';
                    $cochabambaoctubre = $octubre1+$octubre2;
                    $html=$html.'<td align="center">'.number_format($cochabambaoctubre,2,'.',',').' Kg.</td>';
                    $chuquisacaoctubre = $octubre3+$octubre4+$octubre5;
                    $html=$html.'<td align="center">'.number_format($chuquisacaoctubre,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($octubre6=$this->totalAcoPlantas(6,$datoanio.'-10'),2,'.',',').' Kg.</td>
                </tr>  
                <tr>
                    <td align="center">NOVIEMBRE</td>
                    <td align="center">'.number_format($noviembre1=$this->totalAcoPlantas(1,$datoanio.'-11'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($noviembre2=$this->totalAcoPlantas(2,$datoanio.'-11'),2,'.',',').' Kg</td>
                    <td align="center">'.number_format($noviembre3=$this->totalAcoPlantas(3,$datoanio.'-11'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($noviembre4=$this->totalAcoPlantas(4,$datoanio.'-11'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($noviembre5=$this->totalAcoPlantas(5,$datoanio.'-11'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($noviembre6=$this->totalAcoPlantas(6,$datoanio.'-11'),2,'.',',').' Kg.</td>';
                    $totalplantasnoviembre = $noviembre1+$noviembre2+$noviembre3+$noviembre4+$noviembre5+$noviembre6;
                    $html=$html.
                    '<td align="center">'.number_format($totalplantasnoviembre,2,'.',',').' Kg.</td>';
                    $cochabambanoviembre = $noviembre1+$noviembre2;
                    $html=$html.'<td align="center">'.number_format($cochabambanoviembre,2,'.',',').' Kg.</td>';
                    $chuquisacanoviembre = $noviembre3+$noviembre4+$noviembre5;
                    $html=$html.'<td align="center">'.number_format($chuquisacanoviembre,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($noviembre6=$this->totalAcoPlantas(6,$datoanio.'-11'),2,'.',',').' Kg.</td>
                </tr>
                <tr>
                    <td align="center">DICIEMBRE</td>
                    <td align="center">'.number_format($diciembre1=$this->totalAcoPlantas(1,$datoanio.'-12'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($diciembre2=$this->totalAcoPlantas(2,$datoanio.'-12'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($diciembre3=$this->totalAcoPlantas(3,$datoanio.'-12'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($diciembre4=$this->totalAcoPlantas(4,$datoanio.'-12'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($diciembre5=$this->totalAcoPlantas(5,$datoanio.'-12'),2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($diciembre6=$this->totalAcoPlantas(6,$datoanio.'-12'),2,'.',',').' Kg.</td>';
                    $totalplantasdiciembre = $diciembre1+$diciembre2+$diciembre3+$diciembre4+$diciembre5+$diciembre6;
                    $html=$html.
                    '<td align="center">'.number_format($totalplantasdiciembre,2,'.',',').' Kg.</td>';
                    $cochabambadiciembre = $diciembre1+$diciembre2;
                    $html=$html.'<td align="center">'.number_format($cochabambadiciembre,2,'.',',').' Kg.</td>';
                    $chuquisacadiciembre = $diciembre3+$diciembre4+$diciembre5;
                    $html=$html.'<td align="center">'.number_format($chuquisacadiciembre,2,'.',',').' Kg.</td>
                    <td align="center">'.number_format($diciembre6=$this->totalAcoPlantas(6,$datoanio.'-12'),2,'.',',').' Kg.</td>
                </tr>

                <tr>
                    <td align="center"><strong>TOTAL (Kg.)</strong></td>';
                    $totalsamuzabety = $enero1+$febrero1+$marzo1+$abril1+$mayo1+$junio1+$julio1+$agosto1+$septiembre1+$octubre1+$noviembre1+$diciembre1;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalsamuzabety,2,'.',',').' Kg.</strong></td>';
                    $totalshinahota = $enero2+$febrero2+$marzo2+$abril2+$mayo2+$junio2+$julio2+$agosto2+$septiembre2+$octubre2+$noviembre2+$diciembre2;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalshinahota,2,'.',',').' Kg.</strong></td>';
                    $totalmoonteagudo = $enero3+$febrero3+$marzo3+$abril3+$mayo3+$junio3+$julio3+$agosto3+$septiembre3+$octubre3+$noviembre3+$diciembre3;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalmoonteagudo,2,'.',',').' Kg.</strong></td>';
                    $totalvillar = $enero4+$febrero4+$marzo4+$abril4+$mayo4+$junio4+$julio4+$agosto4+$septiembre4+$octubre4+$noviembre4+$diciembre4;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalvillar,2,'.',',').' Kg.</strong></td>';
                    $totalcamargo = $enero5+$febrero5+$marzo5+$abril5+$mayo5+$junio5+$julio5+$agosto5+$septiembre5+$octubre5+$noviembre5+$diciembre5;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalcamargo,2,'.',',').' Kg.</strong></td>';
                    $totalirupana = $enero6+$febrero6+$marzo6+$abril6+$mayo6+$junio6+$julio6+$agosto6+$septiembre6+$octubre6+$noviembre6+$diciembre6;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalirupana,2,'.',',').' Kg.</strong></td>';
                    $totalacopiado = $totalplantasenero+$totalplantasfebrero+$totalplantasmarzo+$totalplantasabril+$totalplantasmayo+$totalplantasjunio+$totalplantasjulio+$totalplantasagosto+$totalplantasseptiembre+$totalplantasoctubre+$totalplantasnoviembre+$totalplantasdiciembre;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalacopiado,2,'.',',').' Kg.</strong></td>';
                    $totalacopiadocochabamba = $cochabamba1+$cochabambafeb+$cochabambamar+$cochabambaabril+$cochabambamayo+$cochabambajunio+$cochabambajulio+$cochabambaagosto+$cochabambaseptiembre+$cochabambaoctubre+$cochabambanoviembre+$cochabambadiciembre;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalacopiadocochabamba,2,'.',',').' Kg.</strong></td>';
                    $totalacopiadochuquisaca = $chuquisacaenero+$chuquisacafebrero+$chuquisacamarzo+$chuquisacaabril+$chuquisacamayo+$chuquisacajunio+$chuquisacajulio+$chuquisacaagosto+$chuquisacaseptiembre+$chuquisacaoctubre+$chuquisacanoviembre+$chuquisacadiciembre;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalacopiadochuquisaca,2,'.',',').' Kg.</strong></td>';
                    $totalacopiadolapaz = $enero6+$febrero6+$marzo6+$abril6+$mayo6+$junio6+$julio6+$agosto6+$septiembre6+$octubre6+$noviembre6+$diciembre6;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totalacopiadolapaz,2,'.',',').' Kg.</strong></td>
                </tr>
                <tr>
                    <td align="center"><strong>TOTAL (Tn.)</strong></td>';
                    $totaltnsamuzabety = $totalsamuzabety/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnsamuzabety,2,'.',',').' Tn.</strong></td>';
                    $totaltnshinahota = $totalshinahota/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnshinahota,2,'.',',').' Tn.</strong></td>';
                    $totaltnmonteagudo = $totalmoonteagudo/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnmonteagudo,2,'.',',').' Tn.</strong></td>';
                    $totaltnvillar = $totalvillar/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnvillar,2,'.',',').' Tn.</strong></td>';
                    $totaltncamargo = $totalcamargo/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltncamargo,2,'.',',').' Tn.</strong></td>';
                    $totaltnirupana = $totalirupana/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnirupana,2,'.',',').' Tn.</strong></td>';
                    $totaltnacopiado = $totalacopiado/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnacopiado,2,'.',',').' Tn.</strong></td>';
                    $totaltnacopiadocochabamba = $totalacopiadocochabamba/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnacopiadocochabamba,2,'.',',').' Tn.</strong></td>';
                    $totaltnacopiadochuquisaca = $totalacopiadochuquisaca/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnacopiadochuquisaca,2,'.',',').' Tn.</strong></td>';
                    $totaltnacopiadolapaz = $totalacopiadolapaz/1000;
                    $html=$html.'
                    <td align="center"><strong>'.number_format($totaltnacopiadolapaz,2,'.',',').' Tn.</strong></td>
                </tr>                           
            </table>';

                $html=$html.'
                            

                            <br>  <br>  <br>  <br> <br> <br> <br> <br>
                            <table>
                            <tr>
                                <td align="center">___________________________</td>
                            </tr>
                            <tr>
                                <td align="center">Responsable de Acopio Nacional</td>
                            </tr>
                            <tr>
                                
                                
                                <td align="center">'.$usuario->prs_nombres.' '.$usuario->prs_paterno.' '.$usuario->prs_materno.'</td>
                            </tr>
                            
                            
                            </table>
                            
                        ';
        // output the HTML content
        $pdf->writeHTML($html, true, 0, true, 0);

        // reset pointer to the last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('Acopio_Miel_Fondos_Avance.pdf', 'I');

    }

    public function totalAcoPlantas($iddestino, $fecha){
        $acopiostotal = Acopio::join('acopio.destino as d','acopio.acopio.aco_id_destino', '=', 'd.des_id')
                                 ->join('acopio.propiedades_miel as prom', 'acopio.acopio.aco_id_prom','=','prom.prom_id')
                                 ->select('acopio.acopio.aco_id','acopio.aco_fecha_acop','prom.prom_total','prom.prom_peso_bruto','prom.prom_peso_neto','acopio.acopio.aco_mat_prim')
                                ->where('acopio.acopio.aco_id_linea','=',3 )
                                ->where('acopio.acopio.aco_estado', '=','A')
                                ->where('acopio.acopio.aco_tipo', '=' , 3)
                                ->where('d.des_id','=',$iddestino)
                                ->where('acopio.acopio.aco_mat_prim','=',1)
                                ->where('acopio.acopio.aco_fecha_acop','LIKE','%'.$fecha.'%')
                                ->OrderBy('acopio.acopio.aco_id', 'DESC')->get();
        $brutoAco=0;
        foreach($acopiostotal as $acopio){
            $brutoAco = $brutoAco + $acopio->prom_peso_bruto;
        }
        return $brutoAco;
    }

}
