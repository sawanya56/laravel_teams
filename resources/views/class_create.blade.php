@extends('layout')

@section('container')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-body">
                <form method="post" action="/class/create">
                    @csrf
                    <div class="form-group">
                        <label for="exampleInputEmail1">team_name</label>
                        <input name="team_name" type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Enter team_name">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">course_code</label>
                        <input name="course_code" type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Enter course_code">

                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">section</label>
                        <input name="section" type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Enter section">

                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">week_of_day</label>
                        <input name="week_of_day" type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Enter week_of_day">

                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">start_time</label>
                        <input name="start_time" type="time" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Enter start_time">

                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">end_time</label>
                        <input name="end_time" type="time" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Enter end_time">

                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">duration_time</label>
                        <input name="duration_time" type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Enter duration_time">

                    </div>
                    <div class="row">
                        <div class="col-3" href="">
                            <button type="button" class="btn btn-primary" href="/main">Back</button>
                        </div>
                        <div class="col-3"></div>
                        <div class="col-3"></div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-block btn-success">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
