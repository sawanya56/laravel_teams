@extends('layout')

@section('container')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table" id="example">
                    <thead>
                        <tr>
                            <td>No</td>
                            <td>Name</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teams as $row)
                            <tr>
                                <td>{{ $row->class_id }}</td>
                                <td>{{ $row->team_name }}</td>
                                <td>
                                    <a href="/class/detail/{{$row->class_id}}"><button>Clcik</button></a>
                                   
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