@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{asset('css/home.css')}}">
@endpush


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    RSS finder
                    <!-- Button trigger modal -->
                    <button type="button" class="ms-3 btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Agregar RSS
                    </button>

                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>{{ session('status') }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div id="buscador">
                        <form action="{{route('home.buscar')}}" method="get" id="buscadorForm">
                            <div class="my-3 position-relative">
                                <input type="text" name="buscar" class="form-control" placeholder="Busqueda">
                                <i class="fa fa-search lupaBuscar submitClick" aria-hidden="true"></i>
                            </div>
                            <div class="w-100 d-flex flex-wrap">

                                @if(!Route::is('home.canales'))
                                <a href="{{route('home.canales')}}" class="ms-2 btn btn-outline-secondary">Mostrar canales</a>
                                @endif

                                @if(!Route::is('index') )
                                    <a href="{{route('index')}}" class="ms-2 btn btn-outline-success">Mostrar entradas</a>
                                @endif

                                @if(!Route::is('home.categorias') )
                                    <a href="{{route('home.categorias')}}" class="ms-2 btn btn-outline-info btn_categorias">Mostrar categorias</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @yield('resultados')


        <div class="col-12 text-center mt-5">
            {{$resultados->links()}}
        </div>

    </div>

    <!-- Modal agregar rss -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar RSS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="agregarRss">
                        <div class="my-3 position-relative">
                            <input type="text" id="nuevo_rss" class="form-control" placeholder="URL de RSS" autocomplete="off">
                            <div class="w-100" id="validacionRss"></div>
                            <button class="btn mt-2 btn-success w-100" id="agregarRssBoton">Agregar</button>
                        </div>
                    </div>
                    <div class="w-100" id="resultadoAgregarRss"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')

    @yield('children_scripts_results_base')

    <script>
        window.onload = function(){
            /*Accion que borra items*/
            $(".bote_basura").click(function(){
                var id = this.id;
                var tipo_elemento = this.getAttribute('tipo_elemento');


                if(!confirm("Seguro que deseas eliminar este "+ tipo_elemento)){
                    return;
                }

                $.ajax({
                    dataType: 'json',
                    type: 'post',
                    async: false,
                    headers: {'X-CSRF-TOKEN': $("[name='csrf-token']").attr('content')},
                    url: "{{route('home.eliminar_elemento')}}/"+id+"/"+tipo_elemento,
                    data: { id: id, tipo_elemento: tipo_elemento },
                }).done(function(data){
                    console.log("data", data);
                    if(data.status === 'ok' && data.message === ''){
                        alert(tipo_elemento+" eliminad@ con éxito")
                        location.reload()
                    }else{
                        alert(data.message);
                    }
                })
            })




            /*Cuando carga el ajax el boton se muestra y se guardara los campos que se desea mostrar*/
            function accionBotonGuardarMostrarCampos(){
                $("#guardarCamposRssResultantes").click(function(){
                    var marcados = [];
                    var hay_para_seleccionar = $("input[name=campoMostrarCheck]").length;

                    $("input[name=campoMostrarCheck]:checked").each(function(index, item){
                            marcados.push(item.value);
                    });

                    if(marcados.length == 0 && hay_para_seleccionar){
                        alert("Favor de seleccionar por lo menos un campo");
                        return;
                    }

                    $.ajax({
                        dataType: 'json',
                        type: 'post',
                        async: false,
                        headers: {'X-CSRF-TOKEN': $("[name='csrf-token']").attr('content')},
                        url: "{{route('home.guardar_mostrar_campos')}}",
                        data: { marcados: marcados, channel_id: $("#channel_id").val() },
                        beforeSend: function() {

                            $("#resultadoAgregarRss").html('' +
                                '<div id="spinnerCargando" class="text-center">'+
                                    '<div class="spinner-border" role="status">'+
                                        '<span class="visually-hidden">Cargando...</span>'+
                                    '</div>'+
                                    '<br><span id="mensajeGuardandoFin">Guardando cambios, favor de esperar</span>'+
                                '</div>'+
                                '');
                        }
                    }).done(function(data){
                        //console.log("data", data);
                        if(data.status == 'ok'){
                            setTimeout(function(){
                                $("#mensajeGuardandoFin").html("Finalizando...");
                                setTimeout(function(){
                                    $('#spinnerCargando')[0].remove();

                                    //$("#resultadoAgregarRss").html('<h1 class="text-center">Listo, favor de recargar la página</h1>');
                                    $("#resultadoAgregarRss").html('<p class="text-center">Entrada agregada</p>' +
                                        '<a href="{{route('home.canales')}}/'+data.data.channel_id+'">Ver entradas agregadas</a>');
                                }, 2000)
                            }, 3000);
                        }
                    })
                })
            }

            /*Si dan clic a un elemento que tenga esta clase se lanza el form*/
            $('.submitClick').each(function(){
                $(this).click(function(){
                    $('#buscadorForm').submit();
                });
            })

            /*Cuando se trata de cargar un rss */
            $("#agregarRssBoton").click(function(){
                var url = $("#nuevo_rss").val()

                if(url == ''){
                    $("#validacionRss").html("");
                    $("#validacionRss").html("<small style='color: red;' class='mt-2'>Campo requerido</small>");
                    return;
                }

                if(!isValidUrl(url)){
                    $("#validacionRss").html("");
                    $("#validacionRss").html("<small style='color: red;' class='mt-2'>Url invalida</small>");
                    return
                }

                $("#nuevo_rss")[0].setAttribute('disabled', '');
                $("#agregarRssBoton")[0].setAttribute('disabled', '');

                $.ajax({
                    dataType: 'json',
                    type: 'post',
                    async: false,
                    headers: {'X-CSRF-TOKEN': $("[name='csrf-token']").attr('content')},
                    url: "{{route('home.agregar_rss')}}",
                    data: { rss_url: url },
                    beforeSend: function() {

                        $("#validacionRss").html("");
                        $("#resultadoAgregarRss").html('' +
                            '<div id="spinnerCargando" class="text-center">'+
                                '<div class="spinner-border" role="status">'+
                                    '<span class="visually-hidden">Cargando...</span>'+
                                '</div>'+
                                    '<br><span>Validando RSS</span>'+
                            '</div>'+
                        '');
                    }
                }).done(function(data){
                    console.log("data", data);
                    /*data.data.fields_show puede devolver:
                    * url_error
                    * file_error
                    * no_content_valid
                    * ok
                    * */
                    if(data.status == 'ok'){
                        /*Esperamos para ver el efecto de cargando*/
                        setTimeout(function(){
                            $('#spinnerCargando')[0].remove()

                            var camposMostrar = [];

                            if(typeof data.data.fields_show != "string"){
                                camposMostrar.push("<h5 class='mb-2'>Selecciona campos a mostrar</h5>");
                                camposMostrar.push("<input type='hidden' id='channel_id' value='"+data.data.channel_id+"'>");
                                camposMostrar.push("<input type='hidden' id='item_id' value='"+data.data.item_id+"'>");

                                data.data.fields_show.forEach(function(field, indiceField){
                                    camposMostrar.push(''+
                                        '<div class="form-check">'+
                                                '<input class="form-check-input" name="campoMostrarCheck" type="checkbox" value="'+field+'" id="campo'+indiceField+'">'+
                                                '<label class="form-check-label" for="campo'+indiceField+'">'+
                                                field+
                                            '</label>'+
                                        '</div>');
                                });

                                camposMostrar.push('<button class="btn mt-2 btn-primary w-100" id="guardarCamposRssResultantes">Guardar</button>');

                            }else if(typeof data.data.fields_show == "string"){
                                if(data.data.fields_show === 'not_more_fields'){
                                    camposMostrar.push('<button class="btn mt-2 btn-primary w-100" id="guardarCamposRssResultantes">Guardar</button>');
                                }
                                else if(data.data.fields_show === "url_error"){
                                    camposMostrar.push("<h5 class='mb-2'>Url no válida</h5>");
                                }
                                else if(data.data.fields_show === "no_content_valid"){
                                    camposMostrar.push("<h5 class='mb-2'>Documento RSS con estructura no valida.</h5>");
                                }
                                else if(data.data.fields_show === "file_error"){
                                    camposMostrar.push("<h5 class='mb-2'>Error al guardar RSS, permisos insuficientes en servidor rw_files.</h5>");
                                }
                                else if(data.data.fields_show === "no_valid_items"){
                                    camposMostrar.push("<h5 class='mb-2'>RSS no posee items en su interior.</h5>");
                                }

                            }

                            $("#resultadoAgregarRss")[0].innerHTML += camposMostrar.join('');

                            accionBotonGuardarMostrarCampos();

                        }, 1000);
                    }
                })
            })
        }
    </script>
@endpush
