@if($col)
    <div class="footer-col">
        <h3 class="footer-col__title"><span class="dot-anchor"></span>{{ $col->title }}</h3>
        <ul class="footer-col__list">
            @foreach($col->items as $item)
                <li><a href="{{ $item->href() }}"@if($item->new_tab) target="_blank" rel="noopener"@endif>{{ $item->label }}</a></li>
            @endforeach
        </ul>
    </div>
@endif
