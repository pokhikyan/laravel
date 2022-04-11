$(document).ready(function () {
    //if ($('#start_scan').length) {
    $(document).on('click', '#start_scan', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).data('href'),
        }).done(function (results) {
            debugger;
            toastr["success"]("Scan started in background", "Success");
        }).fail(function (request, status, error) {
            debugger;
            toastr["error"]("Error queueing scan", "Fail");
        });
    });

    $(document).on('click', '.run_website', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).data('href'),
            data: { company: $(this).data('company'), action: 'run_company_scan' }
        }).done(function (results) {
            debugger;
            toastr["success"]("Scan started in background", "Success");
        }).fail(function (request, status, error) {
            debugger;
            toastr["error"]("Error queueing scan", "Fail");
        });
    })
    //}

/*
    $(document).on('click', '.edit_website', function (e) {
        e.preventDefault();

    })
*/
    $('.form-control-select2').not( ".hidden" ).select2();
});

function set_filter_data( export_type ) {
    debugger;
    var company = $(".filter_form select[name='company']").val();
    $(".export_form input[name='company']").val(company);

    var region = $(".filter_form select[name='region']").val();
    $(".export_form input[name='region']").val(region);

    var city = $(".filter_form select[name='city']").val();
    $(".export_form input[name='city']").val(city);

    var job_category = $(".filter_form select[name='job_category']").val();
    $(".export_form input[name='job_category']").val(job_category);

    var job_type = $(".filter_form select[name='job_type']").val();
    $(".export_form input[name='job_type']").val(job_type);

    var start_date = $(".filter_form input[name='start_date']").val();
    $(".export_form input[name='start_date']").val(start_date);

    var end_date = $(".filter_form input[name='end_date']").val();
    $(".export_form input[name='end_date']").val(end_date);

    $(".export_form input[name='export_type']").val(export_type);

}
