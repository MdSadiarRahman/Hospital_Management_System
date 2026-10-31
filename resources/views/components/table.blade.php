@props([
    'headers' => [],
])

<div class="hms-table-responsive">
    <table {{ $attributes->merge(['class' => 'hms-table']) }}>
        @if(!empty($headers))
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
