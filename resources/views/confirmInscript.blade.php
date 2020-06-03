@extends('layouts.master')
@section('content')
    <div class="col-md-12 well well-md">
        <h2>Inscription du nouveau visiteur réussi</h2>
        <div class="alert alert-success">
            Ses informations de connexion sont : <br>
            Login : {{$login}}<br>
            Mot de passe : {{$mdp}}<br>
        </div>
    </div>
@stop
