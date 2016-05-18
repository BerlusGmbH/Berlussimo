@extends('layouts.main')

@section('submenu')
    <?php include(base_path($submenu)); ?>
@endsection

@section('content')
    @if($content != "")
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        {!! $content !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection