@extends('layouts.app')

@section('content')

    <h1 class="page-title">Companies</h1>


    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p></p>
        </div>
    @endif
    <div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th scope="col">No</th>
            <th scope="col">Name</th>
            <th scope="col">URL</th>
            <th scope="col">Status</th>
            <th scope="col">Date Created</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>

        <?php $i=1; ?>
        @foreach ($websites as $website)
            <tr>
                <td title="id={{$website->id}}">{{$i}}</td>
                <td>{{$website->company}}</td>
                <td  style="max-width: 400px; word-break: break-all">{{$website->url}}</td>
                <td>{{$website->active}}</td>
                <td>{{$website->created_at}}</td>
                <td>
                    <a href="{{route('company.edit', ['id' => $website->id])}}" class="edit_website">Edit</a> |
                    <a href="#" data-href="{{route('data.scan')}}" data-company="{{$website->id}}" class="run_website">Run</a>
                </td>
            </tr>
            <?php $i++; ?>
        @endforeach
        </tbody>
    </table>
    </div>

    {!! $websites->links() !!}

@endsection
