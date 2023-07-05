@extends('web::character.layouts.view', ['viewname' => 'eligibility'])

@section('page_header', trans_choice('web::seat.character', 1) . ' Eligibility Check')
<style>
.charactersList {
    border-bottom: 1px solid #cccccc73;
    padding-bottom: 10px;
}
</style>

@section('character_content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Eligibility Check For {{$mainCharacter}}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2 charactersList">
                    <h4>Information Summery:</h4>
                    @if($meets3MonthKillRequirement)<span class="text-success"><i class="fas fa-check-circle"></i> Meets 3 Month Kill Requirement Of 40 Kills</span></span>@else<span class="text-danger"><i class="fas fa-times-circle"></i> Doesn't Meet 3 Month Kill Requirement Of 40 Kills</span></span>@endif
                    
                    <br>
                    <br>
                    @if($hasATitan)<span class="text-success"><i class="fas fa-check-circle"></i> Has A Titan</span>@else<span class="text-danger"><i class="fas fa-times-circle"></i> Doesn't Have A Titan</span>@endif
                    <br>
                    @if($hasASuper)<span class="text-success"><i class="fas fa-check-circle"></i> Has A Super</span>@else<span class="text-danger"><i class="fas fa-times-circle"></i> Doesn't Have A Super</span>@endif
                    <br>
                    @if($hasACarrier)<span class="text-success"><i class="fas fa-check-circle"></i> Has A Carrier</span>@else<span class="text-danger"><i class="fas fa-times-circle"></i> Doesn't Have A Carrier</span>@endif
                    <br>
                    @if($hasADread)<span class="text-success"><i class="fas fa-check-circle"></i> Has A Dread</span>@else<span class="text-danger"><i class="fas fa-times-circle"></i> Doesn't Have A Dread</span>@endif
                    <br>
                    @if($hasAFAX)<span class="text-success"><i class="fas fa-check-circle"></i> Has A FAX</span>@else<span class="text-danger"><i class="fas fa-times-circle"></i> Doesn't Have A FAX</span>@endif
                </div>
                <div class="col-md-8 mb-2 charactersList overflow-auto" style="max-height:70vh;">
                    @foreach($allAssetsWereLookingFor as $char)
                        <div class="col-md-12 mb-2 charactersList">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="https://imageserver.eveonline.com/Character/{{$char['character_id']}}_64.jpg" class="img-thumbnail" style="width: 64px; height: 64px;">
                                    {{ $char['name'] }}
                                </div>
                                <div class="col-md-6">
                                        <li>{{$char['totalKillsOver3Months']}} Kills Over Last 3 Months</li>
                                        @if($char['hasTitan'])<li><span class="badge badge-success">Owns a Titan</span></li>@endif
                                        @if($char['hasSuper'])<li><span class="badge badge-success">Owns a Super</span></li>@endif
                                        @if($char['hasDread'])<li><span class="badge badge-success">Owns a Dread</span></li>@endif
                                        @if($char['hasFAX'])<li><span class="badge badge-success">Owns a FAX</span></li>@endif
                                        @if($char['hasCarrier'])<li><span class="badge badge-success">Owns a Carrier</span></li>@endif
                                        <br>
                                        @if($char['canFlyTitan'])<li><span class="badge badge-success">Can fly a Titan</span></li>@endif
                                        @if($char['canFlySuper'])<li><span class="badge badge-success">Can fly a Super</span></li>@endif
                                        @if($char['canFlyDread'])<li><span class="badge badge-success">Can fly a Dread</span></li>@endif
                                        @if($char['canFlyFAX'])<li><span class="badge badge-success">Can fly a FAX</span></li>@endif
                                        @if($char['canFlyCarrier'])<li><span class="badge badge-success">Can fly a Carrier</span></li>@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

@stop