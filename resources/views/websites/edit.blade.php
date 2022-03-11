@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h1 class="page-title">Edit Website</h1>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li></li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{route('company.update')}}" method="POST">
        @csrf

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Name:</strong>
                    <input type="text" name="company" value="{{$website->company}}" class="form-control" placeholder="Name">
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>URL</strong>
                    <textarea class="form-control" style="height:150px" name="url"
                              placeholder="url">{{$website->url}}</textarea>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Active</strong>
                    <input
                        type="checkbox"
                        name="active"
                        style="width: 20px; height: 20px; vertical-align: middle; margin-left: 10px;"
                        value="{{$website->active}}" <?php if($website->active) echo 'checked' ?>>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <input type="hidden" name="id" value="{{$website->id}}">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>

    </form>
@endsection
