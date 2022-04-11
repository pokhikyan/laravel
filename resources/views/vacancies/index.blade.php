@extends('layouts.app')

@section('content')

    <div id="header-container">
        <h1>Vacancies</h1>
        <form method="get" action="{{route('vacancy.list')}}">
            <input type="text" name="search" class="form-control search" value="{{request('search')}}" placeholder="Search">
            <button class="form-control" title="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>

        </form>
    </div>


    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p></p>
        </div>
    @endif

    <?php
    $settings = json_decode($settings, 1);
    $filters = isset($settings['filters']) ? $settings['filters'] : $settings;
    ?>
    <div class="filter-container">
        <form method="get" action="{{route('vacancy.list')}}" class="filter_form">
            <div class="row">

            <select name="company" class="form-control form-control-select2 <?php echo !$filters['companies']['status'] ? 'hidden' : '' ?>">
                <option value="0">All Companies</option>
                @foreach ($websites as $website)
                    <option value="{{$website->id}}" @if($website->id == request('company'))  {{'selected'}} @endif >{{$website->company}}</option>
                @endforeach
            </select>

            <select name="region" class="form-control form-control-select2 <?php echo !$filters['regions']['status'] ? 'hidden' : '' ?>">
                <option value="0">All regions</option>
                @foreach ($regions as $region)
                    <option value="{{strip_tags(trim($region->id))}}" @if(strip_tags(trim($region->id)) == request('region'))  {{'selected'}} @endif >{{strip_tags(trim($region->name))}}</option>
                @endforeach
            </select>

            <select name="city" class="form-control form-control-select2 <?php echo !$filters['cities']['status'] ? 'hidden' : '' ?>">
                <option value="0">All cities</option>
                @foreach ($cities as $city)
                    <option value="{{strip_tags(trim($city))}}" @if(strip_tags(trim($city)) == request('city'))  {{'selected'}} @endif >{{strip_tags(trim($city))}}</option>
                @endforeach
            </select>

            <select name="job_category" class="form-control form-control-select2 <?php echo !$filters['categories']['status'] ? 'hidden' : '' ?>">
                <option value="0">All Categories</option>
                @foreach ($categories as $key => $category)
                    <option value="{{strip_tags(trim($key))}}" @if(strip_tags(trim($key)) == request('job_category'))  {{'selected'}} @endif >{{strip_tags(trim($key))}}</option>
                @endforeach
            </select>

            <select name="job_sub_category" class="form-control form-control-select2 <?php echo !$filters['sub_categories']['status'] ? 'hidden' : '' ?>" <?php echo !request('job_category') ? 'disabled' : ''?>>
                <option value="0">All Sub Categories</option>

                @foreach ($sub_categories as $sub_categorie)
                        <option value="{{strip_tags(trim($sub_categorie))}}" @if(strip_tags(trim($sub_categorie)) == request('job_sub_category'))  {{'selected'}} @endif >{{strip_tags(trim($sub_categorie))}}</option>
                @endforeach
            </select>


            </div>
            <div class="row">
                <select name="job_type" class="form-control form-control-select2 <?php echo !$filters['job_type']['status'] ? 'hidden' : '' ?>">
                    <option value="0">All types</option>
                    <option value="1" @if(request('job_type') == 1)  {{'selected'}} @endif>Full time</option>
                    <option value="2" @if(request('job_type') == 2)  {{'selected'}} @endif>Permanent</option>
                    <option value="3" @if(request('job_type') == 3)  {{'selected'}} @endif>Temporary</option>
                    <option value="4" @if(request('job_type') == 4)  {{'selected'}} @endif>Internship</option>
                    <option value="5" @if(request('job_type') == 5)  {{'selected'}} @endif>Praktikum</option>
                    <option value="6" @if(request('job_type') == 6)  {{'selected'}} @endif>Part time</option>
                    <option value="7" @if(request('job_type') == 7)  {{'selected'}} @endif>Limited Duration</option>
                </select>

                <select name="job_level" class="form-control form-control-select2 <?php echo !$filters['job_level']['status'] ? 'hidden' : '' ?>">
                    <option value="0">All levels</option>
                    <option value="1" @if(request('job_level') == 1)  {{'selected'}} @endif>Professional</option>
                    <option value="2" @if(request('job_level') == 2)  {{'selected'}} @endif>Mid level</option>
                    <option value="3" @if(request('job_level') == 3  )  {{'selected'}} @endif>Student</option>
                    <option value="4" @if(request('job_level') == 4  )  {{'selected'}} @endif>Praktikum</option>
                </select>

                <input type="date" name="start_date" class="form-control <?php echo !$filters['start_date']['status'] ? 'hidden' : '' ?>" value="{{request('start_date')}}">
                <input type="date" name="end_date" class="form-control end_date <?php echo !$filters['end_date']['status'] ? 'hidden' : '' ?>" value="{{request('end_date')}}">

                 <div class="filter_col <?php echo !$filters['active_jobs']['status'] ? 'hidden' : '' ?>">
                    <label class="filter_label">Active Jobs</label>
                    <input type="checkbox" name="active_jobs" value="1" <?php echo (request('active_jobs'))?'checked':'' ?>>
                 </div>


                <button class="form-control search-button" title="Filter">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>
            </div>
        </form>
        <form method="get" action="{{route('export.list')}}" class="export_form">
            <input type="hidden" name="company">
            <input type="hidden" name="region">
            <input type="hidden" name="city">
            <input type="hidden" name="job_category">
            <input type="hidden" name="job_type">
            <input type="hidden" name="start_date">
            <input type="hidden" name="end_date">
            <input type="hidden" name="export_type">
            <button class="btn btn-secondary" onclick="set_filter_data('xlsx')">Export to Excel</button>
            <button class="btn btn-secondary" onclick="set_filter_data('csv')">Export to CSV</button>
        </form>

    </div>
    <div class="table-responsive">

        <table class="table table-hover table-striped">
        <thead>
        <tr>
            <?php
            $settings = isset($settings['columns']) ? $settings['columns'] : $settings;
            $columns = array_column($settings, 'order');
            array_multisort($columns, SORT_ASC, $settings);
            foreach ( $settings as $setting ) {
                if($setting['status'] == 0 ) continue;
                ?>
                <th scope="col">{{$setting['title']}}</th>
            <?php
            }
            ?>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($vacancies as $vacancy)
            <tr>
                <?php foreach ( $settings as $key => $setting ) {
                if($setting['status'] == 0 ) continue;
                ?>
                    <?php if($key == 'job_description') { ?>
                        <td>{{substr($vacancy->$key, 0, 200)}}...</td>
                    <?php } elseif($key == 'website_id') { ?>
                        <td>{{$vacancy->company}}</td>
                    <?php } else { ?>
                        <td>{{$vacancy->$key}}</td>
                    <?php } ?>
                <?php } ?>
                <td class="flex-md-row">
                    <a href="{{$vacancy->job_url}}" target="_blank" title="Open in site">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                            <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                        </svg>
                    </a>
                </td>
            </tr>

        @endforeach
        </tbody>
    </table>
    </div>

    @if ( count($vacancies) > 0 )
        {!! $vacancies->appends($get_data)->links() !!}
    @else
        There are no vacancies matching these criteria.
    @endif

@endsection
