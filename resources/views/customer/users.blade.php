@extends('complements.results_base')

@section('resultados')

    <div class="col-12 mt-5" id="resultados">
        <div class="row">
            <div class="col-12 mb-2">
                <h3 class="">
                    <i class="fa fa-plus-circle" aria-hidden="true" style="cursor: pointer; font-size: 30px; color: #2d3748;" data-bs-toggle="modal" data-bs-target="#modalAddCliente"></i>
                    {!! $titulo  !!}
                </h3>

                @cannot('es_super_admin')
                    <i class="mb-5">
                        {{ "Licencias contratadas: ". auth()->user()->role->licenses }}
                        <br>
                        {{  "Licencias empleadas: ". auth()->user()->employees()->count() }}
                    </i>
                @endcannot
            </div>

            <table class="table">
                <thead class="table-dark">
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>A_paterno</th>
                        <th>A_materno</th>
                        <th>Rol</th>
                        @can('es_super_admin')
                            <th>Licencias</th>
                        @endcan
                        <th>Email</th>
                        @can('es_solo_cliente')
                            <th>Elementos</th>
                            <th>Migrar</th>
                        @endcan
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empleados as $empleado)
                        <tr>
                            <td>{{$empleado['user']['id']}}</td>
                            <td>{{$empleado['user']['name']}}</td>
                            <td>{{$empleado['user']['last1']}}</td>
                            <td>{{$empleado['user']['last2']}}</td>

                            <td>{{$empleado['desc_role']['description']}}</td>

                            @can('es_super_admin')
                                <td>{{$empleado['licenses']}}</td>
                            @endcan

                            <td>{{$empleado['user']['email']}}</td>

                            {{--Elementos de usuario--}}
                            @can('es_solo_cliente')
                                <td>
                                    Canales creados: <i>{{$empleado['rss_channels_count']}}</i> <br>
                                    Items creados: <i>{{$empleado->items_count}}</i> <br>
                                </td>


                                {{--Seccion migracion de categorias-canales a otro analista --}}
                                <td>

                                    @if($empleado['role_id'] == 2)
                                        <!-- Button cambiar clave usuario -->
                                        <button type="button" class="btn btn-warning text-white btn-sm" data-bs-toggle="modal" data-bs-target="#modalMigrarElementos{{$empleado['user']['id']}}">
                                            <i class="fa fa-arrows-h" aria-hidden="true"></i>
                                            Migrar
                                        </button>

                                        <!-- Modal para migrar elementos de analista a otro -->
                                        <div class="modal fade" id="modalMigrarElementos{{$empleado['user']['id']}}" tabindex="-1" aria-labelledby="modalMigrarElementosLabel{{$empleado['user']['id']}}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalMigrarElementosLabel{{$empleado['user']['id']}}">Migrar todo de analista</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{route('home.cambiar_analista')}}" method="post">
                                                            <input type="hidden" name="analista_from" value="{{$empleado['user']['id']}}">
                                                            @csrf
                                                            <?php
                                                                $analistas_libres = [];
                                                                foreach ($analistas as $analista){
                                                                    if($analista['user']['id'] != $empleado['user']['id']){
                                                                        $analistas_libres[] = $analista;
                                                                    }
                                                                }
                                                            ?>

                                                            @if(count($analistas_libres) > 0)
                                                                <h4 class="my-3">Seleccione analista al que desea migrar los elementos del usuario: {{$empleado['user']['name']}}</h4>
                                                            @endif

                                                            @forelse($analistas_libres as $analista)
                                                                <div class="form-check mb-4">
                                                                    <input class="form-check-input" type="radio" name="analista_to" value="{{$analista['user']['id']}}" checked>
                                                                    <label class="form-check-label" >
                                                                        {{$analista['user']['name']}}
                                                                    </label>
                                                                </div>
                                                            @empty
                                                                <h5>Al parecer no hay analistas disponibles para realizar la migración</h5>
                                                                <div class="my-3">
                                                                    <button type="button" class="w-100 btn btn-warning text-white crearAnalistaBoton" nombre_modal="modalMigrarElementos{{$empleado['user']['id']}}">Crear analista</button>
                                                                </div>
                                                            @endforelse

                                                            @if(count($analistas_libres) > 0)
                                                                <button class="btn w-100 btn-primary text-white font-weight-bold" onclick="return confirm('¿Seguro?')">Migrar </button>
                                                            @endif

                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            @endcan


                            {{--Seccion de cambiar clave de usuario-cliente en caso de super admin --}}
                            <td>
                                <!-- Button cambiar clave usuario -->
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalContrasena{{$empleado['user']['id']}}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                    Cambiar
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="modalContrasena{{$empleado['user']['id']}}" tabindex="-1" aria-labelledby="modalContrasenaLabel{{$empleado['user']['id']}}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalContrasenaLabel{{$empleado['user']['id']}}">Cambiar contraseña usuario</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{route('home.cambiar_clave_usuario', ['usuario' => $empleado['user']['id']])}}" method="post">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <h4>Usuario: {{$empleado['user']['name']}}</h4>
                                                        <i>Acceso: {{$empleado['user']['email']}}</i>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>Nombre:</label>
                                                        <input type="text" value="{{$empleado['user']['name'] ?? ''}}" class="form-control" name="name" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Apellido paterno:</label>
                                                        <input type="text" value="{{$empleado['user']['last1'] ?? ''}}" class="form-control" name="last1" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Apellido materno:</label>
                                                        <input type="text" value="{{$empleado['user']['last2'] ?? ''}}" class="form-control" name="last2" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Correo:</label>
                                                        <input type="email" value="{{$empleado['user']['email'] ?? ''}}" class="form-control" name="email" required>
                                                    </div>
                                                    @can('es_super_admin')
                                                        <div class="mb-3">
                                                            <label>Licencias:</label>
                                                            <input type="text" value="{{$empleado['licenses'] ?? ''}}" class="form-control" name="licenses" required>
                                                        </div>
                                                    @endcan

                                                    <div class="mb-3">
                                                        <label>Nueva contraseña:</label>
                                                        <input type="password" class="form-control" name="password">
                                                    </div>

                                                    <button class="btn w-100 btn-primary text-white font-weight-bold">Actualizar usuario</button>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            {{--Eliminar usuario--}}
                            <td>
                                <form method="post" action="{{route('home.borrar_usuario', [
                                                'usuario' => $empleado['user']['id'],
                                            ])}}" id="formularioEliminarUsuario{{$empleado['user']['id']}}">
                                    @csrf
                                    @method('delete')
                                    <button class="botonEliminarUsuario btn btn-danger btn-sm" type="button" user_id="{{$empleado['user']['id']}}" {{--onclick="return confirm('Acción irreversible, ¿Borrar usuario?')"--}}>
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty

                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

    <!-- Modal agregar usuario -->
    <div class="modal fade" id="modalAddCliente" tabindex="-1" aria-labelledby="modalAddClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{route('home.crear_usuario')}}" method="post">
                        @csrf
                        <div class="mb-3">
                            @can('es_super_admin')
                                <h5 class="modal-title" id="modalAddClienteLabel">Agregar cliente</h5>
                            @elsecan('es_solo_cliente')
                                <h5 class="modal-title" id="modalAddClienteLabel">Agregar usuario</h5>
                            @endcan
                        </div>

                        <div class="mb-3">
                            <label for="name">Nombre:</label>
                            <input type="text" class="form-control" value="{{old('name' ?? '')}}" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last1">Apellido paterno:</label>
                            <input type="text" class="form-control" value="{{old('last1' ?? '')}}" id="last1" name="last1" required>
                        </div>
                        <div class="mb-3">
                            <label for="last2">Apellido materno:</label>
                            <input type="text" class="form-control" value="{{old('last2' ?? '')}}" id="last2" name="last2" required>
                        </div>
                        <div class="mb-3">
                            <label for="email">Correo:</label>
                            <input type="email" class="form-control" value="{{old('email' ?? '')}}" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password">Contraseña:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        @can('es_super_admin')
                            <div class="mb-3">
                                <label for="licenses">Licencias contratadas:</label>
                                <input type="number" class="form-control" value="{{old('licenses' ?? '')}}" id="licenses" name="licenses">
                            </div>
                        @elsecan('es_solo_cliente')
                            <div class="mb-3">
                                <label for="licenses">Rol que empleara:</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="" selected>[ Selecciona un rol de la lista ]</option>
                                    @foreach($roles as $rol)
                                        <option value="{{$rol['id']}}">{{$rol['description']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endcan

                        <button class="btn w-100 btn-success text-white font-weight-bold">Crear usuario</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('children_scripts_results_base')
    <script language="javascript" type="text/javascript" src="{{asset('js/jquery.highlight-3.yui.js')}}"></script>

    <script>
        $(function(){
            $(".crearAnalistaBoton").click(function(){
                var modal_nombre = this.getAttribute('nombre_modal');
                $("#"+modal_nombre).modal('hide');
                $("#modalAddCliente").modal('show');

            })


            $(".botonEliminarUsuario").click(function(){

                /*Solicitamos confirmacion*/
                if(!confirm('Acción irreversible, ¿Borrar usuario?')) return;

                var usuario_id = this.getAttribute('user_id');

                /*Verificamos si es posible eliminarlo (no tiene canales - items)*/
                var data = makePostRequest( "{{route('home.verifica_tiene_items') }}/"+usuario_id, {} );

                if(data){
                    if(data.status == 'not_empty'){
                        alert("No es posible eliminar al usuario ya que tiene elementos cargados, migra sus elementos a otro analista antes de eliminarlo")
                    }
                    else if(data.status == 'ok'){
                        $("#formularioEliminarUsuario"+usuario_id).submit();
                    }
                }
            })
        });
    </script>
@endsection
