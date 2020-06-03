@extends('layouts.master')
@section('content')
<div class="col-md-12 well well-md">
    <h2>Accès interdit</h2>
    <div class="alert alert-danger">
        Vous n'avez pas l'autorisation d'accéder à cette page
    </div>
</div>
{!! Form::close() !!}
@stop