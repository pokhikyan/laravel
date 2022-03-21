@extends('layouts.app')

@section('content')

    <div id="header-container">
        <h1>Dashboard</h1>
    </div>
    <div class="dashboard-container">

        <div class="section">
            <h3>Total count of vacancies is : {{$data['count']}}</h3>
            <div class="vac_count_cont">
            <?php foreach ($data['resp'] as $data ) { ?>
            <p>Count of <b>{{$data->company}}</b> vacancies is : {{$data->jobCount}}</p>
            <?php } ?>
            </div>
        </div>

    </div>
@endsection
