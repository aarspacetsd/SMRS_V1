@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Welcome Admin</h1>
    <p>Ini adalah dashboard untuk admin.</p>
    <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
</div>
@endsection
