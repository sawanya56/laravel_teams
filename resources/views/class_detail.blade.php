@extends('layout')

@section('container')
    <div class="container-fluid mt-2">
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0" style=" box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <div class="card-body">
                        <h5 style="color:#89375F; font-size: 25px" >{{ $class_detail->team_name }}</h5>
                        <h5>{{ $class_detail->calendar_subject }}</h5>
                        <h5>Team ID : {{ $class_detail->team_id }}</h5>
                        <h5>Channel ID : {{ $class_detail->channel_id }}</h5>
                        <h5 >CLASS ID : {{ $class_detail->class_id }}</h5>

                        @foreach ($schedules as $item)
                            <h5> day :{{ $item->week_of_day }} {{ $item->start_time }}</h5>
                        @endforeach

                        <div class="row">
                            <div class="col-3">
                                <a href="/main">
                                    <button class="styled-button">BACK</button>
                                </a>
                            </div>
                            <div class="col-3"></div>
                            <div class="col-2"></div>
                            <div class="col-4">
                                <button class="styled-button-remove" id="btnDelete">DELETE TEAM WITH DATABASE</button>
                                <form action="/team/delete/all" method="post" id="formDelete">
                                    <input type="hidden" name="class_id" value="{{ $class_detail->class_id }}">
                                    <input type="hidden" name="team_id" value="{{ $class_detail->team_id }}">
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
                        <h4 style="color:#D14D72"><b>Instructor</b></h4>
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
                                    <button type="submit" class="styled-button">Add Instructor</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">

                        <table class="table" id="example">
                            <thead>
                                <tr>
                                    <th style="color:#89375F">No</th>
                                    <th style="color:#89375F">Mail</th>
                                    <th style="color:#89375F">Add Success</th>
                                    <th style="color:#89375F">Button</th>
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
                        <h4 style="color:#D14D72"><b>Student</b></h4>
                        <form method="post" action="/class/add/student" class="pagination-info">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-10">
                                    <input type="email" name="email" class="form-control" id="exampleInputEmail1"
                                        aria-describedby="emailHelp" placeholder="Enter email">
                                    <input type="hidden" name="team_id" value="{{ $class_detail->team_id }}">
                                    <input type="hidden" name="class_id" value="{{ $class_detail->class_id }}">
                                </div>

                                <div class="form-group col-md-2">
                                    <button type="submit" class="styled-button">Add Student</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table" id="example2">
                            <thead>
                                <tr>
                                    <th style="color:#89375F">No</th>
                                    <th style="color:#89375F">Mail</th>
                                    <th style="color:#89375F">Add_success</th>
                                    <th style="color:#89375F">Button</th>
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
                                                <button type="submit" class="styled-button-remove">Remove</button>
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
@endsection
