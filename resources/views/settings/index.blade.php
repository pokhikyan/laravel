@extends('layouts.app')

@section('content')

    <div id="header-container">
        <h1>Settings</h1>
    </div>
    <div class="dashboard-container">

        <div class="section">
            <h5>Show Columns in Vacancies Page</h5>
            <hr>
            <form method="post" action="{{route('settings.update')}}">
                @csrf
                <table>
                    <tr>
                        <th>DB Column</th>
                        <th style="padding-right: 20px">Show</th>
                        <th>Title</th>
                        <th>Order</th>
                    </tr>
                <?php
                $sett = json_decode($settings, 1);
                foreach ( $columns as $column ) {
                    $col = isset($sett[$column]['status']) ? $sett[$column]['status'] : '';
                    $title = isset($sett[$column]['title']) ? $sett[$column]['title'] : $column;
                    $order = isset($sett[$column]['order']) ? $sett[$column]['order'] : 0;
                ?>
                    <tr>
                        <td style="width: 200px">{{$column}}</td>
                        <td style="padding-right: 20px"><input type="checkbox" name="{{$column}}" value="1" <?php echo ($col) ? 'checked' : '' ?>></td>
                        <td><input type="text" name="{{$column}}_title" value="{{$title}}"></td>
                        <td><input style="width: 50px" type="number" name="{{$column}}_order" value="{{$order}}"></td>
                    </tr>
                <?php
                }
                ?>
                </table>
                <input type="submit" class="btn btn-primary" value="Save" style="margin-top: 20px">
            </form>
        </div>

    </div>
@endsection
