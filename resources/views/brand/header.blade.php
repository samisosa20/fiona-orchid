@push('head')
    <link
        href="/favicon.png"
        id="favicon"
        rel="icon"
    >
@endpush

<p class="h2 n-m font-thin v-center gap-3 header-logo justify-content-center">
    @if (Request::path() === "login")
        <img src={{ asset("img/photo.jpg") }} alt="Fiona App" width="100" height="100"/>
    @else
        <img src="/img/logo.svg" alt="Fiona App" width="75" height="75"/>
    @endif
</p>