@extends('layouts.app')

@section('pageTitle', 'Space Access')

@section('content')
<div class="container d-block d-md-none">
  <div class="alert alert-info" role="alert">
    Scroll down for access bookings.
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-sm">
      <div class="card mb-3">
        <h3 class="card-header"><i class="fas fa-key" aria-hidden="true"></i> Door Codes</h3>
        <div class="card-body">
          <dl>
            <dt class="h5">Street Door</dt>
            <h4><span class="badge badge-primary">{{  Meta::get('access_street_door') }}</span></h4>
            <hr>
            <dt class="h5">Inner Door</dt>
            <h4><span class="badge badge-primary">{{  Meta::get('access_inner_door') }}</span></h4>
            @if ($roden = Meta::get('access_roden_street_door'))
            <hr>
            <dt class="h5">Roden Street Door</dt>
            <h4><span class="badge badge-primary">{{  $roden }}</span></h4>
            @endif
          </dl>
        </div>
      </div>
    </div>

    <div class="col-sm">
      <div class="card mb-3">
        <h3 class="card-header"><i class="fas fa-wifi" aria-hidden="true"></i> Member&apos;s WiFi</h3>
        <div class="card-body">
          <dl>
            <dt class="h5">SSID</dt>
            <h4><span class="badge badge-primary">{{  Meta::get('access_wifi_ssid') }}</span></h4>
            <hr>
            <dt class="h5">Password (PSK)</dt>
            <h4><span class="badge badge-primary">{{  Meta::get('access_wifi_password') }}</span></h4>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-sm">
      <div class="card mb-3">
        <h3 class="card-header"><i class="fas fa-wifi" aria-hidden="true"></i> Guest WiFi</h3>
        <div class="card-body">
          <dl>
            <dt class="h5">SSID</dt>
            <h4><span class="badge badge-primary">{{  Meta::get('access_guest_wifi_ssid') }}</span></h4>
            <hr>
            <dt class="h5">Password (PSK)</dt>
            <h4><span class="badge badge-primary">{{  Meta::get('access_guest_wifi_password') }}</span></h4>
          </dl>
        </div>
      </div>
    </div>
  </div>
</div>
@can('gatekeeper.temporaryAccess.view.self')
<br>
<br>
<div class="container">
  <p>Stay informed, find the latest information <a href="https://wiki.nottinghack.org.uk/wiki/Coronavirus_guidelines">on the wiki.</a></p>
</div>
@foreach($buildings as $building)
<div class="container">
  <h2 id="{{ Str::slug($building->getName()) }}">{{ $building->getName() }}</h2>
  <hr>
  @if ($building->getAccessState() == HMS\Entities\Gatekeeper\BuildingAccessState::FULL_OPEN)
  <p>
    {{ $building->getName() }} is currently <strong>{{ $building->getAccessStateString() }}</strong>, you are able to use the building 24/7.<br>
  </p>
  @elseif ($building->getAccessState() == HMS\Entities\Gatekeeper\BuildingAccessState::CLOSED)
  <p>
    {{ $building->getName() }} is currently <strong>{{ $building->getAccessStateString() }}</strong>, and access to members is not allowed.<br>
  </p>
  @endif
</div>
@if ($building->getAccessState() == HMS\Entities\Gatekeeper\BuildingAccessState::SELF_BOOK || $building->getAccessState() == HMS\Entities\Gatekeeper\BuildingAccessState::REQUESTED_BOOK)
<temporary-access
  :building='@json($building)'
  :bookable-areas='@json($building->getBookableAreas()->toArray())'
  :settings='@json($settings)'
  ></temporary-access>
  <br>
  <br>
@endif
@endforeach
@endcan
@endsection