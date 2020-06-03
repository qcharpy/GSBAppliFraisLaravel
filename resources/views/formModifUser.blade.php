@extends('layouts.master')
@section('content')
    {!! Form::open(['url' => 'modifUser']) !!}
    <div class="col-md-12 col-sm-12 well well-md">
        @if (session('error'))
            <div class="alert alert-danger">
                {{session('error')}}
            </div>
        @endif
        <h1>Modifier les informations de <?php echo $info->prenom; ?> <?php echo $info->nom; ?></h1>
        <br>
        <div class="form-horizontal">
            
            <div class="form-group">
                @if($info->aff_role === 'Visiteur')
                <label class="col-md-3 col-sm-3 control-label">Promouvoir en Délégué :</label>
                <div class="col-md-3 col-sm-3">
                
                    <input type="radio" name="role1" id="role1" value="oui">Oui
                    <input type="radio" name="role1" id="role1" value="non" checked>Non
                @endif
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 col-sm-3 control-label">Région d'affectation :</label>
                <div class="col-md-3 col-sm-3">
                    <select name="region" id="region">
                        @foreach ($lesRegion as $region)
                            @if($region->id === $info->aff_reg)
                                <option selected value="{{$region->id}}">{{$region->reg_nom}}</option>
                            @else
                                <option value="{{$region->id}}">{{$region->reg_nom}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <input type="hidden" name="saveReg" id="saveReg" value="{{$info->aff_reg}}">
            <input type="hidden" name="saveId" id="saveId" value="{{$info->id}}">
        </div>
        <div class="form-group">
            <div class="col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3">
                <button type="submit" class="btn btn-default btn-primary">
                    <span class="glyphicon glyphicon-ok"></span> Valider
                </button>
                &nbsp;
                <button type="button" class="btn btn-default btn-primary"
                        onclick="javascript: window.location = '{{ url('/saisirFraisForfait')}}';">
                    <span class="glyphicon glyphicon-remove"></span> Annuler</button>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop