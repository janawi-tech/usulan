{{--
This component displays an image that acts as a clickable link.
When clicked, it opens the full-size image in a new tab.
--}}
<a href="{{ $getState() ? Illuminate\Support\Facades\Storage::url($getState()) : '#' }}" target="_blank">
    <img
        src="{{ $getState() ? Illuminate\Support\Facades\Storage::url($getState()) : '' }}"
        alt="Lampiran"
        style="max-height: 200px; border-radius: 0.5rem;" />
</a>