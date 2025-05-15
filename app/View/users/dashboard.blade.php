@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Welcome User</h1>
    <p>Ini adalah dashboard untuk user biasa.</p>
    <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
</div>
@endsection
