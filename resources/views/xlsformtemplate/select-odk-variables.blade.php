@extends('backpack::layouts.top_left')

@section('content')

<div class=" row container">

<div class="row pl-4 pt-4 w-100">
<div class="col-12 col-xl-12 card">

<div class="card-header d-flex align-items-flex-end justify-content-between">
    <div>
        <h1><b>{{ $entry->title }}</b></h1>
        <h4>Select ODK Variables</h4>
    </div>
</div>


<div class="card-body">
<div class="container-fluid">

<form action="/admin/xlsform-template/{{ $entry->id }}/submitSelectedFields" method="POST">

@csrf

    <table class="table">

    <thead>
        <tr>
            <th scope="col">Selected?</th>
            <th scope="col">Type</th>
            <th scope="col">Name</th>
            <th scope="col">English Label</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($surveySheet as $row)
            <tr>
                <?php $valueArray = array_values($row); ?>
                <?php $checked = in_array($valueArray[1], $selectedFields) ? "checked" : ""; ?>
                <td><input type="checkbox" name="{{ $valueArray[1] }}" value="{{ $valueArray[1] }}" {{ $checked }} ></td>
                <td>{{ $valueArray[0] }}</td>
                <td><b>{{ $valueArray[1] }}</b></td>
                <td>{{ $valueArray[2] }}</td>
            </tr>
        @endforeach
    </tbody>

    </table>

    <input class="btn btn-primary" type="submit" value="Submit">
    <a href="/admin/xlsform-template" class="btn btn-default"><span class="la la-ban"></span> &nbsp;Cancel</a>
    
</form>

</div>
</div>

</div>
</div>

</div>

@endsection