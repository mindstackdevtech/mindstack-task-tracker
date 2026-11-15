{{-- Initials badge. Pass 'person' (a User or null) and optionally 'large' => true. --}}
@php
    $avatarName = $person?->name ?? 'Unknown';
    $avatarParts = array_values(array_filter(explode(' ', trim($avatarName))));
    $avatarInitials = mb_strtoupper(mb_substr($avatarParts[0] ?? '?', 0, 1));
    if (count($avatarParts) > 1) {
        $avatarInitials .= mb_strtoupper(mb_substr($avatarParts[count($avatarParts) - 1], 0, 1));
    }
    $avatarShade = ($person?->id ?? 0) % 3;
@endphp
<span @class(['avatar', 'avatar-'.$avatarShade, 'avatar-lg' => $large ?? false]) role="img" aria-label="{{ $avatarName }}" title="{{ $avatarName }}">{{ $avatarInitials }}</span>
