<?php

namespace App\Support\Livewire;

trait WithFlash
{
    public function flashWarning($message)
    {
        flash()->warning($message);
        $this->emit('addFlash', flash()->getMessage());
    }

    public function flashSuccess($message)
    {
        flash()->success($message);
        $this->emit('addFlash', flash()->getMessage());
    }

    public function flashInfo($message)
    {
        flash()->info($message);
        $this->emit('addFlash', flash()->getMessage());
    }
}
