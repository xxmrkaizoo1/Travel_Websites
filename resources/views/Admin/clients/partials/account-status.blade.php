@if ($client->isSuspended())
    <span class="admin-status status-cancelled">Suspended</span>
@elseif ($client->email_verified_at === null)
    <span class="admin-status status-pending">Email not verified</span>
@else
    <span class="admin-status status-confirmed">Active</span>
@endif
