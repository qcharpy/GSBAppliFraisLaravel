@extends('layouts.master')
@section('content')
@if (Session:: get('id')=='0' || Session::get('id')== null)
<div>
    <h2 class="bvn">Bienvenue sur le site intranet du laboratoire GSB.</h2>
    <h3 class="bvn">Vous devez vous connecter pour accéder aux services de ce site et vous déconnecter à chaque fin de session. </h3>
</div>
@else
<div class="info_home">
    <h3>{{Session::get('nom')}} {{Session::get('prenom')}}</h3>
    <h3>Votre rôle: {{Session::get('aff_role')}}</h3>
    <h3>Votre secteur: {{Session::get('sec_nom')}}</h3>
    <h3>Votre région: {{Session::get('region')}}</h3>
</div>
@endif
@stop
