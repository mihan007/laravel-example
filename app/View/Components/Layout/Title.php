<?php

namespace App\View\Components\Layout;

use Illuminate\View\Component;

class Title extends Component
{
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public $title;

    /**
     * Create a new component instance.
     *
     * @param null $title
     */
    public function __construct($title = null)
    {
        $this->title = $title ? $title . ' | ' . config('app.name') : config('app.name');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.layout.title');
    }
}
