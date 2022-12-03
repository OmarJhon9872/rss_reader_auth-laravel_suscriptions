<div class="col-md-6 col-lg-4">
    <div class="card my-3">
        {{--Solo el cliente y analista puede categorizar elementos--}}
        @can('es_cliente_o_analista')
            <i class="fa fa-pencil-square-o boton_editar" aria-hidden="true" data-bs-toggle="modal" data-bs-target="#modalCambiarNombreCategoria{{ $resultado['id'] }}"></i>

            <i class="fa fa-trash-o bote_basura" tipo_elemento="categoria" id="{{$resultado['id']}}" aria-hidden="true"></i>
        @endcan
        <div class="card-body" >
            <mark style="font-size: 15px;">Categoria:</mark>
            <br>
            <a title="{{$resultado['name']}}" class="w-100 mt-2 titulo_resultado" href="{{ route('home.categorias', ['categoria' => $resultado['id']]) }}" >
                {{$resultado['name']}}
            </a>
            <small class="description_resultado" >
                {{ $resultado['description'] }}
            </small>
        </div>
    </div>
</div>



{{--Solo el cliente y analista puede categorizar elementos--}}
@can('es_cliente_o_analista')
    <!-- Modal editar categoria -->
    <div class="modal fade" id="modalCambiarNombreCategoria{{ $resultado['id'] }}" tabindex="-1" aria-labelledby="modalCambiarNombreCategoria" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCambiarNombreCategoria">Modificar categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('home.actualizarCategoria', ['categoria' => $resultado['id']])}}">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="name" value="{{ $resultado['name'] }}" class="form-control" placeholder="Nombre de categoria" autocomplete="off" required>

                        </div>
                        <div class="mb-3">
                            <input type="text" name="description" value="{{ $resultado['description'] }}" class="form-control" placeholder="Descripción de categoria" required>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-dark w-50">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan
