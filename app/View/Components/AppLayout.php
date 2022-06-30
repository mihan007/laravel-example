<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public $activeMenuItem;
    public $title;
    public $menuItems;
    public $company = null;
    public $menuVisible = null;

    public function __construct($menuActive, $title, $menuItems = null, $company = null, $menuVisible = null)
    {
        $this->activeMenuItem = $menuActive;
        $this->menuItems = $menuItems;
        $this->menuVisible = $menuVisible;
        $this->title = $title;
        $this->company = $company;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('layouts.app');
    }
}
