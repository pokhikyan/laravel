@extends('layouts.app')

@section('content')

    <div id="header-container">
        <h1>Logs</h1>
    </div>

    <div class="filter-container">
    <form method="get" action="{{route('logs.list')}}">
        <input type="date" name="date" class="form-control" value="{{date('mm/dd/YY') }}">

        <button class="form-control" title="Filter">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
        </button>
    </form>
    </div>

    <div class="table-responsive">

        <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th scope="col">N:</th>
            <th scope="col">Date</th>
            <th scope="col">Message</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        @php
            $i = 0;
        @endphp
        @foreach ($logCollection as $logs)
            @php
                $class = $logs->error == 1 ? "class=error" : "";
            @endphp
            <tr {{$class}}>
                <td>{{++$i}}</td>
                <td style="white-space: nowrap;">{{$logs->created_at}}</td>
                <td>{{substr($logs->log, 0, 250)}}</td>
                <td>
                    <form action="{{route('logs.destroy')}}" method="get">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="log_id" value="{{$logs->id}}">
                        <button type="submit" class="btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @if ( count($logCollection) > 0 )
    {!! $logCollection->appends($get_data)->links() !!}
    @else
        There are no logs matching these criteria.
    @endif
@endsection
