@extends('layouts.app')

@section('content')

    <div style="display: flex; align-items: center">
        <h1 class="page-title" style="flex-grow: 1">Users</h1>
        <a href="{{route('user.create')}}" class="btn btn-primary" style="height: 35px">Add New User</a>
    </div>

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
            <th scope="col">Email</th>
            <th scope="col">Role</th>
            <th scope="col">Date Created</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>

        <?php $i=1; ?>
        @foreach ($users as $user)
            <tr>
                <td title="id={{$user->id}}">{{$i}}</td>
                <td>{{$user->name}}</td>
                <td>{{$user->email}}</td>
                <td>{{$user->role}}</td>
                <td>{{$user->created_at}}</td>
                <td>
                    <a href="{{route('user.edit', ['id' => $user->id])}}" class="edit_website">Edit</a> |
                    <a onclick="return confirm('Are you sure you want to delete this user?')" href="{{route('user.delete', ['id' => $user->id])}}" class="edit_website">Delete</a>
                </td>
            </tr>
            <?php $i++; ?>
        @endforeach
        </tbody>
    </table>
    </div>


@endsection
