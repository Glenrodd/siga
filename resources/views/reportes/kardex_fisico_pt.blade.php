@extends('layouts.print')

@section('content')

<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white ">Articulo:</td>
        <td colspan="3" class="text-xs uppercase">
        @if($producto->sab_id == 1)
            {{$producto->rece_nombre.' '.$producto->rece_presentacion}}
        @else
            {{$producto->rece_nombre.' '.$producto->sab_nombre.' '.$producto->rece_presentacion}}
        @endif
        </td>
    </tr>
    <tr>
        <td  class="text-center bg-grey-darker text-xs text-white">Unidad de Medida:</td>
        <td colspan="3" class="text-xs uppercase">{{$producto->umed_nombre}}</td>
    </tr>

</table>
<br>
   

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs">
                Fecha
            </td>
            <td class="px-15 py text-center text-xxs">
                Detalle
            </td>

            <td class="px-15 py text-center text-xxs">
                Entrada
            </td>
            <td class="px-15 py text-center text-xxs">
                Salida
            </td>
            <td class="px-15 py text-center text-xxs">
                Saldo
            </td>
        </tr>
    </thead>
    <tbody>
         @php
            $count=1;    
         @endphp
        @foreach ($tabkarde as $ig)
            <tr class="text-sm">
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $count++ }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{Carbon\Carbon::parse($ig->pht_registrado, 'UTC')->format('d-m-Y')}}</td>
                @if($ig->pth_tipo == 1)
                    <td class="text-center text-xxxs font-bold px-5 py-3">{{ 'ENTRADA' }}</td>
                @else
                    <td class="text-center text-xxxs font-bold px-5 py-3">{{ 'SALIDA' }}</td>
                @endif
                @if($ig->pth_ipt_id != null and $ig->pth_tipo == 1)
                    <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $ig->pth_cantidad  }}</td>
                @else
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">-</td>
                @endif

                @if($ig->pth_dao_id != null)
                    <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $ig->pth_cantidad  }}</td>
                @else
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">-</td>
                @endif

                @if($ig->pth_tipo == 1)
                    <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $ig->pth_cantidad }}</td>
                @else
                    @php
                        $detalle_ingreso = $detallesIngresos->where('ipt_id',$ig->pth_ipt_id)->first();
                        $detalle_orp = DB::table('producto_terminado.despacho_almacen_orp')->where('dao_id', $ig->pth_dao_id)->first();
                        //dd($detalle_ingreso);
                        if ($detalle_orp->dao_cantidad > $detalle_ingreso->ipt_cantidad) 
                        {
                            $det_nro = 0;
                        } else {
                            $detalle_ingreso->ipt_cantidad = $detalle_ingreso->ipt_cantidad - $ig->pth_cantidad;
                            $det_nro = $detalle_ingreso->ipt_cantidad;
                        }
                    @endphp
                    <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ number_format($det_nro, 2, '.', ',')}}</td>             
                @endif
            </tr>
        @endforeach
        {{-- @foreach ($stocks as $stock)
        <tr class="text-sm bg-grey-darker  text-white">
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $count++ }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{Carbon\Carbon::parse($stock->created_at, 'UTC')->format('d-m-Y')}}</td>
                <td class="text-center text-xxxs font-bold px-5 py-3">{{ 'Entrada (NIº'.$stock->article_income_item->article_income->correlative.')' }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->quantity }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->cost }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->quantity * $stock->cost}}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">-</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">-</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">-</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->quantity }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->cost }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->quantity * $stock->cost}}</td>
        </tr>

        @endforeach --}}
    </tbody>

    </table>
<br>

<br>
<br>
<br>
<br>
<br>
<table>
    <tr>
        <td class="text-center text-xxs">Revisado por firma: ............................................</td>
        <td class="text-center text-xxs">Verificado por firma: ............................................</td>
    </tr>
    {{-- <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr> --}}
    <tr>
        <td class="text-center text-xxs">Nombre: ......................................................</td>
        <td class="text-center text-xxs">Nombre: ......................................................</td>
    </tr>

</table> 

@endsection
