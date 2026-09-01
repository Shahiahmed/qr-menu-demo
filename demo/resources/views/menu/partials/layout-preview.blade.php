{{-- Schematic layout thumbnail. "Photo" blocks use bg-accent so they recolour
     with the currently selected theme, matching the main project. --}}
@if ($variant === 'grid')
    <div class="grid h-full grid-cols-2 gap-1">
        @for ($i = 0; $i < 2; $i++)
            <div class="flex flex-col gap-1">
                <div class="h-6 rounded bg-accent"></div>
                <div class="h-1 w-3/4 rounded bg-border-strong"></div>
            </div>
        @endfor
    </div>
@elseif ($variant === 'compact')
    <div class="flex h-full flex-col justify-center gap-1.5">
        @for ($i = 0; $i < 4; $i++)
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 shrink-0 rounded-full bg-accent"></div>
                <div class="h-1 flex-1 rounded bg-border-strong"></div>
            </div>
        @endfor
    </div>
@else
    <div class="flex h-full flex-col justify-center gap-1.5">
        @for ($i = 0; $i < 2; $i++)
            <div class="flex items-center gap-1.5">
                <div class="h-5 w-5 shrink-0 rounded bg-accent"></div>
                <div class="flex flex-1 flex-col gap-1">
                    <div class="h-1 w-full rounded bg-border-strong"></div>
                    <div class="h-1 w-2/3 rounded bg-border"></div>
                </div>
            </div>
        @endfor
    </div>
@endif
