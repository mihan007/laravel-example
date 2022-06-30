<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SyncText extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return <<<'blade'
            <span class="sync-text" wire:loading.delay>{{ empty((string)$slot) ? "синхронизация" : $slot }}</span>
        blade;
    }
}
