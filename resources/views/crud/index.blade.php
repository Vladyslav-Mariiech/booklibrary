@extends('layouts.crud')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="panel panel-default">
                    <div class="body-content-homeP">
                        <div class="content-block">
                            <a>Hello to Admin Page</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
