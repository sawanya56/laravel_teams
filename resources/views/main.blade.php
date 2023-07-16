@extends('layout')

@section('container')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <table class="table" id="example">
                        <thead>
                            <tr>
                                <td style="color:#0d68d7"><b>Class_id</b></td>
                                <td style="color:#0d68d7"><b>Name<b></td>
                                <td style="color:#0d68d7"><b>Action<b></i></td>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teams as $row)
                                <tr>
                                    <td>{{ $row->class_id }}</td>
                                    <td>{{ $row->team_name }}</td>
                                    <td>
                                        <a href="/class/detail/{{ $row->class_id }}"><button
                                                class="btn btn-outline-primary">ดูรายละเอียด</button></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
