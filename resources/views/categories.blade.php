@extends('complements.results_base')

@section('resultados')
        <div class="col-12 mt-5" id="resultados">
            <div class="row">
                <div class="col-12">

                    <h3>
                        <i class="fa fa-plus-circle" aria-hidden="true" style="cursor: pointer; font-size: 30px; color: #2d3748;" data-bs-toggle="modal" data-bs-target="#modalAddCategory"></i>
                        {{$titulo}}
                    </h3>

                </div>
                @forelse($resultados as $resultado)
                    @includeIf('complements.category')
                @empty

                @endforelse
            </div>
        </div>

        <!-- Modal agregar categoria -->
        <div class="modal fade" id="modalAddCategory" tabindex="-1" aria-labelledby="modalAddCategoryLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAddCategoryLabel">Agregar categoria</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="{{route('home.guardarCategoria')}}">
                            @csrf
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" id="nombreCategoria" placeholder="Nombre de categoria" autocomplete="off" required>
                                <div id="existenciaNombreCategoria" class="mt-2"></div>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="description" class="form-control" placeholder="Descripción de categoria" required>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" id="botGuardarCategoria" class="btn btn-dark w-50" disabled>Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('children_scripts_results_base')
    <script>
        $(function(){
            $("#nombreCategoria").keyup(function(){
                var nombre_posible_categoria = this.value;

                if(nombre_posible_categoria.length == 0){
                    $("#existenciaNombreCategoria").html("");
                    return;
                }
                $.ajax({
                    dataType: 'json',
                    type: 'post',
                    async: false,
                    headers: {'X-CSRF-TOKEN': $("[name='csrf-token']").attr('content')},
                    url: "{{route('home.verifica_nombre_categoria')}}",
                    data: { nombre: nombre_posible_categoria },
                }).done(function(data){

                    if(data.status === 'ok'){
                        /* Data puede tener:
                            * coincidencia
                            * exacta
                            * existe*/
                        if(data.coincidencia == 'si' && data.exacta == 'si'){
                            console.log("exacta");
                            $("#existenciaNombreCategoria").html("<i style='color: red;'>Nombre de categoria en uso</i>");
                            $('#botGuardarCategoria').prop('disabled', true);
                        }
                        else if(data.coincidencia == 'si' && data.exacta == 'no'){
                            console.log("posible");
                            $("#existenciaNombreCategoria").html("<i style='color: red;'>Posible coincidencia: "+data.coincidencia_valor+"</i>");

                            $('#botGuardarCategoria').prop('disabled', false);
                        }
                        if(data.coincidencia == 'no'){
                            console.log("No oincidencia");
                            $("#existenciaNombreCategoria").html("");
                            $('#botGuardarCategoria').prop('disabled', false);
                        }
                    }
                })
            })
        });
    </script>
@endsection






















