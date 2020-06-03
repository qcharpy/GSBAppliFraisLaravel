@extends('layouts.master')
@section('content')
    {!! Form::open(['url' => 'addVisitor']) !!}
    <div class="col-md-12 col-sm-12 well well-md">
        @if (session('error'))
            <div class="alert alert-danger">
                {{session('error')}}
            </div>
        @endif
        <h1>Inscrire un nouveau visiteur</h1>
        <br>
        <div class="form-horizontal">
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Nom :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="text" name="name" id="name" class="form-control" maxlength="38" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Prénom :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="text" name="firstName" id="firstName" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">email :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="email" name="mail" id="mail" class="form-control" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Numéro téléphone :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="text" name="tel" id="tel" class="form-control" pattern="[0-9]{3,15}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Adresse :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="text" name="address" id="address" class="form-control" maxlength="30" pattern="[0-9]{1,3}\s[a-z\séèàêâùïüëA-Z-']{1,29}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Code Postal :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="text" name="cp" id="cp" class="form-control" size="5" pattern="[0-9]{5}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Ville :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="text" name="ville" id="ville" class="form-control" maxlength="30" pattern="^[a-zéèàêâùïüëA-Z][a-zéèàêâùïüëA-Z-'\s]{1,30}$" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Rôle :</label>
                <div class="col-md-3 col-sm-3">
                    <input type="radio" name="role" id="role" value="Visiteur">Visiteur
                    <input type="radio" name="role" id="role" value="Délégué">Délégué
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Région d'affectation :</label>
                <div class="col-md-3 col-sm-3">
                    <select name="region" id="region">
                        @foreach ($lesRegion as $region)
                            <option value="{{$region->id}}">{{$region->reg_nom}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3">
                <button type="submit" class="btn btn-default btn-primary">
                    <span class="glyphicon glyphicon-ok"></span> Valider
                </button>
                &nbsp;
                <button type="button" class="btn btn-default btn-primary"
                        onclick="javascript: window.location = '{{ url('/gestionUtilisateurs')}}';">
                    <span class="glyphicon glyphicon-remove"></span> Annuler</button>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop






