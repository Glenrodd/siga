<div class="modal fade modal-primary" data-backdrop="static" data-keyboard="false" id="myCreateMercado" tabindex="-5">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #202040">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    <span style="color: white">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    Registro Mercado
                </h4>
            </div>
            <div class="modal-body">
                <div class="caption">
                        {!! Form::open(['id'=>'proveedor'])!!}
                        <input id="token" name="csrf-token" type="hidden" value="{{ csrf_token() }}">
                            <input id="id_proveedor1" name="id_proveedor" type="hidden" value="">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label>
                                                    Nombre Mercado:
                                                </label>
                                                <span class="block input-icon input-icon-right">
                                                    {!! Form::text('nombreUni', null, array('placeholder' => 'Nombre Mercado','maxlength'=>'250','class' => 'form-control','id'=>'mercado','style'=>'text-transform:uppercase;', 'onkeyup'=>'javascript:this.value=this.value.toUpperCase();')) !!}
                                                </span>  
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </input>
                        </input>
                </div>
            </div>
            <div class="modal-footer">
                 <button class="btn btn-danger" data-dismiss="modal" type="button">
                    Cerrar
                </button>
                {!!link_to('#',$title='Registrar', $attributes=['id'=>'registroMercado','class'=>'btn btn-success','style'=>''], $secure=null)!!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
