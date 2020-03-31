@canany(['profile.view.limited', 'profile.view.all'])
<div class="card">
  <div class="card-header">Tool Bookings</div>
  <booking-calendar-list
    classs="card-body"
    bookings-url="{{ route('api.tools.bookings.index', ['tool' => '_ID_']) }}"
    :initial-bookings="{{ json_encode($bookings) }}"
    :tool-ids="{{ json_encode($toolIds) }}"
    :remove-card-class="true"
    ></booking-calendar-list>
 {{--  <div class="card-footer">
    <a href="#" class="btn btn-primary" target="_blank"><i class="far fa-clock" aria-hidden="true"></i> Schedule an Induction</a>
  </div> --}}
</div>
<div class="card">
  <div class="card-header">Tool Access</div>
  <table class="table">
    <tbody>
      @foreach ($tools as $tool)
      <tr>
        <th scope="row">{{ $tool->getDisplayName() }}</th>
        <td>
          @if ($user->hasRoleByName('tools.' . $tool->getPermissionName() . '.user'))
          <span class="badge badge-pill badge-booking-normal">U</span>
          @endif
          @if ($user->hasRoleByName('tools.' . $tool->getPermissionName() . '.inductor'))
          <span class="badge badge-pill badge-booking-induction">I</span>
          @endif
          @if ($user->hasRoleByName('tools.' . $tool->getPermissionName() . '.maintainer'))
          <span class="badge badge-pill badge-booking-maintenance">M</span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="card-footer">
    <span class="badge badge-pill badge-booking-normal">User</span>
    <span class="badge badge-pill badge-booking-induction">Inductor</span>
    <span class="badge badge-pill badge-booking-maintenance">Maintainer</span>
  </div>
</div>
@endcanany
