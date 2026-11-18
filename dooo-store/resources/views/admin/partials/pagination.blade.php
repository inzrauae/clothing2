@if ($paginator->hasPages() || $paginator->total() > 0)
<div class="pager">
  <span>Showing <b>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</b> of <b>{{ number_format($paginator->total()) }}</b></span>
  @if ($paginator->hasPages())
  <nav aria-label="Pagination">
    @if ($paginator->onFirstPage())
      <span class="pg dis" aria-hidden="true"><x-i n="left"/></span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><x-i n="left"/></a>
    @endif
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="pg dis">{{ $element }}</span>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="pg cur" aria-current="page">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><x-i n="right"/></a>
    @else
      <span class="pg dis" aria-hidden="true"><x-i n="right"/></span>
    @endif
  </nav>
  @endif
</div>
@endif
