@props(['n', 'class' => ''])
<svg {{ $attributes->merge(['class' => trim('i '.$class)]) }} aria-hidden="true" focusable="false"><use href="#ic-{{ $n }}"/></svg>
