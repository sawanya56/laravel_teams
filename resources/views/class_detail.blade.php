@extends('layout')

@section('container')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5>{{ $class_detail->team_name }}</h5>
                    <h5>{{ $class_detail->calendar_subject }}</h5>
                    <h5>Team ID : {{ $class_detail->team_id }}</h5>
                    <h5>Channel ID : {{ $class_detail->channel_id }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-6">
            @if (Session::has('message'))
                <div class="{{ Session::get('alert') }}" role="alert">
                    {{ Session::get('message') }}
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <form method="post" action="/class/add/owner">
                        @csrf
                        <div class="form-group">
                            <label for="exampleInputEmail1">Email address</label>
                            <input type="email" name="email" class="form-control" id="exampleInputEmail1"
                                aria-describedby="emailHelp">
                            <input type="hidden" name="team_id" value="{{ $class_detail->team_id }}">
                            <input type="hidden" name="class_id" value="{{ $class_detail->class_id }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                </div>
                <div class="card-body">
                    <h4>ins</h4>
                    <table class="table" id="example">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mail</th>
                                <th>Add Success</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($instructors as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->email }}</td>
                                    <td style="color: green">{{ $row->add_success }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h4>Student</h4>
                    <table class="table" id="example2">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mail</th>
                                <th>Add_success</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->student_mail }}</td>
                                    <td>{{ $row->add_success }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
