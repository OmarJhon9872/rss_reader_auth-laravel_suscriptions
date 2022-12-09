<div class="col-md-6 col-lg-4">
    <div class="card my-3">

        @can('es_cliente_o_analista')
            <i class="fa fa-trash-o bote_basura" tipo_elemento="item" id="{{$resultado['id']}}" aria-hidden="true"></i>
        @endcan
        <div class="card-body" >

            <!-- Boton para mostrar modal con elementos visibles del item -->
            <mark style="font-size: 15px;">Item</mark>
            <br>
            <a style="cursor: pointer" title="{{$resultado['title']}}" class="w-100 mt-2 titulo_resultado" data-bs-toggle="modal" data-bs-target="#modalItem{{ $resultado['id'] }}">
                {{$resultado['title']}}
            </a>

            <i class="text-black-50" style="font-size: 10px; margin-left: 10px;">
                <i>Categorias: </i>
                <b>
                    @forelse($resultado['categories'] as $categoria)
                        <a href="{{ route('home.categorias', ['categoria' => $categoria['id']]) }}" target="_blank" class="ms-2">
                            {{$categoria['name']}}
                        </a>
                    @empty
                        <i class="ms-2">Sin categorizar</i>
                    @endforelse
                </b>
            </i>
            <small class="description_resultado" style="display: -webkit-box !important;">
                {{ strip_tags($resultado['description'])  }}
            </small>
        </div>
        <div class="card-footer p-1 ps-3" style="font-size: 9px;">
            Canal: <br>
            @if($resultado['rss_channel'] != '')
                <a class="w-100 text-black-50" href="{{$resultado['rss_channel']['channel_url']}}" target="_blank" style="white-space: normal; display: -webkit-box!important; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                    {{$resultado['rss_channel']['channel_title']}}
                </a>
            @else
                Desconocido
            @endif
        </div>
    </div>
</div>

<!-- Modal elemento seleccionado -->
<div class="modal fade" id="modalItem{{ $resultado['id'] }}" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalItemLabel" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%; ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalItemLabel">{{$resultado['title']}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5" >
                <div class="categorizacion d-flex flex-wrap align-items-center">

                    {{--Boton para mostrar las categorias y categorizar elemento --}}

                    {{--Solo analista puede categorizar elementos--}}
                    {{--Solo el cliente y analista puede categorizar elementos--}}
                    @can('es_cliente_o_analista')
                        <a class="text-decoration-none d-flex align-items-center" data-bs-toggle="collapse" href="#collapseCategoriasMostrar{{$resultado['id']}}" role="button" aria-expanded="false" aria-controls="collapseCategoriasMostrar{{$resultado['id']}}">
                            <i class="fa fa-plus-circle" aria-hidden="true" style="font-size: 30px; color: #2d3748;"></i>
                    {{--Solo sera decorativo en caso de que no sea analista--}}
                    @else
                        <a class="text-decoration-none d-flex align-items-center" href="#">
                    @endcan

                        <i class="fa fa-book boton_categorizar" aria-hidden="true"></i>
                    </a>
                    <i>Categorias: </i>
                    <b>
                        @forelse($resultado['categories'] as $categoria)
                            <a href="{{ route('home.categorias', ['categoria' => $categoria['id']]) }}" target="_blank" class="ms-2">
                                {{$categoria['name']}}
                            </a>
                        @empty
                            <i class="ms-2">Sin categorizar</i>
                        @endforelse
                    </b>

                    {{--Solo el cliente y analista puede categorizar elementos--}}
                    @can('es_cliente_o_analista')
                        <div class="collapse w-100 border border-5 rounded my-3" id="collapseCategoriasMostrar{{$resultado['id']}}">
                            <div class="card card-body">
                                <form action="{{route('home.categorizar_elemento', ['id' => $resultado['id'], 'tipo_elemento' => 'item'])}}" method="post">
                                    @csrf

                                    @if(count($categorias))
                                        <h4 class="mb-2">Categorizar elemento: </h4>
                                    @endif

                                    <div class="buscadorCategorizacionCaja my-3">
                                        <input type="text" class="form-control buscadorCategorizacionInput" placeholder="Buscar categoria">
                                    </div>


                                    @forelse($categorias as $categoria)
                                        <div class="buscadorCategorizacionNombre" categoria="{{$categoria['name']}}">
                                            <div class="form-check my-3">
                                                <input class="form-check-input"
                                                       name="categorias[]"
                                                       type="checkbox"
                                                       value="{{$categoria['id']}}"
                                                       id="categoriaelemento{{$categoria['id']}}"
                                                       {{in_array($categoria['id'], collect($resultado['categories'])->pluck('id')->toArray()) ? 'checked': ''}}>
                                                <label class="form-check-label" for="categoriaelemento{{$categoria['id']}}">
                                                    {{$categoria['name']}}
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        Aun no hay categorias en sistema
                                    @endforelse

                                    @if(count($categorias))
                                        <div class="botonSave text-center mt-3">
                                            <button type="submit" class="btn btn-dark w-50">Guardar</button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                        {{--Fin Boton para mostrar las categorias y categorizar elemento --}}
                    {{--Fin can, seccion para clientes y analistas--}}
                    @endcan



                </div>
                <hr>

                {!!  $resultado['description']  !!}
                <hr>
                @foreach($resultado['item_contents'] as $detalle_extra)
                    <br><mark>{{$detalle_extra['name']}}</mark> <br>
                    {!! $detalle_extra['value'] !!}
                    <hr>
                @endforeach
                <br><mark>Link</mark> <br>
                    <a href="{{$resultado['link']}}" target="_blank">
                        Visitar url del enlace
                    </a>
                <hr>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function(){

            /*Buscador de categoria, keyup busca termino de cada item para categorizarlo */
            $('.buscadorCategorizacionInput').keyup(function(){
                var terminoBuscar = this.value.toUpperCase();
                if(terminoBuscar.length !== 0){
                    $('.buscadorCategorizacionNombre').each(function(){
                        let nombreCaja = this.getAttribute('categoria');

                        /*Pasamos a mayusculas y quitamos acentos y espacios*/
                        nombreCaja = nombreCaja.normalize("NFD").replace(/[\u0300-\u036f]/g, '').toUpperCase().trim();
                        terminoBuscar = terminoBuscar.normalize("NFD").replace(/[\u0300-\u036f]/g, '').toUpperCase().trim();

                        if(nombreCaja.indexOf(terminoBuscar) === -1){
                            this.style.display = 'none';
                        }else{
                            this.style.display = 'initial';
                        }
                    });
                }else{
                    $('.buscadorCategorizacionNombre').css('display', 'initial');
                }
            });
        });
    </script>
@endpush
