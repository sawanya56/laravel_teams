@extends('app')

@section('container')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0" style=" box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div class="card card-body">
                    <form method="post" action="/class/create">
                        @csrf
                        <div class="form-group">
                            <label for="exampleInputEmail1" style="color: #1b25bb;">team_name</label>
                            <input name="team_name" type="text" class="form-control" id="team_name"
                                aria-describedby="emailHelp" placeholder="Enter team_name">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1" style="color: #1b25bb;">course_code</label>
                            <input name="course_code" type="text" class="form-control" id="course_code"
                                aria-describedby="emailHelp" placeholder="Enter course_code">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1" style="color: #1b25bb;">section</label>
                            <input name="section" type="text" class="form-control" id="section"
                                aria-describedby="emailHelp" placeholder="Enter section">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1" style="color: #1b25bb;">week_of_day</label>
                            <select class="form-control" name="week_of_day" id="week_of_day">
                                <option>MO</option>
                                <option>TU</option>
                                <option>WE</option>
                                <option>TH</option>
                                <option>FR</option>
                                <option>SA</option>
                                <option>SU</option>
                            </select>

                        </div>
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="exampleInputEmail1" style="color: #1b25bb;">start_time</label>
                                <input name="start_time" type="time" class="form-control" id="start_time"
                                    aria-describedby="emailHelp" placeholder="Enter start_time">

                            </div>
                            <div class="form-group col-6">
                                <label for="exampleInputEmail1" style="color: #1b25bb;">end_time</label>
                                <input name="end_time" type="time" class="form-control" id="end_time"
                                    aria-describedby="emailHelp" placeholder="Enter end_time">

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1" style="color: #1b25bb;">duration_time</label>
                            <input name="duration_time" type="text" class="form-control" id="duration_time"
                                aria-describedby="emailHelp" placeholder="Enter duration_time">

                        </div>
                        <div class="row">
                            <div class="col-3">
                                <a href="/main">
                                    <button type="button" class="btn btn-primary">Back</button>
                                </a>
                            </div>
                            <div class="col-3"></div>
                            <div class="col-3"></div>
                            <div class="col-3">
                                <button type="button" id="btn_save" class="btn btn-block btn-success">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script>
        $('#btn_save').click(function() {
            Swal.fire({
                title: 'Do you want to save?',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Save',
                denyButtonText: `Don't save`,
            }).then((result) => {

                if (result.isConfirmed) {
                    let team_name = $('#team_name').val();
                    let course_code = $('#course_code').val();
                    let section = $('#section').val();
                    let week_of_day = $('#week_of_day').val();
                    let start_time = $('#start_time').val();
                    let end_time = $('#end_time').val();
                    let duration_time = $('#duration_time').val();
                    axios({
                            method: 'post',
                            url: '/api/class/create',
                            data: {
                                team_name: team_name,
                                course_code: course_code,
                                section: section,
                                week_of_day: week_of_day,
                                start_time: start_time,
                                end_time: end_time,
                                duration_time: duration_time,
                            },
                            responseType: 'json'
                        })
                        .then(function(response) {

                            // console.log(response);
                            let data = response.status;
                            if (data == 'success') {
                                Swal.fire('Saved!', '', 'success')

                                $('[data-dismiss="modal"]').trigger('click');
                            }

                        });
                }
            });
        });
    </script>
@endsection
