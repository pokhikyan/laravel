@extends('layouts.app')

@section('content')

    <div id="header-container">
        <h1>Settings</h1>
    </div>
    <div class="settings-container">

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
                $sett = isset($sett['columns']) ? $sett['columns'] : $sett;
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

        <div class="section">
            <h5>Show Filters in Vacancies Page</h5>
            <hr>
            <form method="post" action="{{route('settings.update_filter')}}">
                @csrf
                <table>
                    <tr>
                        <th>Filter Name</th>
                        <th style="padding-right: 20px">Show</th>
                        <th>Label</th>
                        <th>Order</th>
                    </tr>
                <?php
                $sett = json_decode($settings, 1);
                if( !empty($sett['filters']) ) {
                    $filters = $sett['filters'];
                } else {
                    $filters = $default_filters;
                }

                foreach($filters as $key => $val) {
                ?>
                    <tr>
                        <td style="width: 200px">{{$key}}</td>
                        <td style="padding-right: 20px"><input type="checkbox" name="{{$key}}_filter" value="1" <?php echo ($val['status']) ? 'checked' : '' ?>></td>
                        <td><input type="text" name="{{$key}}_title" value="{{$val['title']}}"></td>
                        <td><input style="width: 50px" type="number" name="{{$key}}_order" value="{{$val['order']}}"></td>
                    </tr>
                <?php } ?>
                </table>
                <input type="submit" class="btn btn-primary" value="Save" style="margin-top: 20px">
            </form>
        </div>

    </div>
@endsection
