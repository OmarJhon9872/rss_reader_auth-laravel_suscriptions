@extends('complements.results_base')

@push('styles')
    <link rel="stylesheet" href="{{asset('css/home.css')}}">
@endpush

@section('resultados')
        <div class="col-12 mt-5" id="resultados">
            <div class="row">
                <div class="col-12">
                    <h3>Canales</h3>
                </div>
                @forelse($resultados as $resultado)

                    @includeIf('complements.channel')

                @empty

                @endforelse
            </div>
        </div>
@endsection

@section('children_scripts_results_base')
    <script></script>
@endsection
