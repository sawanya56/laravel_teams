@extends('app')

@section('container')
    <div class="container-fluid mt-2">
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0" style=" box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <div class="card-body">
                        <h5 style="color:rgb(2, 0, 138); font-size: 25px"><b>{{ $class_detail->team_name }}</b></h5>
                        <h5>{{ $class_detail->calendar_subject }}</h5>
                        <h5>Team ID : {{ $class_detail->team_id }}</h5>
                        <h5>Channel ID : {{ $class_detail->channel_id }}</h5>
                        <h5>CLASS ID : {{ $class_detail->class_id }}</h5>

                        @foreach ($schedules as $item)
                            <h5> day :{{ $item->week_of_day }} {{ $item->start_time }}</h5>
                        @endforeach

                        <div class="row">
                            <div class="col-3">
                                <a href="/main">
                                    <button class="btn btn-primary">BACK</button>
                                </a>
                            </div>
                            <div class="col-3"></div>
                            <div class="col-2"></div>
                            <div class="col-4">
                                <button class="btn btn-danger" id="btn_delete_team">DELETE TEAM WITH DATABASE</button>
                                <form action="/team/delete/all" method="post" id="form_delete">
                                    <input type="hidden" name="class_id" id="team_id"
                                        value="{{ $class_detail->class_id }}">
                                    <input type="hidden" name="team_id" id="class_id"
                                        value="{{ $class_detail->team_id }}">
                                    @csrf
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                @if (Session::has('message'))
                    <div class="{{ Session::get('alert') }}" role="alert">
                        {{ Session::get('message') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- INSTRUCTOR --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0" style=" box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <div class="card-body">
                        <h4 style="color:rgb(2, 0, 138)"><b>Instructor</b></h4>
                        <form method="post" action="/class/add/owner">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-10">
                                    {{-- <label for="exampleInputEmail1">Email address</label> --}}
                                    <input type="email" name="email" class="form-control" id="exampleInputEmail1"
                                        aria-describedby="emailHelp" placeholder="Enter email">
                                    <input type="hidden" name="team_id" value="{{ $class_detail->team_id }}">
                                    <input type="hidden" name="class_id" value="{{ $class_detail->class_id }}">
                                </div>
                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-primary">Add Instructor</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">

                        <table class="table" id="example">
                            <thead>
                                <tr>
                                    <th style="color:#1b25bb">No</th>
                                    <th style="color:#1b25bb">Mail</th>
                                    <th style="color:#1b25bb">Add Success</th>
                                    <th style="color:#1b25bb">Button</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($instructors as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->email }}</td>
                                        <td style="color: green">{{ $row->add_success }}</td>
                                        <td><button class="btn btn-danger">remove</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- STUDENT --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0" style=" box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <div class="card-body">
                        <h4 style="color:rgb(2, 0, 138)"><b>Student</b></h4>
                        <form method="post" action="/class/add/student" class="pagination-info">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-10">
                                    <input type="text" name="student_code" class="form-control" id="student_code"
                                        aria-describedby="emailHelp" placeholder="Enter email">
                                    <input type="hidden" name="team_id" value="{{ $class_detail->team_id }}">
                                    <input type="hidden" name="class_id" value="{{ $class_detail->class_id }}">
                                </div>

                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-primary">Add Student</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table" id="example2">
                            <thead>
                                <tr>
                                    <th style="color:#1b25bb">No</th>
                                    <th style="color:#1b25bb">Mail</th>
                                    <th style="color:#1b25bb">Add_success</th>
                                    <th style="color:#1b25bb">Button</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td style="width: 40%">{{ $row->student_mail }}</td>
                                        <td style="color: green">{{ $row->add_success }}</td>
                                        <td style="color: green">
                                            <form action="/class/remove/student" method="post"
                                                onsubmit="return confirm('ลบไหม?');">
                                                @csrf
                                                <input type="hidden" name="team_id"
                                                    value="{{ $class_detail->team_id }}">
                                                <input type="hidden" name="class_id" value="{{ $row->class_id }}">
                                                <input type="hidden" name="email" value="{{ $row->student_mail }}">
                                                <button type="submit" class="btn btn-danger">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
       

        function handleDelete() {
            Swal.fire({
                title: 'Do you want to delete?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form
                    document.getElementById('form_delete').submit();
                    Swal.fire({
                        title: 'Deleting...',
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 1500 
                    }).then(() => {
                        window.location.href = '/home';
                    });
                }
            });
        }

        // Attach the handleDelete function to the button click event
        document.getElementById('btn_delete_team').addEventListener('click', handleDelete);
    </script>
@endsection
