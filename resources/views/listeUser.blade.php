@extends('layouts.master')
@section('content')
<div class="container">
    <div>
        <h1>Gestion des Utilisateurs</h1>
        <div class="text-right">
            <button class="btn btn-primary" onclick="document.location.href='{{url('/ajoutVisiteur')}}';">Ajouter Visiteur</button>
        </div>
    </div>

    <div class="row">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Adresse</th>
                <th>Code Postal</th>
                <th>Ville</th>
                <th>Téléphone</th>
                <th>Adresse Mail</th>
                <th>Rôle</th>
                <th>Région</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($visiteurs as $visiteur)
                <tr>
                    <td>{{$visiteur->nom}}</td>
                    <td>{{$visiteur->prenom}}</td>
                    <td>{{$visiteur->adresse}}</td>
                    <td>{{$visiteur->cp}}</td>
                    <td>{{$visiteur->ville}}</td>
                    <td>{{$visiteur->tel}}</td>
                    <td>{{$visiteur->email}}</td>
                    <td>{{$visiteur->aff_role}}</td>
                    <td>{{$visiteur->reg_nom}}</td>
                    <td><button class="btn" onclick="document.location.href='{{url('/modifLesInfos')}}/{{$visiteur->id}}';"><span class="glyphicon glyphicon-pencil"></span></button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
@stop