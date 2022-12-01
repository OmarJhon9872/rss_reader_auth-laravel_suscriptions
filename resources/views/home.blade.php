@extends('complements.results_base')

@section('resultados')

        <div class="col-12 mt-5" id="resultados">
            <div class="row">
                <div class="col-12">
                    <h3>{!! $titulo  !!}</h3>
                </div>
                @forelse($resultados as $resultado)

                    @if(array_key_exists('channel_title', is_array($resultado) ? $resultado: $resultado->toArray()))

                        @includeIf('complements.channel')

                    @elseif(array_key_exists('rss_channel_id', is_array($resultado) ? $resultado: $resultado->toArray()))

                        @includeIf('complements.item')

                    @elseif(array_key_exists('category_id', is_array($resultado) ? $resultado: $resultado->toArray()))

                        @includeIf('complements.category')

                    @endif
                @empty

                @endforelse
            </div>
        </div>
@endsection

@section('children_scripts_results_base')
    <script language="javascript" type="text/javascript" src="{{asset('js/jquery.highlight-3.yui.js')}}"></script>
@endsection
