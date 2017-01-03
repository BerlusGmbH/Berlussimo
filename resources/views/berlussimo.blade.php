@extends('layouts.main')

@section('submenu')
    <?php include(base_path($submenu)); ?>
@endsection

@section('content')
    @if($content != "")
        <div class="card-panel">
            {!! $content !!}
        </div>
    @endif
@endsection