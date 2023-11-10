@extends('app')

@section('container')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0" style=" box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div class="card-body">
                    <table class="table" id="dataTable">
                        <thead>
                            <tr>
                                <td style="color:#1b25bb"><b>Class_id</b></td>
                                <td style="color:#1b25bb"><b>Name<b></td>
                                <td style="color:#1b25bb"><b>Action<b></i></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teams as $row)
                                <tr>
                                    <td>{{ $row->class_id }}</td>
                                    <td>{{ $row->team_name }}</td>
                                    <td>
                                        <a href="/class/detail/{{ $row->class_id }}"><button
                                                class="btn btn-primary" id="sidebarToggleTop">ดูรายละเอียด</button></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <input type="text" id="class_id">
                    <button id="btn_search">Search</button>
                </div>
                <div class="card-body">
                    <h1 id="hello">Hello World</h1>
                    <ul id="students">
                        <li></li>
                    </ul>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- <script>
        document.getElementById('btn_search').addEventListener('click', function() {

            // let class_id = document.getElementById('class_id').value;
            // getStudent(class_id)
            let htmlInput = document.getElementById('class_id');
            console.log(htmlInput.value);
            document.getElementById('hello').innerHTML = htmlInput.value;
            // htmlInput

        });


        function getStudent(class_id) {

            let end_point = '/api/get/student/class?class_id=' + class_id;

            var request = new XMLHttpRequest();
            request.open("GET", end_point, true);
            request.setRequestHeader("Content-type", "application/json");
            request.send();
            request.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    let response = JSON.parse(this.responseText)
                        console.log(this.responseText,response)
                //     const myList = document.getElementById("students");
                //     myList.innerHTML = "";

                //     response.forEach(function(item, index, array) {
                //         console.log(item.student_mail)
                //         const newListItem = document.createElement("li");
                //         newListItem.textContent = item.student_mail;
                //         myList.appendChild(newListItem);
                //     });
                }
            };
        }
    </script> --}}
@endsection
