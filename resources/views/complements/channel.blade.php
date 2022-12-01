<div class="col-md-6 col-lg-4">
    <div class="card my-3">
        <i class="fa fa-trash-o bote_basura" tipo_elemento="canal" id="{{$resultado['id']}}" aria-hidden="true"></i>
        <div class="card-body" >
            <mark style="font-size: 15px;">Canal:</mark>
            <br>
            <a title="{{$resultado['channel_title']}}" class="w-100 mt-2 titulo_resultado"
               href="{{ route('home.canales', ['canal' => $resultado['id']]) }}">
                {{$resultado['channel_title']}}
            </a>

            <small class="description_resultado" >
                {{ $resultado['channel_description']  }}
            </small>
            <i>
                <a href="{{$resultado['channel_url']}}" target="_blank">Enlace al canal</a>
            </i>
        </div>
    </div>
</div>
